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
        // $schedule->command('inspire')->hourly();
        $schedule->command('uploads:retry')->daily();

        // Planifier la commande pour s'exécuter tous les vendredis à 14h
        //$schedule->command('sms:send-debt-summary')->fridays()->at('14:00');

        $schedule->command('debts:archive')->daily();

        // Planifier la commande chaque vendredi à 14h
        $schedule->command('sms:send-debt-reminders')
                 ->fridays()
                 ->at('14:00');
    
        //$schedule->command('notification:send-overdue-debt-reminders')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
    
    protected $commands = [
        \App\Console\Commands\RetryFailedUploads::class,
        \App\Console\Commands\ArchiveDebtsCommand::class,
        \App\Console\Commands\SendDebtSummarySmsCommand::class,
        \App\Console\Commands\SendDebtReminders::class,
        \App\Console\Commands\SendDebtReminderNotification::class,
    ];
    
}
