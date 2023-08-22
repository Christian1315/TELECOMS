<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Campagne;
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
            "expeditor" => ['required', "integer"],
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
            'expeditor.required' => 'Le champ expeditor est réquis!',

            'group.numeric' => 'Le champ group doit être un nombre entier',
            'expeditor.numeric' => 'Le champ expeditor doit être un nombre entier',
        ];
    }

    static function Campagne_Validator($formDatas)
    {
        $rules = self::Campagne_rules();
        $messages = self::Campagne_messages();

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
        return self::sendResponse($Campagne, 'Campagne enregistré avec succès!!');
    }

    static function retrieveCampagne($id, $innerCall = false)
    {
        $Campagne = Campagne::with(["groupe"])->where('id', $id)->get();
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
        $Campagnes = Campagne::with(["groupe"])->latest()->get();
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

        $Campagne->delete(); #SUPPRESSION DU Campagne;
        return self::sendResponse($Campagne, "Ce Campagne a été supprimé avec succès!!");
    }
}
