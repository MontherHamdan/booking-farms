<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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