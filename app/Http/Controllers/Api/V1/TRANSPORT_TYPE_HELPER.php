<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Type;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;

class TRANSPORT_TYPE_HELPER extends BASE_HELPER
{
    ##======== REGISTER VALIDATION =======##
    static function transportType_rules() : array {
        return [
            'name'=>'required',
            'image'=>'required',
        ];
    }

    static function transportType_messages() : array {
        return [
            'name.required'=>'Veuillez precisez le type!',
            'image.required'=>'Veuillez choisir une image qui illustre ce type de moyen de transport',
        ];
    }

    static function TransportType_Validator($formDatas){
        #
        $rules = self::transportType_rules();
        $messages = self::transportType_messages();

        $validator = Validator::make($formDatas,$rules,$messages);
        return $validator;
    }

    static function findType($id){
        $type = Type::with('transports')->find($id);
        #QUAND L'ID NE CORRESPOND A AUCUN TYPE DE MOYEN DE TRANSPORT
        if(!$type){
            return false;
        }
        #AUTREMENT
        return $type;
    }

    static function types(){
        #RECUPERATION DE TOUT LES TYPES DE MOYENS DE TRANSPORTSR
        $types = Type::with('transports')->orderBy('id','desc')->get();
        
        return self::sendResponse($types,'Listes des types moyens de transport récupérés avec succès!!');
    }


    static function createTransportType($formData){
        $type = Type::create($formData);#ENREGISTREMENT DU TYPE DE TRANSPORT DANS LA DB
        return self::sendResponse($type,'Type de Moyen de transport ajouté avec succès!!');
    }

    static function deleteTransportType($type){
        if(!$type){#QUAND **$type** RETOURNE **FALSE**
            return self::sendError('Ce Type moyen de transport n\'existe pas!',404);
        };

        $type->delete();#SUPPRESSION DU TYPE DE MOYEN DE TRANSPORT;
        return self::sendResponse($type,"Ce Type de moyen de transport a été supprimé avec succès!!");
    }

    static function updateTransporType($type,$formData){
        $type->update($formData);
        $resul = Type::find($type->id);
        return self::sendResponse($resul,"Type de Moyen de transport modifié avec succès!!");
    }

    static function searchTransportType($request) {
        $search = $request['search'];
        $result = collect(Type::with('transports')->get())->filter(function($type) use ($search)
        {
            return Str::contains(strtolower($type['name']),strtolower($search));
        })->all();

        return self::sendResponse($result,'Résultat de la recherche!');
    }
}
