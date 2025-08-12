<?php

namespace App\Traits;

use Carbon\Carbon;

trait BookingFormatterTrait
{
    /**
     * Build booking description for Stripe
     */
    protected function buildBookingDescription($booking, $farm): string
    {
        $dates = implode(', ', array_map(function ($date) {
            return Carbon::parse($date)->format('M d, Y');
        }, $booking->booking_dates));

        $paymentType = $booking->payment_option === 'deposit' ? ' (Deposit)' : ' (Full Payment)';

        return "Farm Booking: {$farm->name_en}{$paymentType} | Dates: {$dates} | Guests: {$booking->guest_count}";
    }

    /**
     * Format dates for display
     */
    protected function formatDatesForDisplay(array $dates): array
    {
        return array_map(function ($date) {
            $carbonDate = Carbon::parse($date);
            return [
                'date' => $date,
                'day' => $carbonDate->format('d'),
                'month' => $carbonDate->format('M'),
                'year' => $carbonDate->format('Y'),
                'day_name' => $carbonDate->format('l'),
                'full' => $carbonDate->format('F d, Y'),
                'short' => $carbonDate->format('M d'),
            ];
        }, $dates);
    }

    /**
     * Get formatted booking period
     */
    protected function getFormattedBookingPeriod(array $dates): array
    {
        $startDate = Carbon::parse(min($dates));
        $endDate = Carbon::parse(max($dates));
        
        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'duration_days' => count($dates),
            'formatted_period' => $startDate->format('M d') . ' - ' . $endDate->format('M d, Y')
        ];
    }
}