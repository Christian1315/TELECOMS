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
    public function handle()
    {

        $suspendSms = DefinitifSMs::where(["sended" => 0])->get();

        foreach ($suspendSms as $sms) {
            $EXPEDITEUR = $sms->expeditor;
            $DESTINATAIRE = $sms->destinataire;
            $MESSAGE = $sms->message;
            $user = User::find($sms->sender);
            $userId = $sms->sender;
            $NombreSms = $sms->sms_count;
            $sms_amount = $sms->amount;

            ###___ENVOIE D'SMS
            if (GET_ACTIVE_FORMULE() == "kingsmspro") {

                ###ENVOIE DE L'SMS VIA L'API DE KING SMS
                $response = SMS_HELPER::SEND_BY_KING_SMS_PRO(
                    $EXPEDITEUR,
                    $DESTINATAIRE,
                    $MESSAGE,
                    $user
                );

                // dd($response);
                if (strlen($MESSAGE) > 1530) {
                    return  false;
                }

                ###___quand le compte de KING SMS PRO est insuffisant
                if (!$response) {
                    return  false;
                }

                ###___quand l'expediteur n'est pas crée sur KING SMS PRO
                if ($response == "sender unauthorized") {
                    return  false;
                }

                ###___quand l'expediteur n'est pas crée sur KING SMS PRO
                if ($response == "sender not found or not check") {
                    return  false;
                }

                if (array_key_exists("status",$response)) {
                    if ($response->status == "LEN") {
                        return  false;
                    }
                }

                ###___Le type de $response->from permet de savoir si l'expediteur est validé sur KING SMS PRO
                if (array_key_exists("status",$response)) {
                    if (gettype($response->from) == "array") {
                        return  false;
                    }
                }

                if (array_key_exists("status",$response)) {
                    if ($response->messageId) {
                        $messageId = $response->messageId;
                    } else {
                        $messageId = null;
                    }
                }else {
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
            $actualise_sms->status = 1;
            $actualise_sms->save();

            ###___
            $sms->sended = 1;
            $sms->save();
        }
    }
}
