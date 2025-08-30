<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'payment_date',
        'notes',
        'payment_details',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'payment_details' => 'array',
    ];

    // Payment Methods
    const METHOD_IBAN = 'iban';
    const METHOD_CLIQ = 'cliq';

    /**
     * Get the user (farm owner) who received this payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who processed this payment
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the farm owner's wallet
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(FarmOwnerWallet::class, 'user_id', 'user_id');
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabel(): string
    {
        return match($this->payment_method) {
            self::METHOD_IBAN => 'IBAN Transfer',
            self::METHOD_CLIQ => 'CLIQ Transfer',
            default => ucfirst($this->payment_method),
        };
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'AED ' . number_format($this->amount, 2);
    }

    /**
     * Get formatted payment details for display
     */
    public function getFormattedPaymentDetailsAttribute(): array
    {
        if (!$this->payment_details) {
            return [];
        }

        if ($this->payment_method === self::METHOD_IBAN) {
            return [
                'IBAN' => $this->payment_details['iban'] ?? 'N/A',
                'Bank Name' => $this->payment_details['bank_name'] ?? 'N/A',
                'Account Holder' => $this->payment_details['account_holder_name'] ?? 'N/A',
            ];
        }

        if ($this->payment_method === self::METHOD_CLIQ) {
            $details = [];
            if (!empty($this->payment_details['cliq_alias'])) {
                $details['CLIQ Alias'] = $this->payment_details['cliq_alias'];
            }
            if (!empty($this->payment_details['cliq_phone'])) {
                $details['CLIQ Phone'] = $this->payment_details['cliq_phone'];
            }
            $details['Account Holder'] = $this->payment_details['account_holder_name'] ?? 'N/A';
            return $details;
        }

        return $this->payment_details;
    }

    /**
     * Create a manual payment record when admin processes payment
     */
    public static function createPaymentRecord(
        int $userId,
        float $amount,
        string $paymentMethod,
        array $bankAccountDetails,
        int $processedById,
        ?string $notes = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_date' => now()->toDateString(),
            'payment_details' => $bankAccountDetails,
            'notes' => $notes,
            'processed_by' => $processedById,
        ]);
    }

    /**
     * Get payments summary for dashboard
     */
    public static function getPaymentsSummary(array $filters = []): array
    {
        $query = self::query();

        // Apply date filters
        if (isset($filters['from_date'])) {
            $query->where('payment_date', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('payment_date', '<=', $filters['to_date']);
        }

        $payments = $query->get();

        return [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'iban_payments' => $payments->where('payment_method', self::METHOD_IBAN)->count(),
            'cliq_payments' => $payments->where('payment_method', self::METHOD_CLIQ)->count(),
            'iban_amount' => $payments->where('payment_method', self::METHOD_IBAN)->sum('amount'),
            'cliq_amount' => $payments->where('payment_method', self::METHOD_CLIQ)->sum('amount'),
        ];
    }

    /**
     * Scopes
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year);
    }

    public function scopeLastMonth($query)
    {
        $lastMonth = now()->subMonth();
        return $query->whereMonth('payment_date', $lastMonth->month)
                    ->whereYear('payment_date', $lastMonth->year);
    }

    public function scopeIban($query)
    {
        return $query->where('payment_method', self::METHOD_IBAN);
    }

    public function scopeCliq($query)
    {
        return $query->where('payment_method', self::METHOD_CLIQ);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeProcessedBy($query, int $adminId)
    {
        return $query->where('processed_by', $adminId);
    }
}