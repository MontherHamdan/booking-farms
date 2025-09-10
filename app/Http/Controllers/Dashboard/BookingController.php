<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FarmBooking;
use App\Models\Farm;
use App\Models\User;
use App\Traits\LogErrorAndRedirectTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    use LogErrorAndRedirectTrait;
    
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request)
    {
        try {
            $query = FarmBooking::with(['farm', 'user', 'coupon']);
            
            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('booking_reference', 'LIKE', "%{$search}%")
                      ->orWhere('customer_name', 'LIKE', "%{$search}%")
                      ->orWhere('customer_email', 'LIKE', "%{$search}%")
                      ->orWhereHas('farm', function($farmQuery) use ($search) {
                          $farmQuery->where('name_en', 'LIKE', "%{$search}%")
                                   ->orWhere('name_ar', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'LIKE', "%{$search}%")
                                   ->orWhere('email', 'LIKE', "%{$search}%");
                      });
                });
            }
            
            // Status filter
            if ($request->filled('booking_status')) {
                $query->where('booking_status', $request->booking_status);
            }
            
            // Payment status filter
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }
            
            // Farm filter
            if ($request->filled('farm_id')) {
                $query->where('farm_id', $request->farm_id);
            }
            
            // Date range filter
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Amount range filter
            if ($request->filled('amount_from')) {
                $query->where('total_amount', '>=', $request->amount_from);
            }
            
            if ($request->filled('amount_to')) {
                $query->where('total_amount', '<=', $request->amount_to);
            }
            
            // Sorting
            $sortBy = $request->get('sort', 'created_at');
            $direction = $request->get('direction', 'desc');
            
            switch ($sortBy) {
                case 'farm_name':
                    $query->join('farms', 'farm_bookings.farm_id', '=', 'farms.id')
                          ->orderBy('farms.name_en', $direction)
                          ->select('farm_bookings.*');
                    break;
                case 'customer_name':
                case 'booking_reference':
                case 'total_amount':
                case 'booking_status':
                case 'payment_status':
                case 'created_at':
                case 'start_date':
                    $query->orderBy($sortBy, $direction);
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
            
            $bookings = $query->paginate(15);
            
            // Get filter options
            $farms = Farm::select('id', 'name_en', 'name_ar')->orderBy('name_en')->get();
            
            return view('admin.bookings.index', compact('bookings', 'farms'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in bookings listing: ');
            return abort(500);
        }
    }
    
    /**
     * Display the specified booking.
     */
    public function show($id)
    {
        try {
            $booking = FarmBooking::with([
                'farm.user', 'farm.city', 'farm.area', 'farm.images',
                'user', 'coupon'
            ])->findOrFail($id);
            
            // Get related bookings from the same user
            $relatedBookings = FarmBooking::where('user_id', $booking->user_id)
                ->where('id', '!=', $booking->id)
                ->with('farm')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            return view('admin.bookings.show', compact('booking', 'relatedBookings'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error viewing booking: ');
            return redirect()->route('dashboard.bookings.index')
                ->with('error', 'Booking not found or error occurred.');
        }
    }
    
    /**
     * Get booking statistics for dashboard.
     */
    public function statistics(Request $request)
    {
        try {
            $period = $request->get('period', 'today'); // today, week, month, year
            
            $startDate = match($period) {
                'today' => Carbon::today(),
                'week' => Carbon::now()->startOfWeek(),
                'month' => Carbon::now()->startOfMonth(),
                'year' => Carbon::now()->startOfYear(),
                default => Carbon::today(),
            };
            
            $endDate = match($period) {
                'today' => Carbon::today()->endOfDay(),
                'week' => Carbon::now()->endOfWeek(),
                'month' => Carbon::now()->endOfMonth(),
                'year' => Carbon::now()->endOfYear(),
                default => Carbon::today()->endOfDay(),
            };
            
            $baseQuery = FarmBooking::whereBetween('created_at', [$startDate, $endDate]);
            
            $stats = [
                'period' => $period,
                'period_label' => ucfirst($period),
                'total_bookings' => (clone $baseQuery)->count(),
                'confirmed_bookings' => (clone $baseQuery)->where('booking_status', 'confirmed')->count(),
                'pending_bookings' => (clone $baseQuery)->where('booking_status', 'pending')->count(),
                'cancelled_bookings' => (clone $baseQuery)->where('booking_status', 'cancelled')->count(),
                'total_revenue' => (clone $baseQuery)->where('booking_status', 'confirmed')->sum('total_amount'),
                'average_booking_value' => (clone $baseQuery)->where('booking_status', 'confirmed')->avg('total_amount'),
                'unique_customers' => (clone $baseQuery)->distinct('user_id')->count('user_id'),
                'bookings_with_coupons' => (clone $baseQuery)->whereNotNull('coupon_id')->count(),
            ];
            
            // Get daily breakdown for charts
            $dailyStats = [];
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $endDate) {
                $dayStart = $currentDate->copy()->startOfDay();
                $dayEnd = $currentDate->copy()->endOfDay();
                
                $dailyStats[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'date_label' => $currentDate->format('M d'),
                    'bookings' => FarmBooking::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    'revenue' => FarmBooking::whereBetween('created_at', [$dayStart, $dayEnd])
                        ->where('booking_status', 'confirmed')
                        ->sum('total_amount'),
                ];
                
                $currentDate->addDay();
            }
            
            $stats['daily_breakdown'] = $dailyStats;
            
            return response()->json($stats);
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error getting booking statistics: ');
            return response()->json(['error' => 'Error fetching statistics'], 500);
        }
    }
    
    /**
     * Export bookings data.
     */
    public function export(Request $request)
    {
        try {
            $query = FarmBooking::with(['farm', 'user', 'coupon']);
            
            // Apply same filters as index
            if ($request->filled('booking_status')) {
                $query->where('booking_status', $request->booking_status);
            }
            
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }
            
            if ($request->filled('farm_id')) {
                $query->where('farm_id', $request->farm_id);
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            $bookings = $query->orderBy('created_at', 'desc')->get();
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="bookings_export_' . date('Y-m-d') . '.csv"',
            ];
            
            $callback = function() use ($bookings) {
                $file = fopen('php://output', 'w');
                
                // Add CSV headers
                fputcsv($file, [
                    'Booking Reference',
                    'Farm Name',
                    'Customer Name',
                    'Customer Email',
                    'Price Type',
                    'Start Date',
                    'End Date',
                    'Guest Count',
                    'Subtotal',
                    'Discount Amount',
                    'Coupon Code',
                    'Coupon Discount',
                    'Total Amount',
                    'Payment Option',
                    'Payment Status',
                    'Booking Status',
                    'Created At'
                ]);
                
                // Add data rows
                foreach ($bookings as $booking) {
                    fputcsv($file, [
                        $booking->booking_reference,
                        $booking->farm->name_en ?: $booking->farm->name_ar,
                        $booking->customer_name ?: $booking->user->name,
                        $booking->customer_email ?: $booking->user->email,
                        ucfirst(str_replace('_', ' ', $booking->price_type)),
                        $booking->start_date?->format('Y-m-d'),
                        $booking->end_date?->format('Y-m-d'),
                        $booking->guest_count,
                        $booking->subtotal,
                        $booking->discount_amount,
                        $booking->coupon_code,
                        $booking->coupon_discount_amount,
                        $booking->total_amount,
                        ucfirst($booking->payment_option),
                        ucfirst($booking->payment_status),
                        ucfirst($booking->booking_status),
                        $booking->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error exporting bookings: ');
            return redirect()->back()->with('error', 'Error exporting bookings.');
        }
    }
    
    /**
     * Update booking status (for admin actions).
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $booking = FarmBooking::findOrFail($id);
            
            $request->validate([
                'status' => 'required|in:confirmed,cancelled,failed',
                'reason' => 'nullable|string|max:500'
            ]);
            
            $oldStatus = $booking->booking_status;
            
            if ($request->status === 'cancelled') {
                $booking->cancel();
            } else {
                $booking->booking_status = $request->status;
                $booking->save();
            }
            
            // Log the status change
            \Log::info('Booking status updated by admin', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'admin_id' => auth()->id(),
                'reason' => $request->reason
            ]);
            
            return redirect()->back()
                ->with('success', "Booking status updated to {$request->status}.");
                
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error updating booking status: ');
            
            return redirect()->back()
                ->with('error', 'Error updating booking status. Please try again.');
        }
    }
    
    /**
     * Get booking reports data.
     */
    public function reports(Request $request)
    {
        try {
            $period = $request->get('period', 'month');
            $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
            $endDate = $request->get('end_date', Carbon::now()->endOfMonth());
            
            if (is_string($startDate)) {
                $startDate = Carbon::parse($startDate);
            }
            if (is_string($endDate)) {
                $endDate = Carbon::parse($endDate);
            }
            
            $baseQuery = FarmBooking::whereBetween('created_at', [$startDate, $endDate]);
            
            // Revenue by status
            $revenueByStatus = FarmBooking::select('booking_status', DB::raw('SUM(total_amount) as total'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('booking_status')
                ->get();
            
            // Top performing farms
            $topFarms = FarmBooking::select('farm_id', DB::raw('COUNT(*) as bookings_count'), DB::raw('SUM(total_amount) as total_revenue'))
                ->with('farm:id,name_en,name_ar')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('booking_status', 'confirmed')
                ->groupBy('farm_id')
                ->orderBy('total_revenue', 'desc')
                ->limit(10)
                ->get();
            
            // Booking trends by price type
            $bookingsByPriceType = FarmBooking::select('price_type', DB::raw('COUNT(*) as count'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('price_type')
                ->get();
            
            // Customer analytics
            $topCustomers = FarmBooking::select('user_id', DB::raw('COUNT(*) as bookings_count'), DB::raw('SUM(total_amount) as total_spent'))
                ->with('user:id,name,email')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('booking_status', 'confirmed')
                ->groupBy('user_id')
                ->orderBy('total_spent', 'desc')
                ->limit(10)
                ->get();
            
            return view('admin.bookings.reports', compact(
                'revenueByStatus', 
                'topFarms', 
                'bookingsByPriceType', 
                'topCustomers',
                'startDate',
                'endDate'
            ));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error generating booking reports: ');
            return redirect()->route('dashboard.bookings.index')
                ->with('error', 'Error generating reports.');
        }
    }
}