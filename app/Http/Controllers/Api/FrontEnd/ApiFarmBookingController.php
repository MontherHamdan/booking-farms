<?php

namespace App\Http\Controllers\Api\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Requests\FrontEnd\CalculatePriceRequest;
use App\Http\Requests\FrontEnd\CreateBookingRequest;
use App\Http\Requests\FrontEnd\GetCheckoutPageDataRequest;
use App\Models\Farm;
use App\Models\FarmBooking;
use App\Services\FarmBookingService;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use App\Traits\FarmPricingTrait;
use App\Traits\StripeWebhookHandlerTrait;
use App\Traits\BookingFormatterTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class ApiFarmBookingController extends Controller
{
    use JsonResponseTrait, 
        ExceptionLoggerTrait, 
        FarmPricingTrait,
        StripeWebhookHandlerTrait,
        BookingFormatterTrait;

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

            // Calculate pricing
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

            // Validate pricing exists
            $pricing = $farm->pricing()->where('price_type', $priceType)->first();
            if (!$pricing) {
                return $this->errorResponse(__('farm.pricing_not_available'), 400);
            }

            // Validate deposit if requested
            if ($paymentOption === 'deposit' && (!$farm->deposit_rate || $farm->deposit_rate <= 0)) {
                return $this->errorResponse(__('farm.deposit_not_available'), 400);
            }

            // Process dates and calculate price
            $processedDates = $this->processDatesByPriceType($dates, $priceType);
            $pricingData = $this->bookingService->calculatePricing($farm, $processedDates, $priceType, $paymentOption);
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
                
                // Process dates and check availability
                $processedDates = $this->processDatesByPriceType($dates, $priceType);
                $availabilityErrors = $this->bookingService->checkAvailability($farm, $processedDates, $priceType);
                
                if (isset($availabilityErrors['unavailable'])) {
                    return $this->errorResponse(__('farm.unavailable_dates', ['dates' => implode(', ', $availabilityErrors['unavailable'])]), 400);
                }
                
                if (isset($availabilityErrors['booked'])) {
                    return $this->errorResponse(__('farm.dates_already_booked', ['dates' => implode(', ', $availabilityErrors['booked'])]), 400);
                }

                // Calculate pricing
                $pricingData = $this->bookingService->calculatePricing($farm, $processedDates, $priceType, $paymentOption);

                // Validate deposit option
                if ($paymentOption === 'deposit' && (!$farm->deposit_rate || $farm->deposit_rate <= 0)) {
                    return $this->errorResponse(__('farm.deposit_not_available'), 400);
                }

                // Create booking
                $bookingData = [
                    'user_id' => auth('sanctum')->id(),
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
                    'farm' => $farm, // Pass farm for setting booking times
                ];

                $booking = $this->bookingService->createBooking($bookingData);

                // Create Stripe Payment Intent
                $paymentIntent = PaymentIntent::create([
                    'amount' => (int) ($pricingData['payment_amount'] * 100),
                    'currency' => 'aed',
                    'payment_method_types' => ['card'],
                    'metadata' => [
                        'booking_id' => $booking->id,
                        'farm_id' => $farmId,
                        'user_id' => auth('sanctum')->id(),
                        'booking_reference' => $booking->booking_reference,
                        'payment_type' => $pricingData['is_deposit'] ? 'deposit' : 'full',
                    ],
                    'description' => $this->buildBookingDescription($booking, $farm),
                    'receipt_email' => $request->customer_email,
                    'confirmation_method' => 'automatic',
                    'confirm' => false,
                ]);

                // Update booking with payment intent ID
                $booking->update(['stripe_payment_intent_id' => $paymentIntent->id]);

                return $this->successResponse(true, [
                    'message' => __('booking.payment_intent_created'),
                    'data' => [
                        'booking_id' => $booking->id,
                        'booking_reference' => $booking->booking_reference,
                        'client_secret' => $paymentIntent->client_secret,
                        'payment_intent_id' => $paymentIntent->id,
                        'amount' => $pricingData['payment_amount'],
                        'currency' => 'aed',
                        'payment_type' => $pricingData['is_deposit'] ? 'deposit' : 'full',
                        'expires_at' => $booking->expires_at,
                        'booking_period' => $booking->booking_period,
                        'time_range' => $booking->booking_time_range,
                    ]
                ], null, 200);
            });

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'create payment intent', 'farm_id' => $farmId]);
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
}