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
     * Get user's bookings - ONLY confirmed, completed, and cancelled
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = FarmBooking::with([
                    'farm:id,name_ar,name_en',
                    'farm.mainImage:id,farm_id,image_path'
                ])
                ->where('user_id', auth('sanctum')->id());

            // Filter by specific status if requested
            if ($request->status) {
                $allowedStatuses = ['confirmed', 'completed', 'cancelled'];
                if (in_array($request->status, $allowedStatuses)) {
                    $query->where('booking_status', $request->status);
                } else {
                    return $this->errorResponse('Invalid status. Allowed: confirmed, completed, cancelled', 400);
                }
            } else {
                // Show only user-relevant bookings (confirmed, completed, cancelled)
                $query->whereIn('booking_status', [
                    FarmBooking::BOOKING_STATUS_CONFIRMED,
                    FarmBooking::BOOKING_STATUS_COMPLETED,
                    FarmBooking::BOOKING_STATUS_CANCELLED,
                ]);
            }

            $bookings = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 10);

            // Transform the booking data
            $bookings->getCollection()->transform(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'farm' => [
                        'id' => $booking->farm->id,
                        'name_en' => $booking->farm->name_en,
                        'name_ar' => $booking->farm->name_ar,
                        'main_image' => $booking->farm->mainImage ? url($booking->farm->mainImage->image_path) : null,
                    ],
                    'price_type' => $booking->price_type,
                    'price_type_label' => __('farm.price_types.' . $booking->price_type),
                    'booking_dates' => $booking->formatted_booking_dates,
                    'start_date' => $booking->start_date?->format('Y-m-d'),
                    'end_date' => $booking->end_date?->format('Y-m-d'),
                    'start_time' => $booking->start_time?->format('H:i'),
                    'end_time' => $booking->end_time?->format('H:i'),
                    'booking_period' => $booking->booking_period,
                    'time_range' => $booking->booking_time_range,
                    'duration_in_days' => $booking->duration_in_days,
                    'guest_count' => $booking->guest_count,
                    'total_amount' => $booking->total_amount,
                    'amount_paid' => $booking->amount_paid,
                    'remaining_amount' => $booking->remaining_amount,
                    'payment_status' => $booking->payment_status,
                    'booking_status' => $booking->booking_status,
                    'can_be_cancelled' => $booking->canBeCancelled(),
                    'created_at' => $booking->created_at,
                    'updated_at' => $booking->updated_at,
                ];
            });

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
                ->whereIn('booking_status', [
                    FarmBooking::BOOKING_STATUS_CONFIRMED,
                    FarmBooking::BOOKING_STATUS_COMPLETED,
                    FarmBooking::BOOKING_STATUS_CANCELLED,
                ])
                ->first();

            if (!$booking) {
                return $this->errorResponse(__('booking.not_found'), 404);
            }

            return $this->successResponse(true, [
                'id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'farm' => [
                    'id' => $booking->farm->id,
                    'name_en' => $booking->farm->name_en,
                    'name_ar' => $booking->farm->name_ar,
                    'main_image' => $booking->farm->mainImage ? url($booking->farm->mainImage->image_path) : null,
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
                'start_date' => $booking->start_date?->format('Y-m-d'),
                'end_date' => $booking->end_date?->format('Y-m-d'),
                'start_time' => $booking->start_time?->format('H:i'),
                'end_time' => $booking->end_time?->format('H:i'),
                'formatted_start_datetime' => $booking->formatted_start_datetime,
                'formatted_end_datetime' => $booking->formatted_end_datetime,
                'booking_period' => $booking->booking_period,
                'time_range' => $booking->booking_time_range,
                'guest_count' => $booking->guest_count,
                'subtotal' => $booking->subtotal,
                'discount_amount' => $booking->discount_amount,
                'total_amount' => $booking->total_amount,
                'deposit_amount' => $booking->deposit_amount,
                'remaining_amount' => $booking->remaining_amount,
                'payment_option' => $booking->payment_option,
                'payment_status' => $booking->payment_status,
                'booking_status' => $booking->booking_status,
                'customer_name' => $booking->customer_name,
                'customer_email' => $booking->customer_email,
                'customer_phone' => $booking->customer_phone,
                'notes' => $booking->notes,
                'created_at' => $booking->created_at,
                'updated_at' => $booking->updated_at,
                'can_be_cancelled' => $booking->canBeCancelled(),
                'amount_paid' => $booking->amount_paid,
                'duration_in_days' => $booking->duration_in_days,
                'is_deposit_payment' => $booking->hasDepositPayment(),
                'booking_summary' => $booking->booking_summary,
            ], null, 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'get booking details', 'booking_id' => $bookingId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Cancel user's booking (only confirmed bookings can be cancelled)
     */
    public function cancel(Request $request, $bookingId): JsonResponse
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

            // Cancel the booking
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