<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ProcessBookingEarningsCommand::class,
    ];
    
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        $schedule->command('bookings:expire-pending')
        ->everyMinutes(30)
        ->withoutOverlapping(10);

        // Process earnings for confirmed bookings every 10 minutes
        $schedule->command('bookings:process-earnings --limit=50')
        ->everyTenMinutes()
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/earnings-processing.log'));

        // Expire pending bookings (existing command, make sure this exists)
        $schedule->command('bookings:expire-pending --limit=100')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/booking-expiry.log'));
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
