<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Campagne;
use App\Models\CampagneGroupe;
use App\Models\Expeditor;
use App\Models\Groupe;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CAMPAGNE_HELPER extends BASE_HELPER
{

    static function Campagne_rules(): array
    {
        return [
            "name" => ['required', Rule::unique("campagnes")],
            "groupes" => ['required'],
            "message" => ['required'],

            'start_date' => ['required', "date"],
            'end_date' => ['required', "date"],
            'sms_send_frequency' => ['required', "integer"],
            'num_time_by_day' => ['required', "integer"],
        ];
    }

    static function Campagne_messages(): array
    {
        return [
            'name.required' => 'Le champ name est réquis!',
            'groupes.required' => 'Veuillez préciser les groupes concernés par cette campagne!',
            'num_time_by_day.required' => 'Veuillez préciser le nombre de fois que la Campagne se lancera par jour',

            'start_date.required' => 'Veuillez préciser la date du lancement de cette campagne',
            'start_date.date' => 'Veuillez choisir un format valide de date',

            'end_date.required' => 'Veuillez préciser la date de fin de cette campagne!',
            'end_date.date' => 'Veuillez choisir un format valide de date',

            'num_time_by_day.required' => 'Veuillez préciser le nombre de fois fois que le message sera envoyer par jour',
            'num_time_by_day.integer' => 'Le nombre de fois fois que le message sera envoyer par jour doit être un entier',
            'sms_send_frequency.required' => 'Veuillez préciser la fréquence d\'envoie/heure du message par jour',
            'sms_send_frequency.integer' => 'Le sms_send_frequency doit être un entier',
        ];
    }

    static function Campagne_Validator($formDatas)
    {
        $rules = self::Campagne_rules();
        $messages = self::Campagne_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    #####UPDATE D'UNE CAMPAGNE ##########
    static function UPDATE_Campagne_rules(): array
    {
        return [
            'status' => ['required', "integer"],
        ];
    }

    static function UPDATE_Campagne_messages(): array
    {
        return [
            'status.required' => 'Le status de la campagne est réquis!',
            'status.integer' => 'Le status doit être un entier',
        ];
    }

    static function UPDATE_Campagne_Validator($formDatas)
    {
        $rules = self::UPDATE_Campagne_rules();
        $messages = self::UPDATE_Campagne_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    static function createCampagne($formData)
    {
        $campagnes = Campagne::whereRaw('status != 3')->whereRaw("status != 4")->get();

        return $campagnes;


        ###_______
        $user = request()->user();
        $expeditor = Expeditor::where("id", $formData["expeditor"])->get();

        if ($expeditor->count() == 0) {
            return self::sendError("Ce expeditor n'existe pas!", 404);
        }
        if ($expeditor[0]->status != 3) {
            return self::sendError("Ce expéditeur n'est pas valide", 404);
        }

        $groupes_ids = $formData["groupes"];
        $groupes_ids = explode(",", $groupes_ids);

        ###_____VERIFIONS SI CES GROUPES EXISTENT D'ABORD
        foreach ($groupes_ids as $id) {
            $groupe = Groupe::where(["id" => $id, "owner" => $user->id, "visible" => 1])->get();
            if ($groupe->count() == 0) {
                return self::sendError("Le groupe d'id :" . $id . " n'existe pas!", 404);
            }
        }

        $Campagne = Campagne::create($formData);
        $Campagne->status = 1;
        $Campagne->num_time_rest = $Campagne->num_time_by_day;
        $Campagne->owner = $user->id;
        $Campagne->save();

        ###_____AFFECTATION DE LA CAMPAGNE AU GROUPE 
        foreach ($groupes_ids as $id) {
            $this_campagne_groupe = CampagneGroupe::where(["campagne_id" => $Campagne->id, "groupe_id" => $id])->get();
            #On verifie d'abord si ce attachement existait déjà 
            if ($this_campagne_groupe->count() == 0) {
                $groupe = Groupe::where(["id" => $id, "owner" => $user->id, "visible" => 1])->get();
                $groupe = $groupe[0];
                $groupe->campagnes()->attach($Campagne);
            }
        }

        return self::sendResponse($Campagne, 'Campagne enregistré avec succès!!');
    }

    static function retrieveCampagne($id)
    {
        $user = request()->user();
        if ($user->is_admin) { ###S'IL S'AGIT D'UN ADMIN
            ###il peut tout recuperer
            $Campagne = Campagne::with(["groupes", "status", "owner", "expeditor"])->where(['id' => $id])->get();
        } else {
            $Campagne = Campagne::with(["groupes", "status", "owner", "expeditor"])->where(['id' => $id, "visible" => 1, "owner" => $user->id])->get();
        }

        if ($Campagne->count() == 0) {
            return self::sendError("Cette Campagne n'existe pas!!", 404);
        }

        return self::sendResponse($Campagne, 'Campagne récupré avec succès!!');
    }

    static function allCampagnes()
    {
        $user = request()->user();
        if ($user->is_admin) { ###S'IL S'AGIT D'UN ADMIN
            ###il peut tout recuperer
            $Campagnes = Campagne::with(["groupes", "status", "owner", "expeditor"])->latest()->get();
        } else {
            $Campagnes = Campagne::with(["groupes", "status", "owner", "expeditor"])->where(["visible" => 1, "owner" => $user->id])->latest()->get();
        }
        return self::sendResponse($Campagnes, 'Campagnes récupérés avec succès!!');
    }

    static function initiateCampagne($id)
    {
        $user = request()->user();

        if ($user->is_admin) {
            $Campagne = Campagne::where(["id" => $id])->get();
        } else {
            $Campagne = Campagne::where(["id" => $id, "owner" => $user->id])->get();
        }

        if ($Campagne->count() == 0) {
            return self::sendError("Cette campagne n'existe pas!", 404);
        }

        $campagne = $Campagne[0];

        ###____VERIFIONS SI CETTE CAMPAGNE A DEJA ETE INITIEE
        if ($campagne->status == 2) {
            return self::sendError("Désolé! Cette campagne est déjà en cours", 505);
        }

        $campagne->status = 2;
        $campagne->save();

        return self::sendResponse($campagne, "Campagne initiée avec succès!");
    }

    static function _updateCampagne($request, $id)
    {
        $user = request()->user();
        $formData = $request->all();

        $Campagne = Campagne::where(["id" => $id, "visible" => 1, "owner" => $user->id])->get();

        if ($Campagne->count() == 0) { #QUAND **$Campagne** n'esxiste pas
            return self::sendError('Cette Campagne n\'existe pas!', 404);
        };
        $Campagne = $Campagne[0];

        ###____VERIFIONS SI CETTE CAMPAGNE A DEJA ETE INITIEE
        if ($Campagne->status == 2) {
            return self::sendError("Désolé! Cette campagne est déjà en cours! Vous ne pouvez pas la modifier!", 505);
        }

        ###____S'IL Y A CHANGEMENT DE STATUS
        if ($request->get("status")) {
            if (!is_numeric($request->get('status'))) {
                return self::sendError("Le status doit être un entier", 505);
            }
            $Campagne->status = $request->get("status");
            $Campagne->save();
        }


        $Campagne->update($formData);
        return self::sendResponse($Campagne, "Campagne modifié avec succès!!");
    }

    static function _deleteCampagne($id)
    {
        $user = request()->user();

        $Campagne = Campagne::where(["id" => $id, "visible" => 1, "owner" => $user->id])->get();

        if ($Campagne->count() == 0) { #QUAND **$Campagne** n'esxiste pas
            return self::sendError('Cette Campagne n\'existe pas!', 404);
        };
        $Campagne = $Campagne[0];

        ###____VERIFIONS SI CETTE CAMPAGNE A DEJA ETE INITIEE
        if ($Campagne->status == 2) {
            return self::sendError("Désolé! Cette campagne est déjà en cours! Vous ne pouvez pas la supprimer!", 505);
        }
        #SUPPRESSION DU Campagne;
        $Campagne->visible = 0;
        $Campagne->deleted_at = now();
        $Campagne->save();
        return self::sendResponse($Campagne, "Ce Campagne a été supprimé avec succès!!");
    }

    static function _stopCampagne($id)
    {
        $user = request()->user();
        if ($user->is_admin) {
            $Campagne = Campagne::where(["id" => $id])->get();
        } else {
            $Campagne = Campagne::where(["id" => $id, "visible" => 1, "owner" => $user->id])->get();
        }

        if ($Campagne->count() == 0) { #QUAND **$Campagne** n'esxiste pas
            return self::sendError('Cette Campagne n\'existe pas!', 404);
        };
        $Campagne = $Campagne[0];

        #STOPER UNE CAMPAGNE;
        $Campagne->status = 4;
        $Campagne->save();
        return self::sendResponse($Campagne, "Ce Campagne a été stopée avec succès!!");
    }
}
