<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreCouponRequest;
use App\Http\Requests\Dashboard\UpdateCouponRequest;
use App\Models\Coupon;
use App\Models\City;
use App\Traits\LogErrorAndRedirectTrait;

class CouponController extends Controller
{
    use LogErrorAndRedirectTrait;
    
    /**
     * Display a listing of the coupons.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $query = Coupon::with('usages')->withCount('usages'); // Add usage count
            
            // Search functionality
            if (request('search')) {
                $search = request('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('code', 'LIKE', "%{$search}%");
                });
            }
            
            // Status filter
            if (request('status_filter')) {
                $query->where('is_active', request('status_filter') === 'active');
            }
            
            // Platform filter
            if (request('platform_filter')) {
                $query->where('platform', request('platform_filter'));
            }
            
            // Discount type filter
            if (request('discount_type_filter')) {
                $query->where('discount_type', request('discount_type_filter'));
            }
            
            // Date filter
            if (request('date_filter')) {
                $now = now();
                switch (request('date_filter')) {
                    case 'active':
                        $query->where('start_date', '<=', $now)
                              ->where('end_date', '>=', $now);
                        break;
                    case 'upcoming':
                        $query->where('start_date', '>', $now);
                        break;
                    case 'expired':
                        $query->where('end_date', '<', $now);
                        break;
                }
            }
            
            // Sorting
            $sortBy = request('sort', 'created_at');
            $direction = request('direction', 'desc');
            
            switch ($sortBy) {
                case 'usages_count':
                    $query->orderBy('usages_count', $direction);
                    break;
                case 'name':
                case 'code':
                case 'start_date':
                case 'end_date':
                case 'discount_value':
                case 'usage_limit':
                case 'created_at':
                    $query->orderBy($sortBy, $direction);
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
            
            $coupons = $query->paginate(10);
            
            // Get cities for filter dropdown
            $cities = City::published()->ordered()->get();
            
            return view('admin.coupons.index', compact('coupons', 'cities'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in coupon page: ');
            return abort(500);
        }
    }

    /**
     * Show the form for creating a new coupon.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            $cities = City::published()->ordered()->get();
            return view('admin.coupons.create', compact('cities'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in coupon create page: ');
            return abort(500);
        }
    }

    /**
     * Store a newly created coupon in storage.
     *
     * @param  \App\Http\Requests\StoreCouponRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCouponRequest $request)
    {
        try {
            $validated = $request->validated();

            Coupon::create($validated);

            return redirect()->route('dashboard.coupons.index')
                             ->with('success', 'Coupon created successfully.');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error storing coupon: ');

            return redirect()->back()
                             ->with('error', 'Internal server error. Please try again later.')
                             ->withInput();
        }
    }

    /**
     * Show the form for editing the specified coupon.
     *
     * @param  int  $coupon_id
     * @return \Illuminate\View\View
     */
    public function edit($coupon_id)
    {
        try {
            $coupon = Coupon::withCount('usages')->findOrFail($coupon_id);
            $cities = City::published()->ordered()->get();
            
            return view('admin.coupons.edit', compact('coupon', 'cities'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in coupon edit page: ');
            
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Internal server error. Please try again later.');
        }
    }

    /**
     * Update the specified coupon in storage.
     *
     * @param  \App\Http\Requests\UpdateCouponRequest  $request
     * @param  int  $coupon_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateCouponRequest $request, $coupon_id)
    {
        try {
            $coupon = Coupon::findOrFail($coupon_id);
            $validated = $request->validated();

            $coupon->update($validated);

            return redirect()->route('dashboard.coupons.index')
                             ->with('success', 'Coupon updated successfully.');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error updating coupon: ');

            return redirect()->back()
                             ->with('error', 'Internal server error. Please try again later.')
                             ->withInput();
        }
    }

    /**
     * Remove the specified coupon from storage.
     *
     * @param  int  $coupon_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($coupon_id)
    {
        try {
            $coupon = Coupon::withCount('usages')->findOrFail($coupon_id);
            
            // Check if coupon has been used
            if ($coupon->usages_count > 0) {
                return redirect()->back()
                    ->with('error', "Cannot delete coupon '{$coupon->name}' because it has been used {$coupon->usages_count} time(s).");
            }
            
            $coupon->delete();
            
            return redirect()->route('dashboard.coupons.index')
                ->with('success', 'Coupon deleted successfully.');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error deleting coupon: ');
            
            return redirect()->back()
                ->with('error', 'Internal server error. Please try again later.');
        }
    }

    /**
     * Show coupon usage details.
     *
     * @param  int  $coupon_id
     * @return \Illuminate\View\View
     */
    public function usages($coupon_id)
    {
        try {
            $coupon = Coupon::with(['usages.user', 'usages.booking'])
                           ->withCount('usages')
                           ->findOrFail($coupon_id);
            
            $usages = $coupon->usages()
                            ->with(['user', 'booking'])
                            ->orderBy('used_at', 'desc')
                            ->paginate(15);
            
            return view('admin.coupons.usages', compact('coupon', 'usages'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in coupon usages page: ');
            
            return redirect()->route('dashboard.coupons.index')
                ->with('error', 'Internal server error. Please try again later.');
        }
    }

    /**
     * Toggle coupon active status.
     *
     * @param  int  $coupon_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($coupon_id)
    {
        try {
            $coupon = Coupon::findOrFail($coupon_id);
            $coupon->update(['is_active' => !$coupon->is_active]);
            
            $status = $coupon->is_active ? 'activated' : 'deactivated';
            
            return redirect()->back()
                ->with('success', "Coupon '{$coupon->name}' has been {$status} successfully.");
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error toggling coupon status: ');
            
            return redirect()->back()
                ->with('error', 'Internal server error. Please try again later.');
        }
    }
}