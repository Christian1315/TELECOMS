<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class TransportController extends TRANSPORT_HELPER
{
    #VERIFIONS SI LE USER EST AUTHENTIFIE
    public function __construct()
    {
        $this->middleware(['auth:api','scope:api-access']);
    }
    
    #RECUPERATION DE TOUT LES MOYENS DE TRANSPORT
    public function ForAll(Request $request) {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(),"GET")==False){ 
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR USER_HELPER
            return $this->sendError("La methode ".$request->method()." n'est pas supportée pour cette requete!!",404);
        };

        $transports = $this->transports();#RETOURNE TOUT LES MOYENS DE TRANSPORTS
        return $this->sendResponse($transports,'Listes des moyens de transport récupérés avec succès!!');
    }

    #RECUPERATION DE TOUT LES MOYENS DE TRANSPORT D'UN USER
    public function ForUser(Request $request,$id) {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(),"GET")==False){ 
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR USER_HELPER
            return $this->sendError("La methode ".$request->method()." n'est pas supportée pour cette requete!!",404);
        };

        return $this->transports($id);#RETOURNE TOUT LES MOYENS DE TRANSPORTS D'UN USER'
    }

    #RECUPERATION DE TOUT LES MOYENS DE TRANSPORT VALIDES D'UN USER
    public function ValidatedForUser(Request $request,$id) {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(),"GET")==False){ 
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR USER_HELPER
            return $this->sendError("La methode ".$request->method()." n'est pas supportée pour cette requete!!",404);
        };

        return $this->validated_transports($id);#RETOURNE TOUT LES MOYENS DE TRANSPORTS D'UN USER'
    }

    #RECUPERATION D'UN MOYENS DE TRANSPORT
    public function Retrieve(Request $request,$id) {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(),"GET")==False){ 
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR USER_HELPER
            return $this->sendError("La methode ".$request->method()." n'est pas supportée pour cette requete!!",404);
        };

        $transport = $this->findTransport($id);#RETOURNE **FALSE** QUAND LE TRANSPORT N'EXISTE PAS & **$transport** QUAND CE DERNIER EXISTE;
        
        if(!$transport){#QUAND **$transport** RETOURNE **FALSE**
            return self::sendError('Ce moyen de transport n\'existe pas!',404);
        };

        return $this->sendResponse($transport,'Moyen de transport récupéré avec succès!!');
    }

    #CREATION D'UN MOYENS DE TRANSPORT
    public function Create(Request $request) {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(),"POST")==False){ 
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR TRANSPORT_HELPER
            return $this->sendError("La methode ".$request->method()." n'est pas supportée pour cette requete!!",404);
        };
        #VALIDATION DES DATAs DEPUIS LA CLASS TRANSPORT_HELPER
        $validator = $this->Transport_Validator($request->all());
       
        if ($validator->fails()) {
            #RENVOIE D'ERREURE VIA **sendResponse** DE LA CLASS BASE_HELPER HERITEE PAR TRANSPORT_HELPER
            return $this->sendError($validator->errors(),404);
        }
        
        #ENREGISTREMENT DANS LA DB VIA **createTransport** DE LA CLASS BASE_HELPER HERITEE PAR TRANSPORT_HELPER
        return $this->createTransport($request->all());
    }

    #MODIFICATION D'UN MOYENS DE TRANSPORT
    public function Update(Request $request,$id) {
        
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(),"PATCH")==False){ 
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR USER_HELPER
            return $this->sendError("La methode ".$request->method()." n'est pas supportée pour cette requete!!",404);
        };

        $transport = $this->findTransport($id);#RETOURNE **FALSE** QUAND LE TRANSPORT N'EXISTE PAS & **$transport** QUAND CE DERNIER EXISTE;

        if(!$transport){#QUAND **$transport** RETOURNE **FALSE**
            return self::sendError('Ce moyen de transport n\'existe pas!',404);
        };

        return $this->updateTransport($transport,$request->all());
    }

    #SUPPRESSION D'UN MOYENS DE TRANSPORT
    public function Delete(Request $request,$id) {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(),"DELETE")==False){ 
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR USER_HELPER
            return $this->sendError("La methode ".$request->method()." n'est pas supportée pour cette requete!!",404);
        };

        $transport = $this->findTransport($id);#RETOURNE **FALSE** QUAND LE TRANSPORT N'EXISTE PAS & **$transport** QUAND CE DERNIER EXISTE;

        if(!$transport){#QUAND **$transport** RETOURNE **FALSE**
            return self::sendError('Ce moyen de transport n\'existe pas!',404);
        };

        $transport->delete();#SUPPRESSION DU MOYEN DE TRANSPORT;
        return $this->sendResponse($transport,"Ce moyen de transport a été supprimé avec succès!!");
    }
}
