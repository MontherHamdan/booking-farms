<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'type' => $this->type,
            'type_label' => __('wallet.transaction_types.' . $this->type),
            'amount' => $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'balance_before' => $this->balance_before,
            'balance_after' => $this->balance_after,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => __('wallet.transaction_status.' . $this->status),
            'booking' => $this->whenLoaded('booking', function () {
                return $this->booking ? [
                    'id' => $this->booking->id,
                    'booking_reference' => $this->booking->booking_reference,
                ] : null;
            }),
            'processed_by' => $this->whenLoaded('processedBy', function () {
                return $this->processedBy ? [
                    'id' => $this->processedBy->id,
                    'name' => $this->processedBy->name,
                ] : null;
            }),
            'metadata' => $this->metadata,
            'transaction_indicators' => [
                'increases_balance' => method_exists($this, 'increasesBalance') ? $this->increasesBalance() : $this->amount > 0,
                'decreases_balance' => method_exists($this, 'decreasesBalance') ? $this->decreasesBalance() : $this->amount < 0,
                'is_earning' => $this->type === 'earning',
                'is_payment' => $this->type === 'manual_payment',
                'is_refund' => $this->type === 'refund',
                'is_commission' => $this->type === 'commission',
            ],
            'created_at' => $this->created_at,
            'processed_at' => $this->processed_at,
            'updated_at' => $this->updated_at,
        ];
    }
}