<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class DeveloperController extends DEVELOPER_HELPER
{
    #VERIFIONS SI LE USER EST AUTHENTIFIE
    public function __construct()
    {
        $this->middleware(['auth:api', 'scope:api-access'])->except(["Send", "GetAllSms", "getSms"]);
    }

    function GenerateDeveloperKey(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR Developer_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        // #VALIDATION DES DATAs DEPUIS LA CLASS BASE_HELPER HERITEE PAR Developer_HELPER
        // $validator = $this->Developer_key_Validator($request->all());

        // if ($validator->fails()) {
        //     #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR Developer_HELPER
        //     return $this->sendError($validator->errors(), 404);
        // }

        #ENREGISTREMENT DANS LA DB VIA **_createDeveloperKey** DE LA CLASS BASE_HELPER HERITEE PAR Developer_HELPER
        return $this->_createDeveloperKey($request->all());
    }


    #GET A Developer
    function _RetrieveDeveloperKey(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "GET") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR Developer_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #RECUPERATION D'UN Developer
        return $this->_retrieveDeveloperKey($request, $id);
    }

    function DeleteDeveloperKey(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "DELETE") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS Developer_HELPER
            return $this->sendError("La méthode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        return $this->_deleteDeveloperKey($id);
    }

    function RegenerateDeveloperKey(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS Developer_HELPER
            return $this->sendError("La méthode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        return $this->_updateDeveloperKey($id);
    }

    #SEND AN SMS
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
        // $expediteur = $request->expediteur;

        #ENREGISTREMENT DANS LA DB VIA **sendSms** DE LA CLASS BASE_HELPER HERITEE PAR SMS_HELPER
        return $this->sendSms($request, $phone, $message);
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
        return $this->allSms($request);
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
        return $this->retrieveSms($request, $id);
    }
}
