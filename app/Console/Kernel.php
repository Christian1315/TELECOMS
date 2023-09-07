<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command("app:send-campagne-to-groupe")->everyMinute();##PARCOURIR LES CAMPAGNES CHAQUE MINUITE
        $schedule->command("app:reinitialize_campagne_after_a_day")->daily(); ##REINITIALISATION DES NOMBRE D'ENVOIE DES CAMPAGNES CHAQUE MINUIT
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
