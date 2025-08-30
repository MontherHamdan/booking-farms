<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FarmOwnerWalletService;
use App\Models\FarmBooking;

class ProcessBookingEarningsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:process-earnings {--limit=100 : Maximum number of bookings to process}';

    /**
     * The console command description.
     */
    protected $description = 'Process earnings for confirmed bookings that haven\'t been processed yet';

    protected FarmOwnerWalletService $walletService;

    public function __construct(FarmOwnerWalletService $walletService)
    {
        parent::__construct();
        $this->walletService = $walletService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Starting to process earnings for confirmed bookings (limit: {$limit})...");
        
        // Get bookings that need earnings processing
        $bookingsToProcess = FarmBooking::needsEarningsProcessing()
                                       ->with(['farm', 'farm.user'])
                                       ->limit($limit)
                                       ->get();
        
        if ($bookingsToProcess->isEmpty()) {
            $this->info('No bookings found that need earnings processing.');
            return 0;
        }

        $this->info("Found {$bookingsToProcess->count()} bookings to process.");
        
        $processed = 0;
        $failed = 0;
        $progressBar = $this->output->createProgressBar($bookingsToProcess->count());
        $progressBar->start();

        foreach ($bookingsToProcess as $booking) {
            try {
                $result = $this->walletService->processBookingEarning($booking);
                $processed++;
                
                $this->newLine();
                $this->line("✅ Processed: {$booking->booking_reference} - Farm Owner Earning: AED {$result['farm_owner_earning']}");
                
            } catch (\Exception $e) {
                $failed++;
                
                $this->newLine();
                $this->error("❌ Failed: {$booking->booking_reference} - Error: {$e->getMessage()}");
                
                // Log the error for debugging
                \Log::error('Command failed to process booking earning', [
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
        $this->info("Processing completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Processed Successfully', $processed],
                ['Failed', $failed],
                ['Total', $processed + $failed],
            ]
        );
        
        if ($failed > 0) {
            $this->warn("Some bookings failed to process. Check the logs for details.");
            return 1;
        }
        
        return 0;
    }
}