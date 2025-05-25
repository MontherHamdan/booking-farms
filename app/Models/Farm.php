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
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'passengers_count',
        'not_available_dates',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'not_available_dates' => 'array',
    ];

    /**
     * Get the user that owns the farm.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the city that the farm belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
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
        return $this->getPriceAfterOffer($this->minimum_price);
    }

    /**
     * Get the maximum price after offer discount.
     */
    public function getMaximumPriceAfterOfferAttribute(): float
    {
        return $this->getPriceAfterOffer($this->maximum_price);
    }

    /**
     * Get formatted not available dates.
     */
    public function getFormattedNotAvailableDatesAttribute(): array
    {
        if (!$this->not_available_dates) {
            return [];
        }

        return array_map(function ($date) {
            return [
                'date' => $date,
                'formatted' => \Carbon\Carbon::parse($date)->format('Y-m-d'),
                'human_readable' => \Carbon\Carbon::parse($date)->format('M d, Y'),
            ];
        }, $this->not_available_dates);
    }
}