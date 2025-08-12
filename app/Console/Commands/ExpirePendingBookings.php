<?php

namespace App\Console\Commands;

use App\Models\FarmBooking;
use App\Services\FarmBookingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpirePendingBookings extends Command
{
    protected $signature = 'bookings:expire-pending
                            {--dry-run : Show what would be expired without actually updating}
                            {--limit=100 : Maximum number of bookings to process in one run}';

    protected $description = 'Expire pending bookings that have passed their expiration time';

    protected FarmBookingService $bookingService;

    public function __construct(FarmBookingService $bookingService)
    {
        parent::__construct();
        $this->bookingService = $bookingService;
    }

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $this->info("🔍 Checking for expired bookings" . ($isDryRun ? ' (DRY RUN)' : ''));

        // Get bookings that should be expired
        $bookingsToExpire = FarmBooking::shouldBeExpired()
            ->with(['farm', 'user'])
            ->limit($limit)
            ->get();

        if ($bookingsToExpire->isEmpty()) {
            $this->info('✅ No pending bookings found that need to be expired.');
            $this->displayCurrentStats();
            return self::SUCCESS;
        }

        $this->info("📋 Found {$bookingsToExpire->count()} booking(s) to expire");
        $this->newLine();

        $expiredCount = 0;
        $failedCount = 0;

        foreach ($bookingsToExpire as $booking) {
            try {
                $this->displayBookingInfo($booking);

                if (!$isDryRun) {
                    $this->bookingService->expireBooking($booking);
                    $this->line("  ✅ Expired (booking_status: failed, payment_status: expired)");
                } else {
                    $this->line("  ⚠️ Would be expired (dry run)");
                }

                $expiredCount++;

            } catch (\Exception $e) {
                $failedCount++;
                $this->error("  ❌ Failed: {$e->getMessage()}");
                
                Log::error('Console command failed to expire booking', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'error' => $e->getMessage(),
                ]);
            }

            $this->newLine();
        }

        $this->displaySummary($expiredCount, $failedCount, $isDryRun);

        if (!$isDryRun && $expiredCount > 0) {
            $this->displayCurrentStats();
        }

        return $failedCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function displayBookingInfo(FarmBooking $booking): void
    {
        $this->line("📌 Booking: {$booking->booking_reference}");
        $this->line("  Farm: {$booking->farm->name_en}");
        $this->line("  Customer: {$booking->customer_name} ({$booking->customer_email})");
        $this->line("  Amount: AED {$booking->total_amount}");
        $this->line("  Expired: {$booking->expires_at->format('Y-m-d H:i:s')} ({$booking->expires_at->diffForHumans()})");
        $this->line("  Current Status: {$booking->booking_status} / {$booking->payment_status}");
    }

    private function displaySummary(int $expiredCount, int $failedCount, bool $isDryRun): void
    {
        $action = $isDryRun ? 'Would expire' : 'Expired';
        
        $this->info("📊 Summary:");
        $this->info("  {$action}: {$expiredCount} booking(s)");
        
        if ($failedCount > 0) {
            $this->error("  Failed: {$failedCount} booking(s)");
        }

        if (!$isDryRun && $expiredCount > 0) {
            $this->info("  ✅ {$expiredCount} bookings successfully expired");
            $this->info("  Status changed to: booking_status = 'failed', payment_status = 'expired'");
        }
    }

    private function displayCurrentStats(): void
    {
        $stats = $this->bookingService->getDetailedBookingStats();
        
        $this->newLine();
        $this->info("📈 Current System Statistics:");
        $this->line("  • Pending: {$stats['pending']}");
        $this->line("  • Confirmed: {$stats['confirmed']}");
        $this->line("  • Failed Total: {$stats['failed']['total']}");
        $this->line("    ├─ Payment Failed: {$stats['failed']['payment_failed']}");
        $this->line("    └─ Expired: {$stats['failed']['expired']}");
        $this->line("  • Cancelled: {$stats['cancelled']}");
        $this->line("  • Completed: {$stats['completed']}");
        
        if ($stats['maintenance']['should_be_expired'] > 0) {
            $this->warn("  ⚠️  Should be expired: {$stats['maintenance']['should_be_expired']}");
        }
    }
}