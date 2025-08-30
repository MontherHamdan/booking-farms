<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FarmBooking;
use Carbon\Carbon;
use Exception;

class CompleteBookingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:complete {--limit=100 : Maximum number of bookings to process}';

    /**
     * The console command description.
     */
    protected $description = 'Mark confirmed bookings as completed when their time period has ended';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Starting to complete expired bookings (limit: {$limit})...");
        
        // Get confirmed bookings that have ended
        $bookingsToComplete = FarmBooking::with(['farm.pricing'])
                                        ->where('booking_status', FarmBooking::BOOKING_STATUS_CONFIRMED)
                                        ->limit($limit)
                                        ->get()
                                        ->filter(function ($booking) {
                                            return $booking->hasEnded();
                                        });
        
        if ($bookingsToComplete->isEmpty()) {
            $this->info('No bookings found that need to be completed.');
            return 0;
        }

        $this->info("Found {$bookingsToComplete->count()} bookings to complete.");
        
        $completed = 0;
        $failed = 0;
        $progressBar = $this->output->createProgressBar($bookingsToComplete->count());
        $progressBar->start();

        foreach ($bookingsToComplete as $booking) {
            try {
                $this->completeBooking($booking);
                $completed++;
                
                $this->newLine();
                $this->line("✅ Completed: {$booking->booking_reference} - Farm: {$booking->farm->name_en}");
                
            } catch (Exception $e) {
                $failed++;
                
                $this->newLine();
                $this->error("❌ Failed: {$booking->booking_reference} - Error: {$e->getMessage()}");
                
                // Log the error for debugging
                \Log::error('Command failed to complete booking', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info("Completion process finished!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Completed Successfully', $completed],
                ['Failed', $failed],
                ['Total Processed', $completed + $failed],
            ]
        );
        
        if ($failed > 0) {
            $this->warn("Some bookings failed to complete. Check the logs for details.");
            return 1;
        }
        
        return 0;
    }

    /**
     * Complete a booking and handle related processes
     */
    private function completeBooking(FarmBooking $booking): void
    {
        $oldStatus = $booking->booking_status;
        
        // Update booking status
        $booking->update([
            'booking_status' => FarmBooking::BOOKING_STATUS_COMPLETED
        ]);

        // Log the completion
        \Log::info('Booking completed automatically', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'farm_id' => $booking->farm_id,
            'customer_name' => $booking->customer_name,
            'old_status' => $oldStatus,
            'new_status' => FarmBooking::BOOKING_STATUS_COMPLETED,
            'end_datetime_passed' => true,
        ]);

        // TODO: Send completion notification to customer
        // $this->sendCustomerCompletionNotification($booking);
        
        // TODO: Send completion notification to farm owner  
        // $this->sendFarmOwnerCompletionNotification($booking);
        
        // TODO: Trigger any post-completion processes (reviews, etc.)
        // $this->triggerPostCompletionProcesses($booking);
    }

    // TODO: Implement notification methods when email system is ready
    
    /**
     * Send completion notification to customer
     * 
     * @param FarmBooking $booking
     */
    // private function sendCustomerCompletionNotification(FarmBooking $booking): void
    // {
    //     // TODO: Implement customer notification
    //     // - Thank customer for choosing the farm
    //     // - Ask for review/rating
    //     // - Provide support contact if needed
    // }

    /**
     * Send completion notification to farm owner
     * 
     * @param FarmBooking $booking  
     */
    // private function sendFarmOwnerCompletionNotification(FarmBooking $booking): void
    // {
    //     // TODO: Implement farm owner notification
    //     // - Notify that booking was completed successfully
    //     // - Provide booking summary
    //     // - Earnings information if relevant
    // }

    /**
     * Trigger post-completion processes
     * 
     * @param FarmBooking $booking
     */
    // private function triggerPostCompletionProcesses(FarmBooking $booking): void
    // {
    //     // TODO: Implement post-completion logic
    //     // - Enable review/rating for customer
    //     // - Update farm availability if needed
    //     // - Analytics tracking
    //     // - Integration with other systems
    // }
}