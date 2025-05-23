<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmPricing extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'farm_id',
        'price_type',
        'saturday_price',
        'sunday_price',
        'monday_price',
        'tuesday_price',
        'wednesday_price',
        'thursday_price',
        'friday_price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'saturday_price' => 'decimal:2',
        'sunday_price' => 'decimal:2',
        'monday_price' => 'decimal:2',
        'tuesday_price' => 'decimal:2',
        'wednesday_price' => 'decimal:2',
        'thursday_price' => 'decimal:2',
        'friday_price' => 'decimal:2',
    ];

    /**
     * Get the farm that owns the pricing.
     */
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * Get the minimum price for this pricing type.
     */
    public function getMinPriceAttribute(): float
    {
        return min([
            $this->saturday_price,
            $this->sunday_price,
            $this->monday_price,
            $this->tuesday_price,
            $this->wednesday_price,
            $this->thursday_price,
            $this->friday_price,
        ]);
    }

    /**
     * Get the maximum price for this pricing type.
     */
    public function getMaxPriceAttribute(): float
    {
        return max([
            $this->saturday_price,
            $this->sunday_price,
            $this->monday_price,
            $this->tuesday_price,
            $this->wednesday_price,
            $this->thursday_price,
            $this->friday_price,
        ]);
    }

    /**
     * Get price for a specific day.
     */
    public function getPriceForDay(string $day): float
    {
        $dayColumn = strtolower($day) . '_price';
        return $this->$dayColumn ?? 0;
    }

    /**
     * Get all days with their prices as an array.
     */
    public function getDayPricesAttribute(): array
    {
        return [
            'saturday' => $this->saturday_price,
            'sunday' => $this->sunday_price,
            'monday' => $this->monday_price,
            'tuesday' => $this->tuesday_price,
            'wednesday' => $this->wednesday_price,
            'thursday' => $this->thursday_price,
            'friday' => $this->friday_price,
        ];
    }
}