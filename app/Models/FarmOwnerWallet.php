<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class FarmOwnerWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'pending_balance',
        'total_earned',
        'total_paid_out',
        'platform_commission_rate',
        'is_active',
        'last_transaction_at',
        'last_payment_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_paid_out' => 'decimal:2',
        'platform_commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'last_transaction_at' => 'datetime',
        'last_payment_at' => 'datetime',
    ];

    /**
     * Get the farm owner (user) that owns this wallet
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transactions for this wallet
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id');
    }

    /**
     * Get recent transactions
     */
    public function recentTransactions(int $limit = 10)
    {
        return $this->transactions()
                    ->latest()
                    ->limit($limit);
    }

    /**
     * Add money to wallet (NEW: supports pending balance)
     */
    public function addFunds(float $amount, string $description, array $metadata = [], bool $isPending = false): WalletTransaction
    {
        $balanceBefore = $this->balance;
        $pendingBalanceBefore = $this->pending_balance;
        
        if ($isPending) {
            // Add to pending balance
            $this->increment('pending_balance', $amount);
            $transactionType = 'pending_earning';
        } else {
            // Add to confirmed balance
            $this->increment('balance', $amount);
            $this->increment('total_earned', $amount);
            $transactionType = 'earning_confirmed'; // ✅ FIXED
        }
        
        $this->update(['last_transaction_at' => now()]);
    
        return $this->transactions()->create([
            'reference' => $this->generateTransactionReference('ADD'),
            'type' => $transactionType,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->fresh()->balance,
            'pending_balance_before' => $pendingBalanceBefore,
            'pending_balance_after' => $this->fresh()->pending_balance,
            'description' => $description,
            'status' => 'completed',
            'metadata' => $metadata,
            'processed_at' => now(),
        ]);
    }

    /**
     * Move funds from pending to confirmed balance (NEW)
     */
    public function confirmPendingFunds(float $amount, string $description, array $metadata = []): WalletTransaction
    {
        if ($this->pending_balance < $amount) {
            throw new \InvalidArgumentException('Insufficient pending balance');
        }

        $balanceBefore = $this->balance;
        $pendingBalanceBefore = $this->pending_balance;

        // Move from pending to confirmed
        $this->decrement('pending_balance', $amount);
        $this->increment('balance', $amount);
        $this->increment('total_earned', $amount);
        $this->update(['last_transaction_at' => now()]);

        return $this->transactions()->create([
            'reference' => $this->generateTransactionReference('CONF'),
            'type' => 'earning_confirmed',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->fresh()->balance,
            'pending_balance_before' => $pendingBalanceBefore,
            'pending_balance_after' => $this->fresh()->pending_balance,
            'description' => $description,
            'status' => 'completed',
            'metadata' => $metadata,
            'processed_at' => now(),
        ]);
    }

    /**
     * Deduct money from wallet
     */
    public function deductFunds(float $amount, string $type, string $description, array $metadata = []): WalletTransaction
    {
        if ($this->balance < $amount) {
            throw new \InvalidArgumentException('Insufficient wallet balance');
        }

        $balanceBefore = $this->balance;
        $pendingBalanceBefore = $this->pending_balance;
        
        $this->decrement('balance', $amount);
        
        if ($type === 'manual_payment') {
            $this->increment('total_paid_out', $amount);
        }
        
        $this->update(['last_transaction_at' => now()]);

        return $this->transactions()->create([
            'reference' => $this->generateTransactionReference('DED'),
            'type' => $type,
            'amount' => -$amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->fresh()->balance,
            'pending_balance_before' => $pendingBalanceBefore,
            'pending_balance_after' => $this->fresh()->pending_balance,
            'description' => $description,
            'status' => 'completed',
            'metadata' => $metadata,
            'processed_at' => now(),
        ]);
    }

    /**
     * Get manual payments for this wallet 
     */
    public function manualPayments(): HasMany
    {
        return $this->hasMany(ManualPayment::class, 'user_id', 'user_id');
    }

    /**
     * Check if wallet is eligible for payment (only confirmed balance)
     */
    public function isEligibleForPayment(): bool
    {
        $minimumAmount = PlatformSetting::getMinimumTransferAmount();
        return $this->balance >= $minimumAmount; // Only confirmed balance
    }

    /**
     * Check if ready for next payment based on frequency setting
     */
    public function isReadyForPayment(): bool
    {
        $frequencyDays = PlatformSetting::getTransferFrequencyDays();
        return $this->getDaysSinceLastPayment() >= $frequencyDays && $this->isEligibleForPayment();
    }

    /**
     * Get days since last payment
     */
    public function getDaysSinceLastPayment(): int
    {
        if (!$this->last_payment_at) {
            return $this->created_at->diffInDays(now());
        }

        return $this->last_payment_at->diffInDays(now());
    }

    /**
     * Get total available balance (confirmed + pending) for display purposes
     */
    public function getTotalAvailableBalance(): float
    {
        return $this->balance + $this->pending_balance;
    }

    /**
     * Get wallet statistics (UPDATED)
     */
    public function getStatistics(): array
    {
        return [
            'total_earned' => $this->total_earned,
            'current_balance' => $this->balance,
            'pending_balance' => $this->pending_balance, // NEW
            'total_available' => $this->getTotalAvailableBalance(), // NEW
            'total_paid_out' => $this->total_paid_out,
            'total_transactions' => $this->transactions()->count(),
            'commission_rate' => $this->platform_commission_rate,
            'last_transaction' => $this->last_transaction_at,
            'last_payment' => $this->last_payment_at,
            'days_since_last_payment' => $this->getDaysSinceLastPayment(),
            'is_eligible_for_payment' => $this->isEligibleForPayment(),
            'is_ready_for_payment' => $this->isReadyForPayment(),
        ];
    }

    /**
     * Generate transaction reference
     */
    public function generateTransactionReference(string $prefix = 'TXN'): string 
    {
        return $prefix . '-' . strtoupper(uniqid()) . '-' . $this->user_id;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

    public function scopeWithPendingBalance($query)
    {
        return $query->where('pending_balance', '>', 0);
    }
}