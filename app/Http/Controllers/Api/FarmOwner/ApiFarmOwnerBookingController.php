<?php

namespace App\Http\Controllers\Api\FarmOwner;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmOwnerBookingCollection;
use App\Http\Resources\FarmOwnerShowBookingResource;
use App\Models\FarmBooking;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ApiFarmOwnerBookingController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;

    /**
     * Get all bookings for farm owner's farms
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $farmOwnerId = Auth::id();
            
            $query = FarmBooking::with([
                'farm:id,name_ar,name_en,user_id',
                'farm.mainImage:id,farm_id,image_path',
                'user:id,name,email,phone',
                'coupon:id,code,name'
            ])
            ->whereHas('farm', function ($q) use ($farmOwnerId) {
                $q->where('user_id', $farmOwnerId);
            });

            // Filter by booking status
            $status = $request->input('status');
            if ($status && in_array($status, ['pending', 'confirmed', 'failed', 'cancelled', 'completed'])) {
                $query->where('booking_status', $status);
            } else {
                // Default: show confirmed, completed, and cancelled bookings (exclude pending and failed)
                $query->whereIn('booking_status', [
                    FarmBooking::BOOKING_STATUS_CONFIRMED,
                    FarmBooking::BOOKING_STATUS_COMPLETED,
                    FarmBooking::BOOKING_STATUS_CANCELLED,
                ]);
            }

            // Filter by farm
            if ($request->filled('farm_id')) {
                $query->whereHas('farm', function ($q) use ($request, $farmOwnerId) {
                    $q->where('id', $request->farm_id)
                      ->where('user_id', $farmOwnerId); // Ensure farm belongs to owner
                });
            }

            // Filter by date range
            if ($request->filled('from_date')) {
                $query->where('start_date', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->where('end_date', '<=', $request->to_date);
            }

            // Filter by payment status
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            // Search by booking reference or customer name
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('booking_reference', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_email', 'like', "%{$search}%");
                });
            }

            $bookings = $query->orderBy('created_at', 'desc')
                            ->paginate($request->per_page ?? 15);

            return $this->successResponse(true, new FarmOwnerBookingCollection($bookings), null, 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'get farm owner bookings',
                'user_id' => Auth::id(),
                'filters' => $request->only(['status', 'farm_id', 'from_date', 'to_date', 'payment_status', 'search'])
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get specific booking details
     */
    public function show($bookingId): JsonResponse
    {
        try {
            $farmOwnerId = Auth::id();

            $booking = FarmBooking::with([
                'farm:id,name_ar,name_en,description_ar,description_en,user_id,city_id,area_id',
                'farm.city:id,name_ar,name_en',
                'farm.area:id,name_ar,name_en',
                'farm.mainImage:id,farm_id,image_path',
                'user:id,name,email,phone,avatar',
                'coupon:id,code,name,discount_description'
            ])
            ->whereHas('farm', function ($q) use ($farmOwnerId) {
                $q->where('user_id', $farmOwnerId);
            })
            ->where('id', $bookingId)
            ->first();

            if (!$booking) {
                return $this->errorResponse(__('booking.not_found'), 404);
            }

            return $this->successResponse(true, new FarmOwnerShowBookingResource($booking), null, 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'show farm owner booking',
                'booking_id' => $bookingId,
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get booking statistics for farm owner
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $farmOwnerId = Auth::id();
            
            // Base query for farm owner's bookings
            $baseQuery = FarmBooking::whereHas('farm', function ($q) use ($farmOwnerId) {
                $q->where('user_id', $farmOwnerId);
            })->whereIn('booking_status', [
                FarmBooking::BOOKING_STATUS_CONFIRMED,
                FarmBooking::BOOKING_STATUS_COMPLETED,
                FarmBooking::BOOKING_STATUS_CANCELLED,
            ]);

            // Get date range (default to current month)
            $fromDate = $request->input('from_date', now()->startOfMonth());
            $toDate = $request->input('to_date', now()->endOfMonth());

            $stats = [
                'period' => [
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                ],
                'bookings' => [
                    'total' => (clone $baseQuery)->whereBetween('created_at', [$fromDate, $toDate])->count(),
                    'confirmed' => (clone $baseQuery)->where('booking_status', 'confirmed')
                                                    ->whereBetween('created_at', [$fromDate, $toDate])->count(),
                    'completed' => (clone $baseQuery)->where('booking_status', 'completed')
                                                    ->whereBetween('created_at', [$fromDate, $toDate])->count(),
                    'cancelled' => (clone $baseQuery)->where('booking_status', 'cancelled')
                                                    ->whereBetween('created_at', [$fromDate, $toDate])->count(),
                ],
                'revenue' => [
                    'total_bookings_value' => (clone $baseQuery)->whereBetween('created_at', [$fromDate, $toDate])
                                                               ->sum('total_amount'),
                    'total_earnings' => (clone $baseQuery)->whereBetween('created_at', [$fromDate, $toDate])
                                                         ->where('earnings_processed', true)
                                                         ->sum('farm_owner_earning'),
                    'total_commission' => (clone $baseQuery)->whereBetween('created_at', [$fromDate, $toDate])
                                                           ->where('earnings_processed', true)
                                                           ->sum('platform_commission_amount'),
                    'average_booking_value' => (clone $baseQuery)->whereBetween('created_at', [$fromDate, $toDate])
                                                                 ->avg('total_amount') ?: 0,
                ],
                'by_farm' => $this->getBookingsByFarm($farmOwnerId, $fromDate, $toDate),
                'by_month' => $this->getBookingsByMonth($farmOwnerId),
            ];

            return $this->successResponse(true, $stats, null, 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'get farm owner booking statistics',
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get recent bookings for farm owner dashboard
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $farmOwnerId = Auth::id();
            $limit = $request->input('limit', 5);

            $recentBookings = FarmBooking::with([
                'farm:id,name_ar,name_en',
                'farm.mainImage:id,farm_id,image_path'
            ])
            ->whereHas('farm', function ($q) use ($farmOwnerId) {
                $q->where('user_id', $farmOwnerId);
            })
            ->whereIn('booking_status', ['confirmed', 'completed', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

            $formattedBookings = $recentBookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'customer_name' => $booking->customer_name,
                    'farm' => [
                        'id' => $booking->farm->id,
                        'name_en' => $booking->farm->name_en,
                        'name_ar' => $booking->farm->name_ar,
                        'main_image' => $booking->farm->mainImage ? url($booking->farm->mainImage->image_path) : null,
                    ],
                    'booking_period' => $booking->booking_period,
                    'total_amount' => $booking->total_amount,
                    'farm_owner_earning' => $booking->farm_owner_earning,
                    'booking_status' => $booking->booking_status,
                    'booking_status_label' => __('booking.status.' . $booking->booking_status),
                    'payment_status' => $booking->payment_status,
                    'payment_status_label' => __('booking.payment_status.' . $booking->payment_status),
                    'created_at' => $booking->created_at,
                ];
            });

            return $this->successResponse(true, $formattedBookings, null, 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'get recent bookings',
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get bookings grouped by farm
     */
    private function getBookingsByFarm($farmOwnerId, $fromDate, $toDate): array
    {
        $bookingsByFarm = FarmBooking::with('farm:id,name_ar,name_en')
                                   ->whereHas('farm', function ($q) use ($farmOwnerId) {
                                       $q->where('user_id', $farmOwnerId);
                                   })->whereIn('booking_status', [
                                        FarmBooking::BOOKING_STATUS_CONFIRMED,
                                        FarmBooking::BOOKING_STATUS_COMPLETED,
                                        FarmBooking::BOOKING_STATUS_CANCELLED,
                                    ])
                                   ->whereBetween('created_at', [$fromDate, $toDate])
                                   ->selectRaw('farm_id, COUNT(*) as bookings_count, SUM(total_amount) as total_revenue, SUM(farm_owner_earning) as total_earnings')
                                   ->groupBy('farm_id')
                                   ->get();

        return $bookingsByFarm->map(function ($item) {
            return [
                'farm_id' => $item->farm_id,
                'farm' => [
                    'id' => $item->farm_id,
                    'name_en' => $item->farm->name_en,
                    'name_ar' => $item->farm->name_ar,
                ],
                'bookings_count' => $item->bookings_count,
                'total_revenue' => $item->total_revenue,
                'total_earnings' => $item->total_earnings ?: 0,
            ];
        })->toArray();
    }

    /**
     * Get bookings by month for the last 12 months
     */
    private function getBookingsByMonth($farmOwnerId): array
    {
        $monthlyStats = FarmBooking::whereHas('farm', function ($q) use ($farmOwnerId) {
                                    $q->where('user_id', $farmOwnerId);
                                 })->whereIn('booking_status', [
                                    FarmBooking::BOOKING_STATUS_CONFIRMED,
                                    FarmBooking::BOOKING_STATUS_COMPLETED,
                                    FarmBooking::BOOKING_STATUS_CANCELLED,
                                ])
                                 ->where('created_at', '>=', now()->subMonths(12))
                                 ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as bookings_count, SUM(total_amount) as total_revenue, SUM(farm_owner_earning) as total_earnings')
                                 ->groupBy('month')
                                 ->orderBy('month')
                                 ->get();

        return $monthlyStats->map(function ($item) {
            return [
                'month' => $item->month,
                'bookings_count' => $item->bookings_count,
                'total_revenue' => $item->total_revenue,
                'total_earnings' => $item->total_earnings ?: 0,
            ];
        })->toArray();
    }
}