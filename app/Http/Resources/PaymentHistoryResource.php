<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentHistoryResource extends JsonResource
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
            'amount' => $this->amount,
            'formatted_amount' => method_exists($this, 'formatted_amount') ? $this->formatted_amount : number_format($this->amount, 2),
            'payment_method' => $this->payment_method,
            'payment_method_label' => __('wallet.payment_methods.' . $this->payment_method),
            'payment_date' => $this->payment_date?->format('Y-m-d'),
            'formatted_payment_date' => $this->payment_date?->format('M d, Y'),
            'payment_details' => $this->payment_details,
            'formatted_payment_details' => method_exists($this, 'formatted_payment_details') ? $this->formatted_payment_details : $this->payment_details,
            'notes' => $this->notes,
            'processed_by' => $this->whenLoaded('processedBy', function () {
                return $this->processedBy ? [
                    'id' => $this->processedBy->id,
                    'name' => $this->processedBy->name,
                ] : null;
            }),
            'bank_account_info' => $this->when($this->payment_method === 'iban', function () {
                return [
                    'account_holder_name' => $this->payment_details['account_holder_name'] ?? null,
                    'bank_name' => $this->payment_details['bank_name'] ?? null,
                    'iban_masked' => $this->getMaskedAccountNumber(),
                ];
            }),
            'cliq_info' => $this->when($this->payment_method === 'cliq', function () {
                return [
                    'cliq_identifier' => $this->payment_details['cliq_identifier'] ?? null,
                    'identifier_type' => $this->payment_details['identifier_type'] ?? null,
                    'recipient_name' => $this->payment_details['recipient_name'] ?? null,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get masked account number for privacy
     */
    private function getMaskedAccountNumber(): ?string
    {
        if (!isset($this->payment_details['iban'])) {
            return null;
        }

        $iban = $this->payment_details['iban'];
        $length = strlen($iban);
        
        if ($length <= 8) {
            return $iban; // Too short to mask
        }

        // Show first 4 and last 4 characters
        return substr($iban, 0, 4) . str_repeat('*', $length - 8) . substr($iban, -4);
    }
}