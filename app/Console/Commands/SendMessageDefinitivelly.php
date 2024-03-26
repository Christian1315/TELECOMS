<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V1\SMS_HELPER;
use App\Models\DefinitifSMs;
use App\Models\Sms;
use App\Models\User;
use Illuminate\Console\Command;

class SendMessageDefinitivelly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-message-definitivelly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    protected $delivered = false;
    protected $messageId = "";

    public function handle()
    {
        set_time_limit(0);
        $suspendSms = DefinitifSMs::where(["sended" => 0])->get();

        foreach ($suspendSms as $sms_dif) {
            $EXPEDITEUR = $sms_dif->expeditor;
            $DESTINATAIRE = $sms_dif->destinataire;
            $MESSAGE = $sms_dif->message;
            $user = User::find($sms_dif->sender);
            $userId = $sms_dif->sender;
            $NombreSms = $sms_dif->sms_count;
            $sms_amount = $sms_dif->amount;


            ###___ENVOIE D'SMS
            if (GET_ACTIVE_FORMULE() == "kingsmspro") {

                ###ENVOIE DE L'SMS VIA L'API DE KING SMS
                $res = SMS_HELPER::SEND_BY_KING_SMS_PRO(
                    $EXPEDITEUR,
                    $DESTINATAIRE,
                    $MESSAGE,
                    $user
                );

                ##___TRANSFORMONS L'OBJET EN ARRAY
                $response =  (array)$res;

                ##___INITIATIATION DU STATUS A FALSE
                $sms_status = false;
                $messageId = "messageId";

                if (array_key_exists("status", $response)) {
                    $sms_status = $sms_status;

                    if ($response["status"] == "ACT") {
                        $sms_status = true;
                    }
                }

                if (array_key_exists("messageId", $response)) {
                    if ($response["messageId"]) {
                        $messageId = $response["messageId"];
                    }
                }
            } elseif (GET_ACTIVE_FORMULE() == "oceanic") {
                ###ENVOIE DE L'SMS VIA L'API DE OCEANIC

                $response = self::SEND_BY_OCEANIC_HTTP(
                    $EXPEDITEUR,
                    $DESTINATAIRE,
                    urlencode($MESSAGE)
                );

                ##RECUPERATION DU MESSAGE ID
                $data = explode("ID: ", $response);
                $data2 = explode(" To: ", $data[1]);
                $messageId = $data2[0];
            }

            ##__On decredite  le compte seulement quand le status est true
            if ($sms_status) {
                if (!Is_User_AN_ADMIN($userId)) { ##S'IL S'AGIT D'UN SIMPLE USER

                    #####DECREDITATION DE SON SOLDE
                    Decredite_User_Account($userId, $NombreSms);
                } else { ## S'IL S'AGIT D'UN ADMIN
                    ###~~VERIFIONS SI LE SOLDE DU COMPTE ADMIN **premier admin ID 1** EST SUFFISANT

                    #####DECREDITATION DE SON SOLDE
                    Decredite_User_Account(1, $NombreSms);
                }
            }

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

            $actualise_sms = Sms::create($data);
            $actualise_sms->owner = $userId;
            $actualise_sms->status = $sms_status ? 1 : 2;
            $actualise_sms->delivered = $sms_status ? 1 : 0;
            $actualise_sms->save();

            ###___
            ###__NOTIFIONS QUE L'SMS DEFINITIF A ETE DELIVRE SI LE STATUS EST "ACT"

            $sms_dif->sended = 1;
            $sms_dif->delivered = 1;
            $sms_dif->save();
        }
    }
}
