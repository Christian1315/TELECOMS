<?php

namespace App\Http\Controllers\Api\V1;

use App\Imports\ContactsImport;
use App\Models\Contact;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ContactController extends CONTACT_HELPER
{
    #VERIFIONS SI LE USER EST AUTHENTIFIE
    public function __construct()
    {
        $this->middleware(['auth:api', 'scope:api-access']);
    }

    public function ContactCreate(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR CONTACT_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #VALIDATION DES DATAs
        $validation = $this->Contact_Validator($request->all());
        if ($validation->fails()) {
            return $this->sendError($validation->errors(), 404);
        }

        #ENREGISTREMENT DANS LA DB VIA **createContact** DE LA CLASS BASE_HELPER HERITEE PAR CONTACT_HELPER
        return $this->createContact($request->all());
    }

    public function ImportContacts(Request $request)
    {
        ### VEUILLEZ CHOISIR COMME UN EXEMPLAIE, LE FICHIER EXCEL **contacts.xlsx** QUI SE TROUVE DANS LA RACINE DU PROJET
        if (!$request->file('contacts')) {
            return $this->sendError("Veuillez charger le fichier excel!", 404);
        }
        $formdata = $request->file('contacts');

        $data = Excel::import(new ContactsImport, $formdata);

        $contacts = Contact::all();

        foreach ($contacts as $contact) {
            $contact_duplicates = Contact::where([
                "lastname" => $contact->lastname,
                "firstname" => $contact->firstname,
                "phone" => $contact->phone,
                "detail" => $contact->detail,
                "owner" => request()->user()->id,
            ])->get();

            if ($contact_duplicates->count() > 1) {
                foreach ($contact_duplicates as $key => $contact_duplicate) {
                    if ($key > 0) { ##On conserve le premier et on supprime les doublons
                        $contact_duplicate->delete();
                    }
                }
            }
        }
        return $this->sendResponse($data, "Contacts importés avec succès!!");
    }

    public function Contacts(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "GET") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR CONTACT_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #ENREGISTREMENT DANS LA DB VIA **allContacts** DE LA CLASS BASE_HELPER HERITEE PAR CONTACT_HELPER
        return $this->allContacts();
    }

    public function ContactRetrieve(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "GET") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR CONTACT_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #ENREGISTREMENT DANS LA DB VIA **retrieveContact** DE LA CLASS BASE_HELPER HERITEE PAR CONTACT_HELPER
        return $this->retrieveContact($id);
    }

    public function AttachContactToGroupe(Request $request)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR CONTACT_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        #VALIDATION DES DATAs
        $validation = $this->Attachement_Validator($request->all());
        if ($validation->fails()) {
            return $this->sendError($validation->errors(), 404);
        }

        return $this->addContactToGroupe($request->all());
    }

    public function UpdateContact(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "POST") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR CONTACT_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        // return $request;
        return $this->_updateContact($request->all(), $id);
    }

    public function DeleteContact(Request $request, $id)
    {
        #VERIFICATION DE LA METHOD
        if ($this->methodValidation($request->method(), "DELETE") == False) {
            #RENVOIE D'ERREURE VIA **sendError** DE LA CLASS BASE_HELPER HERITEE PAR CONTACT_HELPER
            return $this->sendError("La methode " . $request->method() . " n'est pas supportée pour cette requete!!", 404);
        };

        return $this->_deleteContact($id);
    }
}
