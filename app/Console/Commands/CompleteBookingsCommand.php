<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FarmBooking;
use App\Services\FarmOwnerWalletService;
use Exception;
use Carbon\Carbon;

class CompleteBookingsCommand extends Command
{
    protected $signature = 'bookings:complete {--limit=100 : Maximum number of bookings to process}';
    protected $description = 'Mark confirmed bookings as completed and confirm their earnings';

    protected FarmOwnerWalletService $walletService;

    public function __construct(FarmOwnerWalletService $walletService)
    {
        parent::__construct();
        $this->walletService = $walletService;
    }

    public function handle()
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Starting to complete expired bookings and confirm earnings (limit: {$limit})...");
        
        // IMPROVED: Filter at database level instead of in PHP memory
        $bookingsToComplete = $this->getEndedBookings($limit);
        
        if ($bookingsToComplete->isEmpty()) {
            $this->info('No bookings found that need to be completed.');
            return 0;
        }

        $this->info("Found {$bookingsToComplete->count()} bookings to complete.");
        
        $completed = 0;
        $earningsConfirmed = 0;
        $failed = 0;
        
        $progressBar = $this->output->createProgressBar($bookingsToComplete->count());
        $progressBar->start();

        foreach ($bookingsToComplete as $booking) {
            try {
                $result = $this->completeBookingWithEarnings($booking);
                
                if ($result['completed']) {
                    $completed++;
                }
                
                if ($result['earnings_confirmed']) {
                    $earningsConfirmed++;
                }
                
                $this->newLine();
                $this->line("✅ Completed: {$booking->booking_reference} - Farm: {$booking->farm->name_en}" . 
                          ($result['earnings_confirmed'] ? ' (Earnings Confirmed)' : ''));
                
            } catch (Exception $e) {
                $failed++;
                
                $this->newLine();
                $this->error("❌ Failed: {$booking->booking_reference} - Error: {$e->getMessage()}");
                
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
                ['Bookings Completed', $completed],
                ['Earnings Confirmed', $earningsConfirmed],
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
     * IMPROVED: Get ended bookings using database-level filtering
     * Replicates the exact logic from FarmBooking::hasEnded() method
     */
    private function getEndedBookings(int $limit)
    {
        $now = Carbon::now();
        
        return FarmBooking::with(['farm.pricing', 'farm.user'])
            ->where('booking_status', FarmBooking::BOOKING_STATUS_CONFIRMED)
            // Exact replica of hasEnded() logic at database level
            ->whereNotNull('end_date')     // Must have end_date
            ->whereNotNull('end_time')     // Must have end_time  
            ->whereRaw('CONCAT(end_date, " ", TIME_FORMAT(end_time, "%H:%i:%s")) < ?', [$now])
            ->limit($limit)
            ->get();
    }

    /**
     * Complete a booking and confirm its earnings
     */
    private function completeBookingWithEarnings(FarmBooking $booking): array
    {
        $oldStatus = $booking->booking_status;
        $earningsWereProcessed = $booking->earnings_processed;
        $earningsWereConfirmed = $booking->earnings_confirmed;
        
        // Mark booking as completed
        $booking->update([
            'booking_status' => FarmBooking::BOOKING_STATUS_COMPLETED
        ]);

        $result = [
            'completed' => true,
            'earnings_confirmed' => false,
        ];

        // Confirm earnings if they were processed but not yet confirmed
        if ($earningsWereProcessed && !$earningsWereConfirmed) {
            try {
                $confirmationResult = $this->walletService->confirmBookingEarning($booking);
                $result['earnings_confirmed'] = true;
                $result['earnings_amount'] = $confirmationResult['earning_amount'];
                
                $this->line("  💰 Earnings confirmed: AED {$confirmationResult['earning_amount']} moved to balance");
                
            } catch (Exception $e) {
                \Log::error('Failed to confirm earnings for completed booking', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'error' => $e->getMessage(),
                ]);
                
                $this->warn("  ⚠️  Earnings confirmation failed for {$booking->booking_reference}: {$e->getMessage()}");
            }
        }

        // Log the completion
        \Log::info('Booking completed automatically', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'farm_id' => $booking->farm_id,
            'customer_name' => $booking->customer_name,
            'old_status' => $oldStatus,
            'new_status' => FarmBooking::BOOKING_STATUS_COMPLETED,
            'earnings_processed' => $earningsWereProcessed,
            'earnings_confirmed' => $result['earnings_confirmed'],
            'earnings_amount' => $result['earnings_amount'] ?? null,
            'end_datetime_passed' => true,
        ]);

        return $result;
    }
}