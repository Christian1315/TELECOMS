<?php

namespace App\Console\Commands;

use App\Models\Campagne;
use Illuminate\Console\Command;

class Reinitialize_Campagne_After_A_Day extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reinitialize_campagne_after_a_day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reactualise une campagne après un jour, pour que le scénario d\'envoie se répète le prochain jour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $campagnes = Campagne::all();

        foreach ($campagnes as $campagne) {
            $num_time_by_day = $campagne->num_time_by_day;
            ##ON REACTUALISE LE NOMBRE D'ENVOIE PAR JOUR
            $campagne->num_time_rest = $num_time_by_day;
            ##ON REACTUALISE LA DATE D'ENVOIE PRECEDENTE à null
            $campagne->previous_send_date = Null;

            $campagne->save();
        }
    }
}
