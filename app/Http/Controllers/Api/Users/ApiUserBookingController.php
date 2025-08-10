<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\FarmBooking;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiUserBookingController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;

    /**
     * Get user's bookings (excluding pending payments)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = FarmBooking::with([
                    'farm:id,name_ar,name_en',
                    'farm.mainImage:id,farm_id,image_path'
                ])
                ->where('user_id', auth('sanctum')->id());
    
            // By default, only show finalized bookings (exclude pending payments)
            if ($request->status) {
                // Allow filtering by specific status
                $query->where('booking_status', $request->status);
            } else {
                // Only show bookings that have been processed (actual booking history)
                $query->whereIn('booking_status', [
                    FarmBooking::BOOKING_STATUS_CONFIRMED,  // Payment successful, booking confirmed
                    FarmBooking::BOOKING_STATUS_CANCELLED,  // Booking was cancelled
                    FarmBooking::BOOKING_STATUS_COMPLETED   // Booking date passed, service completed
                ]);
                // Explicitly exclude PENDING (incomplete payments)
            }
    
            $bookings = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 10);
    
            return $this->successResponse(true, $bookings, null, 200);
    
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'get user bookings']);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get specific booking details for the authenticated user
     */
    public function show($bookingId): JsonResponse
    {
        try {
            $booking = FarmBooking::with([
                    'farm:id,name_ar,name_en,city_id,area_id',
                    'farm.city:id,name_ar,name_en',
                    'farm.area:id,name_ar,name_en',
                    'farm.mainImage:id,farm_id,image_path'
                ])
                ->where('id', $bookingId)
                ->where('user_id', auth('sanctum')->id())
                ->first();

            if (!$booking) {
                return $this->errorResponse(__('booking.not_found'), 404);
            }

            $isDepositPayment = $booking->deposit_amount > 0;

            return $this->successResponse(true, [
                'id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'farm' => [
                    'id' => $booking->farm->id,
                    'name_en' => $booking->farm->name_en,
                    'name_ar' => $booking->farm->name_ar,
                    'main_image' => $booking->farm->mainImage ? $booking->farm->mainImage->image_path : null,
                    'city' => $booking->farm->city ? [
                        'id' => $booking->farm->city->id,
                        'name_ar' => $booking->farm->city->name_ar,
                        'name_en' => $booking->farm->city->name_en,
                    ] : null,
                    'area' => $booking->farm->area ? [
                        'id' => $booking->farm->area->id,
                        'name_ar' => $booking->farm->area->name_ar,
                        'name_en' => $booking->farm->area->name_en,
                    ] : null,
                ],
                'price_type' => $booking->price_type,
                'price_type_label' => __('farm.price_types.' . $booking->price_type),
                'booking_dates' => $booking->formatted_booking_dates,
                'guest_count' => $booking->guest_count,
                'subtotal' => $booking->subtotal,
                'discount_amount' => $booking->discount_amount,
                'total_amount' => $booking->total_amount,
                'deposit_amount' => $booking->deposit_amount,
                'remaining_amount' => $booking->remaining_amount,
                'payment_type' => $isDepositPayment ? 'deposit' : 'full',
                'payment_status' => $booking->payment_status,
                'booking_status' => $booking->booking_status,
                'customer_name' => $booking->customer_name,
                'customer_email' => $booking->customer_email,
                'customer_phone' => $booking->customer_phone,
                'notes' => $booking->notes,
                'expires_at' => $booking->expires_at,
                'created_at' => $booking->created_at,
                'updated_at' => $booking->updated_at,
                
                // Additional user-friendly fields
                'can_be_cancelled' => $booking->canBeCancelled(),
                // 'is_expired' => $booking->isExpired(),
                'amount_paid' => $booking->amount_paid,
                'duration_in_days' => $booking->duration_in_days,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
            ], null, 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'get booking details', 'booking_id' => $bookingId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Cancel user's booking
     */
    public function cancel($bookingId): JsonResponse
    {
        try {
            $booking = FarmBooking::where('id', $bookingId)
                ->where('user_id', auth('sanctum')->id())
                ->first();

            if (!$booking) {
                return $this->errorResponse(__('booking.not_found'), 404);
            }

            if (!$booking->canBeCancelled()) {
                return $this->errorResponse(__('booking.cannot_be_cancelled'), 400);
            }

            $booking->cancel();

            return $this->successResponse(true, [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'new_status' => $booking->fresh()->booking_status,
                'message' => __('booking.cancelled_successfully'),
            ], __('booking.cancelled_successfully'), 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'cancel user booking', 'booking_id' => $bookingId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
}