<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DeveloperKey;
use App\Models\Expeditor;
use App\Models\Sms;
use App\Models\User;
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

    static function sms_rules(): array
    {
        return [
            'phone' => ['required', 'numeric'],
            'message' => ['required'],
            'expediteur' => ['required'],
        ];
    }

    static function sms_messages(): array
    {
        return [
            'phone.required' => 'Le champ phone est réquis!',
            'expediteur.required' => 'Le champ expediteur est réquis!',
            'phone.numeric' => 'Le phone doit être un nombre entier',
            'message.required' => 'Le champ message est réquis!',
            // 'message.max' => 'Le message ne doit pas depasser 300 caractères!',
        ];
    }

    static function Sms_Validator($formDatas)
    {
        $rules = self::sms_rules();
        $messages = self::sms_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    static function _retrieveDeveloperKey($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return self::sendError("Cet utilisateur n'existe pas", 505);
        }
        $Developer = DeveloperKey::with(["user"])->where(['owner' => $userId])->get();
        if ($Developer->count() == 0) {
            return self::sendError("Ce Developer n'existe pas!!", 404);
        }
        return self::sendResponse($Developer, 'Developer récuperé avec succès!!');
    }

    // ##BLOQUER UNE CLE API
    static function blocApiKey($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return self::sendError("Cet utilisateur n'existe pas", 505);
        }
        $Developer = DeveloperKey::with(["user"])->where(['owner' => $userId])->first();
        if (!$Developer) {
            return self::sendError("Ce Utilisateur ne dispose pas de clé API !!", 404);
        }

        // ###
        if (!$Developer->actif) {
            return self::sendError("Ce compte API est déjà bloqué!", 505);
        }

        // ###
        $Developer->actif = 0;
        $Developer->save();
        return self::sendResponse($Developer, 'Compte API bloqué avec succès!!');
    }

    // ###DEBLOQUER UNE CLE API
    static function deBlocApiKey($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return self::sendError("Cet utilisateur n'existe pas", 505);
        }
        $Developer = DeveloperKey::with(["user"])->where(['owner' => $userId])->first();
        if (!$Developer) {
            return self::sendError("Ce Utilisateur ne dispose pas de clé API !!", 404);
        }

        // ###
        if ($Developer->actif) {
            return self::sendError("Ce compte API est déjà débloqué!", 505);
        }

        // ###
        $Developer->actif = 1;
        $Developer->save();
        return self::sendResponse($Developer, 'Compte API débloqué avec succès!!');
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

    static function sendSms($request, $phone, $message)
    {
        ### VERIFIONS SI LA REQUETE CONTIENT la Clé API dans le header
        $api_key = $request->header()['api-key'][0];
        $user_id = $request->header()['id'][0];

        if ($api_key == '') {
            return self::sendError("La clé API est réquise dans le header", 505);
        }

        if ($user_id == '') {
            return self::sendError("Veuillez renseigner votre ID", 505);
        }

        ### VERIFIONS SI Clé API Existe
        $dev = DeveloperKey::where(["key" => $api_key])->first();
        if (!$dev) {
            return self::sendError("La clé API n'existe pas", 505);
        }

        ### VERIFIONS SI Clé API EST VALIDE(voyons si elle appartient au user en question)
        $dev = DeveloperKey::where(["key" => $api_key, "owner" => $user_id])->first();
        if (!$dev) {
            return self::sendError("La clé API ne vous appartient pas", 505);
        }

        ### VERIFIONS SI CETTE Clé API EST ACTIVE
        $dev = DeveloperKey::where(["key" => $api_key, "owner" => $user_id])->first();
        if (!$dev->actif) {
            return self::sendError("Votre compte API est désactivé!", 505);
        }

        ####==== VOYONS SI L'EXPEDITEUR  EXISTE=======###
        $expeditor = Expeditor::where(["name" => $request->get("expediteur")])->get();
        if ($expeditor->count() == 0) {
            return self::sendError("Ce expéditeur n'existe pas!", 404);
        }

        ####==== VOYONS SI L'EXPEDITEUR APPARTIENT AU USER EN QUESTION =======###
        $expeditor = Expeditor::where(["name" => $request->get("expediteur"), "owner" => $user_id])->get();
        if ($expeditor->count() == 0) {
            return self::sendError("Ce expéditeur ne vous appartient pas!", 404);
        }

        ##===== Verifions si l'expediteur est valide ou pas =========####
        if ($expeditor[0]->status != 3) {
            return self::sendError("Ce expéditeur existe, mais n'est pas validé!", 404);
        }

        if (GET_ACTIVE_FORMULE() == "kingsmspro") {
            if (strlen($message) > 1530) {
                return self::sendError("Echec d'envoie du message! Le message ne doit pas depasser 1530 caractères!", 505);
            }
        }

        $user = User::find($user_id);
        ###====== ENVOIE D'SMS ======

        return SMS_HELPER::_sendSms(
            $phone,
            $message,
            $request->get("expediteur"),
            false,
            $user
        );

        return self::sendResponse($user, 'Sms envoyé avec succès!!');
    }

    static function allSms($request)
    {
        ### VERIFIONS SI LA REQUETE CONTIENT la Clé API dans le header
        $api_key = $request->header()['api-key'][0];
        $user_id = $request->header()['id'][0];

        if ($api_key == '') {
            return self::sendError("La clé API est réquise dans le header", 505);
        }

        if ($user_id == '') {
            return self::sendError("Veuillez renseigner votre ID", 505);
        }

        // $user = User::find($user_id);

        ### VERIFIONS SI Clé API Existe
        $dev = DeveloperKey::where(["key" => $api_key])->get();
        if ($dev->count() == 0) {
            return self::sendError("La clé API n'existe pas", 505);
        }

        ### VERIFIONS SI Clé API EST VALIDE(voyons si elle appartient au user en question)
        $dev = DeveloperKey::where(["key" => $api_key, "owner" => $user_id])->get();
        if ($dev->count() == 0) {
            return self::sendError("La clé API ne vous appartient pas", 505);
        }

        ### VERIFIONS SI CETTE Clé API EST ACTIVE
        $dev = DeveloperKey::where(["key" => $api_key, "owner" => $user_id])->first();
        if (!$dev->actif) {
            return self::sendError("Votre compte API est désactivé!", 505);
        }

        $sms =  Sms::where(["owner" => $user_id])->orderBy("id", "desc")->get();
        return self::sendResponse($sms, 'Tout les sms récupérés avec succès!!');
    }

    static function retrieveSms($request, $id)
    {
        ### VERIFIONS SI LA REQUETE CONTIENT la Clé API dans le header
        $api_key = $request->header()['api-key'][0];
        $user_id = $request->header()['id'][0];

        ### VERIFIONS SI Clé API Existe
        $dev = DeveloperKey::where(["key" => $api_key])->get();
        if ($dev->count() == 0) {
            return self::sendError("La clé API n'existe pas", 505);
        }

        ### VERIFIONS SI Clé API EST VALIDE(voyons si elle appartient au user en question)
        $dev = DeveloperKey::where(["key" => $api_key, "owner" => $user_id])->get();
        if ($dev->count() == 0) {
            return self::sendError("La clé API ne vous appartient pas", 505);
        }

        ### VERIFIONS SI CETTE Clé API EST ACTIVE
        $dev = DeveloperKey::where(["key" => $api_key, "owner" => $user_id])->first();
        if (!$dev->actif) {
            return self::sendError("Votre compte API est désactivé!", 505);
        }

        $sms = Sms::where(["id" => $id, "owner" => $user_id])->get();
        if ($sms->count() == 0) {
            return self::sendError("Ce sms n'existe pas!", 404);
        }
        return self::sendResponse($sms, "Sms récupéré avec succès:!!");
    }
}
