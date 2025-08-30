<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ProcessBookingEarningsCommand::class,
        \App\Console\Commands\CompleteBookingsCommand::class,
        \App\Console\Commands\ExpirePendingBookings::class,
    ];
    
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Expire pending bookings every 5 minutes
        $schedule->command('bookings:expire-pending --limit=100')
        ->everyFiveMinutes()
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/booking-expiry.log'));

        // Process earnings for confirmed bookings every 10 minutes
        $schedule->command('bookings:process-earnings --limit=50')
        ->everyTenMinutes()
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/earnings-processing.log'));

        // Complete expired bookings every 15 minutes
        $schedule->command('bookings:complete --limit=50')
        ->everyFifteenMinutes()
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/booking-completion.log'));
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