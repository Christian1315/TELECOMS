<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Campagne;
use App\Models\CampagneStatus;
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
            "group" => ['required', "integer"],
            "end_date" => ['required'],
            "num_time_by_day" => ['required', "integer"],
            // "Campagne" => ['required', "integer"],
            "message" => ['required'],
        ];
    }

    static function Campagne_messages(): array
    {
        return [
            'name.required' => 'Le champ name est réquis!',
            'group.required' => 'Le champ group est réquis!',
            'end_date.required' => 'Le champ end_date est réquis!',
            'num_time_by_day.required' => 'Le champ num_time_by_day est réquis!',
            // 'campagne.required' => 'Le champ Campagne est réquis!',

            'group.numeric' => 'Le champ group doit être un nombre entier',
            // 'campagne.numeric' => 'Le champ Campagne doit être un nombre entier',
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
            'status.integer' => 'Le status doit etre un entier',
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

        $groupe = Groupe::where("id", $formData["group"])->get();
        if ($groupe->count() == 0) {
            return self::sendError("Ce groupe n'existe pas!", 404);
        }

        $expeditor = Expeditor::where("id", $formData["expeditor"])->get();
        // return $expeditor;
        if ($expeditor->count() == 0) {
            return self::sendError("Ce expeditor n'existe pas!", 404);
        }

        if ($expeditor[0]->status != 3) {
            return self::sendError("Ce expéditeur n'est pas valide", 404);
        }


        $Campagne = Campagne::create($formData);
        $Campagne->status = 1;
        $Campagne->owner = request()->user()->id;
        $Campagne->save();

        return self::sendResponse($Campagne, 'Campagne enregistré avec succès!!');
    }

    static function retrieveCampagne($id, $innerCall = false)
    {
        $Campagne = Campagne::with(["groupe", "status"])->where(['id' => $id, "visible" => 1])->get();
        if ($Campagne->count() == 0) {
            return self::sendError("Ce Campagne n'existe pas!!", 404);
        }
        #$innerCall: Cette variable determine si la function **retrieveCampagne** est appéle de l'intérieur
        if ($innerCall) {
            return $Campagne;
        }
        return self::sendResponse($Campagne, 'Campagne récupré avec succès!!');
    }

    static function allCampagnes()
    {
        $Campagnes = Campagne::with(["groupe", "status"])->where("visible", 1)->latest()->get();
        return self::sendResponse($Campagnes, 'Campagnes récupérés avec succès!!');
    }

    static function _updateCampagne($formData, $id)
    {
        $Campagne = Campagne::find($id);
        if (!$Campagne) { #QUAND **$Campagne** n'esxiste pas
            return self::sendError('Ce Campagne n\'existe pas!', 404);
        };
        $Campagne->update($formData);
        return self::sendResponse($Campagne, "Campagne modifié avec succès!!");
    }

    static function _deleteCampagne($id)
    {
        $Campagne = Campagne::find($id);

        if (!$Campagne) { #QUAND **$Campagne** n'esxiste pas
            return self::sendError('Ce Campagne n\'existe pas!', 404);
        };

        #SUPPRESSION DU Campagne;
        $Campagne->visible = 0;
        $Campagne->deleted_at = now();
        $Campagne->save();
        return self::sendResponse($Campagne, "Ce Campagne a été supprimé avec succès!!");
    }

    static function _updateCampagneStatus($request, $id)
    {
        $Campagne = Campagne::find($id);
        if (!$Campagne) { #QUAND **$Campagne** n'existe pas
            return self::sendError('Cette Campagne n\'existe pas!', 404);
        };

        $CampagneSatatus = CampagneStatus::find($request->status);
        if (!$CampagneSatatus) { #QUAND **$Campagne status** n'existe pas
            return self::sendError('Ce status Campagne n\'existe pas!', 404);
        };

        $Campagne->status = $request->get("status");
        $Campagne->save();

        // $data = $Campagne->update(["status" => $request->get("status")]); #UPDATE DU STATUS De L'Campagne;
        return self::sendResponse($Campagne, "Le status de cette Campagne a été modifié avec succès!!");
    }
}
