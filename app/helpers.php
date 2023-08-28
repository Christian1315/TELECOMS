<?php

use App\Mail\SendEmail;
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
    $date = new DateTimeImmutable();
    $micro = (int)$date->format('Uu'); // Timestamp in microseconds
    return $micro;
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

function Login_To_Frik_SMS()
{
    $response = Http::post(env("SEND_SMS_API_URL") . "/api/v1/login", [
        "account" => "admin",
        "password" => "admin",
    ]);

    return $response;
}

function Send_SMS($phone, $message, $token)
{

    $response = Http::withHeaders([
        'Authorization' => "Bearer " . $token,
    ])->post(env("SEND_SMS_API_URL") . "/api/v1/sms/send", [
        "phone" => $phone,
        "message" => $message,
        "expediteur" => env("EXPEDITEUR"),
    ]);

    $response->getBody()->rewind();
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
