<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Contact;
use App\Models\ContactGroupe;
use App\Models\Groupe;
use Illuminate\Support\Facades\Validator;

class GROUPE_HELPER extends BASE_HELPER
{
    static function groupe_rules(): array
    {
        return [
            'name' => ['required'],
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

    static function createGroupe($request)
    {
        $formData = $request->all();

        #TRAITEMENT DU CHAMP **contacts** S'IL EST renseigné PAR LE USER
        if ($request->get("contacts")) {
            $contacts_ids = $formData["contacts"];
            // $contacts_ids = explode(",", $contacts);
            foreach ($contacts_ids as $id) {
                $contact = Contact::where(["id" => $id, "visible" => 1])->get();
                if ($contact->count() == 0) {
                    return self::sendError("Le contact d'id :" . $id . " n'existe pas!", 404);
                }
            }

            ####CREATION DU GROUPE
            $groupe = Groupe::create($formData);

            foreach ($contacts_ids as $id) {
                $contact = Contact::where(["id" => $id, "visible" => 1])->get();
                $contact = $contact[0];
                ##### AFFECTATION DU CONTACT AU GROUPE
                $contact->groupes()->attach($groupe);
            }
        } else {
            ####CREATION DU GROUPE
            $groupe = Groupe::create($formData);
        }

        $groupe->owner = request()->user()->id;
        $groupe->save();
        return self::sendResponse($groupe, 'Groupe crée avec succès!!');
    }

    static function retrieveGroupe($id)
    {
        $user = request()->user();
        if ($user->is_admin) { ###S'IL S'AGIT D'UN ADMIN
            ###il peut tout recuperer
            $groupe = Groupe::with(["contacts", "campagnes", "owner"])->where(["id" => $id])->get();
        } else {
            $groupe = Groupe::with(["contacts", "campagnes", "owner"])->where(["id" => $id, "visible" => 1, "owner" => $user->id])->get();
        }

        if ($groupe->count() == 0) { #QUAND **$groupe** n'existe pas
            return self::sendError('Ce groupe n\'existe pas!', 404);
        };
        $groupe = $groupe[0];

        return self::sendResponse($groupe, 'Groupe récupéré avec succès!!');
    }

    static function allGroupes()
    {
        $user = request()->user();
        if ($user->is_admin) { ###S'IL S'AGIT D'UN ADMIN
            ###il peut tout recuperer
            $Groupes = Groupe::with(["contacts", "campagnes", "owner"])->latest()->get();
        } else {
            $Groupes = Groupe::with(["contacts", "campagnes", "owner"])->where(["visible" => 1, "owner" => $user->id])->latest()->get();
        }
        return self::sendResponse($Groupes, 'Groupes récupérés avec succès!!');
    }

    static function _updateGroupe($request, $id)
    {
        $formData = $request->all();
        $user = request()->user();

        $groupe = Groupe::with(["contacts"])->where(["id" => $id, "visible" => 1, "owner" => $user->id])->latest()->get();

        if ($groupe->count() == 0) { #QUAND **$groupe** n'existe pas
            return self::sendError('Ce groupe n\'existe pas!', 404);
        };

        $groupe = $groupe[0];

        #TRAITEMENT DU CHAMP **contacts** S'IL EST renseigné PAR LE USER
        if ($request->get("contacts")) {
            $contacts_ids = $formData["contacts"];
            // $contacts_ids = explode(",", $contacts);
            foreach ($contacts_ids as $id) {
                $contact = Contact::where(["id" => $id, "visible" => 1])->get();
                if ($contact->count() == 0) {
                    return self::sendError("Le contact d'id :" . $id . " n'existe pas!", 404);
                }
                #======== AFFECTATION DU CONTACT AU GROUPE

                ####VERIFIONS SI CE ATTACHEMENT EXISTE DEJA
                $contact = $contact[0];

                $contactGroupe = ContactGroupe::where(["contact_id" => $contact->id, "groupe_id" => $groupe->id])->get();
                if ($contactGroupe->count() == 0) { #### SI L'ATTACHEMENT N'EXISTE PAS
                    // ATTACHEMENT
                    $contact->groupes()->attach($groupe);
                }
                ##AUTREMENT ON N'ATTACHE PAS
            }
        }

        $groupe->update($formData);
        return self::sendResponse($groupe, "Groupe modifié avec succès!!");
    }

    static function _deleteGroupe($id)
    {
        $user = request()->user();

        $groupe = Groupe::where(["id" => $id, "visible" => 1, "owner" => $user->id])->get();

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
