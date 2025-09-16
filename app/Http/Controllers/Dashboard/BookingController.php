<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FarmBooking;
use App\Models\Farm;
use App\Models\User;
use App\Traits\LogErrorAndRedirectTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
     * Show the form for editing the specified booking.
     */
    public function edit($id)
    {
        try {
            $booking = FarmBooking::with([
                'farm.user', 'farm.city', 'farm.area', 'farm.pricing',
                'user', 'coupon'
            ])->findOrFail($id);
            
            // Get available farms for potential reassignment (admin only)
            $farms = Farm::with(['city', 'area'])
                ->where('status', 'approved')
                ->orderBy('name_en')
                ->get();
            
            return view('admin.bookings.edit', compact('booking', 'farms'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error loading booking edit form: ');
            return redirect()->route('dashboard.bookings.index')
                ->with('error', 'Booking not found or error occurred.');
        }
    }
    
    /**
     * Update the specified booking.
     */
    public function update(Request $request, $id)
    {
        try {
            $booking = FarmBooking::with(['farm', 'user'])->findOrFail($id);
            $originalBooking = clone $booking;
            
            $validated = $request->validate([
                // Customer Information
                'customer_name' => 'nullable|string|max:255',
                'customer_email' => 'nullable|email|max:255',
                'customer_phone' => 'nullable|string|max:20',
                
                // Booking Details
                'guest_count' => 'required|integer|min:1|max:100',
                'notes' => 'nullable|string|max:1000',
                
                // Dates and Times (for display, actual changes would need price recalculation)
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i',
                
                // Status Updates
                'booking_status' => [
                    'required', 
                    Rule::in(['pending', 'confirmed', 'cancelled', 'failed', 'completed'])
                ],
                'payment_status' => [
                    'required', 
                    Rule::in(['pending', 'paid', 'partially_paid', 'failed', 'expired', 'refunded'])
                ],
                
                // Admin-only fields
                'farm_id' => 'sometimes|exists:farms,id',
                
                // Update reason
                'update_reason' => 'required|string|max:500',
            ]);
            
            // Track changes for logging
            $changes = [];
            $criticalChanges = false;
            
            // Update basic fields
            foreach (['customer_name', 'customer_email', 'customer_phone', 'guest_count', 'notes'] as $field) {
                if (isset($validated[$field]) && $booking->$field !== $validated[$field]) {
                    $changes[$field] = [
                        'old' => $booking->$field,
                        'new' => $validated[$field]
                    ];
                    $booking->$field = $validated[$field];
                }
            }
            
            // Handle date/time changes (requires careful handling)
            if (isset($validated['start_date']) && $booking->start_date?->format('Y-m-d') !== $validated['start_date']) {
                $changes['start_date'] = [
                    'old' => $booking->start_date?->format('Y-m-d'),
                    'new' => $validated['start_date']
                ];
                $booking->start_date = Carbon::parse($validated['start_date']);
                $criticalChanges = true;
            }
            
            if (isset($validated['end_date']) && $booking->end_date?->format('Y-m-d') !== $validated['end_date']) {
                $changes['end_date'] = [
                    'old' => $booking->end_date?->format('Y-m-d'),
                    'new' => $validated['end_date']
                ];
                $booking->end_date = Carbon::parse($validated['end_date']);
                $criticalChanges = true;
            }
            
            // Handle time changes
            if (isset($validated['start_time'])) {
                $newStartTime = Carbon::parse($validated['start_time'])->format('H:i:s');
                if ($booking->start_time?->format('H:i:s') !== $newStartTime) {
                    $changes['start_time'] = [
                        'old' => $booking->start_time?->format('H:i'),
                        'new' => Carbon::parse($validated['start_time'])->format('H:i')
                    ];
                    $booking->start_time = Carbon::parse($validated['start_time']);
                }
            }
            
            if (isset($validated['end_time'])) {
                $newEndTime = Carbon::parse($validated['end_time'])->format('H:i:s');
                if ($booking->end_time?->format('H:i:s') !== $newEndTime) {
                    $changes['end_time'] = [
                        'old' => $booking->end_time?->format('H:i'),
                        'new' => Carbon::parse($validated['end_time'])->format('H:i')
                    ];
                    $booking->end_time = Carbon::parse($validated['end_time']);
                }
            }
            
            // Handle status changes with business logic
            $oldBookingStatus = $booking->booking_status;
            $oldPaymentStatus = $booking->payment_status;
            
            if ($validated['booking_status'] !== $oldBookingStatus) {
                $changes['booking_status'] = [
                    'old' => $oldBookingStatus,
                    'new' => $validated['booking_status']
                ];
                
                // Apply business logic for status changes
                $this->handleBookingStatusChange($booking, $validated['booking_status'], $oldBookingStatus);
                $criticalChanges = true;
            }
            
            if ($validated['payment_status'] !== $oldPaymentStatus) {
                $changes['payment_status'] = [
                    'old' => $oldPaymentStatus,
                    'new' => $validated['payment_status']
                ];
                
                // Apply business logic for payment status changes
                $this->handlePaymentStatusChange($booking, $validated['payment_status'], $oldPaymentStatus);
                $criticalChanges = true;
            }
            
            // Handle farm change (admin only - very critical)
            if (isset($validated['farm_id']) && $booking->farm_id !== $validated['farm_id']) {
                $oldFarm = $booking->farm;
                $newFarm = Farm::find($validated['farm_id']);
                
                if ($newFarm) {
                    $changes['farm_id'] = [
                        'old' => $oldFarm->name_en ?: $oldFarm->name_ar,
                        'new' => $newFarm->name_en ?: $newFarm->name_ar
                    ];
                    $booking->farm_id = $validated['farm_id'];
                    $criticalChanges = true;
                }
            }
            
            // Update booking dates array if dates changed
            if (isset($changes['start_date']) || isset($changes['end_date'])) {
                $booking->booking_dates = $this->generateBookingDatesArray(
                    $booking->start_date,
                    $booking->end_date
                );
            }
            
            // Save the booking
            $booking->save();
            
            // Log the update
            \Log::info('Booking updated by admin', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'admin_id' => auth()->id(),
                'changes' => $changes,
                'critical_changes' => $criticalChanges,
                'update_reason' => $validated['update_reason']
            ]);
            
            $message = 'Booking updated successfully.';
            if ($criticalChanges) {
                $message .= ' Critical changes were made - please review the booking details.';
            }
            
            return redirect()->route('dashboard.bookings.show', $booking->id)
                ->with('success', $message);
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error updating booking: ');
            
            return redirect()->back()
                ->with('error', 'Error updating booking. Please try again.')
                ->withInput();
        }
    }
    
    /**
     * Handle booking status changes with business logic
     */
    private function handleBookingStatusChange(FarmBooking $booking, string $newStatus, string $oldStatus): void
    {
        switch ($newStatus) {
            case 'confirmed':
                if ($oldStatus === 'pending') {
                    // Auto-process earnings if payment is already made
                    if (in_array($booking->payment_status, ['paid', 'partially_paid']) && !$booking->earnings_processed) {
                        try {
                            $booking->processEarnings();
                        } catch (\Exception $e) {
                            \Log::error('Failed to auto-process earnings during status change', [
                                'booking_id' => $booking->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
                break;
                
            case 'cancelled':
                // Handle cancellation logic
                if ($booking->earnings_processed || $booking->earnings_confirmed) {
                    try {
                        $walletService = app(\App\Services\FarmOwnerWalletService::class);
                        $walletService->processBookingRefund($booking);
                    } catch (\Exception $e) {
                        \Log::error('Failed to process refund during status change', [
                            'booking_id' => $booking->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                break;
                
            case 'completed':
                // Auto-confirm earnings if they're processed but not confirmed
                if ($booking->earnings_processed && !$booking->earnings_confirmed) {
                    try {
                        $booking->confirmEarnings();
                    } catch (\Exception $e) {
                        \Log::error('Failed to auto-confirm earnings during completion', [
                            'booking_id' => $booking->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                break;
        }
        
        $booking->booking_status = $newStatus;
    }
    
    /**
     * Handle payment status changes with business logic
     */
    private function handlePaymentStatusChange(FarmBooking $booking, string $newStatus, string $oldStatus): void
    {
        switch ($newStatus) {
            case 'paid':
                // If booking is confirmed and earnings not processed, process them
                if ($booking->booking_status === 'confirmed' && !$booking->earnings_processed) {
                    try {
                        $booking->processEarnings();
                    } catch (\Exception $e) {
                        \Log::error('Failed to auto-process earnings during payment status change', [
                            'booking_id' => $booking->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Reset remaining amount for full payment
                $booking->remaining_amount = 0;
                break;
                
            case 'partially_paid':
                // Set remaining amount if it's a deposit payment
                if ($booking->hasDepositPayment()) {
                    $booking->remaining_amount = $booking->total_amount - $booking->deposit_amount;
                }
                break;
                
            case 'refunded':
                // Handle refund logic if needed
                break;
        }
        
        $booking->payment_status = $newStatus;
    }
    
    /**
     * Generate booking dates array from start and end dates
     */
    private function generateBookingDatesArray(Carbon $startDate, Carbon $endDate): array
    {
        $dates = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }
        
        return $dates;
    }
    
    /**
     * Update booking status (enhanced version).
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $booking = FarmBooking::findOrFail($id);
            
            $request->validate([
                'status' => 'required|in:pending,confirmed,cancelled,failed,completed',
                'reason' => 'nullable|string|max:500'
            ]);
            
            $oldStatus = $booking->booking_status;
            $newStatus = $request->status;
            
            // Handle different status changes
            switch ($newStatus) {
                case 'cancelled':
                    $booking->cancel();
                    break;
                    
                case 'confirmed':
                    $booking->booking_status = 'confirmed';
                    // Auto-process earnings if payment is made
                    if (in_array($booking->payment_status, ['paid', 'partially_paid']) && !$booking->earnings_processed) {
                        try {
                            $booking->processEarnings();
                        } catch (\Exception $e) {
                            \Log::error('Failed to auto-process earnings during confirmation', [
                                'booking_id' => $booking->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    $booking->save();
                    break;
                    
                case 'completed':
                    $booking->booking_status = 'completed';
                    // Auto-confirm earnings if they're processed
                    if ($booking->earnings_processed && !$booking->earnings_confirmed) {
                        try {
                            $booking->confirmEarnings();
                        } catch (\Exception $e) {
                            \Log::error('Failed to auto-confirm earnings during completion', [
                                'booking_id' => $booking->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    $booking->save();
                    break;
                    
                case 'failed':
                    $booking->markAsFailed();
                    break;
                    
                case 'pending':
                    $booking->booking_status = 'pending';
                    $booking->save();
                    break;
                    
                default:
                    $booking->booking_status = $newStatus;
                    $booking->save();
            }
            
            // Log the status change
            \Log::info('Booking status updated by admin', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'admin_id' => auth()->id(),
                'reason' => $request->reason
            ]);
            
            return redirect()->back()
                ->with('success', "Booking status updated to {$newStatus}.");
                
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error updating booking status: ');
            
            return redirect()->back()
                ->with('error', 'Error updating booking status. Please try again.');
        }
    }

    // ... (rest of the existing methods: statistics, export, reports remain unchanged)
    
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