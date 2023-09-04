<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class CampagneController extends CAMPAGNE_HELPER
{
    #VERIFIONS SI LE USER EST AUTHENTIFIE
    public function __construct()
    {
        $this->middleware(['auth:api', 'scope:api-access']);
    }

    public function CampagneCreate(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR Campagne_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #VALIDATION DES DATAs
        $validation = $this->Campagne_Validator($request->all());
        if ($validation->fails()) {
            return $this->sendError($validation->errors(), 404);
        }

        #ENREGISTREMENT DANS LA DB VIA **createCampagne** DE LA CLASS BASE_HELPER HERITEE PAR Campagne_HELPER
        return $this->createCampagne($request->all());
    }

    public function Campagnes(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "GET") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR Campagne_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #ENREGISTREMENT DANS LA DB VIA **allCampagnes** DE LA CLASS BASE_HELPER HERITEE PAR Campagne_HELPER
        return $this->allCampagnes();
    }

    public function CampagneRetrieve(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "GET") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR Campagne_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #ENREGISTREMENT DANS LA DB VIA **retrieveCampagne** DE LA CLASS BASE_HELPER HERITEE PAR Campagne_HELPER
        return $this->retrieveCampagne($id);
    }

    public function _InitiateCampagne(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "GET") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR Campagne_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        return $this->initiateCampagne($id);
    }

    public function UpdateCampagne(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR Campagne_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        return $this->_updateCampagne($request->all(), $id);
    }

    function UpdateCampagneStatus(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS Expeditor_HELPER
            return $this->sendError("La méthode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #VALIDATION DES DATAs DEPUIS LA CLASS BASE_HELPER HERITEE PAR Expeditor_HELPER
        $validator = $this->UPDATE_Campagne_Validator($request->all());

        if ($validator->fails()) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR Expeditor_HELPER
            return $this->sendError($validator->errors(), 404);
        }

        return $this->_updateCampagneStatus($request, $id);
    }

    public function DeleteCampagne(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "DELETE") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR Campagne_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        return $this->_deleteCampagne($id);
    }
}
