<?php

namespace App\Http\Controllers\Api\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Requests\FrontEnd\CalculatePriceRequest;
use App\Http\Requests\FrontEnd\CreateBookingRequest;
use App\Http\Requests\FrontEnd\GetCheckoutPageDataRequest;
use App\Http\Requests\FrontEnd\ValidateCouponRequest;
use App\Models\Farm;
use App\Models\FarmBooking;
use App\Services\FarmBookingService;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use App\Traits\FarmPricingTrait;
use App\Traits\StripeWebhookHandlerTrait;
use App\Traits\BookingFormatterTrait;
use App\Traits\CouponTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;

class ApiFarmBookingController extends Controller
{
    use JsonResponseTrait, 
        ExceptionLoggerTrait, 
        FarmPricingTrait,
        StripeWebhookHandlerTrait,
        BookingFormatterTrait,
        CouponTrait;

    protected FarmBookingService $bookingService;

    public function __construct(FarmBookingService $bookingService)
    {
        $this->bookingService = $bookingService;
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Calculate farm price based on selected dates and price type
     */
    public function calculatePrice(CalculatePriceRequest $request, $farmId): JsonResponse
    {
        try {
            $farm = Farm::with(['pricing', 'offers'])->find($farmId);

            if (!$farm) {
                return $this->errorResponse(__('farm.not_found', ['id' => $farmId]), 404);
            }

            $dates = $request->dates;
            $priceType = $request->price_type;

            // Get pricing for selected type
            $pricing = $farm->pricing()->where('price_type', $priceType)->first();
            if (!$pricing) {
                return $this->errorResponse(__('farm.pricing_not_available', ['price_type' => __('farm.price_types.' . $priceType)]), 400);
            }

            // Process dates
            $processedDates = $this->processDatesByPriceType($dates, $priceType);

            // Check availability
            $availabilityErrors = $this->bookingService->checkAvailability($farm, $processedDates, $priceType);
            
            if (isset($availabilityErrors['unavailable'])) {
                return $this->errorResponse(__('farm.unavailable_dates', ['dates' => implode(', ', $availabilityErrors['unavailable'])]), 400);
            }
            
            if (isset($availabilityErrors['booked'])) {
                return $this->errorResponse(__('farm.dates_already_booked', ['dates' => implode(', ', $availabilityErrors['booked'])]), 400);
            }

            // Calculate pricing (without coupon - this is just for display)
            $pricingData = $this->bookingService->calculatePricing($farm, $processedDates, $priceType);

            // Return pricing information
            $data = [
                'dates' => $processedDates,
                'price_type' => $priceType,
                'price_before_offer' => $pricingData['subtotal'],
                'offer_percentage' => $pricingData['offer_percentage'],
                'is_offer' => $pricingData['offer_percentage'] > 0,
                'discount_amount' => $pricingData['discount_amount'],
                'price_after_offer' => $pricingData['total'],
                'deposit_available' => $farm->deposit_rate > 0,
                'deposit_rate' => $farm->deposit_rate,
                'deposit_amount' => $pricingData['deposit_amount'],
                'remaining_amount' => $pricingData['remaining_amount'],
                'pricing_details' => $pricingData['pricing_details'],
            ];

            return $this->successResponse(true, $data, null, 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'calculate farm price', 'farm_id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Validate coupon code
     */
    public function validateCoupon(ValidateCouponRequest $request, $farmId): JsonResponse
    {
        try {
            $farm = Farm::find($farmId);
            if (!$farm) {
                return $this->errorResponse(__('farm.not_found', ['id' => $farmId]), 404);
            }

            $couponCode = $request->coupon_code;
            $dates = $request->dates ?? [];
            $userId = auth('sanctum')->id();
            $platform = $request->header('User-Agent-Platform', 'web');

            if (!$userId) {
                return $this->errorResponse(__('auth.unauthenticated'), 401);
            }

            // Process dates if provided
            $processedDates = !empty($dates) ? $this->processDatesByPriceType($dates, $request->price_type ?? 'day_use') : [];

            // Validate coupon
            $validation = $this->bookingService->validateCoupon($couponCode, $farm, $processedDates, $userId, $platform);

            if ($validation['valid']) {
                return $this->successResponse(true, [
                    'valid' => true,
                    'coupon' => [
                        'id' => $validation['coupon']->id,
                        'code' => $validation['coupon']->code,
                        'name' => $validation['coupon']->name,
                        'description' => $validation['coupon']->discount_description,
                        'discount_type' => $validation['coupon']->discount_type,
                        'discount_value' => $validation['coupon']->discount_value,
                        'max_discount' => $validation['coupon']->max_discount,
                    ]
                ], __('coupon.valid'), 200);
            } else {
                return $this->errorResponse(implode(' ', $validation['errors']), 400);
            }

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'validate coupon', 'farm_id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }



    /**
     * Get checkout page data
     */
    public function getCheckoutPageData(GetCheckoutPageDataRequest $request, $farmId): JsonResponse
    {
        try {
            $farm = Farm::with(['pricing', 'offers', 'mainImage', 'city', 'area'])->find($farmId);

            if (!$farm) {
                return $this->errorResponse(__('farm.not_found', ['id' => $farmId]), 404);
            }

            $dates = $request->dates;
            $priceType = $request->price_type;
            $paymentOption = $request->payment_option;
            $couponCode = $request->coupon_code;

            // Validate pricing exists
            $pricing = $farm->pricing()->where('price_type', $priceType)->first();
            if (!$pricing) {
                return $this->errorResponse(__('farm.pricing_not_available'), 400);
            }

            // Validate deposit if requested
            if ($paymentOption === 'deposit' && (!$farm->deposit_rate || $farm->deposit_rate <= 0)) {
                return $this->errorResponse(__('farm.deposit_not_available'), 400);
            }

            // Get user ID for coupon validation
            $userId = auth('sanctum')->id();
            $platform = $request->header('User-Agent-Platform', 'web');

            // Process dates and calculate price
            $processedDates = $this->processDatesByPriceType($dates, $priceType);
            $pricingData = $this->bookingService->calculatePricing(
                $farm, 
                $processedDates, 
                $priceType, 
                $paymentOption, 
                $couponCode, 
                $userId, 
                $platform
            );
            $periodData = $this->getFormattedBookingPeriod($processedDates);

            // Get time information
            $startTime = $pricing->start_time ? Carbon::parse($pricing->start_time)->format('H:i') : null;
            $endTime = $pricing->end_time ? Carbon::parse($pricing->end_time)->format('H:i') : null;

            // Return checkout page data
            $data = [
                'farm' => [
                    'id' => $farm->id,
                    'name' => $farm->name_en ?: $farm->name_ar,
                    'main_image' => $farm->mainImage ? url($farm->mainImage->image_path) : null,
                    'city' => $farm->city->name_en ?? $farm->city->name_ar ?? null,
                    'area' => $farm->area->name_en ?? $farm->area->name_ar ?? null,
                ],
                'booking' => [
                    'dates' => $processedDates,
                    'formatted_dates' => $this->formatDatesForDisplay($processedDates),
                    'start_date' => $periodData['start_date'],
                    'end_date' => $periodData['end_date'],
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'time_range' => $pricing->time_range,
                    'duration_days' => $periodData['duration_days'],
                    'price_type' => $priceType,
                    'price_type_label' => __('farm.price_types.' . $priceType),
                    'guest_count' => $request->guest_count,
                ],
                'pricing' => [
                    'subtotal' => $pricingData['subtotal'],
                    'discount_amount' => $pricingData['discount_amount'],
                    'offer_percentage' => $pricingData['offer_percentage'],
                    'coupon_applied' => $pricingData['coupon_applied'] ?? false,
                    'coupon_discount_amount' => $pricingData['coupon_discount_amount'] ?? 0,
                    'coupon_details' => $pricingData['coupon_details'] ?? null,
                    'coupon_errors' => $pricingData['coupon_errors'] ?? null,
                    'total' => $pricingData['total'],
                    'payment_option' => $paymentOption,
                    'paying_now' => $pricingData['payment_amount'],
                    'deposit_amount' => $pricingData['deposit_amount'],
                    'remaining_amount' => $pricingData['remaining_amount'],
                    'is_deposit' => $pricingData['is_deposit'],
                ]
            ];

            return $this->successResponse(true, $data, null, 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'get checkout data', 'farm_id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Create booking and Payment Intent
     */
    public function createPaymentIntent(CreateBookingRequest $request, $farmId): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request, $farmId) {
                $farm = Farm::with(['pricing', 'offers'])->find($farmId);

                if (!$farm) {
                    return $this->errorResponse(__('farm.not_found', ['id' => $farmId]), 404);
                }

                $dates = $request->dates;
                $priceType = $request->price_type;
                $paymentOption = $request->payment_option ?? 'full';
                $couponCode = $request->coupon_code;
                
                // Handle saved cards and new card saving
                $paymentMethodId = $request->payment_method_id; // Saved card ID
                $saveCard = $request->save_card ?? false; // Whether to save new card
                
                // Get user and ensure they can make payments
                $userId = auth('sanctum')->id();
                $user = auth('sanctum')->user();
                
                // Check if user can create Stripe customer (has email OR phone)
                if (!$user->canCreateStripeCustomer()) {
                    $missingInfo = $user->getMissingContactInfo();
                    return $this->errorResponse(__('card.validation.contact_info_required'), 422);
                }

                // If using saved card, verify user has Stripe account
                if ($paymentMethodId && !$user->hasStripeAccount()) {
                    return $this->errorResponse(__('card.validation.no_saved_cards'), 422);
                }

                // Create or get Stripe customer
                try {
                    $stripeCustomerId = $user->createOrGetStripeCustomer();
                } catch (\Exception $e) {
                    return $this->errorResponse(__('card.validation.contact_info_required'), 422);
                }
                
                // Process dates and check availability
                $processedDates = $this->processDatesByPriceType($dates, $priceType);
                $availabilityErrors = $this->bookingService->checkAvailability($farm, $processedDates, $priceType);
                
                if (isset($availabilityErrors['unavailable'])) {
                    return $this->errorResponse(__('farm.unavailable_dates', ['dates' => implode(', ', $availabilityErrors['unavailable'])]), 400);
                }
                
                if (isset($availabilityErrors['booked'])) {
                    return $this->errorResponse(__('farm.dates_already_booked', ['dates' => implode(', ', $availabilityErrors['booked'])]), 400);
                }

                $platform = $request->header('User-Agent-Platform', 'web');

                // Calculate pricing with coupon
                $pricingData = $this->bookingService->calculatePricing(
                    $farm, 
                    $processedDates, 
                    $priceType, 
                    $paymentOption, 
                    $couponCode, 
                    $userId, 
                    $platform
                );

                // Check for coupon errors
                if (!empty($pricingData['coupon_errors'])) {
                    return $this->errorResponse(implode(' ', $pricingData['coupon_errors']), 400);
                }

                // Validate deposit option
                if ($paymentOption === 'deposit' && (!$farm->deposit_rate || $farm->deposit_rate <= 0)) {
                    return $this->errorResponse(__('farm.deposit_not_available'), 400);
                }

                // Verify saved card belongs to user
                if ($paymentMethodId) {
                    if (!$this->verifyPaymentMethodOwnership($paymentMethodId, $stripeCustomerId)) {
                        return $this->errorResponse(__('card.validation.card_not_found'), 404);
                    }
                }

                // Create booking
                $bookingData = [
                    'user_id' => $userId,
                    'farm_id' => $farmId,
                    'price_type' => $priceType,
                    'booking_dates' => $processedDates,
                    'guest_count' => $request->guest_count,
                    'subtotal' => $pricingData['subtotal'],
                    'discount_amount' => $pricingData['discount_amount'],
                    'total_amount' => $pricingData['total'],
                    'deposit_amount' => $pricingData['is_deposit'] ? $pricingData['deposit_amount'] : 0,
                    'remaining_amount' => $pricingData['is_deposit'] ? $pricingData['remaining_amount'] : 0,
                    'payment_option' => $paymentOption,
                    'customer_name' => $request->customer_name,
                    'customer_email' => $request->customer_email,
                    'customer_phone' => $request->customer_phone,
                    'notes' => $request->notes,
                    'expires_at' => now()->addMinutes(30),
                    'farm' => $farm,
                ];

                // Add coupon data if applied
                if ($pricingData['coupon_applied'] && isset($pricingData['coupon_details'])) {
                    $bookingData['coupon_id'] = $pricingData['coupon_details']['id'];
                    $bookingData['coupon_code'] = $pricingData['coupon_details']['code'];
                    $bookingData['coupon_discount_amount'] = $pricingData['coupon_discount_amount'];
                }

                $booking = $this->bookingService->createBooking($bookingData);

                // Prepare Payment Intent with saved card support
                $paymentIntentData = [
                    'amount' => (int) ($pricingData['payment_amount'] * 100),
                    'currency' => 'aed',
                    'customer' => $stripeCustomerId, // Associate with customer
                    'payment_method_types' => ['card'], // ✅ Only accept cards
                    'metadata' => [
                        'booking_id' => $booking->id,
                        'farm_id' => $farmId,
                        'user_id' => $userId,
                        'booking_reference' => $booking->booking_reference,
                        'payment_type' => $pricingData['is_deposit'] ? 'deposit' : 'full',
                        'coupon_code' => $booking->coupon_code ?? null,
                        'coupon_discount' => $booking->coupon_discount_amount ?? 0,
                    ],
                    'description' => $this->buildBookingDescription($booking, $farm),
                ];

                // Add receipt email only if user has email
                if (!empty($user->email)) {
                    $paymentIntentData['receipt_email'] = $user->email;
                }

                // If using saved card, attach it
                if ($paymentMethodId) {
                    $paymentIntentData['payment_method'] = $paymentMethodId;
                    $paymentIntentData['confirmation_method'] = 'manual';
                    $paymentIntentData['confirm'] = true;
                } else {
                    // For new cards
                    $paymentIntentData['confirmation_method'] = 'automatic';
                    $paymentIntentData['confirm'] = false;
                    
                    // Setup for future usage if user wants to save card
                    if ($saveCard) {
                        $paymentIntentData['setup_future_usage'] = 'off_session';
                    }
                }

                // Create Stripe Payment Intent
                $paymentIntent = PaymentIntent::create($paymentIntentData);

                // Update booking with payment intent ID
                $booking->update(['stripe_payment_intent_id' => $paymentIntent->id]);

                $responseData = [
                    'message' => __('booking.payment_intent_created'),
                    'data' => [
                        'booking_id' => $booking->id,
                        'booking_reference' => $booking->booking_reference,
                        'payment_intent_id' => $paymentIntent->id,
                        'amount' => $pricingData['payment_amount'],
                        'currency' => 'aed',
                        'payment_type' => $pricingData['is_deposit'] ? 'deposit' : 'full',
                        'expires_at' => $booking->expires_at,
                        'booking_period' => $booking->booking_period,
                        'time_range' => $booking->booking_time_range,
                        'coupon_applied' => $pricingData['coupon_applied'] ?? false,
                        'coupon_savings' => $pricingData['coupon_discount_amount'] ?? 0,
                    ]
                ];

                // Add client secret for new cards, or status for saved cards
                if ($paymentMethodId) {
                    // For saved cards, payment might be complete or require action
                    $responseData['data']['status'] = $paymentIntent->status;
                    if ($paymentIntent->status === 'requires_action') {
                        $responseData['data']['client_secret'] = $paymentIntent->client_secret;
                    }
                } else {
                    // For new cards, always provide client secret
                    $responseData['data']['client_secret'] = $paymentIntent->client_secret;
                }

                return $this->successResponse(true, $responseData, null, 200);
            });

        } catch (\Exception $e) {
            $this->logException($e, ['action' => 'create payment intent', 'farm_id' => $farmId]);
            
            // Handle specific Stripe customer creation errors
            if (strpos($e->getMessage(), 'Either email or phone') !== false) {
                return $this->errorResponse(__('card.validation.contact_info_required'), 422);
            }
            
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Confirm payment status after Stripe processing
     */
    public function confirmPayment(Request $request, $bookingId): JsonResponse
    {
        try {
            $booking = FarmBooking::where('id', $bookingId)
                ->where('user_id', auth('sanctum')->id())
                ->first();

            if (!$booking || !$booking->stripe_payment_intent_id) {
                return $this->errorResponse(__('booking.not_found'), 404);
            }

            $paymentIntent = PaymentIntent::retrieve($booking->stripe_payment_intent_id);
            
            if ($paymentIntent->status === 'succeeded') {
                $booking->markAsPaid($paymentIntent->id);
                
                return $this->successResponse(true, [
                    'status' => 'succeeded',
                    'booking_status' => $booking->fresh()->booking_status,
                    'booking_reference' => $booking->booking_reference,
                    'message' => __('booking.payment_successful'),
                    'coupon_used' => $booking->hasCoupon(),
                    'coupon_savings' => $booking->coupon_discount_amount ?? 0,
                ], null, 200);
            } elseif ($paymentIntent->status === 'requires_action') {
                return $this->successResponse(true, [
                    'status' => 'requires_action',
                    'client_secret' => $paymentIntent->client_secret,
                    'message' => __('booking.additional_authentication_required'),
                ], null, 200);
            } else {
                return $this->successResponse(false, [
                    'status' => $paymentIntent->status,
                    'message' => __('booking.payment_' . $paymentIntent->status),
                ], null, 200);
            }

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'confirm payment', 'booking_id' => $bookingId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function handleStripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload in Stripe webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature in Stripe webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        try {
            switch ($event['type']) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSucceeded($event['data']['object']);
                    break;
                
                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailed($event['data']['object']);
                    break;
                
                case 'payment_intent.requires_action':
                    $this->handlePaymentRequiresAction($event['data']['object']);
                    break;
                
                default:
                    Log::info('Unhandled Stripe webhook event: ' . $event['type']);
            }

            return response()->json(['status' => 'success'], 200);

        } catch (Exception $e) {
            Log::error('Error processing Stripe webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    private function verifyPaymentMethodOwnership($paymentMethodId, $customerId): bool
    {
        try {
            $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
            return $paymentMethod->customer === $customerId;
        } catch (\Exception $e) {
            return false;
        }
    }
}