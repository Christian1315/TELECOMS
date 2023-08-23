<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class DeveloperController extends DEVELOPER_HELPER
{
    #VERIFIONS SI LE USER EST AUTHENTIFIE
    public function __construct()
    {
        $this->middleware(['auth:api', 'scope:api-access']);
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

    // #GET ALL Developers
    // function DeveloperKeys(Request $request)
    // {
    //     #VERIFICATION DE LA METHOD
    //     if ($this->methodValidation($request->method(), "GET") == False) {
    //         #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR Developer_HELPER
    //         return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
    //     };

    //     return $this->allDeveloperKeys();
    // }

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

    function RegenerateDeveloperKey(Request $request,$id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS Developer_HELPER
            return $this->sendError("La méthode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        return $this->_updateDeveloperKey($id);
    }
}
