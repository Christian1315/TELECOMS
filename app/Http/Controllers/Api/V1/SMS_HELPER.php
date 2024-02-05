<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Expeditor;
use App\Models\Groupe;
use App\Models\Sms;
use App\Models\Solde;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class SMS_HELPER extends BASE_HELPER
{
    ##======== REGISTER VALIDATION =======##
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

    static function send_sms_via_ocean_post($expediteur, $phone, $message)
    {
        $url = env("OCEANIC_BASE_URL");
        $postdata = array(
            'user' => env("OCEANIC_USER"),
            'password' => env("OCEANIC_PASSWORD"),
            'from' => $expediteur,
            'to' => $phone,
            'text' => $message,
            'api' => env("OCEANIC_API"),
        );

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
            $response = curl_exec($curl);
            curl_close($curl);
        } catch (\Throwable $th) {
            $response = false;
        }
        return $response;
    }

    public static function SEND_BY_OCEANIC_HTTP($from, $to, $message)
    {
        ###__ ENVOIE DE L'SMS VIA L'API DU FOURNISSEUR
        $user = env("OCEANIC_USER");
        $password = env("OCEANIC_PASSWORD");
        $url = env("OCEANIC_BASE_URL") . "?user=" . $user . "&password=" . $password . "&from=" . $from . "&to=" . $to . "&text=" . $message;

        $response = Http::get($url);
        try {
        } catch (\Throwable $th) {
            $response = false;
        }
        return $response;
    }

    
    public static function SEND_BY_KING_SMS_PRO($EXPEDITEUR, $DESTINATAIRE, $MESSAGE,$USER)
    {
        $BASE_URL = env("BASE_URL");

        if (Is_THIS_ORION_ACCOUNT($USER)) {
            $API_KEY = env("OLD_API_KEY");
            $CLIENT_ID = env("OLD_CLIENT_ID");
        } else {
            $API_KEY = env("API_KEY");
            $CLIENT_ID = env("CLIENT_ID");
        }

        $url = $BASE_URL . "/send"; #URL D'ENVOIE DE L'SMS

        $smsData   = array(
            'from' => $EXPEDITEUR, //l'expediteur
            'to' => '' . $DESTINATAIRE . '', //destination au format international sans "+" ni "00". Ex: 22890443679
            'type' => 1, //type de message text et flash
            'message' => $MESSAGE, //le contenu de votre sms
            'dlr' => 's' // 1 pour un retour par contre 0
        );

        $response = Http::withHeaders([
            'APIKEY' => $API_KEY,
            'CLIENTID' => $CLIENT_ID
        ])->post($url, $smsData);

        $response = json_decode($response);
        // try {
        // } catch (\Throwable $th) {
        //     $response = false;
        // }

        return $response;
    }

    static function _sendSms($phone, $message, $expediteur, $out_call = false, $user = null)
    {
        ####==== TRAITEMENT DE L'EXPEDITEUR =======###
        $expeditor = Expeditor::where(["name" => $expediteur])->get();
        if ($expeditor->count() == 0) {
            return self::sendError("Ce expéditeur n'existe pas!", 404);
        }
        
        #SI L'OPERATION NE PRECISE PAS LE USER, ON PRENDS CELUI QUI EST CONNECTE PAR DEFAUT
        if (!$user) {
            $user = request()->user();
        }

        $userId =  $user->id;
        if (!Is_User_AN_ADMIN($userId)) {
            if ($expeditor[0]->owner != $userId) {
                return self::sendError("Désolé! Ce expéditeur ne vous appartient pas!", 505);
            }
        }

        ##===== Verifions si l'expediteur est valide ou pas =========####
        if ($expeditor[0]->status != 3) {
            return self::sendError("Ce expéditeur existe, mais n'est pas validé!", 404);
        }

        $EXPEDITEUR = $expediteur;
        $DESTINATAIRE = $phone;
        $MESSAGE = $message;

        $NombreSms = SMS_NUMBER($MESSAGE); ##NOMBRE D'SMS PAR MESSAGE
        if (!Is_User_AN_ADMIN($userId)) { ##S'IL S'AGIUT D'UN SIMPLE USER
            ###~~VERIFIONS SI LE SOLDE DU USER EST SUFFISANT
            if (!Is_User_Account_Enough($userId, $NombreSms)) { #IL NE DISPOSE PAS D'UN SOLDE SUFFISANT
                return self::sendError("Echec d'envoie d'SMS! Votre solde est insuffisant. Veuillez le recharger", 505);
            }
            #####DECREDITATION DE SON SOLDE
            Decredite_User_Account($userId, $NombreSms);
        } else { ## S'IL S'AGIT D'UN ADMIN
            ###~~VERIFIONS SI LE SOLDE DU COMPTE ADMIN **premier admin ID 1** EST SUFFISANT
            #### Pour les deux admins, on ne considère que le compte admin 1(le compte admin 2 PPJJJOEL ne dispose pas de compte)
            if (!Is_User_Account_Enough(1, $NombreSms)) { #IL NE DISPOSE PAS D'UN SOLDE SUFFISANT
                return self::sendError("Echec d'envoie d'SMS! Le solde du compte admin 1 est insuffisant. Veuillez le recharger", 505);
            }

            #####DECREDITATION DE SON SOLDE
            Decredite_User_Account(1, $NombreSms);
        }

        ###___ENVOIE D'SMS
        if (GET_ACTIVE_FORMULE() == "kingsmspro") {

            ###ENVOIE DE L'SMS VIA L'API DE KING SMS
            $response = self::SEND_BY_KING_SMS_PRO(
                $EXPEDITEUR,
                $DESTINATAIRE,
                $MESSAGE,
                $user
            );

            if (strlen($MESSAGE) > 1530) {
                if ($out_call) {
                    return false;
                }
                return self::sendError("Echec d'envoie du message! Le message ne doit pas depasser 1530 caractères!", 505);
            }

            ###___quand le compte de KING SMS PRO est insuffisant
            if (!$response) {
                if ($out_call) {
                    return false;
                }
                return self::sendError("Echec d'envoie du message! ", 505);
            }

            ###___quand l'expediteur n'est pas crée sur KING SMS PRO
            if ($response == "sender unauthorized") {
                if ($out_call) {
                    return false;
                }
                return self::sendError("Echec d'envoie du message! L'expediteur a un soucis!", 505);
            }

            ###___quand l'expediteur n'est pas crée sur KING SMS PRO
            if ($response == "sender not found or not check") {
                if ($out_call) {
                    return false;
                }
                return self::sendError("Echec d'envoie du message! L'expediteur a un soucis!", 505);
            }
            
            if ($response->status == "LEN") {
                if ($out_call) {
                    return false;
                }
                return self::sendError("Echec d'envoie du message! Le message est trop long!", 505);
            }

            ###___Le type de $response->from permet de savoir si l'expediteur est validé sur KING SMS PRO
            if (gettype($response->from) == "array") {
                if ($out_call) {
                    return false;
                }
                return self::sendError("Echec d'envoie du message! L'expediteur a un soucis!", 505);
            }

            if ($response->messageId) {
                $messageId = $response->messageId;
            } else {
                $messageId = null;
            }
        } elseif (GET_ACTIVE_FORMULE() == "oceanic") {
            ###ENVOIE DE L'SMS VIA L'API DE OCEANIC

            $response = self::SEND_BY_OCEANIC_HTTP(
                $EXPEDITEUR,
                $DESTINATAIRE,
                urlencode($MESSAGE)
            );

            if ($response == false) {
                if ($out_call) {
                    return false;
                }
                return self::sendError("1 Echec d'envoie du message!", 505);
            }

            if ($response == "ERR: NO USER FOUND") { ###ECHEC D'ENVOIS D'SMS
                if ($out_call) {
                    return false;
                }
                return self::sendError("2 Echec d'envoie du message!", 505);
            }

            if ($response === "ERR: MESSAGE NOT SENT To: $phone") { ###ECHEC D'ENVOIS D'SMS
                if ($out_call) {
                    return false;
                }
                return self::sendError("3 Echec d'envoie du message!", 505);
            }

            if (!strpos($response, "ID: ")) {
                if ($out_call) {
                    return false;
                }
                return self::sendError("4 Echec d'envoie du message!", 505);
            }

            ##RECUPERATION DU MESSAGE ID
            $data = explode("ID: ", $response);
            $data2 = explode(" To: ", $data[1]);
            $messageId = $data2[0];
        } else {
            if ($out_call) {
                return false;
            }
            return self::sendError("Aucune formule d'envoie n'est active!", 505);
        }

        ###____
        $sms_amount = env("COST_OF_ONE_SMS") * $NombreSms;

        // return ;
        #ENREGISTREMENT DES INFOS DE L'SMS DANS LA DB
        $data = [
            "messageId" => $messageId,
            "from" => $EXPEDITEUR,
            "to" => $DESTINATAIRE,
            "message" => $MESSAGE,
            // "type" => $result->type,
            // "route" => $result->route,
            "sms_count" => $NombreSms,
            "amount" => $sms_amount,
            // "currency" => $result->currency,
            "sms_num" => $NombreSms,
        ];

        $sms = Sms::create($data);
        $sms->owner = $userId;
        $sms->status = 1;
        $sms->save();

        if (!$out_call) {
            return self::sendResponse($sms, 'Sms envoyé avec succès!!');
        }
    }

    static function send_sms_from_other_plateforme($phone, $message, $expediteur)
    {
        $user = User::find(1);

        $EXPEDITEUR = $expediteur;
        $DESTINATAIRE = $phone;
        $MESSAGE = $message;

        $response = self::SEND_BY_KING_SMS_PRO(
            $EXPEDITEUR,
            $DESTINATAIRE,
            $MESSAGE,
            $user
        );

        if ($response == false) {
            return false;
        }

        if ($response == "sender unauthorized") {
            return False;
        }

        ###___Le type de $response->from permet de savoir si l'expediteur est validé sur KING SMS PRO
        if (gettype($response->from) == "array") {
            return false;
        };

        if ($response->messageId) {
            $messageId = $response->messageId;
        } else {
            $messageId = null;
        };

        $NombreSms = SMS_NUMBER($MESSAGE); ##NOMBRE D'SMS PAR MESSAGE
        $sms_amount = env("COST_OF_ONE_SMS") * $NombreSms;

        #ENREGISTREMENT DES INFOS DE L'SMS DANS LA DB
        $data = [
            "messageId" => $messageId,
            "from" => $EXPEDITEUR,
            "to" => $DESTINATAIRE,
            "message" => $MESSAGE,
            "sms_count" => $NombreSms,
            "amount" => $sms_amount,
            "sms_num" => $NombreSms,
        ];

        ###___
        $sms = Sms::create($data);
        $sms->owner = $user ? $user->id : null;
        $sms->status = 1;
        $sms->save();
        return true;
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
        $user = request()->user();
        $groupe = Groupe::with(['contacts'])->where(["id" => $formData['groupe_id'], "owner" => request()->user()->id])->get();
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

        if (!Is_User_AN_ADMIN($user->id)) {
            if ($expeditor[0]->owner != $user->id) {
                return self::sendError("Ce expediteur ne vous appartient pas!!", 404);
            }
        }

        ##===== Verifions si l'expediteur est valide ou pas =========####
        if ($expeditor[0]->status != 3) {
            return self::sendError("Ce expéditeur existe, mais n'est pas validé!", 404);
        }



        $message = $formData['message'];
        $expediteur = $formData['expediteur'];

        if (GET_ACTIVE_FORMULE() == "kingsmspro") {
            if (strlen($message) > 1530) {
                return self::sendError("Echec d'envoie du message! Le message ne doit pas depasser 1530 caractères!", 505);
            }
        }


        $NombreSms = SMS_NUMBER($message); ##NOMBRE D'SMS PAR MESSAGE
        if (!Is_User_AN_ADMIN($user->id)) { ##S'IL S'AGIUT D'UN SIMPLE USER
            ###~~VERIFIONS SI LE SOLDE DU USER EST SUFFISANT
            if (!Is_User_Account_Enough($user->id, $NombreSms)) { #IL NE DISPOSE PAS D'UN SOLDE SUFFISANT
                return self::sendError("Echec d'envoie d'SMS! Votre solde est insuffisant. Veuillez le recharger", 505);
            }
        } else { ## S'IL S'AGIT D'UN ADMIN
            ###~~VERIFIONS SI LE SOLDE DU COMPTE ADMIN **premier admin ID 1** EST SUFFISANT
            #### Pour les deux admins, on ne considère que le compte admin 1(le compte admin 2 PPJJJOEL ne dispose pas de compte)
            if (!Is_User_Account_Enough(1, $NombreSms)) { #IL NE DISPOSE PAS D'UN SOLDE SUFFISANT
                return self::sendError("Echec d'envoie d'SMS! Le solde du compte admin 1 est insuffisant. Veuillez le recharger", 505);
            }
        }

        foreach ($contacts as $contact) {
            self::_sendSms(
                $contact->phone,
                $message,
                $expediteur,
                true
            );
        }
        return self::sendResponse($formData, "Message envoyé au groupe " . $groupe->name . " avec succès");
    }

    static function allSms()
    {
        $user = request()->user();
        if ($user->is_admin) { ###S'IL S'AGIT D'UN ADMIN
            ###il peut tout recuperer
            $sms =  Sms::with(["status"])->orderBy("id", "desc")->get();
        } else {
            $sms =  Sms::where(["owner" => $user->id])->orderBy("id", "desc")->get();
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
            $sms = Sms::with(["status"])->where(["id" => $id, "owner" => $user->id])->get();
        }
        if ($sms->count() == 0) {
            return self::sendError("Ce sms n'existe pas!", 404);
        }
        return self::sendResponse($sms, "Sms récupéré avec succès:!!");
    }
}
