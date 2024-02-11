<?php

namespace App\Console\Commands;

use App\Models\Sms;
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
    public function handle()
    {
        $EXPEDITEUR = "";
        $DESTINATAIRE = "";
        $MESSAGE = "";
        $user = "";
        $userId = "";
        $NombreSms = "";


        ###___ENVOIE D'SMS
        if (GET_ACTIVE_FORMULE() == "kingsmspro") {

            ###ENVOIE DE L'SMS VIA L'API DE KING SMS
            $response = self::SEND_BY_KING_SMS_PRO(
                $EXPEDITEUR,
                $DESTINATAIRE,
                $MESSAGE,
                $user
            );


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

            ##RECUPERATION DU MESSAGE ID
            $data = explode("ID: ", $response);
            $data2 = explode(" To: ", $data[1]);
            $messageId = $data2[0];
        }

        ####____GESTION DU SOLDE
        if (!Is_User_AN_ADMIN($userId)) { ##S'IL S'AGIT D'UN SIMPLE USER

            #####DECREDITATION DE SON SOLDE
            Decredite_User_Account($userId, $NombreSms);
        } else { ## S'IL S'AGIT D'UN ADMIN
            ###~~VERIFIONS SI LE SOLDE DU COMPTE ADMIN **premier admin ID 1** EST SUFFISANT

            #####DECREDITATION DE SON SOLDE
            Decredite_User_Account(1, $NombreSms);
        }

        ###____
        $sms_amount = env("COST_OF_ONE_SMS") * $NombreSms;

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
    }
}
