<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Farm extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'city_id',
        'area_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'guest_count',
        'not_available_dates',
        'deposit_rate',
        'status',
        'current_step',
        'latitude',
        'longitude',
    ];

    const Pending  = 'pending';
    const Active   = 'active';
    const Rejected = 'rejected';
    const Disabled = 'disabled';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'not_available_dates' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the user that owns the farm.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // In Farm model
    public function scopeActive($query)
    {
        return $query->where('status', self::Active);
    }

    /**
     * Get the city that the farm belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the area that the farm belongs to.
    */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get the features for the farm.
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'farm_feature');
    }

    /**
     * Get the images for the farm.
     */
    public function images(): HasMany
    {
        return $this->hasMany(FarmImage::class);
    }

    /**
     * Get the main image for the farm.
     */
    public function mainImage()
    {
        return $this->hasOne(FarmImage::class)->where('is_main', true);
    }

    /**
     * Get the pricing for the farm.
     */
    public function pricing(): HasMany
    {
        return $this->hasMany(FarmPricing::class);
    }

    /**
     * Get all ratings for the farm.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(FarmRating::class);
    }

    /**
     * Get available price types (where all days have pricing).
     */
    public function getAvailablePriceTypesAttribute(): array
    {
        return $this->pricing->filter(function ($pricing) {
            return $this->isPriceTypeComplete($pricing);
        })->pluck('price_type')->toArray();
    }

    /**
     * Check if a price type has complete pricing for all days.
     */
    private function isPriceTypeComplete($pricing): bool
    {
        $dayPrices = [
            $pricing->saturday_price,
            $pricing->sunday_price,
            $pricing->monday_price,
            $pricing->tuesday_price,
            $pricing->wednesday_price,
            $pricing->thursday_price,
            $pricing->friday_price,
        ];

        foreach ($dayPrices as $price) {
            if (!$price || $price <= 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all offers for the farm.
     */
    public function offers(): HasMany
    {
        return $this->hasMany(FarmOffer::class);
    }

    /**
     * Get the current valid offer for the farm.
     */
    public function currentOffer(): HasOne
    {
        return $this->hasOne(FarmOffer::class)->valid()->orderBy('percentage', 'desc');
    }

    /**
     * Get day use pricing.
     */
    public function dayUsePricing()
    {
        return $this->hasOne(FarmPricing::class)->where('price_type', 'day_use');
    }

    /**
     * Get night pricing.
     */
    public function nightPricing()
    {
        return $this->hasOne(FarmPricing::class)->where('price_type', 'night');
    }

    /**
     * Get full day pricing.
     */
    public function fullDayPricing()
    {
        return $this->hasOne(FarmPricing::class)->where('price_type', 'full_day');
    }

    /**
     * Get all bookings for the farm.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(FarmBooking::class);
    }

    /**
     * Check if farm has a valid offer.
     */
    public function hasValidOffer(): bool
    {
        return $this->offers()->valid()->exists();
    }

    /**
     * Get the current offer percentage (0 if no valid offer).
     */
    public function getCurrentOfferPercentage(): float
    {
        $offer = $this->currentOffer;
        return $offer ? $offer->percentage : 0;
    }

    /**
     * Calculate price after offer discount.
     */
    public function getPriceAfterOffer(float $originalPrice): float
    {
        if (!$this->hasValidOffer()) {
            return $originalPrice;
        }

        $discount = ($originalPrice * $this->getCurrentOfferPercentage()) / 100;
        return max(0, $originalPrice - $discount);
    }

    /**
     * Get the minimum price across all pricing types.
     */
    public function getMinimumPriceAttribute(): float
    {
        $allPrices = [];
        
        foreach ($this->pricing as $pricing) {
            $allPrices[] = $pricing->min_price;
        }
        
        return empty($allPrices) ? 0 : min($allPrices);
    }

    /**
     * Get the maximum price across all pricing types.
     */
    public function getMaximumPriceAttribute(): float
    {
        $allPrices = [];
        
        foreach ($this->pricing as $pricing) {
            $allPrices[] = $pricing->max_price;
        }
        
        return empty($allPrices) ? 0 : max($allPrices);
    }

    /**
     * Get the minimum price after offer discount.
     */
    public function getMinimumPriceAfterOfferAttribute(): float
    {
        return round($this->getPriceAfterOffer($this->minimum_price), 2);
    }

    /**
     * Get the maximum price after offer discount.
     */
    public function getMaximumPriceAfterOfferAttribute(): float
    {
        return round($this->getPriceAfterOffer($this->maximum_price), 2);
    }

    /**
     * Get unavailable dates for a specific price type
     */
    public function getUnavailableDatesForPriceType(string $priceType): array
    {
        if (!$this->not_available_dates) {
            return [];
        }

        $unavailableDates = [];
        $notAvailableData = $this->not_available_dates;

        // Handle both old format (simple array) and new format (price-type specific)
        if ($this->isOldFormatUnavailableDates($notAvailableData)) {
            // Old format - all dates unavailable for all price types
            return $notAvailableData;
        }

        // New format - price-type specific
        $specificDates = $notAvailableData[$priceType] ?? [];
        
        // For full_day requests, also check if both day_use AND night are unavailable
        if ($priceType === 'full_day') {
            $dayUseDates = $notAvailableData['day_use'] ?? [];
            $nightDates = $notAvailableData['night'] ?? [];
            
            // If both day_use and night are unavailable on the same date, full_day is also unavailable
            $bothUnavailable = array_intersect($dayUseDates, $nightDates);
            $unavailableDates = array_merge($specificDates, $bothUnavailable);
        } else {
            // For day_use or night, also include full_day unavailable dates
            $fullDayDates = $notAvailableData['full_day'] ?? [];
            $unavailableDates = array_merge($specificDates, $fullDayDates);
        }

        return array_unique($unavailableDates);
    }

    /**
     * Check if dates format is old (simple array) or new (price-type specific)
     */
    private function isOldFormatUnavailableDates($data): bool
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        // If it's a sequential array of dates, it's old format
        return array_keys($data) === range(0, count($data) - 1);
    }

    /**
     * Set unavailable dates for specific price types
     */
    public function setUnavailableDatesForPriceType(string $priceType, array $dates): void
    {
        $notAvailableData = $this->not_available_dates ?? [];

        // Convert old format to new format if needed
        if ($this->isOldFormatUnavailableDates($notAvailableData)) {
            $notAvailableData = [
                'day_use' => $notAvailableData,
                'night' => $notAvailableData,
                'full_day' => $notAvailableData,
            ];
        }

        // Set dates for the specific price type
        $notAvailableData[$priceType] = $dates;

        $this->not_available_dates = $notAvailableData;
    }

    /**
     * Add unavailable date for specific price type
     */
    public function addUnavailableDateForPriceType(string $priceType, string $date): void
    {
        $currentDates = $this->getUnavailableDatesForPriceType($priceType);
        
        if (!in_array($date, $currentDates)) {
            $currentDates[] = $date;
            $this->setUnavailableDatesForPriceType($priceType, $currentDates);
        }
    }

    /**
     * Remove unavailable date for specific price type
     */
    public function removeUnavailableDateForPriceType(string $priceType, string $date): void
    {
        $currentDates = $this->getUnavailableDatesForPriceType($priceType);
        
        $updatedDates = array_filter($currentDates, function($d) use ($date) {
            return $d !== $date;
        });
        
        $this->setUnavailableDatesForPriceType($priceType, array_values($updatedDates));
    }

    /**
     * Get formatted not available dates (backward compatibility)
     */
    public function getFormattedNotAvailableDatesAttribute(): array
    {
        if (!$this->not_available_dates) {
            return [];
        }

        $notAvailableData = $this->not_available_dates;

        // Handle old format
        if ($this->isOldFormatUnavailableDates($notAvailableData)) {
            return array_map(function ($date) {
                return [
                    'date' => $date,
                    'formatted' => \Carbon\Carbon::parse($date)->format('Y-m-d'),
                    'human_readable' => \Carbon\Carbon::parse($date)->format('M d, Y'),
                    'price_types' => ['day_use', 'night', 'full_day'], // All types for old format
                ];
            }, $notAvailableData);
        }

        // Handle new format - create a consolidated view
        $allDates = [];
        foreach (['day_use', 'night', 'full_day'] as $priceType) {
            $dates = $notAvailableData[$priceType] ?? [];
            foreach ($dates as $date) {
                if (!isset($allDates[$date])) {
                    $allDates[$date] = [];
                }
                $allDates[$date][] = $priceType;
            }
        }

        $formatted = [];
        foreach ($allDates as $date => $priceTypes) {
            $formatted[] = [
                'date' => $date,
                'formatted' => \Carbon\Carbon::parse($date)->format('Y-m-d'),
                'human_readable' => \Carbon\Carbon::parse($date)->format('M d, Y'),
                'price_types' => array_unique($priceTypes),
            ];
        }

        return $formatted;
    }

    /**
     * Get users who favorited this farm.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorite_farms', 'farm_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Check if this farm is favorited by a specific user.
     */
    public function isFavoriteByUser($userId): bool
    {
        if (!$userId) {
            return false;
        }
        
        return $this->favoritedBy()->where('user_id', $userId)->exists();
    }

    /**
     * Get the average rating for this farm.
     */
    public function getAverageRatingAttribute(): float
    {
        return round($this->ratings()->avg('rating') ?? 0, 1);
    }

    /**
     * Get the total number of ratings for this farm.
     */
    public function getTotalRatingsAttribute(): int
    {
        return $this->ratings()->count();
    }

    /**
     * Get rating breakdown (count of each rating value).
     */
    public function getRatingBreakdownAttribute(): array
    {
        $breakdown = [
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0
        ];
        
        // Get all ratings grouped by star level
        $ratings = $this->ratings()
            ->selectRaw('FLOOR(CAST(rating AS DECIMAL(2,1))) as star_level, COUNT(*) as count')
            ->groupBy('star_level')
            ->get();
        
        foreach ($ratings as $ratingData) {
            $starLevel = (string)$ratingData->star_level;
            if (isset($breakdown[$starLevel])) {
                $breakdown[$starLevel] = $ratingData->count;
            }
        }
        
        return $breakdown;
    }

    /**
     * Get the latest ratings for this farm.
     */
    public function getLatestRatingsAttribute()
    {
        return $this->ratings()
                    ->with('user:id,name,avatar')
                    ->latest()
                    ->limit(5)
                    ->get();
    }

    /**
     * Get average rating for display: null if less than 3 ratings, actual average if 3+ ratings.
     */
    public function getDisplayAverageRatingAttribute(): ?float
    {
        $ratingsCount = $this->total_ratings;
        
        if ($ratingsCount >= 3) {
            return $this->average_rating;
        }
        
        return null;
    }

    /**
     * Get the coordinates as a formatted string
     */
    public function getCoordinatesAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return $this->latitude . ', ' . $this->longitude;
        }
        return null;
    }

    /**
     * Check if farm has coordinates
     */
    public function hasCoordinates()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }
}