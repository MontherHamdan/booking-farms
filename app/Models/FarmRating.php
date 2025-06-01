<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmRating extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'farm_id',
        'user_id',
        'rating',
        'review',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'decimal:1',
    ];

    /**
     * Get the farm that this rating belongs to.
     */
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * Get the user who made this rating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Validate rating value (1.0 to 5.0 in 0.5 increments).
     */
    public static function isValidRating($rating): bool
    {
        // Check if rating is between 1.0 and 5.0
        if ($rating < 1.0 || $rating > 5.0) {
            return false;
        }

        // Check if rating is in 0.5 increments
        return ($rating * 2) == floor($rating * 2);
    }

    /**
     * Get formatted rating (e.g., "4.5 stars").
     */
    public function getFormattedRatingAttribute(): string
    {
        return $this->rating . ' star' . ($this->rating != 1 ? 's' : '');
    }

    /**
     * Get truncated review.
     */
    public function getTruncatedReviewAttribute(): string
    {
        if (!$this->review) {
            return '';
        }
        
        return strlen($this->review) > 100 ? substr($this->review, 0, 100) . '...' : $this->review;
    }
}