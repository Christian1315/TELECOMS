<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Solde;
use Illuminate\Support\Facades\Validator;

class SOLD_HELPER extends BASE_HELPER
{

    ####CREDITATION DE SOLDE
    static function sold_rules(): array
    {
        return [
            'user_id' => ['required', "integer"],
            'solde_amount' => ['required', "integer"],
        ];
    }

    static function sold_messages(): array
    {
        return [
            'user_id.required' => 'Le user id est réquis!',
            'solde_amount.required' => 'Le solde amount est réquis!',

            'user_id.integer' => 'Le user id doit etre un entier!',
            'solde_amount.integer' => 'Le solde amount doit etre un entier!',

        ];
    }

    static function Sold_Validator($formDatas)
    {
        $rules = self::sold_rules();
        $messages = self::sold_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    static function crediteSolde($request)
    {
        $user = request()->user();
        $formData = $request->all();

        ##RECUPERATION DU SOLD EN QUESTION
        $solde = Solde::where(["owner" => $formData["user_id"], "visible" => 1])->get();
        if ($solde->count() == 0) {
            return self::sendError("Ce solde n'existe pas!", 404);
        }
        ####CREDITATION DU Solde

        ##~~l'ancien solde
        $old_solde = $solde[0];
        $old_solde->visible = 0;
        $old_solde->save();


        ##~~le nouveau solde
        $new_solde = new Solde();
        $new_solde->solde = $old_solde->solde + $formData["solde_amount"]; ##creditation du compte
        $new_solde->manager = $user->id;
        $new_solde->owner = $old_solde->owner;
        $new_solde->credited_at = now();
        $new_solde->save();

        $manager = $new_solde->manager_with_name->firstname;

        #===== ENVOIE D'SMS AU USER DU COMPTE =======~####

        $message = "Votre Solde a été crédité de " . $formData["solde_amount"] . " sur FRIK SMS par << " . $manager . " >>";
        $phone = $old_solde->owner_phone->phone;
        $email = $old_solde->owner_phone->email;
        $expediteur = env("EXPEDITEUR");

        SMS_HELPER::_sendSms(
            $phone,
            $message,
            $expediteur,
            false,
            $user,
        );

        #=====ENVOIE D'EMAIL =======~####
        Send_Email(
            $email,
            "Solde crédité sur FRIK-SMS",
            $message,
        );
        return self::sendResponse($new_solde, "Solde crédité de " . $formData["solde_amount"] . " avec succès!!");
    }

    static function retrieveSolde($id)
    {

        $Solde = Solde::with(["owner", "manager"])->where(["id" => $id, "visible" => 1])->get();

        if ($Solde->count() == 0) { #QUAND **$Solde** n'existe pas
            return self::sendError('Ce Solde n\'existe pas!', 404);
        };
        $Solde = $Solde[0];

        return self::sendResponse($Solde, 'Solde récupéré avec succès!!');
    }

    static function allSoldes()
    {

        $Soldes = Solde::with(["owner", "manager"])->latest()->get();
        return self::sendResponse($Soldes, 'Soldes récupérés avec succès!!');
    }
}
