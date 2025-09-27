<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_ar',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all farm owner bank accounts for this bank
     */
    public function farmOwnerBankAccounts(): HasMany
    {
        return $this->hasMany(FarmOwnerBankAccount::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get name based on locale
     */
    public function getName($locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'ar' ? $this->name_ar : $this->name_en;
    }
}