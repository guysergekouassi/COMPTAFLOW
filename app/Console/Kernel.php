<!-- <?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Enregistre la commande de nettoyage des PDFs
        \App\Console\Commands\CleanPreviewPDFs::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Supprime tous les fichiers previews tous les jours à minuit
        $schedule->command('previews:clean')->daily();

        // Exemple : autres tâches planifiées
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        // Charge les commandes depuis le répertoire Commands
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
