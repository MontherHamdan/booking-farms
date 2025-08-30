<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmOwnerBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_type',
        'iban',
        'bank_name',
        'cliq_alias',
        'cliq_phone',
        'account_holder_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Account Types
    const TYPE_IBAN = 'iban';
    const TYPE_CLIQ = 'cliq';

    /**
     * Get the user (farm owner) that owns this bank account
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if account is IBAN type
     */
    public function isIbanAccount(): bool
    {
        return $this->account_type === self::TYPE_IBAN;
    }

    /**
     * Check if account is CLIQ type
     */
    public function isCliqAccount(): bool
    {
        return $this->account_type === self::TYPE_CLIQ;
    }

    /**
     * Get formatted account details for display
     */
    public function getFormattedAccountDetailsAttribute(): array
    {
        if ($this->isIbanAccount()) {
            return [
                'type' => 'IBAN Transfer',
                'iban' => $this->iban,
                'bank_name' => $this->bank_name,
                'account_holder' => $this->account_holder_name,
            ];
        }

        if ($this->isCliqAccount()) {
            return [
                'type' => 'CLIQ Transfer',
                'alias' => $this->cliq_alias,
                'phone' => $this->cliq_phone,
                'account_holder' => $this->account_holder_name,
            ];
        }

        return [];
    }

    /**
     * Get account type label
     */
    public function getAccountTypeLabel(): string
    {
        return match($this->account_type) {
            self::TYPE_IBAN => 'IBAN Transfer',
            self::TYPE_CLIQ => 'CLIQ Transfer',
            default => ucfirst($this->account_type),
        };
    }

    /**
     * Get primary identifier for the account
     */
    public function getPrimaryIdentifierAttribute(): string
    {
        if ($this->isIbanAccount()) {
            return $this->iban ?: 'No IBAN';
        }

        if ($this->isCliqAccount()) {
            $identifiers = [];
            if ($this->cliq_alias) {
                $identifiers[] = $this->cliq_alias;
            }
            if ($this->cliq_phone) {
                $identifiers[] = $this->cliq_phone;
            }
            return implode(' / ', $identifiers) ?: 'No CLIQ details';
        }

        return 'Unknown';
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeIban($query)
    {
        return $query->where('account_type', self::TYPE_IBAN);
    }

    public function scopeCliq($query)
    {
        return $query->where('account_type', self::TYPE_CLIQ);
    }
}