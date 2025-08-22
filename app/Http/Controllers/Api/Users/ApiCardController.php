<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddCardRequest;
use App\Http\Requests\User\DeleteCardRequest;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use Exception;
use Stripe\StripeClient;
use Stripe\PaymentMethod;
use Illuminate\Support\Facades\Auth;

class ApiCardController extends Controller
{
    use ExceptionLoggerTrait, JsonResponseTrait;

    public function __construct()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Add Card to User Account (Updated to accept PaymentMethod ID from frontend)
     */
    public function addCard(AddCardRequest $request)
    {
        try {
            $user = Auth::user();

            // Create or get Stripe customer
            $stripeCustomerId = $user->createOrGetStripeCustomer();

            // Retrieve the PaymentMethod created on frontend
            $paymentMethod = PaymentMethod::retrieve($request->payment_method_id);
            
            // Check if PaymentMethod is valid
            if (!$paymentMethod || $paymentMethod->type !== 'card') {
                return $this->errorResponse('Invalid payment method', 422);
            }

            // Check if PaymentMethod is already attached to a customer
            if ($paymentMethod->customer) {
                // Check if it's attached to the same customer
                if ($paymentMethod->customer === $stripeCustomerId) {
                    return $this->errorResponse('This card is already saved to your account', 422);
                } else {
                    return $this->errorResponse('This card is already attached to another account', 422);
                }
            }

            // Check if user already has a card with same fingerprint
            if ($this->checkCardAlreadyExistsByFingerprint($stripeCustomerId, $paymentMethod->card->fingerprint)) {
                return $this->errorResponse('This card is already saved to your account', 422);
            }

            // Attach PaymentMethod to customer
            $paymentMethod->attach(['customer' => $stripeCustomerId]);

            return $this->successResponse(true, [
                'card_id' => $paymentMethod->id,
                'brand' => ucfirst($paymentMethod->card->brand),
                'last4' => $paymentMethod->card->last4,
                'exp_month' => $paymentMethod->card->exp_month,
                'exp_year' => $paymentMethod->card->exp_year,
            ], 'Card added successfully', 200);

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $this->logException($e, ['action' => 'add card', 'user_id' => auth()->id()]);
            return $this->errorResponse('Invalid payment method provided', 422);
        } catch (\Exception $e) {
            $this->logException($e, ['action' => 'add card', 'user_id' => auth()->id()]);
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Get User's Saved Cards
     */
    public function getCards()
    {
        try {
            $user = Auth::user();

            // If user doesn't have Stripe account, return empty array
            if (!$user->hasStripeAccount()) {
                return $this->successResponse(true, [
                    'cards' => [],
                    'total_cards' => 0,
                ], 'No cards found', 200);
            }

            $uniqueCards = [];

            $paymentMethods = PaymentMethod::all([
                'customer' => $user->stripe_id,
                'type' => 'card',
            ]);

            foreach ($paymentMethods as $paymentMethod) {
                $uniqueIdentifier = $paymentMethod->card->fingerprint;

                // Check for duplicates
                $existingCard = array_filter($uniqueCards, function ($card) use ($uniqueIdentifier) {
                    return $card->unique_identifier === $uniqueIdentifier;
                });

                if (empty($existingCard)) {
                    $uniqueCards[] = (object)[
                        'id' => $paymentMethod->id,
                        'brand' => ucfirst($paymentMethod->card->brand),
                        'last4' => $paymentMethod->card->last4,
                        'exp_month' => $paymentMethod->card->exp_month,
                        'exp_year' => $paymentMethod->card->exp_year,
                        'unique_identifier' => $uniqueIdentifier,
                        'created_at' => $paymentMethod->created,
                    ];
                }
            }

            return $this->successResponse(true, [
                'cards' => $uniqueCards,
                'total_cards' => count($uniqueCards),
            ], null, 200);

        } catch (\Exception $e) {
            $this->logException($e, ['action' => 'get cards', 'user_id' => auth()->id()]);
            return $this->errorResponse('Error loading cards', 500);
        }
    }

    /**
     * Delete Saved Card
     */
    public function deleteCard(DeleteCardRequest $request)
    {
        try {
            $user = Auth::user();

            // Check if user has Stripe account
            if (!$user->hasStripeAccount()) {
                return $this->errorResponse('Card not found', 404);
            }

            // Verify the card belongs to the user
            if (!$this->cardBelongsToUser($request->card_id, $user->stripe_id)) {
                return $this->errorResponse('Card not found', 404);
            }

            $paymentMethod = PaymentMethod::retrieve($request->card_id);
            $paymentMethod->detach();

            return $this->successResponse(true, null, 'Card removed successfully', 200);

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return $this->errorResponse('Card not found', 404);
        } catch (\Exception $e) {
            $this->logException($e, ['action' => 'delete card', 'user_id' => auth()->id()]);
            return $this->errorResponse('Error deleting card', 500);
        }
    }

    /**
     * Check if card already exists by fingerprint
     */
    private function checkCardAlreadyExistsByFingerprint($customerId, $fingerprint)
    {
        try {
            $paymentMethods = PaymentMethod::all([
                'customer' => $customerId,
                'type' => 'card',
            ]);

            foreach ($paymentMethods as $paymentMethod) {
                if ($paymentMethod->card->fingerprint === $fingerprint) {
                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            $this->logException($e, ['action' => 'check card exists by fingerprint']);
            return false;
        }
    }

    /**
     * Verify card belongs to user
     */
    private function cardBelongsToUser($cardId, $customerId)
    {
        try {
            $paymentMethod = PaymentMethod::retrieve($cardId);
            return $paymentMethod->customer === $customerId;
        } catch (\Exception $e) {
            return false;
        }
    }
}