<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Solde;
use Illuminate\Http\Request;

class SoldeController extends SOLD_HELPER
{
    #VERIFIONS SI LE USER EST AUTHENTIFIE
    public function __construct()
    {
        $this->middleware(['auth:api', 'scope:api-access']);
        $this->middleware("CheckAdmin")->except([
            "RetrieveSold",
            "RetrieveUserSold",
            "RetrieveSold"
        ]);

        set_time_limit(0);
    }

    function CredidateSold(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR SOLD_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        $validator = $this->Sold_Validator($request->all());

        if ($validator->fails()) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR SOLD_HELPER
            return $this->sendError($validator->errors(), 404);
        }

        #ENREGISTREMENT DANS LA DB VIA **crediteSolde** DE LA CLASS BASE_HELPER HERITEE PAR SOLD_HELPER
        return $this->crediteSolde($request);
    }

    #GET ALL SOLDES
    function Soldes(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "GET") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR SOLD_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        return $this->allSoldes();
    }

    #RECUPERER UN SOLD
    function RetrieveSold(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "GET") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR SOLD_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #RECUPERATION D'UN USER VIA SON **id**
        return $this->retrieveSolde($id);
    }

    #RECUPERER UN USER SOLD
    function RetrieveUserSold(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "GET") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR SOLD_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #RECUPERATION D'UN USER VIA SON **id**
        return $this->retrieveUserSolde($id);
    }

    function RetrieveUserSoldManage()
    {
        set_time_limit(0);

        $Soldes = Solde::where(["owner" => 4])->get();

        $formatedSoldes = [];
        foreach ($Soldes as $solde) {
            if ($solde->id>=15692) {
                array_push($formatedSoldes,$solde);
            }
            # code...
        }

        $data = [
            // "soldes"=>$formatedSoldes,
            "count"=>count($formatedSoldes),
        ];

        return self::sendResponse($data, 'Solde abattoir revu!!');
    }
}
