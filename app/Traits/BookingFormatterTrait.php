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
     * Format dates for display with localized day names
     */
    protected function formatDatesForDisplay(array $dates): array
    {
        return array_map(function ($date) {
            $carbonDate = Carbon::parse($date);
            return [
                'date' => $date,
                'day' => $this->localizeNumbers($carbonDate->format('d')),
                'month' => $carbonDate->format('M'),
                'year' => $this->localizeNumbers($carbonDate->format('Y')),
                'day_name' => $this->getLocalizedDayName($carbonDate),
                'full' => $carbonDate->format('F d, Y'),
                'short' => $carbonDate->format('M d'),
            ];
        }, $dates);
    }

    /**
     * Get localized day name based on app locale
     */
    private function getLocalizedDayName(Carbon $date): string
    {
        $dayNumber = $date->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
        
        // Map Carbon's dayOfWeek to our translation keys
        $dayKeys = [
            0 => 'sunday',
            1 => 'monday', 
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday'
        ];
        
        $dayKey = $dayKeys[$dayNumber];
        
        return __("farm.days.{$dayKey}");
    }

    /**
     * Get formatted booking period
     */
    private function getCorrectBookingPeriod(array $processedDates, string $priceType, $pricing): array
    {
        $startDate = Carbon::parse(min($processedDates));
        $endDate = Carbon::parse(max($processedDates));

        // Handle night bookings specifically
        if ($priceType === 'night' && $pricing->start_time && $pricing->end_time) {
            $startTime = Carbon::parse($pricing->start_time);
            $endTime = Carbon::parse($pricing->end_time);

            // If end time is earlier than start time, it means it goes to next day
            if ($endTime->format('H:i') < $startTime->format('H:i')) {
                $endDate = $startDate->copy()->addDay();
            }
        }

        // Handle full_day bookings that might include overnight portions
        if ($priceType === 'full_day' && $pricing->start_time && $pricing->end_time) {
            // For full day, we need to check if it includes overnight timing
            // This would typically be determined by checking if there's a night pricing component
            // For now, we'll keep the original end_date for full_day unless there's specific logic needed
        }

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'duration_days' => $startDate->diffInDays($endDate) + 1,
            'formatted_period' => $startDate->format('M d') . ' - ' . $endDate->format('M d, Y')
        ];
    }

    /**
     * Get formatted time data with AM/PM format and duration calculation
     */
    private function getFormattedTimeData($pricing): array
    {
        if (!$pricing->start_time || !$pricing->end_time) {
            return [
                'start_time_12h' => null,
                'end_time_12h' => null,
                'time_range_12h' => null,
                'duration_hours' => 0,
            ];
        }

        $startTime = Carbon::parse($pricing->start_time);
        $endTime = Carbon::parse($pricing->end_time);

        // Format times in 12-hour format with localized AM/PM
        $startTime12h = $this->formatTimeWithLocalizedAmPm($startTime);
        $endTime12h = $this->formatTimeWithLocalizedAmPm($endTime);

        // Create time range string
        $timeRange12h = $startTime12h . ' - ' . $endTime12h;

        // Calculate duration in hours
        $durationHours = $this->calculateDurationHours($startTime, $endTime);

        return [
            'start_time_12h' => $startTime12h,
            'end_time_12h' => $endTime12h,
            'time_range_12h' => $timeRange12h,
            'duration_hours' => $durationHours,
        ];
    }

    /**
     * Format time with localized AM/PM indicators and numbers
     */
    private function formatTimeWithLocalizedAmPm(Carbon $time): string
    {
        $timeString = $time->format('g:i');
        $isAm = $time->format('A') === 'AM';
        
        $amPm = $isAm 
            ? __('farm.time_format.am') 
            : __('farm.time_format.pm');
        
        // Localize the numbers if the locale is Arabic
        $localizedTimeString = $this->localizeNumbers($timeString);
            
        return $localizedTimeString . ' ' . $amPm;
    }

    /**
     * Convert Latin numerals to Arabic numerals if locale is Arabic
     */
    private function localizeNumbers($text): string
    {
        // Check if current locale is Arabic
        if (app()->getLocale() === 'ar') {
            // Map of Latin to Arabic numerals
            $arabicNumerals = [
                '0' => '٠',
                '1' => '١', 
                '2' => '٢',
                '3' => '٣',
                '4' => '٤',
                '5' => '٥',
                '6' => '٦',
                '7' => '٧',
                '8' => '٨',
                '9' => '٩'
            ];
            
            return strtr($text, $arabicNumerals);
        }
        
        return $text;
    }

    /**
     * Calculate duration in hours between start and end time
     * Handles overnight bookings correctly
     */
    private function calculateDurationHours(Carbon $startTime, Carbon $endTime): float
    {
        // Clone to avoid modifying original times
        $start = $startTime->copy();
        $end = $endTime->copy();

        // If end time is earlier than start time, it means it goes to next day
        if ($end->format('H:i') < $start->format('H:i')) {
            $end->addDay();
        }

        // Calculate difference in hours
        $diffInMinutes = $end->diffInMinutes($start);
        $durationHours = $diffInMinutes / 60;

        return round($durationHours, 1); // Round to 1 decimal place
    }

    /**
     * Get localized time range
     */
    private function getLocalizedTimeRange(): ?string
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        $startTime12h = $this->formatTimeWithLocalizedAmPm($startTime);
        $endTime12h = $this->formatTimeWithLocalizedAmPm($endTime);

        if (app()->getLocale() === 'ar') {
            return "من {$startTime12h} الى {$endTime12h}";
        } else {
            return "From {$startTime12h} to {$endTime12h}";
        }
    }

    /**
     * Get duration in hours
     */
    private function getDurationHours(): ?float
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        return $this->calculateDurationHours($startTime, $endTime);
    }
}