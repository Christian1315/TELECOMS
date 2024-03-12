<?php

use App\Http\Controllers\Api\V1\SMS_HELPER;
use App\Mail\SendEmail;
use App\Models\Expeditor;
use App\Models\Right;
use App\Models\SmsModel;
use App\Models\Solde;
use App\Models\User;
use App\Notifications\MailNotification;
use Illuminate\Support\Facades\Notification;

function userCount()
{
    return count(User::all()) + 1;
}

function Custom_Timestamp()
{
    $date = now();
    $timestamp = strtotime($date);
    return $timestamp;
}

function Get_Username($user, $type)
{
    $created_date = $user->created_at;

    $year = explode("-", $created_date)[0]; ##RECUPERATION DES TROIS PREMIERS LETTRES DU USERNAME
    $an = substr($year, -2);

    $username =  $type . $an . userCount();
    return $username;
}

##Ce Helper permet de creér le passCode de réinitialisation de mot de passe
function Get_passCode($user, $type)
{
    $created_date = $user->created_at;

    $year = explode("-", $created_date)[0]; ##RECUPERATION DES TROIS PREMIERS LETTRES DU USERNAME
    $an = substr($year, -2);
    $timestamp = substr(Custom_Timestamp(), -3);

    $passcode =  $timestamp . $type . $an . userCount();
    return $passcode;
}

##Ce Helper permet de creér le passCode de réinitialisation de mot de passe
function Get_compte_active_Code($user, $type)
{
    $created_date = $user->created_at;
    $year = explode("-", $created_date)[0]; ##RECUPERATION DES TROIS PREMIERS LETTRES DU USERNAME
    $an = substr($year, -2);
    $timestamp = substr(Custom_Timestamp(), -3);

    $passcode =  $timestamp . $type . $an . userCount();
    return $passcode;
}

##======== CE HELPER PERMET D'ENVOYER DES SMS VIA PHONE ==========## 

function Campagne_Initiation($campagne)
{
    $owner = User::find($campagne->owner);

    $start_time = strtotime($campagne->start_date);
    $end_time = strtotime($campagne->end_date);
    $now = Custom_Timestamp(); ##ON AJOUTE 1heure(en local) au timestamp pour que cela corresponde au timestamp actuel
    // return $start_time . " " . $now . " " . $end_time;

    ##_______VERIFIONS SI LA PERIODE DE LA CAMPAGNE EST PASSEE OU PAS_______
    if ($start_time < $now && $now < $end_time) {

        ##_____
        $previous_send_date_time = strtotime($campagne->previous_send_date); ##Time du premier envoie
        $sms_send_frequency_time = $campagne->sms_send_frequency * 3600; ##Frequence d'envoie en seconde
        $next_send_time =  $previous_send_date_time + $sms_send_frequency_time; ##Time du prochain envoie

        ##__verifions si **num_time_rest** permet de faire l'operation
        $num_time_rest = $campagne->num_time_rest;

        if ($campagne->previous_send_date != Null) { ###Après le premier envoie

            if (Custom_Timestamp() == $next_send_time || Custom_Timestamp() > $next_send_time) {
                if ($num_time_rest > 0) {
                    $expeditor = Expeditor::find($campagne->expeditor);
                    $contacts = $campagne->groupes[0]->contacts;

                    #### ENVOIE D'SMS
                    foreach ($contacts as $contact) {
                        SMS_HELPER::_sendSms(
                            $contact->phone,
                            $campagne->message,
                            $expeditor->name,
                            false,
                            $owner,
                        );
                    }

                    ###___DECREMENTONS LE **num_time_rest** (le nombre de fois restant pour cette campagne)
                    $campagne->num_time_rest = $num_time_rest - 1;

                    ###___NOTONS LA DATE DE CE ENVOIE D'SMS
                    $campagne->previous_send_date = now();
                    ##__
                    $campagne->save();
                }
            }
        } else {
            ##__
            if ($num_time_rest > 0) { ### LE PREMIER ENVOIE
                $expeditor = Expeditor::find($campagne->expeditor);
                $contacts = $campagne->groupes[0]->contacts;

                #### ENVOIE D'SMS
                foreach ($contacts as $contact) {
                    SMS_HELPER::_sendSms(
                        $contact->phone,
                        $campagne->message,
                        $expeditor->name,
                        false,
                        $owner,
                    );
                }

                ###___DECREMENTONS LE **num_time_rest** (le nombre de fois restant pour cette campagne)
                $campagne->num_time_rest = $num_time_rest - 1;

                ###___NOTONS QUE CETTE CAMPAGNE EST LANCEE
                $campagne->status = 2;

                ###___NOTONS LA DATE DE CE ENVOIE D'SMS
                $campagne->previous_send_date = now();
                ##__
                $campagne->save();
            }
        }
    } elseif ($now > $end_time) {
        ###___NOTONS QUE CETTE CAMPAGNE EST TERMINEE
        $campagne->status = 3;
        $campagne->save();
    }
}

function Send_Email($email, $subject, $message)
{
    $data = [
        "subject" => $subject,
        "message" => $message,
    ];
    Mail::to($email)->send(new SendEmail($data));
}

function Send_Notification($receiver, $subject, $message)
{
    $data = [
        "subject" => $subject,
        "message" => $message,
    ];

    Notification::send($receiver, new MailNotification($data));
}

function SMS_NUMBER($message)
{
    $NombreSms = 1; #PAR DEFAUT
    $msg_caracters_number = strlen($message);

    ##GESTION DE LA TAILLE DU MESSAGE
    $One_sms_caracter_limit = env("ONE_SMS_CARACTER_LIMIT");

    #SI LE NOMBRE DE CARACTERE DEPASSE LA LIMIT D'UN SMS
    if ($msg_caracters_number > $One_sms_caracter_limit) {
        #~~Cherchons le nombre de message correspondant aux caracteres en voyés par le USER
        $NombreSms = $msg_caracters_number / $One_sms_caracter_limit;
    }
    $int_part =  floor($NombreSms); #PARTIE ENTIERE DU NOMBRE DE MESSAGE
    $decimal_part =  $NombreSms - $int_part; #PARTIE DECIMALE DU NOMBRE DE MESSAGE
    if ($decimal_part > 0) { ##SI LE RESTE EST SUPERIEUR A 0,ON ARRONDIE A 1
        #~~~enfin retenons le nombre de message correponds aux nombres de caractères du user
        $NombreSms = $int_part + 1;
    }

    return $NombreSms;
}

##======== CE HELPER PERMET DE VERIFIER SI LE USER DISPOSE D'UN COMPTE SUFFISANT OU PAS ==========## 
function Is_User_Account_Enough($userId, $NombreSms)
{
    ####___________
    // $solde = Solde::where(['owner' => $userId])->whereRaw('visible > 0')->get();
    $solde = Solde::where(['owner' => $userId])->orderBy("id", "desc")->get();
    $solde = $solde[0];
    if ($solde->visible < 1) {
        return false;
    }
    if (!$solde) {
        return false; ##IL NE DISPOSE MEME PAS DE COMPTE
    }
    ###Il DISPOSE D'UN COMPTE
    // $solde = $solde[0];
    if ($solde->solde >= $NombreSms) {
        return true; #Son solde est suffisant! il peut envoyer d'sms
    }

    return false; #Son solde est insuffisant, il ne peut pas envoyer d'SMS
}

function CAMPAGNE_PERIOD($start_date, $end_date)
{
    $date1 = strtotime($start_date);
    $date2 = strtotime($end_date);

    $nbJoursTimestamp = $date2 - $date1;
    $nbJours = $nbJoursTimestamp / 86400;

    $int_part =  floor($nbJours); #PARTIE ENTIERE
    $decimal_part =  $nbJours - $int_part; #PARTIE DECIMALE
    if ($decimal_part > 0) { ##SI LE RESTE EST SUPERIEUR A 0,ON ARRONDIE A 1
        #~~~enfin retenons le nombre de jours correponds à la période de la campagne
        $nbJours = $int_part + 1;
    }

    return $nbJours;
}

##======== CE HELPER PERMET DE DECREDITER LE SOLDE D'USER ==========## 
function Decredite_User_Account($userId, $NombreSms)
{
    $solde = Solde::where(['owner' => $userId, 'visible' => 1])->get()->last();

    if ($solde) {
        ##~~l'ancien solde
        $old_solde = $solde;
        $old_solde->visible = 0;
        $old_solde->save();

        ##~~le nouveau solde
        $new_solde = new Solde();
        $new_solde->solde = $old_solde->solde - $NombreSms; ##creditation du compte
        $new_solde->owner = $old_solde->owner;
        if (request()->user()) {
            $new_solde->manager = request()->user()->id;
        }
        $new_solde->decredited_at = now();
        $new_solde->save();
    }
}

##======== CE HELPER PERMET DE VERIFIER SI LE USER EST UN ADMIN OU PAS ==========## 
function Is_User_AN_ADMIN($userId)
{ #
    $user = User::where(['id' => $userId, 'is_admin' => 1])->get();
    if (count($user) == 0) {
        return false;
    }
    return true; #il est un Admin
}

function Is_THIS_ADMIN_PPJJOEL()
{ #
    $user = request()->user();
    if ($user->id == 2) {
        return true; #il est PPJJOEL
    }
    return false; #il n'est pas PPJJOEL
}

function Is_THIS_ORION_ACCOUNT($USER)
{ #
    if (!$USER) {
        return false;
    }

    if ($USER->id == 4) {
        return true; #il est ORION
    }
    return false; #il n'est pas ORION
}


##======== CE HELPER PERMET DE RECUPERER LES DROITS D'UN UTILISATEUR ==========## 
function User_Rights($rangId, $profilId)
{ #
    $rights = Right::with(["action", "profil", "rang"])->where(["rang" => $rangId, "profil" => $profilId])->get();
    return $rights;
}

##======== CE HELPER PERMET DE RECUPERER TOUTS LES DROITS PAR DEFAUT ==========## 
function All_Rights()
{ #
    $allrights = Right::with(["action", "profil", "rang"])->get();
    return $allrights;
}

##======== CE HELPER PERMET DE RECUPERER LA FORMULE D'ENVOIE D'SMS ==========## 
function GET_ACTIVE_FORMULE()
{
    $formule = SmsModel::where(["active" => 1])->first();
    if (!$formule) {
        return null;
    }
    return $formule->name;
}
