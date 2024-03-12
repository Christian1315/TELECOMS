<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Solde;
use App\Models\User;
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

        $user_to_credite = User::find($formData["user_id"]);
        if (!$user_to_credite) {
            return self::sendError("Ce utilisateur n'existe pas! Vous ne pouvez donc pas créditer son solde!", 404);
        }

        ##SI UN ADMIN VEUT CREDITER SON PROPRE COMPTE
        if ($formData["user_id"] == $user->id) {
            return self::sendError("Vous n'êtes pas autorisé.e à créditer votre solde!", 505);
        }

        ##SI LE SOLDE A CREDITER EST CELUI D'UN ADMIN
        if ($user_to_credite->is_admin) {
            ##Il faut que ça soit seul le compte PPJJOEL qui puisse crediter le solde d'un autre admin
            if (!Is_THIS_ADMIN_PPJJOEL()) {
                return self::sendError("Vous n'êtes pas autorisé à créditer ce solde!", 505);
            }
        }

        ###EMPECHER LE COMPTE PPJJOEL A CREDITER LE COMPTE D'UN SIMPLE USER
        if (!$user_to_credite->is_admin) {
            if (Is_THIS_ADMIN_PPJJOEL()) {
                return self::sendError("Le compte PPJJOEL n'est pas autorisé.e à créditer le solde d'un simple User!", 505);
            }
        }

        ###VERIFIONS SI L'ADMIN *admin* DISPOSE D'UN SOLDE SUFFISANT POUR CREDITER LE SOLD D'UN SIMPLE USER
        if (!Is_THIS_ADMIN_PPJJOEL()) {
            if (!Is_User_Account_Enough($user->id, $formData["solde_amount"])) { #IL NE DISPOSE PAS D'UN SOLDE SUFFISANT
                return self::sendError("Echec de Créditation du solde du user " . $formData["user_id"] . ". Votre solde est insuffisant! Veuillez contactez l'Admin PPJJOEL pour le recharger!", 505);
            }
        }

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
        
        $new_solde->old_sold = $old_solde->solde;
        $new_solde->added_sold = $formData["solde_amount"];
        
        $new_solde->credited_at = now();
        $new_solde->save();
        
        $manager = $new_solde->manager_with_name->firstname;
        
        ####DECREDITATION DU SOLDE DE L'ADMIN S'IL C'EST L'ADMIN 1 QUI CREDITE LE SOLDE D'UN USER
        if (!Is_THIS_ADMIN_PPJJOEL()) {
            Decredite_User_Account($user->id, $formData["solde_amount"]);
        }

        #===== ENVOIE D'SMS AU USER DU COMPTE =======~####
        $message = "Votre Solde a été crédité de " . $formData["solde_amount"] . " sur FRIK SMS par << " . $manager . " >>";
        $phone = $old_solde->owner_phone->phone;
        $email = $old_solde->owner_phone->email;
        $expediteur = env("EXPEDITEUR");

        try {
            // SMS_HELPER::_sendSms(
            //     $phone,
            //     $message,
            //     $expediteur,
            //     false,
            //     $user,
            // );

            Send_Notification(
                User::find($old_solde->owner),
                "SOLDE CREDITE SUR FRIK-SMS",
                $message
            );
        } catch (\Throwable $th) {
            //throw $th;
        }

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

    static function retrieveUserSolde($id)
    {
        $Solde = Solde::with(["owner", "manager"])->where(["owner" => $id])->get();

        return self::sendResponse($Solde, 'Solde récupéré avec succès!!');
    }
    
}
