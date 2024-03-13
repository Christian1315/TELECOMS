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

        // dd($suspendSms);
        foreach ($suspendSms as $sms_dif) {
            $EXPEDITEUR = $sms_dif->expeditor;
            $DESTINATAIRE = $sms_dif->destinataire;
            $MESSAGE = $sms_dif->message;
            $user = User::find($sms_dif->sender);
            $userId = $sms_dif->sender;
            $NombreSms = $sms_dif->sms_count;
            $sms_amount = $sms_dif->amount;

            // echo "dd".array_key_exists("status",["status"=>1]);
            // dd(GET_ACTIVE_FORMULE());
            ###___ENVOIE D'SMS
            if (GET_ACTIVE_FORMULE() == "kingsmspro") {

                ###ENVOIE DE L'SMS VIA L'API DE KING SMS
                $response = SMS_HELPER::SEND_BY_KING_SMS_PRO(
                    $EXPEDITEUR,
                    $DESTINATAIRE,
                    $MESSAGE,
                    $user
                );
                // dd(array_key_exists("status", $response));
                // echo "dd".$response;

                // if (strlen($MESSAGE) > 1530) {
                //     return  false;
                // }

                // ###___quand le compte de KING SMS PRO est insuffisant
                // if (!$response) {
                //     return  false;
                // }

                // ###___quand l'expediteur n'est pas crée sur KING SMS PRO
                // if ($response == "sender unauthorized") {
                //     return  false;
                // }

                // ###___quand l'expediteur n'est pas crée sur KING SMS PRO
                // if ($response == "sender not found or not check") {
                //     return  false;
                // }

                ###___Le type de $response->from permet de savoir si l'expediteur est validé sur KING SMS PRO
                // dd($response);
                // if (gettype($response) == "array") {
                //     dd($response->status);

                //     if (array_key_exists("status", $response)) {
                //     }
                // }
                // dd("gogo");
                // if ($response->status == "ACT") {
                //     $this->delivered = true;
                // }

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

            // if ($this->delivered) {
            ####____GESTION DU SOLDE
            if (!Is_User_AN_ADMIN($userId)) { ##S'IL S'AGIT D'UN SIMPLE USER

                #####DECREDITATION DE SON SOLDE
                Decredite_User_Account($userId, $NombreSms);
            } else { ## S'IL S'AGIT D'UN ADMIN
                ###~~VERIFIONS SI LE SOLDE DU COMPTE ADMIN **premier admin ID 1** EST SUFFISANT

                #####DECREDITATION DE SON SOLDE
                Decredite_User_Account(1, $NombreSms);
            }

            ###__NOTIFIONS QUE L'SMS DEFINITIF A ETE DELIVRE
            // $sms_dif->delivered = true;
            // $sms_dif->save();
            // }

            #ENREGISTREMENT DES INFOS DE L'SMS DANS LA DB
            $data = [
                "messageId" => "messageId",
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
            // if ($this->delivered) {
            //     $actualise_sms->delivered = true;
            // }
            $actualise_sms->save();

            ###___
            $sms_dif->sended = 1;
            $sms_dif->save();
        }
    }
}
