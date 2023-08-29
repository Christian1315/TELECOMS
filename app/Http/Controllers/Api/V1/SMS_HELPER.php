<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Expeditor;
use App\Models\Groupe;
use App\Models\Sms;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class SMS_HELPER extends BASE_HELPER
{
    ##======== REGISTER VALIDATION =======##
    static function BASE_URL()
    {
        $BASE_URL = "https://edok-api.kingsmspro.org/api/v1/sms";
        return $BASE_URL;
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
        ];
    }

    static function Sms_Validator($formDatas)
    {
        $rules = self::sms_rules();
        $messages = self::sms_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    static function groupe_sms_rules(): array
    {
        return [
            'groupe_id' => ['required', 'numeric'],
            'message' => ['required'],
            'expediteur' => ['required'],
        ];
    }

    static function groupe_sms_messages(): array
    {
        return [
            'groupe_id.required' => 'Le champ groupe_id est réquis!',
            'expediteur.required' => 'Le champ expediteur est réquis!',
            'groupe_id.numeric' => 'Le groupe_id doit être un nombre entier',
            'message.required' => 'Le champ message est réquis!',
        ];
    }

    static function Groupe_Sms_Validator($formDatas)
    {
        $rules = self::groupe_sms_rules();
        $messages = self::groupe_sms_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    static function sms_rapports_rules(): array
    {
        return [
            'date_start' => ['required'],
            'date_end' => ['required'],
        ];
    }

    static function sms_rapports_messages(): array
    {
        return [
            'date_start.required' => 'Le champ date_start est réquis!',
            'date_end.required' => 'Le champ date_end est réquis!',
        ];
    }

    static function Sms_rapport_Validator($formDatas)
    {
        $rules = self::sms_rapports_rules();
        $messages = self::sms_rapports_messages();

        $validator = Validator::make($formDatas, $rules, $messages);
        return $validator;
    }

    static function _sendSms($phone, $message, $expediteur)
    {
        // return $message;
        $BASE_URL = env("BASE_URL");
        $API_KEY = env("API_KEY");
        $CLIENT_ID = env("CLIENT_ID");

        ####==== TRAITEMENT DE L'EXPEDITEUR =======###
        $expeditor = Expeditor::where(["name" => $expediteur])->get();
        if ($expeditor->count() == 0) {
            return self::sendError("Ce expéditeur n'existe pas!", 404);
        }

        ##===== Verifions si l'expediteur est valide ou pas =========####
        if ($expeditor[0]->status != 3) {
            return self::sendError("Ce expéditeur existe, mais n'est pas validé!", 404);
        }


        $user = request()->user();

        $EXPEDITEUR = $expediteur;
        $DESTINATAIRE = $phone;
        $MESSAGE = $message;
        $msg_caracters_number = strlen($MESSAGE);

        $url = $BASE_URL . "/send"; #URL D'ENVOIE DE L'SMS

        $smsData   = array(
            'from' => $EXPEDITEUR, //l'expediteur
            'to' => '' . $DESTINATAIRE . '', //destination au format international sans "+" ni "00". Ex: 22890443679
            'type' => 1, //type de message text et flash
            'message' => $MESSAGE, //le contenu de votre sms
            'dlr' => 's' // 1 pour un retour par contre 0
        );

        if (!Is_User_AN_ADMIN($user->id)) {
            $NombreSms = 1; #PAR DEFAUT

            ##GESTION DE LA TAILLE DU MESSAGE

            $One_sms_caracter_limit = env("ONE_SMS_CARACTER_LIMIT");

            #SI LE NOMBRE DE CARACTERE DEPASSE LA LIMIT D'UN SMS
            if ($msg_caracters_number > $One_sms_caracter_limit) {
                #~~Cherchons le nombre de message correspondant aux caracteres en voyés par le USER
                $NombreSms = ($msg_caracters_number / $One_sms_caracter_limit);
            }
            $int_part =  floor($NombreSms); #PARTIE ENTIERE DU NOMBRE DE MESSAGE
            $decimal_part =  $NombreSms - $int_part; #PARTIE DECIMALE DU NOMBRE DE MESSAGE
            if ($decimal_part > 0) { ##SI LE RESTE EST SUPERIEUR A 0,ON ARRONDIE A 1
                #~~~enfin retenons le nombre de message correponds aux nombres de caractères du user
                $NombreSms = $NombreSms + 1;
            }

            ###~~VERIFIONS SI LE SOLDE DU USER EST SUFFISANT

            $sms_amount = env("COST_OF_ONE_SMS") * $NombreSms;

            if (!Is_User_Account_Enough($user->id, $sms_amount)) { #IL NE DISPOSE PAS D'UN SOLDE SUFFISANT
                return self::sendError("Echec d'envoie d'SMS! Votre solde est insuffisant. Veuillez le recharger", 505);
            }


            #####DECREDITATION DE SON SOLDE
            #~~SEULEMENT POUR LES NON ADMINS
            Decredite_User_Account(request()->user()->id, $sms_amount);
        }

        ###ENVOIE DE L'SMS VIA L'API DU FOURNISSEUR
        $response = Http::withHeaders([
            'APIKEY' => $API_KEY,
            'CLIENTID' => $CLIENT_ID
        ])->post($url, $smsData);

        $result = json_decode($response);
        if (!$result->status === "ACT") { #LE MESSAGE N'A PAS ETE ENVOYE
            return self::sendError("L'envoie a échoué", 505);
        }

        #ENREGISTREMENT DES INFOS DE L'SMS DANS LA DB

        $data = [
            "messageId" => $result->messageId,
            "from" => $result->from,
            "to" => $result->to,
            "message" => $result->message,
            "type" => $result->type,
            "route" => $result->route,
            "sms_count" => $result->sms_count,
            "amount" => $user->is_admin ? $result->amount : $sms_amount, #$result->amount s'il est un admin
            "currency" => $result->currency,
            "sms_num" => $user->is_admin ? null : $NombreSms, #null s'il est un admin
        ];

        $sms = Sms::create($data);
        $sms->owner = request()->user()->id;
        $sms->status = 1;
        $sms->save();

        return self::sendResponse($sms, 'Sms envoyé avec succès!!');
    }

    static function smsReports($formData)
    {
        $date_start = $formData['date_start'];
        $date_end = $formData['date_end'];

        $user = request()->user();
        $response =  Sms::with(["status"])->where(["owner" => $user->id])->whereBetween('created_at', [$date_start, $date_end])->get();

        return self::sendResponse($response, 'Rapport recupéré avec succès!!');
    }

    static function SendGroupeSms($formData)
    {
        $groupe = Groupe::with(['contacts'])->where(["id" => $formData['groupe_id'], "owner" => request()->user()->id])->get();
        if ($groupe->count() == 0) {
            return self::sendError("Ce groupe n'existe pas!!", 404);
        }

        $groupe = $groupe[0];
        $contacts =  $groupe->contacts;

        if ($contacts->count() == 0) {
            return self::sendError("Ce groupe ne contient aucun contact!!", 404);
        }
        $message = $formData['message'];
        $expediteur = $formData['expediteur'];


        foreach ($contacts as $contact) {
            $phone =  $contact->phone;

            $BASE_URL = env("BASE_URL");
            $API_KEY = env("API_KEY");
            $CLIENT_ID = env("CLIENT_ID");

            ####==== TRAITEMENT DE L'EXPEDITEUR =======###
            $expeditor = Expeditor::where(["name" => $expediteur])->get();
            if ($expeditor->count() == 0) {
                return self::sendError("Ce expéditeur n'existe pas!", 404);
            }

            ##===== Verifions si l'expediteur est valide ou pas =========####
            if ($expeditor[0]->status != 3) {
                return self::sendError("Ce expéditeur existe, mais n'est pas validé!", 404);
            }

            $user = request()->user();

            $EXPEDITEUR = $expediteur;
            $DESTINATAIRE = $phone;
            $MESSAGE = $message;
            $msg_caracters_number = strlen($MESSAGE);

            $url = $BASE_URL . "/send"; #URL D'ENVOIE DE L'SMS

            $smsData   = array(
                'from' => $EXPEDITEUR, //l'expediteur
                'to' => '' . $DESTINATAIRE . '', //destination au format international sans "+" ni "00". Ex: 22890443679
                'type' => 1, //type de message text et flash
                'message' => $MESSAGE, //le contenu de votre sms
                'dlr' => 's' // 1 pour un retour par contre 0
            );

            if (!Is_User_AN_ADMIN($user->id)) {
                $NombreSms = 1; #PAR DEFAUT

                ##GESTION DE LA TAILLE DU MESSAGE

                $One_sms_caracter_limit = env("ONE_SMS_CARACTER_LIMIT");

                #SI LE NOMBRE DE CARACTERE DEPASSE LA LIMIT D'UN SMS
                if ($msg_caracters_number > $One_sms_caracter_limit) {
                    #~~Cherchons le nombre de message correspondant aux caracteres en voyés par le USER
                    $NombreSms = ($msg_caracters_number / $One_sms_caracter_limit);
                }
                $int_part =  floor($NombreSms); #PARTIE ENTIERE DU NOMBRE DE MESSAGE
                $decimal_part =  $NombreSms - $int_part; #PARTIE DECIMALE DU NOMBRE DE MESSAGE
                if ($decimal_part > 0) { ##SI LE RESTE EST SUPERIEUR A 0,ON ARRONDIE A 1
                    #~~~enfin retenons le nombre de message correponds aux nombres de caractères du user
                    $NombreSms = $NombreSms + 1;
                }

                ###~~VERIFIONS SI LE SOLDE DU USER EST SUFFISANT

                $sms_amount = env("COST_OF_ONE_SMS") * $NombreSms;

                if (!Is_User_Account_Enough($user->id, $sms_amount)) { #IL NE DISPOSE PAS D'UN SOLDE SUFFISANT
                    return self::sendError("Echec d'envoie d'SMS! Votre solde est insuffisant. Veuillez le recharger", 505);
                }


                #####DECREDITATION DE SON SOLDE
                #~~SEULEMENT POUR LES NON ADMINS
                Decredite_User_Account(request()->user()->id, $sms_amount);
            }
            // return $phone;

            ###ENVOIE DE L'SMS VIA L'API DU FOURNISSEUR
            $response = Http::withHeaders([
                'APIKEY' => $API_KEY,
                'CLIENTID' => $CLIENT_ID
            ])->post($url, $smsData);

            $result = json_decode($response);
            if (!$result->status === "ACT") { #LE MESSAGE N'A PAS ETE ENVOYE
                return self::sendError("L'envoie a échoué", 505);
            }

            #ENREGISTREMENT DES INFOS DE L'SMS DANS LA DB

            $data = [
                "messageId" => $result->messageId,
                "from" => $result->from,
                "to" => $result->to,
                "message" => $result->message,
                "type" => $result->type,
                "route" => $result->route,
                "sms_count" => $result->sms_count,
                "amount" => $user->is_admin ? $result->amount : $sms_amount, #$result->amount s'il est un admin
                "currency" => $result->currency,
                "sms_num" => $user->is_admin ? null : $NombreSms, #null s'il est un admin
            ];

            $sms = Sms::create($data);
            $sms->owner = request()->user()->id;
            $sms->status = 1;
            $sms->save();
        }
        // return $phone;
        return self::sendResponse($formData, "Message envoyé au groupe " . $groupe->name . " avec succès");
    }

    static function allSms()
    {
        $user = request()->user();
        if ($user->is_admin) { ###S'IL S'AGIT D'UN ADMIN
            ###il peut tout recuperer
            $sms =  Sms::with(["status"])->get();
        } else {
            $sms =  Sms::where(["owner" => $user->id])->get();
        }
        return self::sendResponse($sms, 'Tout les sms récupérés avec succès!!');
    }

    static function retrieveSms($id)
    {
        $user = request()->user();
        if ($user->is_admin) { ###S'IL S'AGIT D'UN ADMIN
            ###il peut tout recuperer
            $sms = Sms::with(["status"])->where(["id" => $id])->get();
        } else {
            $sms = Sms::with(["status"])->where(["id" => $id, "owner" => request()->user()->id])->get();
        }
        if ($sms->count() == 0) {
            return self::sendError("Ce sms n'existe pas!", 404);
        }
        return self::sendResponse($sms, "Sms récupéré avec succès:!!");
    }
}
