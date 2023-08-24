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
    static function BASE_URL()
    {
        $BASE_URL = "https://edok-api.kingsmspro.org/api/v1/sms";
        return $BASE_URL;
    }

    static function sms_rules(): array
    {
        return [
            'phone' => ['required', 'numeric'],
            'message' => ['required', 'max:300'],
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
            'message.max' => 'Le message ne doitb pas depasser 300 caractères!',
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
            'message' => ['required', 'max:300'],
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
            'message.max' => 'Le message ne doit pas depasser 300 caractères!',
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

    static function sendSms($phone, $message, $expediteur)
    {
        $BASE_URL = env("BASE_URL");
        $API_KEY = env("API_KEY");
        $CLIENT_ID = env("CLIENT_ID");

        ####==== TRAITEMENT DE L'EXPEDITEUR =======###
        $expeditor = Expeditor::where(["name" => $expediteur])->get();
        if ($expeditor->count() == 0) {
            return self::sendError("Ce expéditeur n'existe pas!", 404);
        }

        ##===== Verifions si l'expediteur est valide ou pas =========####
        if ($expeditor[0]->status != 3) {
            return self::sendError("Ce expéditeur existe, mais n'est pas validé!", 404);
        }

        $EXPEDITEUR = $expediteur;
        $DESTINATAIRE = $phone;
        $MESSAGE = $message;

        $url = $BASE_URL . "/send"; #URL D'ENVOIE DE L'SMS

        $smsData   = array(
            'from' => $EXPEDITEUR, //l'expediteur
            'to' => '' . $DESTINATAIRE . '', //destination au format international sans "+" ni "00". Ex: 22890443679
            'type' => 1, //type de message text et flash
            'message' => $MESSAGE, //le contenu de votre sms
            'dlr' => 's' // 1 pour un retour par contre 0
        );

        $response = Http::withHeaders([
            'APIKEY' => $API_KEY,
            'CLIENTID' => $CLIENT_ID
        ])->post($url, $smsData);

        $result = json_decode($response);
        if (!$result->status === "ACT") { #LE MESSAGE N'A PAS ETE ENVOYE
            return self::sendError("L'envoie a échoué", 505);
        }

        #ENREGISTREMENT DES INFOS DE L'SMS DANS LA DB

        $data = [
            "messageId" => $result->messageId,
            "from" => $result->from,
            "to" => $result->to,
            "message" => $result->message,
            "type" => $result->type,
            "route" => $result->route,
            "sms_count" => $result->sms_count,
            "amount" => $result->amount,
            "currency" => $result->currency,
            "status" => $result->status
        ];

        $sms = Sms::create($data);
        $sms->owner = request()->user()->id;
        $sms->save();

        return self::sendResponse($result, 'Sms envoyé avec succès!!');
    }

    static function smsReports($formData)
    {
        $BASE_URL = env("BASE_URL");
        $API_KEY = env("API_KEY");
        $CLIENT_ID = env("CLIENT_ID");

        $url = $BASE_URL . "/reports"; #URL DE RAPPORTS D'SMS

        $smsData   = array(
            'date_start' => $formData['date_start'],
            'date_end' => $formData['date_start'],
            // 'type' => 1
        );

        $response = Http::withHeaders([
            'APIKEY' => $API_KEY,
            'CLIENTID' => $CLIENT_ID
        ])->post($url, $smsData);

        // return $response;
        $result = json_decode($response);
        
        return self::sendResponse($result, 'Rapport recupéré avec succès!!');
    }

    function SendGroupeSms($formData)
    {
        $groupe = Groupe::with('contacts')->find($formData['groupe_id']);
        if (!$groupe) {
            return self::sendError("Ce groupe n'existe pas!!", 404);
        }

        $contacts =  $groupe->contacts;

        if ($contacts->count() == 0) {
            return self::sendError("Ce groupe ne contient aucun contact!!", 404);
        }
        $message = $formData['message'];
        $expediteur = $formData['expediteur'];


        foreach ($contacts as $contact) {
            $phone =  $contact->phone;
            // SENDING SMS
            self::sendSms($phone, $message, $expediteur);
        }
        return self::sendResponse($formData, "Message envoyé au groupe " . $groupe->name . " avec succès");
    }

    static function allSms()
    {
        $sms =  Sms::where(["owner" => request()->user()->id])->get();
        return self::sendResponse($sms, 'Tout les sms récupérés avec succès!!');
    }

    static function retrieveSms($id)
    {
        $sms = Sms::where(["id" => $id, "owner" => request()->user()->id])->get();
        if ($sms->count() == 0) {
            return self::sendError("Ce sms n'existe pas!", 404);
        }
        return self::sendResponse($sms, "Sms récupéré avec succès:!!");
    }
}
