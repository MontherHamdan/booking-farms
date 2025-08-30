<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    // Setting Keys
    const TRANSFER_FREQUENCY_DAYS = 'transfer_frequency_days';
    const MINIMUM_TRANSFER_AMOUNT = 'minimum_transfer_amount';
    const DEFAULT_COMMISSION_RATE = 'default_commission_rate';
    const MINIMUM_COMMISSION_RATE = 'minimum_commission_rate';
    const MAXIMUM_COMMISSION_RATE = 'maximum_commission_rate';

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, ?string $description = null): void
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
            ]
        );

        // Clear cache
        Cache::forget("setting.{$key}");
    }

    /**
     * Get transfer frequency in days
     */
    public static function getTransferFrequencyDays(): int
    {
        return (int) self::get(self::TRANSFER_FREQUENCY_DAYS, 14);
    }

    /**
     * Get minimum transfer amount
     */
    public static function getMinimumTransferAmount(): float
    {
        return (float) self::get(self::MINIMUM_TRANSFER_AMOUNT, 50.00);
    }

    /**
     * Set transfer frequency days
     */
    public static function setTransferFrequencyDays(int $days): void
    {
        self::set(self::TRANSFER_FREQUENCY_DAYS, $days, 'Number of days between manual transfers to farm owners');
    }

    /**
     * Set minimum transfer amount
     */
    public static function setMinimumTransferAmount(float $amount): void
    {
        self::set(self::MINIMUM_TRANSFER_AMOUNT, $amount, 'Minimum amount required for manual transfer');
    }

    /**
     * Get default commission rate for new farm owners
     */
    public static function getDefaultCommissionRate(): float
    {
        return (float) self::get(self::DEFAULT_COMMISSION_RATE, 5.00);
    }

        /**
     * Get minimum allowed commission rate
     */
    public static function getMinimumCommissionRate(): float
    {
        return (float) self::get(self::MINIMUM_COMMISSION_RATE, 0.00);
    }

    /**
     * Get maximum allowed commission rate
     */
    public static function getMaximumCommissionRate(): float
    {
        return (float) self::get(self::MAXIMUM_COMMISSION_RATE, 50.00);
    }

    /**
     * Set default commission rate
     */
    public static function setDefaultCommissionRate(float $rate): void
    {
        $minRate = self::getMinimumCommissionRate();
        $maxRate = self::getMaximumCommissionRate();
        
        if ($rate < $minRate || $rate > $maxRate) {
            throw new \InvalidArgumentException("Commission rate must be between {$minRate}% and {$maxRate}%");
        }
        
        self::set(self::DEFAULT_COMMISSION_RATE, $rate, 'Default commission rate for new farm owners');
    }

    /**
     * Get all settings as key-value pairs
     */
    public static function getAllSettings(): array
    {
        return self::pluck('value', 'key')->toArray();
    }

    /**
     * Get formatted settings for dashboard
     */
    public static function getFormattedSettings(): array
    {
        return [
            'transfer_frequency' => [
                'label' => 'Transfer Frequency',
                'value' => self::getTransferFrequencyDays() . ' days',
                'description' => 'How often manual transfers are made to farm owners',
            ],
            'minimum_amount' => [
                'label' => 'Minimum Transfer Amount',
                'value' => 'AED ' . number_format(self::getMinimumTransferAmount(), 2),
                'description' => 'Minimum balance required before transfer is eligible',
            ],
            'commission' => [
                'label' => 'Default Commission Rate',
                'value' => self::getDefaultCommissionRate() . '%',
                'description' => 'Default commission rate for new farm owners',
            ],
            'commission_limits' => [
                'label' => 'Commission Rate Limits',
                'value' => self::getMinimumCommissionRate() . '% - ' . self::getMaximumCommissionRate() . '%',
                'description' => 'Allowed commission rate range',
            ],
        ];
    }

    /**
     * Boot method to clear cache when settings change
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            Cache::forget("setting.{$setting->key}");
        });

        static::deleted(function ($setting) {
            Cache::forget("setting.{$setting->key}");
        });
    }
}