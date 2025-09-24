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
        
        // SolaFriq scheduled tasks
        $schedule->command('queue:work --stop-when-empty')->everyMinute();
        
        // Send installment payment reminders daily at 9 AM
        $schedule->call(function () {
            // Logic to send installment payment reminders
        })->dailyAt('09:00');
        
        // Check for expired warranties daily at 6 AM
        $schedule->call(function () {
            // Logic to check and notify about expired warranties
        })->dailyAt('06:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}