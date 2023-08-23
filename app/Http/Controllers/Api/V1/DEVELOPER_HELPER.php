<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DeveloperKey;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use Illuminate\Validation\Rule;

class DEVELOPER_HELPER extends BASE_HELPER
{

    static function DEVELOPER_key_rules(): array
    {
        return [
            'key' => ['required', Rule::unique("developers")],
        ];
    }

    static function Developer_key_messages(): array
    {
        return [
            'key.required' => 'Le key est réquis!',
        ];
    }

    static function Developer_key_Validator($formDatas)
    {
        $rules = self::Developer_key_rules();
        $messages = self::Developer_key_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    static function _createDeveloperKey()
    {
        ##VERIFIONS SI CE USER DISPOSE DEJA D'UNE CLE
        $dev = DeveloperKey::where(["owner" => request()->user()->id])->get();
        if ($dev->count() != 0) {
            return self::sendError("Vous disposez déjà d'une clé API", 505);
        }
        $Developer = new DeveloperKey();
        $Developer->key = Str::uuid();
        $Developer->owner = request()->user()->id;
        $Developer->save();
        return self::sendResponse($Developer, 'La clé API a été générée avec succès!!');
    }

    static function retrieveDeveloperKey($id, $innerCall = false)
    {
        $Developer = DeveloperKey::with(["user"])->where('id', $id)->get();
        if ($Developer->count() == 0) {
            return self::sendError("Ce Developer n'existe pas!!", 404);
        }
        #$innerCall: Cette variable determine si la function **retrieveDeveloper** est appéle de l'intérieur
        if ($innerCall) {
            return $Developer;
        }
        return self::sendResponse($Developer, 'Developer récuperé avec succès!!');
    }

    static function allDeveloperKeys()
    {
        $Developers = DeveloperKey::with(["user"])->orderBy("id", "desc")->get();
        return self::sendResponse($Developers, 'Developers récupérés avec succès!!');
    }

    static function _deleteDeveloperKey($id)
    {
        $Developer = DeveloperKey::find($id);

        if (!$Developer) { #QUAND **$Developer** n'existe pas
            return self::sendError('Ce Developer n\'existe pas!', 404);
        };

        $Developer->delete(); #SUPPRESSION De Developer;
        return self::sendResponse($Developer, "Ce Expediteur a été supprimé avec succès!!");
    }

    static function _updateDeveloperKey($id)
    {
        $Developer = DeveloperKey::where(["id" => $id, "owner" => request()->user()->id])->get();

        if ($Developer->count() == 0) {
            return self::sendError("Cette clé API n'existe pas", 404);
        }
        $Developer = $Developer[0];
        $Developer->key = Str::uuid();
        $Developer->save();
        return self::sendResponse($Developer, "La Clé API de ce développeur a été modifié avec succès!!");
    }
}
