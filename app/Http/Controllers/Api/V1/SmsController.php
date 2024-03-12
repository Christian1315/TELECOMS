<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Sms;
use Illuminate\Http\Request;

class SmsController extends SMS_HELPER
{
    #VERIFIONS SI LE USER EST AUTHENTIFIE
    public function __construct()
    {
        $this->middleware(['auth:api', 'scope:api-access'])->except(["_Send_Sms_From_Other_Plateforme"]);

        set_time_limit(0);
    }

    #SEND AN SMS UNITAIRE
    function SendViaOceanic(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR SMS_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #VALIDATION DES DATAs
        $validation = $this->Sms_Validator($request->all());
        if ($validation->fails()) {
            return $this->sendError($validation->errors(), 404);
        }

        $message = $request->message;
        $phone = $request->phone;
        $expediteur = $request->expediteur;

        #ENREGISTREMENT DANS LA DB VIA **_sendSms** DE LA CLASS BASE_HELPER HERITEE PAR SMS_HELPER
        return $this->SEND_BY_OCEANIC_HTTP($expediteur, $phone, $message);
    }

    #SEND AN SMS UNITAIRE
    function Send(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR SMS_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #VALIDATION DES DATAs
        $validation = $this->Sms_Validator($request->all());
        if ($validation->fails()) {
            return $this->sendError($validation->errors(), 404);
        }

        $message = $request->message;
        $phone = $request->phone;
        $expediteur = $request->expediteur;

        #ENREGISTREMENT DANS LA DB VIA **_sendSms** DE LA CLASS BASE_HELPER HERITEE PAR SMS_HELPER
        return $this->_sendSms($phone, $message, $expediteur);
    }

    #SEND AN SMS UNITAIRE VIA OTHER PLATEFORME
    function _Send_Sms_From_Other_Plateforme(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR SMS_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #VALIDATION DES DATAs
        $validation = $this->Sms_Validator($request->all());
        if ($validation->fails()) {
            return $this->sendError($validation->errors(), 404);
        }

        $message = $request->message;
        $phone = $request->phone;
        $expediteur = $request->expediteur;
        #ENREGISTREMENT DANS LA DB VIA **send_sms_from_other_plateforme** DE LA CLASS BASE_HELPER HERITEE PAR SMS_HELPER
        return $this->send_sms_from_other_plateforme($phone, $message, $expediteur);
    }

    #SEND AN GROUPE SMS
    function SmsGroupe(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR SMS_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #VALIDATION DES DATAs
        $validation = $this->Groupe_Sms_Validator($request->all());
        if ($validation->fails()) {
            return $this->sendError($validation->errors(), 404);
        }

        #ENREGISTREMENT DANS LA DB VIA **SendGroupeSms** DE LA CLASS BASE_HELPER HERITEE PAR SMS_HELPER
        return $this->SendGroupeSms($request->all());
    }

    #GET ALL SMS
    function GetAllSms(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "GET") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR SMS_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #RECUPERATION DE TOUT LES UTILISATEURS AVEC LEURS ROLES & TRANSPORTS
        return $this->allSms();
    }

    #RECUPERER UN SMS
    function getSms(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "GET") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR SMS_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #RECUPERATION D'UN SMS VIA SON **id**
        return $this->retrieveSms($id);
    }

    #SMS RAPPORTS
    function SmsRapports(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR SMS_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #VALIDATION DES DATAs
        $validation = $this->Sms_rapport_Validator($request->all());
        if ($validation->fails()) {
            return $this->sendError($validation->errors(), 404);
        }

        #RECUPERATION D'UN SMS VIA SON **id**
        return $this->smsReports($request->all());
    }

    function SmsUser(Request $request, $id)
    {
        $sms = Sms::where(["owner" => $id])->get();

        $data = [
            "smsAll" => $sms,
            "count" => count($sms),
        ];
        return $this->sendResponse($data, "Sms user recupéres avec succès!");
    }
}
