<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class DifferedSmsController extends DIFERED_SMS_HELPER
{
    #VERIFIONS SI LE USER EST AUTHENTIFIE
    public function __construct()
    {
        $this->middleware(['auth:api', 'scope:api-access']);
    }

    function CreateDiferedSmsGroupe(Request $request)
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

        return $this->SendGroupeSms($request);
    }

    function CreateDiferedSmsContact(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR SMS_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #VALIDATION DES DATAs
        $validation = $this->Contact_Sms_Validator($request->all());
        if ($validation->fails()) {
            return $this->sendError($validation->errors(), 404);
        }

        return $this->SendContactSms($request);
    }
}
