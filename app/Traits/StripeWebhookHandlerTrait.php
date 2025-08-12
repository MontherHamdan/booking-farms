<?php
namespace App\Traits;

use App\Models\FarmBooking;
use Illuminate\Support\Facades\Log;

trait StripeWebhookHandlerTrait
{
    /**
     * Handle payment succeeded
     */
    protected function handlePaymentSucceeded($paymentIntent): void
    {
        $bookingId = $paymentIntent['metadata']['booking_id'] ?? null;
        
        if (!$bookingId) {
            Log::error('No booking ID in payment intent metadata');
            return;
        }

        $booking = FarmBooking::find($bookingId);
        if (!$booking) {
            Log::error('Booking not found for succeeded payment intent: ' . $bookingId);
            return;
        }

        $booking->markAsPaid($paymentIntent['id']);
        
        // TODO: Send confirmation email to customer
        // TODO: Send notification to farm owner
        
        Log::info('Booking marked as paid: ' . $booking->booking_reference);
    }

    /**
     * Handle payment failed
     */
    protected function handlePaymentFailed($paymentIntent): void
    {
        $bookingId = $paymentIntent['metadata']['booking_id'] ?? null;
        
        if (!$bookingId) {
            Log::error('No booking ID in payment intent metadata');
            return;
        }

        $booking = FarmBooking::find($bookingId);
        if (!$booking) {
            Log::error('Booking not found for failed payment intent: ' . $bookingId);
            return;
        }

        $booking->markAsFailed();
        Log::info('Booking marked as failed: ' . $booking->booking_reference);
    }

    /**
     * Handle payment requires action (for 3D Secure, etc.)
     */
    protected function handlePaymentRequiresAction($paymentIntent): void
    {
        // Log for debugging - frontend will handle the action
        Log::info('Payment requires action: ' . $paymentIntent['id']);
    }
}