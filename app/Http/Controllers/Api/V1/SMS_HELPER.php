<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Expeditor;
use App\Models\Groupe;
use App\Models\Sms;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class SMS_HELPER extends BASE_HELPER
{
    ##======== REGISTER VALIDATION =======##

    static function sms_rules(): array
    {
        return [
            'phone' => ['required', 'numeric'],
            'message' => ['required'],
            'expediteur' => ['required'],
        ];
    }

    static function sms_messages(): array
    {
        return [
            'phone.required' => 'Le champ phone est réquis!',
            'expediteur.required' => 'Le champ expediteur est réquis!',
            'phone.numeric' => 'Le phone doit être un nombre entier',
            'message.required' => 'Le champ message est réquis!',
        ];
    }

    static function Sms_Validator($formDatas)
    {
        $rules = self::sms_rules();
        $messages = self::sms_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    static function groupe_sms_rules(): array
    {
        return [
            'groupe_id' => ['required', 'numeric'],
            'message' => ['required'],
            'expediteur' => ['required'],
        ];
    }

    static function groupe_sms_messages(): array
    {
        return [
            'groupe_id.required' => 'Le champ groupe_id est réquis!',
            'expediteur.required' => 'Le champ expediteur est réquis!',
            'groupe_id.numeric' => 'Le groupe_id doit être un nombre entier',
            'message.required' => 'Le champ message est réquis!',
        ];
    }

    static function Groupe_Sms_Validator($formDatas)
    {
        $rules = self::groupe_sms_rules();
        $messages = self::groupe_sms_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    static function sms_rapports_rules(): array
    {
        return [
            'date_start' => ['required'],
            'date_end' => ['required'],
        ];
    }

    static function sms_rapports_messages(): array
    {
        return [
            'date_start.required' => 'Le champ date_start est réquis!',
            'date_end.required' => 'Le champ date_end est réquis!',
        ];
    }

    static function Sms_rapport_Validator($formDatas)
    {
        $rules = self::sms_rapports_rules();
        $messages = self::sms_rapports_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    function send_sms_via_ocean_post($expediteur, $phone, $message)
    {
        $url = env("OCEANIC_BASE_URL");
        $postdata = array(
            'user' => env("OCEANIC_USER"),
            'password' => env("OCEANIC_PASSWORD"),
            'from' => $expediteur,
            'to' => $phone,
            'text' => $message,
            'api' => env("OCEANIC_API"),
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        $reponse = curl_exec($curl);
        curl_close($curl);

        if ($reponse == "ERR: NO USER FOUND") {
            return self::sendError("Echec d'envoie", 505);
        } else {
            return self::sendResponse($reponse, "Message envoyé avec succès!");
        }
    }

    public static function SEND_BY_OCEANIC_HTTP($from, $to, $message)
    {
        ###ENVOIE DE L'SMS VIA L'API DU FOURNISSEUR
        $user = env("OCEANIC_USER");
        $password = env("OCEANIC_PASSWORD");
        $url = env("OCEANIC_BASE_URL") . "?user=" . $user . "&password=" . $password . "&from=" . $from . "&to=" . $to . "&text=" . $message;

        $response = Http::get($url);

        return $response;
    }

    static function _sendSms($phone, $message, $expediteur, $out_call = false, $user = null)
    {
        // $BASE_URL = env("BASE_URL");
        // $API_KEY = env("API_KEY");
        // $CLIENT_ID = env("CLIENT_ID");

        ####==== TRAITEMENT DE L'EXPEDITEUR =======###
        $expeditor = Expeditor::where(["name" => $expediteur])->get();
        if ($expeditor->count() == 0) {
            return self::sendError("Ce expéditeur n'existe pas!", 404);
        }

        #SI L'OPERATION NE PRECISE PAS LE USER, ON PRENDS CELUI QUI EST CONNECTE PAR DEFAUT
        if (!$user) {
            $user = request()->user();
        }

        $userId =  $user->id;

        if ($expeditor[0]->owner != $userId) {
            return self::sendError("Désolé! Ce expéditeur ne vous appartient pas!", 505);
        }

        ##===== Verifions si l'expediteur est valide ou pas =========####
        if ($expeditor[0]->status != 3) {
            return self::sendError("Ce expéditeur existe, mais n'est pas validé!", 404);
        }

        // $user_is_admin = $user->is_admin;

        $EXPEDITEUR = $expediteur;
        $DESTINATAIRE = $phone;
        $MESSAGE = $message;

        // $url = $BASE_URL . "/send"; #URL D'ENVOIE DE L'SMS

        // $smsData   = array(
        //     'from' => $EXPEDITEUR, //l'expediteur
        //     'to' => '' . $DESTINATAIRE . '', //destination au format international sans "+" ni "00". Ex: 22890443679
        //     'type' => 1, //type de message text et flash
        //     'message' => $MESSAGE, //le contenu de votre sms
        //     'dlr' => 's' // 1 pour un retour par contre 0
        // );

        $NombreSms = SMS_NUMBER($MESSAGE); ##NOMBRE D'SMS PAR MESSAGE

        if (!Is_User_AN_ADMIN($userId)) {

            ###~~VERIFIONS SI LE SOLDE DU USER EST SUFFISANT
            if (!Is_User_Account_Enough($userId, $NombreSms)) { #IL NE DISPOSE PAS D'UN SOLDE SUFFISANT
                return self::sendError("Echec d'envoie d'SMS! Votre solde est insuffisant. Veuillez le recharger", 505);
            }
            #####DECREDITATION DE SON SOLDE
            Decredite_User_Account(request()->user()->id, $NombreSms);
        }

        ###ENVOIE DE L'SMS VIA L'API DU FOURNISSEUR

        $response = self::SEND_BY_OCEANIC_HTTP(
            $EXPEDITEUR,
            $DESTINATAIRE,
            $MESSAGE
        );

        // $response = Http::withHeaders([
        //     'APIKEY' => $API_KEY,
        //     'CLIENTID' => $CLIENT_ID
        // ])->post($url, $smsData);
        // $result = json_decode($response);

        if ($response == "ERR: NO USER FOUND") { ###ECHEC D'ENVOIS D'SMS
            return False;
        }

        ##RECUPERATION DU MESSAGE ID
        $data = explode("ID: ", $response);
        $data2 = explode(" To: ", $data[1]);
        $messageId = $data2[0];

        $sms_amount = env("COST_OF_ONE_SMS") * $NombreSms;
        #ENREGISTREMENT DES INFOS DE L'SMS DANS LA DB
        $data = [
            "messageId" => $messageId,
            "from" => $EXPEDITEUR,
            "to" => $DESTINATAIRE,
            "message" => $MESSAGE,
            // "type" => $result->type,
            // "route" => $result->route,
            "sms_count" => $NombreSms,
            "amount" => $sms_amount,
            // "currency" => $result->currency,
            "sms_num" => $NombreSms,
        ];

        $sms = Sms::create($data);
        $sms->owner = $userId;
        $sms->status = 1;
        $sms->save();

        if (!$out_call) {
            return self::sendResponse($sms, 'Sms envoyé avec succès!!');
        }
    }

    static function smsReports($formData)
    {
        $date_start = $formData['date_start'];
        $date_end = $formData['date_end'];

        $user = request()->user();
        $response =  Sms::with(["status"])->where(["owner" => $user->id])->whereBetween('created_at', [$date_start, $date_end])->get();

        return self::sendResponse($response, 'Rapport recupéré avec succès!!');
    }

    static function SendGroupeSms($formData)
    {
        $groupe = Groupe::with(['contacts'])->where(["id" => $formData['groupe_id'], "owner" => request()->user()->id])->get();
        if ($groupe->count() == 0) {
            return self::sendError("Ce groupe n'existe pas!!", 404);
        }

        $groupe = $groupe[0];
        $contacts =  $groupe->contacts;

        if ($contacts->count() == 0) {
            return self::sendError("Ce groupe ne contient aucun contact!!", 404);
        }
        $message = $formData['message'];
        $expediteur = $formData['expediteur'];


        foreach ($contacts as $contact) {
            self::_sendSms(
                $contact->phone,
                $message,
                $expediteur,
                true
            );
        }
        return self::sendResponse($formData, "Message envoyé au groupe " . $groupe->name . " avec succès");
    }

    static function allSms()
    {
        $user = request()->user();
        if ($user->is_admin) { ###S'IL S'AGIT D'UN ADMIN
            ###il peut tout recuperer
            $sms =  Sms::with(["status"])->get();
        } else {
            $sms =  Sms::where(["owner" => $user->id])->get();
        }
        return self::sendResponse($sms, 'Tout les sms récupérés avec succès!!');
    }

    static function retrieveSms($id)
    {
        $user = request()->user();
        if ($user->is_admin) { ###S'IL S'AGIT D'UN ADMIN
            ###il peut tout recuperer
            $sms = Sms::with(["status"])->where(["id" => $id])->get();
        } else {
            $sms = Sms::with(["status"])->where(["id" => $id, "owner" => $user->id])->get();
        }
        if ($sms->count() == 0) {
            return self::sendError("Ce sms n'existe pas!", 404);
        }
        return self::sendResponse($sms, "Sms récupéré avec succès:!!");
    }
}
