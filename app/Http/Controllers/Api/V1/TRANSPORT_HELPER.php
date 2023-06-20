<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\TransportResource;
use App\Models\Transport;
use App\Models\Type;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;

class TRANSPORT_HELPER extends BASE_HELPER
{
    ##======== REGISTER VALIDATION =======##
    static function transport_rules() : array {
        return [
            'user_id'=>'required',
            'type_id'=>'required|integer',
            'fabric_year'=>'required',
            'circulation_year'=>'required',
            'tech_visit'=>'required',
            'tech_visit_expire'=>'required',
            'gris_card'=>'required',
            'assurance_card'=>'required',
        ];
    }

    static function transport_messages() : array {
        return [
            'user_id.required'=>'Veuillez precisez l\'id du transporteur!',
            'type_id.required'=>'Veuillez précisez le type de moyen de transport que vous essayez d\'ajouter',
            'type_id.integer'=>'Ce champ requiert un entier',
            'fabric_year.required'=>'Veuillez precisez la date de fabrication!',
            'circulation_year.required'=>'Veuillez precisez la date de la mise en circulation!',
            'tech_visit.required'=>'envoyer une photo de la visite technique!',
            'tech_visit_expire.required'=>'Veuillez precisez la date d\'expiration de la visite technique!',
            'gris_card.required'=>'Veuillez envoyer une photo de la carte grise!',
            'assurance_card.required'=>'Veuillez envoyer une photo de la carte d\'assurance!',
        ];
    }

    static function Transport_Validator($formDatas){
        #
        $rules = self::transport_rules();
        $messages = self::transport_messages();

        $validator = Validator::make($formDatas,$rules,$messages);
        return $validator;
    }

    static function findTransport($id){
        $transport = Transport::with(['user','type'])->find($id);
        #QUAND L'ID NE CORRESPOND A AUCUN MOYEN DE TRANSPORT
        if(!$transport){
            return false;
        }
        #AUTREMENT
        return $transport;
        // return new TransportResource($transport);
    }

    static function createTransport($formData){
        // return $formData;
        $user = User::find($formData['user_id']);
        #QUAND L'ID NE CORRESPOND A AUCUN UTILISATEUR
        if(!$user){
            return self::sendError('Ce ID ne corresponds à aucun utilisateur n\'existe pas!',404);
        }

        $type = Type::find($formData['type_id']);
        // return $role->count()!==0;
        if (!$type) {
            return self::sendError('Ce type de moyen de transport n\' existe pas dans la DB!',404);
        }
        $t = Transport::create($formData);#ENREGISTREMENT DU USER DANS LA DB
        
        $transport = Transport::with(['user','type'])->find($t['id']);
        return self::sendResponse($transport,'Moyen de transport ajouté avec succès!!');
    }

    static function transports($id=null){
        #RECUPERATION DE TOUT LES MOYENS DE TRANSPORTS DANS LA DB
        if(!$id){
            $transports = Transport::with(['user','type'])->orderBy('id','desc')->get();
            return $transports;
        }

        $user = User::find($id);
        #QUAND L'ID NE CORRESPOND A AUCUN MOYEN DE TRANSPORT
        if(!$user){
            return self::sendError("Cet ID ne corresponds à aucun utilisateur!",404);
        }
        
        #RECUPERATION DE TOUT LES MOYENS DE TRANSPORTS DANS D'UN USER
        $transports = Transport::with(['user','type'])->where('user_id','=',$id)->orderBy('id','desc')->get();

        return self::sendResponse($transports,'Listes des moyens de transport récupérés avec succès!!');
    }

    static function validated_transports($id=null){
        #RECUPERATION DE TOUT LES MOYENS DE TRANSPORTS VALIDES DANS LA DB
        if(!$id){
            $transports = Transport::with(['user','type'])->where(['user_id'=>$id,'is_validated'=>true])->orderBy('id','desc')->get();
            return $transports;
        }

        $user = User::find($id);
        #QUAND L'ID NE CORRESPOND A AUCUN MOYEN DE TRANSPORT
        if(!$user){
            return self::sendError("Cet ID ne corresponds à aucun utilisateur!",404);
        }
        
        #RECUPERATION DE TOUT LES MOYENS DE TRANSPORTS VALIDES DANS D'UN USER
        $transports = Transport::with(['user','type'])->where(['user_id'=>$id,'is_validated'=>true])->orderBy('id','desc')->get();

        return self::sendResponse($transports,'Listes des moyens de transport récupérés avec succès!!');
    }

    static function updateTransport($transport,$formData){
        $transport->update($formData);
        $resul = Transport::find($transport->id);
        return self::sendResponse($resul,"Moyen de transport modifié avec succès!!");
    }

    static function deleteTransport($transport){
        if(!$transport){#QUAND **$transport** RETOURNE **FALSE**
            return self::sendError('Ce moyen de transport n\'existe pas!',404);
        };

        $transport->delete();#SUPPRESSION DU MOYEN DE TRANSPORT;
        return self::sendResponse($transport,"Ce moyen de transport a été supprimé avec succès!!");
    }
}
