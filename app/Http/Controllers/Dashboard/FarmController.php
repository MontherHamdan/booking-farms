<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\City;
use App\Models\Area;
use App\Models\Feature;
use App\Models\User;
use App\Traits\LogErrorAndRedirectTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FarmController extends Controller
{
    use LogErrorAndRedirectTrait;
    
    /**
     * Display a listing of farms.
     */
    public function index(Request $request)
    {
        try {
            $query = Farm::with(['user', 'city', 'area', 'images', 'pricing', 'offers'])
                         ->withCount(['ratings', 'bookings']);
            
            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name_en', 'LIKE', "%{$search}%")
                      ->orWhere('name_ar', 'LIKE', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'LIKE', "%{$search}%")
                                   ->orWhere('email', 'LIKE', "%{$search}%");
                      });
                });
            }
            
            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // City filter
            if ($request->filled('city_id')) {
                $query->where('city_id', $request->city_id);
            }
            
            // Owner filter
            if ($request->filled('owner_id')) {
                $query->where('user_id', $request->owner_id);
            }
            
            // Sorting
            $sortBy = $request->get('sort', 'created_at');
            $direction = $request->get('direction', 'desc');
            
            switch ($sortBy) {
                case 'owner_name':
                    $query->join('users', 'farms.user_id', '=', 'users.id')
                          ->orderBy('users.name', $direction)
                          ->select('farms.*');
                    break;
                case 'city_name':
                    $query->join('cities', 'farms.city_id', '=', 'cities.id')
                          ->orderBy('cities.name_en', $direction)
                          ->select('farms.*');
                    break;
                case 'bookings_count':
                case 'ratings_count':
                case 'status':
                case 'created_at':
                case 'updated_at':
                    $query->orderBy($sortBy, $direction);
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
            
            $farms = $query->paginate(10);
            
            // Get filter options
            $cities = City::published()->ordered()->get();
            // $owners = User::whereHas('farms')->orderBy('name')->get();
            $owners = User::orderBy('name')->get();
            
            return view('admin.farms.index', compact('farms', 'cities', 'owners'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in farms listing: ');
            return abort(500);
        }
    }
    
    /**
     * Display the specified farm.
     */
    public function show($id)
    {
        try {
            $farm = Farm::with([
                'user', 'city', 'area', 'features', 'images', 'pricing', 
                'offers', 'ratings.user', 'bookings.user'
            ])->findOrFail($id);
            
            // Get recent bookings for this farm
            $recentBookings = $farm->bookings()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Calculate stats
            $stats = [
                'total_bookings' => $farm->bookings()->count(),
                'confirmed_bookings' => $farm->bookings()->where('booking_status', 'confirmed')->count(),
                'pending_bookings' => $farm->bookings()->where('booking_status', 'pending')->count(),
                'total_revenue' => $farm->bookings()->where('booking_status', 'confirmed')->sum('total_amount'),
                'average_rating' => $farm->ratings()->avg('rating'),
                'total_ratings' => $farm->ratings()->count(),
            ];
            
            return view('admin.farms.show', compact('farm', 'recentBookings', 'stats'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error viewing farm: ');
            return redirect()->route('dashboard.farms.index')
                ->with('error', 'Farm not found or error occurred.');
        }
    }
    
    /**
     * Show the form for editing the specified farm.
     */
    public function edit($id)
    {
        try {
            $farm = Farm::with(['features', 'images', 'pricing', 'offers'])->findOrFail($id);
            $cities = City::published()->ordered()->get();
            $areas = Area::where('city_id', $farm->city_id)->published()->ordered()->get();
            $features = Feature::ordered()->get();
            
            return view('admin.farms.edit', compact('farm', 'cities', 'areas', 'features'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in farm edit page: ');
            return redirect()->route('dashboard.farms.index')
                ->with('error', 'Farm not found or error occurred.');
        }
    }
    
    /**
     * Update the specified farm in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $farm = Farm::findOrFail($id);
            
            // Validate the request
            $validated = $request->validate([
                'name_ar' => 'nullable|string|max:255',
                'name_en' => 'nullable|string|max:255', 
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'city_id' => 'required|exists:cities,id',
                'area_id' => 'required|exists:areas,id',
                'guest_count' => 'required|integer|min:1',
                'deposit_rate' => 'nullable|numeric|min:0|max:100',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'features' => 'nullable|array',
                'features.*' => 'exists:features,id',
                'status' => 'required|in:pending,active,rejected,disabled',
            ]);
            
            DB::beginTransaction();
            
            // Update basic farm information
            $farm->update($validated);
            
            // Update features
            if ($request->has('features')) {
                $farm->features()->sync($request->features);
            }
            
            DB::commit();
            
            return redirect()->route('dashboard.farms.show', $farm->id)
                ->with('success', 'Farm updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logErrorAndRedirect($e, 'Error updating farm: ');
            
            return redirect()->back()
                ->with('error', 'Error updating farm. Please try again.')
                ->withInput();
        }
    }
    
    /**
     * Update farm status.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $farm = Farm::findOrFail($id);
            
            $request->validate([
                'status' => 'required|in:pending,active,rejected,disabled',
                'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500'
            ]);
            
            $oldStatus = $farm->status;
            $farm->status = $request->status;
            
            // If rejecting, store the reason
            if ($request->status === 'rejected' && $request->filled('rejection_reason')) {
                // You might want to add a rejection_reason column to farms table
                // or create a separate farm_status_logs table
                $farm->rejection_reason = $request->rejection_reason;
            }
            
            $farm->save();
            
            // You could add notification logic here to inform the farm owner
            
            $statusMessages = [
                'active' => 'Farm has been approved and is now active.',
                'rejected' => 'Farm has been rejected.',
                'disabled' => 'Farm has been disabled.',
                'pending' => 'Farm status set to pending.'
            ];
            
            return redirect()->back()
                ->with('success', $statusMessages[$request->status]);
                
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error updating farm status: ');
            
            return redirect()->back()
                ->with('error', 'Error updating farm status. Please try again.');
        }
    }
    
    /**
     * Delete a farm image.
     */
    public function deleteImage(Request $request, $farmId, $imageId)
    {
        try {
            $farm = Farm::findOrFail($farmId);
            $image = $farm->images()->findOrFail($imageId);
            
            // Delete from S3
            if ($image->image_path) {
                $baseUrl = Storage::disk('s3')->url('');
                if (strpos($image->image_path, $baseUrl) === 0) {
                    $path = str_replace($baseUrl, '', $image->image_path);
                    Storage::disk('s3')->delete($path);
                }
            }
            
            // Delete from database
            $image->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error deleting farm image: ');
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting image.'
            ], 500);
        }
    }
    
    /**
     * Get areas by city (AJAX).
     */
    public function getAreasByCity($cityId)
    {
        try {
            $areas = Area::where('city_id', $cityId)
                ->published()
                ->ordered()
                ->get(['id', 'name_en', 'name_ar']);
                
            return response()->json($areas);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load areas'], 500);
        }
    }
    
    /**
     * Bulk status update.
     */
    public function bulkStatusUpdate(Request $request)
    {
        try {
            $request->validate([
                'farm_ids' => 'required|array',
                'farm_ids.*' => 'exists:farms,id',
                'status' => 'required|in:active,rejected,disabled,pending'
            ]);
            
            $updated = Farm::whereIn('id', $request->farm_ids)
                ->update(['status' => $request->status]);
            
            return redirect()->back()
                ->with('success', "Successfully updated {$updated} farm(s) status to {$request->status}.");
                
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in bulk status update: ');
            
            return redirect()->back()
                ->with('error', 'Error updating farm statuses. Please try again.');
        }
    }
}