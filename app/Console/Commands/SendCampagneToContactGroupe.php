<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V1\SMS_HELPER;
use App\Models\Campagne;
use App\Models\CampagneGroupe;
use App\Models\Expeditor;
use App\Models\User;
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
        ##___LES CAMPAGNES QUI NE SONT PAS FINIES
        $campagnes = Campagne::whereRaw('status != 4')->get();

        foreach ($campagnes as $campagne) {
            Campagne_Initiation($campagne);
        }
    }
}
