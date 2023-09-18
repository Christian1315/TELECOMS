<?php

use App\Http\Controllers\Api\V1\SMS_HELPER;
use App\Mail\SendEmail;
use App\Models\Expeditor;
use App\Models\Right;
use App\Models\Solde;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

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
    $now = Custom_Timestamp();

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
            if ($num_time_rest > 0) { ###LE PREMIER ENVOIE
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


##======== CE HELPER PERMET DE VERIFIER SI LE USER DISPOSE D'UN COMPTE SUFFISANT OU PAS ==========## 
function Is_User_Account_Enough($userId, $sms_amount)
{
    $solde = Solde::where(['owner' => $userId, 'visible' => 1])->get();
    if (count($solde) == 0) {
        return false; ##IL NE DISPOSE MEME PAS DE COMPTE
    }

    $solde = $solde[0];
    if ($solde->solde > $sms_amount) {
        return true; #Son solde est suffisant! il peut envoyer d'sms
    }
    return false; #Son solde est insuffisant, il ne peut pas envoyer d'SMS
}

##======== CE HELPER PERMET DE DECREDITER LE SOLDE D'USER ==========## 
function Decredite_User_Account($userId, $sms_amount)
{
    $solde = Solde::where(['owner' => $userId, 'visible' => 1])->get();

    ##~~l'ancien solde
    $old_solde = $solde[0];
    $old_solde->visible = 0;
    $old_solde->save();

    ##~~le nouveau solde
    $new_solde = new Solde();
    $new_solde->solde = $old_solde->solde - $sms_amount; ##creditation du compte
    $new_solde->owner = $old_solde->owner;
    $new_solde->decredited_at = now();
    $new_solde->save();
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
