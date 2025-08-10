<?php

namespace App\Http\Controllers\Api\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Requests\FrontEnd\CalculatePriceRequest;
use App\Http\Requests\FrontEnd\CreateBookingRequest;
use App\Http\Requests\FrontEnd\GetCheckoutPageDataRequest;
use App\Models\Farm;
use App\Models\FarmBooking;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use App\Traits\FarmPricingTrait;
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
    use JsonResponseTrait, ExceptionLoggerTrait, FarmPricingTrait;

    public function __construct()
    {
        // Set Stripe API key
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Calculate farm price based on selected dates and price type
     * This is a simple API just for price calculation
     */
    public function calculatePrice(CalculatePriceRequest $request, $farmId): JsonResponse
    {
        try {
            // Fetch farm with pricing and offers
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

            // Process dates based on price type
            $processedDates = $this->processDatesByPriceType($dates, $priceType);

            // Check unavailable dates for this specific price type
            $unavailableDates = $farm->getUnavailableDatesForPriceType($priceType);
            $conflictingUnavailable = array_intersect($processedDates, $unavailableDates);
            if ($conflictingUnavailable) {
                return $this->errorResponse(__('farm.unavailable_dates', ['dates' => implode(', ', $conflictingUnavailable)]), 400);
            }

            // Check for existing bookings on these dates with the same price type
            $existingBookings = FarmBooking::where('farm_id', $farmId)
                ->where('price_type', $priceType)
                ->where('booking_status', FarmBooking::BOOKING_STATUS_CONFIRMED)
                ->get();

            $bookedDates = [];
            foreach ($existingBookings as $booking) {
                $bookedDates = array_merge($bookedDates, $booking->booking_dates ?? []);
            }

            // Also check for full_day bookings that would conflict with day_use/night
            if (in_array($priceType, ['day_use', 'night'])) {
                $fullDayBookings = FarmBooking::where('farm_id', $farmId)
                    ->where('price_type', 'full_day')
                    ->where('booking_status', FarmBooking::BOOKING_STATUS_CONFIRMED)
                    ->get();
                    
                foreach ($fullDayBookings as $booking) {
                    $bookedDates = array_merge($bookedDates, $booking->booking_dates ?? []);
                }
            }

            // Check for day_use + night combination that would conflict with full_day
            if ($priceType === 'full_day') {
                $dayUseBookings = FarmBooking::where('farm_id', $farmId)
                    ->whereIn('price_type', ['day_use', 'night'])
                    ->where('booking_status', FarmBooking::BOOKING_STATUS_CONFIRMED)
                    ->get();
                    
                foreach ($dayUseBookings as $booking) {
                    $bookedDates = array_merge($bookedDates, $booking->booking_dates ?? []);
                }
            }

            $conflictingBookedDates = array_intersect($processedDates, array_unique($bookedDates));
            if ($conflictingBookedDates) {
                return $this->errorResponse(__('farm.dates_already_booked', ['dates' => implode(', ', $conflictingBookedDates)]), 400);
            }

            // Calculate subtotal before discount
            $subtotal = collect($processedDates)->sum(function ($date) use ($pricing) {
                $day = strtolower(Carbon::parse($date)->format('l'));
                return $pricing->{"{$day}_price"} ?? 0;
            });

            // Determine current offer percentage
            $offer = $farm->currentOffer;
            $percentage = $offer ? (float) $offer->percentage : 0.0;

            // Compute discount and final total
            $discountAmount = ($subtotal * $percentage) / 100;
            $total = $subtotal - $discountAmount;

            // Calculate deposit (if required)
            $depositAmount = 0;
            $remainingAmount = $total;
            
            if ($farm->deposit_rate && $farm->deposit_rate > 0) {
                $depositAmount = ($total * $farm->deposit_rate) / 100;
                $remainingAmount = $total - $depositAmount;
            }

            // Return ONLY pricing information
            $data = [
                'dates' => $processedDates,
                'price_type' => $priceType,
                'price_before_offer' => $subtotal,
                'offer_percentage' => $percentage,
                'is_offer' => $percentage > 0,
                'discount_amount' => $discountAmount,
                'price_after_offer' => $total,
                'deposit_available' => $farm->deposit_rate > 0,
                'deposit_rate' => $farm->deposit_rate,
                'deposit_amount' => $depositAmount,
                'remaining_amount' => $remainingAmount,
                'pricing_details' => [
                    'start_time' => $pricing->formatted_start_time,
                    'end_time' => $pricing->formatted_end_time,
                    'time_range' => $pricing->time_range,
                    'duration_hours' => $pricing->duration_in_hours,
                ],
            ];

            return $this->successResponse(true, $data, null, 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'calculate farm price', 'farm_id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get checkout page data (farm details + price info)
     * Call this when navigating to checkout page
     */
    public function getCheckoutPageData(GetCheckoutPageDataRequest $request, $farmId): JsonResponse
    {
        try {

            // Fetch farm with all needed relations
            $farm = Farm::with(['pricing', 'offers', 'mainImage', 'city', 'area'])->find($farmId);

            if (!$farm) {
                return $this->errorResponse(__('farm.not_found', ['id' => $farmId]), 404);
            }

            $dates = $request->dates;
            $priceType = $request->price_type;
            $paymentOption = $request->payment_option;

            // Get pricing for selected type
            $pricing = $farm->pricing()->where('price_type', $priceType)->first();
            if (!$pricing) {
                return $this->errorResponse(__('farm.pricing_not_available'), 400);
            }

            // Validate deposit availability if deposit payment is requested
            if ($paymentOption === 'deposit' && (!$farm->deposit_rate || $farm->deposit_rate <= 0)) {
                return $this->errorResponse(__('farm.deposit_not_available'), 400);
            }

            // Process dates and calculate price
            $processedDates = $this->processDatesByPriceType($dates, $priceType);
            
            // Calculate pricing
            $subtotal = collect($processedDates)->sum(function ($date) use ($pricing) {
                $day = strtolower(Carbon::parse($date)->format('l'));
                return $pricing->{"{$day}_price"} ?? 0;
            });

            $offer = $farm->currentOffer;
            $percentage = $offer ? (float) $offer->percentage : 0.0;
            $discountAmount = ($subtotal * $percentage) / 100;
            $total = $subtotal - $discountAmount;

            // Calculate payment amounts based on option
            $depositAmount = 0;
            $remainingAmount = 0;
            $paymentAmount = $total;

            if ($paymentOption === 'deposit' && $farm->deposit_rate > 0) {
                $depositAmount = ($total * $farm->deposit_rate) / 100;
                $remainingAmount = $total - $depositAmount;
                $paymentAmount = $depositAmount;
            }

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
                    'price_type' => $priceType,
                    'price_type_label' => __('farm.price_types.' . $priceType),
                    'guest_count' => $request->guest_count,
                    'time_range' => $pricing->time_range ?? null,
                ],
                'pricing' => [
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'offer_percentage' => $percentage,
                    'total' => $total,
                    'payment_option' => $paymentOption,
                    'paying_now' => $paymentAmount,
                    'deposit_amount' => $depositAmount,
                    'remaining_amount' => $remainingAmount,
                    'is_deposit' => $paymentOption === 'deposit',
                ]
            ];

            return $this->successResponse(true, $data, null, 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'get checkout data', 'farm_id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Create booking and Payment Intent for custom checkout
     */
    public function createPaymentIntent(CreateBookingRequest $request, $farmId): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request, $farmId) {
                $farm = Farm::with(['pricing', 'offers'])->find($farmId);

                if (!$farm) {
                    return $this->errorResponse(__('farm.not_found', ['id' => $farmId]), 404);
                }

                // Validate availability again
                $dates = $request->dates;
                $priceType = $request->price_type;
                $paymentOption = $request->payment_option ?? 'full';
                
                $processedDates = $this->processDatesByPriceType($dates, $priceType);
                
                // Check price-type specific unavailable dates
                $unavailableDates = $farm->getUnavailableDatesForPriceType($priceType);
                $conflictingUnavailable = array_intersect($processedDates, $unavailableDates);
                if ($conflictingUnavailable) {
                    return $this->errorResponse(__('farm.unavailable_dates', ['dates' => implode(', ', $conflictingUnavailable)]), 400);
                }

                // Check for conflicts with existing bookings (same logic as calculatePrice)
                $existingBookings = FarmBooking::where('farm_id', $farmId)
                    ->where('price_type', $priceType)
                    ->where('booking_status', FarmBooking::BOOKING_STATUS_CONFIRMED)
                    ->get();

                $bookedDates = [];
                foreach ($existingBookings as $booking) {
                    $bookedDates = array_merge($bookedDates, $booking->booking_dates ?? []);
                }

                // Cross-price-type booking conflicts
                if (in_array($priceType, ['day_use', 'night'])) {
                    $fullDayBookings = FarmBooking::where('farm_id', $farmId)
                        ->where('price_type', 'full_day')
                        ->where('booking_status', FarmBooking::BOOKING_STATUS_CONFIRMED)
                        ->get();
                        
                    foreach ($fullDayBookings as $booking) {
                        $bookedDates = array_merge($bookedDates, $booking->booking_dates ?? []);
                    }
                }

                if ($priceType === 'full_day') {
                    $dayUseBookings = FarmBooking::where('farm_id', $farmId)
                        ->whereIn('price_type', ['day_use', 'night'])
                        ->where('booking_status', FarmBooking::BOOKING_STATUS_CONFIRMED)
                        ->get();
                        
                    foreach ($dayUseBookings as $booking) {
                        $bookedDates = array_merge($bookedDates, $booking->booking_dates ?? []);
                    }
                }

                $conflictingBookedDates = array_intersect($processedDates, array_unique($bookedDates));
                if ($conflictingBookedDates) {
                    return $this->errorResponse(__('farm.dates_already_booked', ['dates' => implode(', ', $conflictingBookedDates)]), 400);
                }

                // Calculate pricing
                $pricing = $farm->pricing()->where('price_type', $priceType)->first();
                $subtotal = collect($processedDates)->sum(function ($date) use ($pricing) {
                    $day = strtolower(Carbon::parse($date)->format('l'));
                    return $pricing->{"{$day}_price"} ?? 0;
                });

                $offer = $farm->currentOffer;
                $percentage = $offer ? (float) $offer->percentage : 0.0;
                $discountAmount = ($subtotal * $percentage) / 100;
                $total = $subtotal - $discountAmount;

                // Calculate deposit and remaining amount
                $depositAmount = 0;
                $remainingAmount = $total;
                $paymentAmount = $total; // Default to full payment
                $isDepositPayment = false;

                // Check if deposit payment is requested and available
                if ($paymentOption === 'deposit' && $farm->deposit_rate && $farm->deposit_rate > 0) {
                    $depositAmount = ($total * $farm->deposit_rate) / 100;
                    $remainingAmount = $total - $depositAmount;
                    $paymentAmount = $depositAmount; // Only charge deposit now
                    $isDepositPayment = true;
                } elseif ($paymentOption === 'deposit' && (!$farm->deposit_rate || $farm->deposit_rate <= 0)) {
                    return $this->errorResponse(__('farm.deposit_not_available'), 400);
                }

                // Create booking record
                $booking = FarmBooking::create([
                    'user_id' => auth('sanctum')->id(),
                    'farm_id' => $farmId,
                    'price_type' => $priceType,
                    'booking_dates' => $processedDates,
                    'guest_count' => $request->guest_count,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $total,
                    'deposit_amount' => $isDepositPayment ? $depositAmount : 0,
                    'remaining_amount' => $isDepositPayment ? $remainingAmount : 0,
                    'payment_option' => $paymentOption,
                    'customer_name' => $request->customer_name,
                    'customer_email' => $request->customer_email,
                    'customer_phone' => $request->customer_phone,
                    'notes' => $request->notes,
                    'expires_at' => now()->addMinutes(30), // 30 minutes to complete payment
                ]);

                // Create Stripe Payment Intent for custom checkout
                $paymentIntent = PaymentIntent::create([
                    'amount' => (int) ($paymentAmount * 100),
                    'currency' => 'aed',
                    'payment_method_types' => ['card'],
                    'metadata' => [
                        'booking_id' => $booking->id,
                        'farm_id' => $farmId,
                        'user_id' => auth('sanctum')->id(),
                        'booking_reference' => $booking->booking_reference,
                        'payment_type' => $isDepositPayment ? 'deposit' : 'full',
                    ],
                    'description' => $this->buildBookingDescription($booking, $farm),
                    'receipt_email' => $request->customer_email,
                    // Enable automatic confirmation when payment succeeds
                    'confirmation_method' => 'automatic',
                    'confirm' => false, // Don't confirm yet, let frontend do it
                ]);

                // Update booking with payment intent ID
                $booking->update([
                    'stripe_payment_intent_id' => $paymentIntent->id
                ]);

                // Return payment intent details for frontend
                return $this->successResponse(true, [
                    'message'    => __('booking.payment_intent_created'),
                    'data'       =>[
                        'booking_id' => $booking->id,
                        'booking_reference' => $booking->booking_reference,
                        'client_secret' => $paymentIntent->client_secret,
                        'payment_intent_id' => $paymentIntent->id,
                        'amount' => $paymentAmount,
                        'currency' => 'aed',
                        'payment_type' => $isDepositPayment ? 'deposit' : 'full',
                        'expires_at' => $booking->expires_at,
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

            // Retrieve payment intent from Stripe
            $paymentIntent = PaymentIntent::retrieve($booking->stripe_payment_intent_id);
            
            if ($paymentIntent->status === 'succeeded') {
                // Payment succeeded
                $booking->markAsPaid($paymentIntent->id);
                
                return $this->successResponse(true, [
                    'status' => 'succeeded',
                    'booking_status' => $booking->fresh()->booking_status,
                    'booking_reference' => $booking->booking_reference,
                    'message' => __('booking.payment_successful'),
                ], null, 200);
            } elseif ($paymentIntent->status === 'requires_action') {
                // 3D Secure or additional action required
                return $this->successResponse(true, [
                    'status' => 'requires_action',
                    'client_secret' => $paymentIntent->client_secret,
                    'message' => __('booking.additional_authentication_required'),
                ], null, 200);
            } else {
                // Payment failed or still processing
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
     * Handle successful payment webhook from Stripe
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

    /**
     * Handle payment succeeded
     */
    private function handlePaymentSucceeded($paymentIntent)
    {
        $bookingId = $paymentIntent['metadata']['booking_id'] ?? null;
        
        if (!$bookingId) {
            Log::error('No booking ID in payment intent metadata');
            return;
        }

        $booking = FarmBooking::find($bookingId);
        if (!$booking) {
            Log::error('Booking not found for succeeded payment intent: ' . $bookingId);
            return;
        }

        $booking->markAsPaid($paymentIntent['id']);
        
        // TODO: Send confirmation email to customer
        // TODO: Send notification to farm owner
        
        Log::info('Booking marked as paid: ' . $booking->booking_reference);
    }

    /**
     * Handle payment failed
     */
    private function handlePaymentFailed($paymentIntent)
    {
        $bookingId = $paymentIntent['metadata']['booking_id'] ?? null;
        
        if (!$bookingId) {
            Log::error('No booking ID in payment intent metadata');
            return;
        }

        $booking = FarmBooking::find($bookingId);
        if (!$booking) {
            Log::error('Booking not found for failed payment intent: ' . $bookingId);
            return;
        }

        $booking->markAsFailed();
        Log::info('Booking marked as failed: ' . $booking->booking_reference);
    }

    /**
     * Handle payment requires action (for 3D Secure, etc.)
     */
    private function handlePaymentRequiresAction($paymentIntent)
    {
        // Log for debugging - frontend will handle the action
        Log::info('Payment requires action: ' . $paymentIntent['id']);
    }

    /**
     * Build booking description for Stripe
     */
    private function buildBookingDescription($booking, $farm): string
    {
        $dates = implode(', ', array_map(function ($date) {
            return Carbon::parse($date)->format('M d, Y');
        }, $booking->booking_dates));

        $paymentType = $booking->payment_option === 'deposit' ? ' (Deposit)' : ' (Full Payment)';

        return "Farm Booking: {$farm->name_en}{$paymentType} | Dates: {$dates} | Guests: {$booking->guest_count}";
    }

    /**
     * Format dates for display
     */
    private function formatDatesForDisplay(array $dates): array
    {
        return array_map(function ($date) {
            $carbonDate = Carbon::parse($date);
            return [
                'date' => $date,
                'day' => $carbonDate->format('d'),
                'month' => $carbonDate->format('M'),
                'year' => $carbonDate->format('Y'),
                'day_name' => $carbonDate->format('l'),
                'full' => $carbonDate->format('F d, Y'),
                'short' => $carbonDate->format('M d'),
            ];
        }, $dates);
    }
}