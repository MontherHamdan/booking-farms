<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'start_date',
        'end_date',
        'discount_type',
        'discount_value',
        'max_discount',
        'usage_limit',
        'platform',
        'cities',
        'usage_limit_per_user_type',
        'usage_limit_per_user_count',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'cities' => 'array',
        'is_active' => 'boolean',
    ];

    // Discount type constants
    const DISCOUNT_TYPE_PERCENTAGE = 'percentage';
    const DISCOUNT_TYPE_FIXED_AMOUNT = 'fixed_amount';

    // Platform constants
    const PLATFORM_WEB = 'web';
    const PLATFORM_MOBILE = 'mobile';
    const PLATFORM_BOTH = 'both';

    // Usage limit per user constants
    const USAGE_LIMIT_SINGLE = 'single';
    const USAGE_LIMIT_MULTIPLE = 'multiple';
    const USAGE_LIMIT_UNLIMITED = 'unlimited';

    /**
     * Boot method to ensure code is uppercase
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($coupon) {
            $coupon->code = strtoupper($coupon->code);
        });

        static::updating(function ($coupon) {
            $coupon->code = strtoupper($coupon->code);
        });
    }

    /**
     * RELATIONSHIPS
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(FarmBooking::class);
    }

    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'coupon_cities');
    }

    /**
     * SCOPES
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->active()
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    public function scopeForPlatform($query, string $platform)
    {
        return $query->where(function ($q) use ($platform) {
            $q->where('platform', $platform)
              ->orWhere('platform', self::PLATFORM_BOTH);
        });
    }

    public function scopeForCity($query, int $cityId)
    {
        return $query->where(function ($q) use ($cityId) {
            $q->whereNull('cities')  // All cities
              ->orWhereJsonContains('cities', $cityId);  // Specific city
        });
    }

    /**
     * VALIDATION METHODS
     */
    public function isValid(): bool
    {
        return $this->is_active 
            && $this->start_date <= now() 
            && $this->end_date >= now();
    }

    public function isValidForPlatform(string $platform): bool
    {
        return $this->platform === self::PLATFORM_BOTH || $this->platform === $platform;
    }

    public function isValidForCity(?int $cityId): bool
    {
        // If no cities specified, valid for all cities
        if (empty($this->cities)) {
            return true;
        }

        // If city ID provided, check if it's in the allowed cities
        return $cityId && in_array($cityId, $this->cities);
    }

    public function hasReachedUsageLimit(): bool
    {
        if (!$this->usage_limit) {
            return false; // Unlimited usage
        }

        return $this->usages()->count() >= $this->usage_limit;
    }

    public function hasUserReachedLimit(int $userId): bool
    {
        if ($this->usage_limit_per_user_type === self::USAGE_LIMIT_UNLIMITED) {
            return false;
        }

        $userUsageCount = $this->usages()->where('user_id', $userId)->count();

        if ($this->usage_limit_per_user_type === self::USAGE_LIMIT_SINGLE) {
            return $userUsageCount >= 1;
        }

        if ($this->usage_limit_per_user_type === self::USAGE_LIMIT_MULTIPLE) {
            return $userUsageCount >= ($this->usage_limit_per_user_count ?? 1);
        }

        return false;
    }

    public function canBeUsedByUser(int $userId, ?int $cityId = null, string $platform = 'web'): array
    {
        $errors = [];

        if (!$this->isValid()) {
            if (!$this->is_active) {
                $errors[] = __('coupon.inactive');
            } elseif ($this->start_date > now()) {
                $errors[] = __('coupon.not_started');
            } elseif ($this->end_date < now()) {
                $errors[] = __('coupon.expired');
            }
        }

        if (!$this->isValidForPlatform($platform)) {
            $errors[] = __('coupon.platform_not_allowed');
        }

        if (!$this->isValidForCity($cityId)) {
            $errors[] = __('coupon.city_not_allowed');
        }

        if ($this->hasReachedUsageLimit()) {
            $errors[] = __('coupon.usage_limit_reached');
        }

        if ($this->hasUserReachedLimit($userId)) {
            $errors[] = __('coupon.user_limit_reached');
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * DISCOUNT CALCULATION
     */
    public function calculateDiscount(float $amount): array
    {
        $discountAmount = 0;

        if ($this->discount_type === self::DISCOUNT_TYPE_PERCENTAGE) {
            $discountAmount = ($amount * $this->discount_value) / 100;
            
            // Apply max discount limit if set
            if ($this->max_discount && $discountAmount > $this->max_discount) {
                $discountAmount = $this->max_discount;
            }
        } elseif ($this->discount_type === self::DISCOUNT_TYPE_FIXED_AMOUNT) {
            $discountAmount = min($this->discount_value, $amount); // Can't discount more than total
        }

        $finalAmount = max(0, $amount - $discountAmount);

        return [
            'original_amount' => $amount,
            'discount_amount' => round($discountAmount, 2),
            'final_amount' => round($finalAmount, 2),
            'discount_percentage' => $amount > 0 ? round(($discountAmount / $amount) * 100, 2) : 0
        ];
    }

    /**
     * USAGE TRACKING
     */
    public function markAsUsed(int $userId, int $bookingId): CouponUsage
    {
        return $this->usages()->create([
            'user_id' => $userId,
            'booking_id' => $bookingId,
            'used_at' => now(),
        ]);
    }

    /**
     * ATTRIBUTES
     */
    public function getUsageCountAttribute(): int
    {
        return $this->usages()->count();
    }

    public function getRemainingUsagesAttribute(): ?int
    {
        if (!$this->usage_limit) {
            return null; // Unlimited
        }

        return max(0, $this->usage_limit - $this->usage_count);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date < now();
    }

    public function getIsStartedAttribute(): bool
    {
        return $this->start_date <= now();
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if (!$this->is_started) {
            return 'not_started';
        }

        if ($this->is_expired) {
            return 'expired';
        }

        if ($this->hasReachedUsageLimit()) {
            return 'usage_limit_reached';
        }

        return 'active';
    }

    public function getDiscountDescriptionAttribute(): string
    {
        if ($this->discount_type === self::DISCOUNT_TYPE_PERCENTAGE) {
            $description = $this->discount_value . '% off';
            if ($this->max_discount) {
                $description .= ' (max ' . number_format($this->max_discount, 2) . ')';
            }
            return $description;
        }
    
        return number_format($this->discount_value, 2) . ' off';
    }

    public function getCityNamesAttribute(): array
    {
        if (empty($this->cities)) {
            return ['All Cities'];
        }

        return City::whereIn('id', $this->cities)->pluck('name_en')->toArray();
    }

    public function getPlatformLabelAttribute(): string
    {
        return match($this->platform) {
            self::PLATFORM_WEB => 'Web Only',
            self::PLATFORM_MOBILE => 'Mobile Only',
            self::PLATFORM_BOTH => 'Web & Mobile',
            default => 'Unknown'
        };
    }

    public function getUsageLimitDescriptionAttribute(): string
    {
        if (!$this->usage_limit) {
            return 'Unlimited uses';
        }

        $remaining = $this->remaining_usages;
        return "{$remaining} / {$this->usage_limit} uses remaining";
    }

    public function getUserLimitDescriptionAttribute(): string
    {
        return match($this->usage_limit_per_user_type) {
            self::USAGE_LIMIT_SINGLE => 'One use per user',
            self::USAGE_LIMIT_MULTIPLE => ($this->usage_limit_per_user_count ?? 1) . ' uses per user',
            self::USAGE_LIMIT_UNLIMITED => 'Unlimited uses per user',
            default => 'Unknown'
        };
    }
}