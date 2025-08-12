<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingCollection;
use App\Http\Resources\ShowBookingResource;
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

            return $this->successResponse(true, new BookingCollection($bookings), null, 200);

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

            return $this->successResponse(true, new ShowBookingResource($booking), null, 200);

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