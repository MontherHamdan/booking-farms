<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreAreaRequest;
use App\Http\Requests\Dashboard\UpdateAreaRequest;
use App\Models\Area;
use App\Models\City;
use App\Traits\LogErrorAndRedirectTrait;

class AreaController extends Controller
{
    use LogErrorAndRedirectTrait;
    
    /**
     * Display a listing of the areas.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $query = Area::with('city');
            
            // Search functionality
            if (request('search')) {
                $search = request('search');
                $query->where(function($q) use ($search) {
                    $q->where('name_en', 'LIKE', "%{$search}%")
                      ->orWhere('name_ar', 'LIKE', "%{$search}%");
                });
            }
            
            // City filter
            if (request('city_filter')) {
                $query->where('city_id', request('city_filter'));
            }
            
            // Status filter
            if (request('status_filter')) {
                $query->where('status', request('status_filter'));
            }
            
            // Sorting
            $sortBy = request('sort', 'order');
            $direction = request('direction', 'asc');
            
            switch ($sortBy) {
                case 'city_name':
                    $query->join('cities', 'areas.city_id', '=', 'cities.id')
                          ->orderBy('cities.name_en', $direction)
                          ->select('areas.*');
                    break;
                case 'name_en':
                case 'status':
                case 'order':
                case 'created_at':
                    $query->orderBy($sortBy, $direction);
                    break;
                default:
                    $query->orderBy('order', 'asc');
            }
            
            // If no primary sorting by order, add it as secondary
            if ($sortBy !== 'order') {
                $query->orderBy('order', 'asc');
            }
            
            $areas = $query->paginate(7);
            
            // Get cities for filter dropdown
            $cities = City::published()->ordered()->get();
            
            return view('admin.areas.index', compact('areas', 'cities'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in area page: ');
            return abort(500);
        }
    }

    /**
     * Show the form for creating a new area.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            $cities = City::published()->ordered()->get();
            
            return view('admin.areas.create', compact('cities'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in area create page: ');
            return abort(500);
        }
    }

    /**
     * Store a newly created area in storage.
     *
     * @param  \App\Http\Requests\StoreAreaRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreAreaRequest $request)
    {
        try {
            $validated = $request->validated();

            // Default ordering per city
            if (empty($validated['order'])) {
                $validated['order'] = (Area::where('city_id', $validated['city_id'])->max('order') ?? 0) + 1;
            }

            Area::create($validated);

            return redirect()->route('dashboard.areas.index')
                             ->with('success', 'Area created successfully.');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error storing area: ');

            return redirect()->back()
                             ->with('error', 'Internal server error. Please try again later.')
                             ->withInput();
        }
    }

    /**
     * Show the form for editing the specified area.
     *
     * @param  int  $area_id
     * @return \Illuminate\View\View
     */
    public function edit($area_id)
    {
        try {
            $area = Area::findOrFail($area_id);
            $cities = City::published()->ordered()->get();
            
            return view('admin.areas.edit', compact('area', 'cities'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in area edit page: ');
            
            return redirect()->route('dashboard.areas.index')
                ->with('error', 'Internal server error. Please try again later.');
        }
    }

    /**
     * Update the specified area in storage.
     *
     * @param  \App\Http\Requests\UpdateAreaRequest  $request
     * @param  int  $area_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateAreaRequest $request, $area_id)
    {
        try {
            $area = Area::findOrFail($area_id);
            $validated = $request->validated();

            $area->update($validated);

            return redirect()->route('dashboard.areas.index')
                             ->with('success', 'Area updated successfully.');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error updating area: ');

            return redirect()->back()
                             ->with('error', 'Internal server error. Please try again later.')
                             ->withInput();
        }
    }

    /**
     * Remove the specified area from storage.
     *
     * @param  int  $area_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($area_id)
    {
        try {
            $area = Area::findOrFail($area_id);
            $area->delete();
            
            return redirect()->route('dashboard.areas.index')
                ->with('success', 'Area deleted successfully.');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error deleting area: ');
            
            return redirect()->back()
                ->with('error', 'Internal server error. Please try again later.');
        }
    }
}