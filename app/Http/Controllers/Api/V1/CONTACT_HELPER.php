<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Contact;
use App\Models\Groupe;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CONTACT_HELPER extends BASE_HELPER
{

    static function contact_rules(): array
    {
        return [
            'firstname' => ['required'],
            'lastname' => ['required'],
            'phone' => ['required', 'numeric', Rule::unique("contacts")],
            'detail' => ['required', 'max:200'],
        ];
    }

    static function contact_messages(): array
    {
        return [
            'phone.required' => 'Le champ phone est réquis!',
            'phone.numeric' => 'Le phone doit être un nombre entier',
            'phone.unique' => 'Ce contact existe déjà',
            'firstname.required' => 'Le champ firstname est réquis!',
            'lastname.required' => 'Le champ lastname est réquis!',
            'detail.required' => 'Le detail est réquis!',
            'detail.max' => 'Le detail ne doit pas depasser 150 caractères!',
        ];
    }

    static function Contact_Validator($formDatas)
    {
        $rules = self::contact_rules();
        $messages = self::contact_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    #================== ATTACHEMENT VALIDATION ================#
    static function attachement_rules(): array
    {
        return [
            'contact_id' => ['required'],
            'groupe_id' => ['required'],
        ];
    }

    static function attachement_messages(): array
    {
        return [
            'contact_id.required' => 'Le champ contact_id est réquis!',
            'groupe_id.required' => 'Le champ groupe_id est réquis!',
        ];
    }

    static function Attachement_Validator($formDatas)
    {
        $rules = self::attachement_rules();
        $messages = self::attachement_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }


    static function createContact($formData)
    {
        $contact = Contact::create($formData);
        $data = self::retrieveContact($contact->id, true);
        return self::sendResponse($data, 'Contact enregistré avec succès!!');
    }

    static function retrieveContact($id, $innerCall = false)
    {
        $contact = Contact::with(["groupes"])->where(['id' => $id, "visible" => 1])->get();
        if ($contact->count() == 0) {
            return self::sendError("Ce contact n'existe pas!!", 404);
        }
        #$innerCall: Cette variable determine si la function **retrieveContact** est appéle de l'intérieur
        if ($innerCall) {
            return $contact;
        }
        return self::sendResponse($contact, 'Contact récupré avec succès!!');
    }

    static function allContacts()
    {
        $contacts = Contact::with(["groupes"])->where(["visible" => 1])->latest()->get();
        return self::sendResponse($contacts, 'Contacts récupérés avec succès!!');
    }

    static function addContactToGroupe($formData)
    {
        $contact = Contact::where(["id" => $formData['contact_id'], "visible" => 1])->get();
        $groupe = Groupe::where(["id" => $formData['groupe_id'], "visible" => 1])->get();

        if ($contact->count() == 0) {
            return self::sendError("Ce contact n'existe pas!", 404);
        }

        if ($groupe->count() == 0) {
            return self::sendError("Ce groupe n'existe pas!", 404);
        }

        // ATTACHEMENT

        $contact->groupes()->attach($groupe);

        $data = self::retrieveContact($contact->id, true);
        return self::sendResponse($data, 'Contact ajouté au groupe avec succès!!');
    }

    static function _updateContact($formData, $id)
    {
        $contact = Contact::where(["id" => $id, "visible" => 1])->get();

        if ($contact->count() == 0) { #QUAND **$contact** n'existe pas
            return self::sendError('Ce contact n\'existe pas!', 404);
        };

        $contact->update($formData);
        return self::sendResponse($contact, "Contact modifié avec succès!!");
    }

    static function _deleteContact($id)
    {
        $contact = Contact::where(["id" => $id, "visible" => 1])->get();

        if ($contact->count() == 0) { #QUAND **$contact** n'existe pas
            return self::sendError('Ce contact n\'existe pas!', 404);
        };
        $contact = $contact[0];
        #SUPPRESSION DU CONTACT;
        $contact->visible = 0;
        $contact->deleted_at = now();
        $contact->save();
        return self::sendResponse($contact, "Ce contact a été supprimé avec succès!!");
    }
}
