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
        'total_paid_out', // RENAMED from total_withdrawn
        'platform_commission_rate',
        'is_active',
        'last_transaction_at',
        'last_payment_at', // NEW: track last manual payment
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_paid_out' => 'decimal:2', // RENAMED
        'platform_commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'last_transaction_at' => 'datetime',
        'last_payment_at' => 'datetime', // NEW
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
     * Add money to wallet
     */
    public function addFunds(float $amount, string $description, array $metadata = []): WalletTransaction
    {
        $balanceBefore = $this->balance;
        $this->increment('balance', $amount);
        $this->increment('total_earned', $amount);
        $this->update(['last_transaction_at' => now()]);

        return $this->transactions()->create([
            'reference' => $this->generateTransactionReference('ADD'),
            'type' => 'earning',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->fresh()->balance,
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
        if ($this->available_balance < $amount) {
            throw new \InvalidArgumentException('Insufficient wallet balance');
        }

        $balanceBefore = $this->balance;
        $this->decrement('balance', $amount);
        
        if ($type === 'manual_payment') {
            $this->increment('total_paid_out', $amount);
        }
        
        $this->update(['last_transaction_at' => now()]);

        return $this->transactions()->create([
            'reference' => $this->generateTransactionReference('DED'),
            'type' => $type,
            'amount' => -$amount, // Negative for deductions
            'balance_before' => $balanceBefore,
            'balance_after' => $this->fresh()->balance,
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
     * Process manual payment (NEW)
     */
    public function processManualPayment(float $amount, string $paymentMethod, array $bankDetails, int $adminId, ?string $notes = null): ManualPayment
    {
        if ($this->balance < $amount) {
            throw new \InvalidArgumentException('Insufficient wallet balance');
        }

        DB::transaction(function() use ($amount, $paymentMethod, $bankDetails, $adminId, $notes) {
            // Deduct from wallet
            $this->deductFunds($amount, 'manual_payment', "Manual payment processed", [
                'payment_method' => $paymentMethod,
                'processed_by' => $adminId,
            ]);

            // Create payment record
            $payment = ManualPayment::createPaymentRecord(
                $this->user_id,
                $amount,
                $paymentMethod,
                $bankDetails,
                $adminId,
                $notes
            );

            // Update wallet totals
            $this->increment('total_paid_out', $amount);
            $this->update(['last_payment_at' => now()]);

            return $payment;
        });
    }

    /**
     * Check if wallet is eligible for payment 
     */
    public function isEligibleForPayment(): bool
    {
        $minimumAmount = PlatformSetting::getMinimumTransferAmount();
        return $this->balance >= $minimumAmount;
    }

    /**
     * Check if ready for next payment based on frequency setting (NEW)
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
     * Get wallet statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_earnings' => $this->total_earned,
            'current_balance' => $this->balance,
            'total_paid_out' => $this->total_paid_out, // RENAMED
            'pending_balance' => $this->pending_balance,
            'total_transactions' => $this->transactions()->count(),
            'commission_rate' => $this->platform_commission_rate,
            'last_transaction' => $this->last_transaction_at,
            'last_payment' => $this->last_payment_at, // NEW
            'days_since_last_payment' => $this->getDaysSinceLastPayment(), // NEW
            'is_eligible_for_payment' => $this->isEligibleForPayment(), // NEW
            'is_ready_for_payment' => $this->isReadyForPayment(), // NEW
        ];
    }

    /**
     * Generate transaction reference
     */
    private function generateTransactionReference(string $prefix = 'TXN'): string
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
}