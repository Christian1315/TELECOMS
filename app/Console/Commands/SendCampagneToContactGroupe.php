<?php

namespace App\Console\Commands;

use App\Models\Campagne;
use Illuminate\Console\Command;

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
        ##___LES CAMPAGNES QUI NE SONT NI FINIES NI STOPEE
        $campagnes = Campagne::whereRaw('status != 3')->whereRaw("status != 4")->get();

        foreach ($campagnes as $campagne) {
            Campagne_Initiation($campagne);
        }
    }
}
