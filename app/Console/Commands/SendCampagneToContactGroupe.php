<?php

namespace App\Console\Commands;

use App\Models\Campagne;
use App\Models\CampagneGroupe;
use App\Models\Expeditor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendCampagneToContactGroupe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-campagne-to-groupe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie de message vers des contacts  de leur groupe de Campagne';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $campagne = Campagne::find($this->argument("campagne_id"));
        $campagnes = Campagne::all();

        foreach ($campagnes as $campagne) {
            # code...
            // $start_date = $campagne->start_date;
            // $end_date = $campagne->end_date;

            // $start_time = strtotime($start_date);
            // $end_time = strtotime($end_date);

            $expeditor = Expeditor::find($campagne->expeditor);
            $contacts = $campagne->groupes[0]->contacts;

            #### ENVOIE D'SMS
            $sms_login =  Login_To_Frik_SMS();
            foreach ($contacts as $contact) {
                if ($sms_login['status']) {
                    $token =  $sms_login['data']['token'];

                    Http::withHeaders([
                        'Authorization' => "Bearer " . $token,
                    ])->post(env("SEND_SMS_API_URL") . "/api/v1/sms/send", [
                        "phone" => $contact->phone,
                        "message" => $campagne->message,
                        "expediteur" => $expeditor->name,
                    ]);
                }
            }

            ###___NOTONS QUE CETTE CAMPAGNE EST LANCEE
            $campagne->initiated = 1;
            $campagne->save();
        }
    }
}
