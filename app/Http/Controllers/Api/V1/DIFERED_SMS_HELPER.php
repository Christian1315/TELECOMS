<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Contact;
use App\Models\DifferedSms;
use App\Models\Expeditor;
use App\Models\Groupe;

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
        $sms = DifferedSms::where(["owner" => $user->id])->orderBy("id", "desc")->get();

        return self::sendResponse($sms, "Messages differés récupérés avec succès!!");
    }
}
