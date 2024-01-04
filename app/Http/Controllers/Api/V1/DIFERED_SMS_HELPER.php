<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Contact;
use App\Models\DifferedSms;
use App\Models\Expeditor;
use App\Models\Groupe;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class DIFERED_SMS_HELPER extends BASE_HELPER
{
    ##======== REGISTER VALIDATION =======##

    ###_____GROUPES
    static function groupe_sms_rules(): array
    {
        return [
            'group' => ['required', 'integer'],
            'send_date' => ['required', 'date'],
            'message' => ['required'],
            'expediteur' => ['required'],
        ];
    }

    static function groupe_sms_messages(): array
    {
        return [
            'group.required' => 'Le champ groupe est réquis!',
            'send_date.required' => 'La date d\'envoie est réquise!',
            'send_date.date' => 'Le format est mal choisi!',
            'expediteur.required' => 'Le champ expediteur est réquis!',
            'message.required' => 'Le champ message est réquis!',
            'groupe.integer' => 'Le groupe doit être un entier',
        ];
    }

    static function Groupe_Sms_Validator($formDatas)
    {
        $rules = self::groupe_sms_rules();
        $messages = self::groupe_sms_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    ###_____CONTACTS

    static function contact_sms_rules(): array
    {
        return [
            'send_date' => ['required', 'date'],
            'contact' => ['required', 'integer'],
            'message' => ['required'],
            'expediteur' => ['required'],
        ];
    }

    static function contact_sms_messages(): array
    {
        return [
            'send_date.required' => 'La date d\'envoie est réquise!',
            'send_date.date' => 'Le format est mal choisi!',
            'contact.required' => 'Le champ contact est réquis!',
            'expediteur.required' => 'Le champ expediteur est réquis!',
            'message.required' => 'Le champ message est réquis!',
            'contact.integer' => 'Le contact doit être un entier',
        ];
    }

    static function Contact_Sms_Validator($formDatas)
    {
        $rules = self::contact_sms_rules();
        $messages = self::contact_sms_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    static function SendGroupeSms($request)
    {
        $formData = $request->all();

        $user = request()->user();
        $groupe = Groupe::where(["id" => $formData['group'], "owner" => $user->id])->get();
        if ($groupe->count() == 0) {
            return self::sendError("Ce groupe n'existe pas!!", 404);
        }

        $groupe = $groupe[0];
        $contacts =  $groupe->contacts;

        if ($contacts->count() == 0) {
            return self::sendError("Ce groupe ne contient aucun contact!!", 404);
        }

        ####==== TRAITEMENT DE L'EXPEDITEUR =======###
        $expeditor = Expeditor::where(["name" => $formData["expediteur"]])->get();
        if ($expeditor->count() == 0) {
            return self::sendError("Ce expéditeur n'existe pas!", 404);
        }

        ##===== Verifions si l'expediteur est valide ou pas =========####
        if ($expeditor[0]->status != 3) {
            return self::sendError("Ce expéditeur existe, mais n'est pas validé!", 404);
        }

        ####VERIFIONS S'IL DISPOSE D'UN SOLDE SUFFISANT POUR L'ENVOIE DE CE MESSAGE DIFFERE
        $NombreSms_by_contact = SMS_NUMBER($formData["message"]); ##NOMBRE D'SMS PAR CONTACT
        $total_sms_num = $contacts->count() * $NombreSms_by_contact; ##NOMBRE TOTAL D'SMS POUR TOUT LES CONTACTS DE CE GROUPE

        // if (!Is_User_AN_ADMIN($user->id)) {
        //     if (!Is_User_Account_Enough($user->id, $total_sms_num)) {
        //         return self::sendError("Vous ne disposez pas d'un solde suffisant pour effectuer ce envoie differe! Veuillez augmenter votre solde!", 505);
        //     };
        // }

        if (!Is_User_AN_ADMIN($user->id)) { ##S'IL S'AGIUT D'UN SIMPLE USER
            ###~~VERIFIONS SI LE SOLDE DU USER EST SUFFISANT
            if (!Is_User_Account_Enough($user->id, $total_sms_num)) { #IL NE DISPOSE PAS D'UN SOLDE SUFFISANT
                return self::sendError("Echec d'envoie d'SMS differé! Votre solde est insuffisant. Veuillez le recharger", 505);
            }
            #####DECREDITATION DE SON SOLDE
            Decredite_User_Account($user->id, $total_sms_num);
        } else { ## S'IL S'AGIT D'UN ADMIN
            ###~~VERIFIONS SI LE SOLDE DU COMPTE ADMIN **premier admin ID 1** EST SUFFISANT
            #### Pour les deux admins, on ne considère que le compte admin 1(le compte admin 2 PPJJJOEL ne dispose pas de compte)
            if (!Is_User_Account_Enough(1, $total_sms_num)) { #IL NE DISPOSE PAS D'UN SOLDE SUFFISANT
                return self::sendError("Echec d'envoie d'SMS differé! Le solde du compte admin 1 est insuffisant. Veuillez le recharger", 505);
            }

            #####DECREDITATION DE SON SOLDE
            Decredite_User_Account(1, $total_sms_num);
        }


        $diff_sms = DifferedSms::create($formData);
        $diff_sms->owner = $user->id;
        $diff_sms->save();

        return self::sendResponse($formData, "Message différé crée avec succès!");
    }

    static function SendContactSms($request)
    {
        $formData = $request->all();
        $user = request()->user();
        $contact = Contact::where(["id" => $formData['contact'], "owner" => $user->id])->get();
        if ($contact->count() == 0) {
            return self::sendError("Ce contact n'existe pas!!", 404);
        }

        ####==== TRAITEMENT DE L'EXPEDITEUR =======###
        $expeditor = Expeditor::where(["name" => $formData["expediteur"]])->get();
        if ($expeditor->count() == 0) {
            return self::sendError("Ce expéditeur n'existe pas!", 404);
        }

        ##===== Verifions si l'expediteur est valide ou pas =========####
        if ($expeditor[0]->status != 3) {
            return self::sendError("Ce expéditeur existe, mais n'est pas validé!", 404);
        }

        ####VERIFIONS S'IL DISPOSE D'UN SOLDE SUFFISANT POUR L'ENVOIE DE CE MESSAGE DIFFERE
        $NombreSms = SMS_NUMBER($formData["message"]); ##NOMBRE D'SMS PAR CONTACT
        // if (!Is_User_AN_ADMIN($user->id)) {
        //     if (!Is_User_Account_Enough($user->id, $NombreSms)) {
        //         return self::sendError("Vous ne disposez pas d'un solde suffisant pour effectuer ce envoie differe! Veuillez augmenter votre solde!", 505);
        //     };
        // }


        if (!Is_User_AN_ADMIN($user->id)) { ##S'IL S'AGIUT D'UN SIMPLE USER
            ###~~VERIFIONS SI LE SOLDE DU USER EST SUFFISANT
            if (!Is_User_Account_Enough($user->id, $NombreSms)) { #IL NE DISPOSE PAS D'UN SOLDE SUFFISANT
                return self::sendError("Echec d'envoie d'SMS différé! Votre solde est insuffisant. Veuillez le recharger", 505);
            }
            #####DECREDITATION DE SON SOLDE
            Decredite_User_Account($user->id, $NombreSms);
        } else { ## S'IL S'AGIT D'UN ADMIN
            ###~~VERIFIONS SI LE SOLDE DU COMPTE ADMIN **premier admin ID 1** EST SUFFISANT
            #### Pour les deux admins, on ne considère que le compte admin 1(le compte admin 2 PPJJJOEL ne dispose pas de compte)
            if (!Is_User_Account_Enough(1, $NombreSms)) { #IL NE DISPOSE PAS D'UN SOLDE SUFFISANT
                return self::sendError("Echec d'envoie d'SMS différé! Le solde du compte admin 1 est insuffisant. Veuillez le recharger", 505);
            }

            #####DECREDITATION DE SON SOLDE
            Decredite_User_Account(1, $NombreSms);
        }


        // $send_time = strtotime($formData["send_date"]);
        // $send_time = $formData["send_date"];

        // $now = now();

        // return $send_time."  &&&&&   ".$now;

        $contact = $contact[0];
        $contact =  $contact->phone;

        $diff_sms = DifferedSms::create($formData);
        $diff_sms->owner = $user->id;
        $diff_sms->save();

        return self::sendResponse($formData, "Message différé crée avec succès!");
    }

    static function allsms()
    {
        $user = request()->user();
        if (!Is_User_AN_ADMIN($user->id)) {
            $sms = DifferedSms::where(["owner" => $user->id])->orderBy("id", "desc")->get();
        } else {
            $sms = DifferedSms::orderBy("id", "desc")->get();
        }

        return self::sendResponse($sms, "Messages differés récupérés avec succès!!");
    }
}
