<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Contact;
use App\Models\Groupe;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GROUPE_HELPER extends BASE_HELPER
{

    static function groupe_rules(): array
    {
        return [
            'name' => ['required', Rule::unique("groupes", "name")],
            'description' => ['required'],
        ];
    }

    static function groupe_messages(): array
    {
        return [
            'name.required' => 'Le champ name est réquis!',
            'name.unique' => 'Ce groupe existe déjà',
            'description.required' => 'Le champ description est réquis!',
        ];
    }

    static function Groupe_Validator($formDatas)
    {
        $rules = self::groupe_rules();
        $messages = self::groupe_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    static function createGroupe($formData)
    {
        $groupe = Groupe::create($formData);
        $data = self::retrieveGroupe($groupe->id, true);
        return self::sendResponse($data, 'Groupe crée avec succès!!');
    }

    static function retrieveGroupe($id, $innerCall = false)
    {
        $groupe = Groupe::where(["id" => $id, "visible" => 1])->get();

        if ($groupe->count() == 0) { #QUAND **$groupe** n'existe pas
            return self::sendError('Ce groupe n\'existe pas!', 404);
        };
        #$innerCall: Cette variable determine si la function **retrieveGroupe** est appéle de l'intérieur
        if ($innerCall) {
            return $groupe;
        }
        return self::sendResponse($groupe, 'Groupe récupéré avec succès!!');
    }

    static function allGroupes()
    {
        $Groupes = Groupe::with(["contacts"])->where(["visible" => 1])->latest()->get();
        return self::sendResponse($Groupes, 'Groupes récupérés avec succès!!');
    }

    static function _updateGroupe($formData, $id)
    {
        $groupe = Groupe::where(["id" => $id, "visible" => 1])->get();

        if ($groupe->count() == 0) { #QUAND **$groupe** n'existe pas
            return self::sendError('Ce groupe n\'existe pas!', 404);
        };
        $groupe->update($formData);
        return self::sendResponse($groupe, "Groupe modifié avec succès!!");
    }

    static function _deleteGroupe($id)
    {
        $groupe = Groupe::where(["id" => $id, "visible" => 1])->get();

        if ($groupe->count() == 0) { #QUAND **$groupe** n'existe pas
            return self::sendError('Ce groupe n\'existe pas!', 404);
        };
        $groupe = $groupe[0];
        #SUPPRESSION DU GROUPE;
        $groupe->visible = 0;
        $groupe->deleted_at = now();
        $groupe->save();
        return self::sendResponse($groupe, "Ce groupe a été supprimé avec succès!!");
    }
}
