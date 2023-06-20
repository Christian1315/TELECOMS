<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Frets;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class FRET_HELPER extends BASE_HELPER
{
    ##======== REGISTER VALIDATION =======##
    static function fret_rules() : array {
        return [
            'user_id'=>'required|integer',
            'name'=>'required',
            'nature'=>'required',
            'vol_or_quant'=>'required',
            'charg_date'=>'required|date',
            'charg_location'=>'required',
            'charg_destination'=>'required',
            'axles_num'=>'required|integer',
            'fret_img'=>'required',
        ];
    }

    static function fret_messages() : array {
        return [
            'user_id.required'=>'Veuillez precisez l\'id du transporteur!',
            'user_id.integer'=>'Ce Champ doit etre un entier!',
            'name.required'=>'Veuillez precisez le nom du fret',
            'nature.required'=>'Veuillez precisez la nature du fret!',
            'vol_or_quant.required'=>'Veuillez précisez le volume ou la quantité du fret!',
            'charg_date.required'=>'Veuillez précisez la date du chargement!',
            'charg_date.date'=>'Ce Champ doit etre une date!',
            'charg_location.required'=>'Veuillez précisez le lieu du chargement!',
            'charg_destination.required'=>'Veuillez précisez la destination du fret!',
            'axles_num.required'=>'Veuillez précisez le nombre d’essieux du fret!',
            'axles_num.integer'=>'Ce Champ doit etre un entier',
            'fret_img.required'=>'Veuillez choisir une image du fret!',
        ];
    }

    static function Fret_Validator($formDatas){
        #
        $rules = self::fret_rules();
        $messages = self::fret_messages();

        $validator = Validator::make($formDatas,$rules,$messages);
        return $validator;
    }

    static function createFret($formData){
        $user = User::find($formData['user_id']);
        #QUAND L'ID NE CORRESPOND A AUCUN UTILISATEUR
        if(!$user){
            return self::sendError('Ce ID ne corresponds à aucun utilisateur n\'existe pas!',404);
        }
        
        $fret = Frets::create($formData);#ENREGISTREMENT DU USER DANS LA DB
        return self::sendResponse($fret,'Fret ajouté avec succès!!');
    }

    static function findFret($id){
        $fret = Frets::find($id);
        #QUAND L'ID NE CORRESPOND A AUCUN FRET
        if(!$fret){
            return false;
        }
        #AUTREMENT
        return $fret;
        // return new TransportResource($fret);
    }

    static function frets($id=null){
        #RECUPERATION DE TOUT LES FRETs DANS LA DB
        if(!$id){
            $frets = Frets::with('user')->orderBy('id','desc')->get();
            return $frets;
        }

        $user = User::find($id);
        #QUAND L'ID NE CORRESPOND A AUCUN FRET
        if(!$user){
            return self::sendError("Cet ID ne corresponds à aucun utilisateur!",404);
        }
        
        #RECUPERATION DE TOUT LES FRETs DANS D'UN USER
        $frets = Frets::with('user')->where('user_id','=',$id)->orderBy('id','desc')->get();

        return self::sendResponse($frets,'Listes des frets récupérés avec succès!!');
    }


    static function validated_frets($id=null){
        #RECUPERATION DE TOUT LES FRETs VALIDES DANS LA DB
        if(!$id){
            $frets = Frets::with('user')->where(['user_id'=>$id,'is_validated'=>true])->orderBy('id','desc')->get();
            return $frets;
        }

        $user = User::find($id);
        #QUAND L'ID NE CORRESPOND A AUCUN FRET
        if(!$user){
            return self::sendError("Cet ID ne corresponds à aucun utilisateur!",404);
        }
        
        #RECUPERATION DE TOUT LES FRETs VALIDES DANS D'UN USER
        $frets = Frets::with('user')->where(['user_id'=>$id,'is_validated'=>true])->orderBy('id','desc')->get();

        return self::sendResponse($frets,'Listes des frets récupérés avec succès!!');
    }

    static function updateFret($fret,$formData){
        $fret->update($formData);
        $resul = Frets::find($fret->id);
        return self::sendResponse($resul,"Fret modifié avec succès!!");
    }
}
