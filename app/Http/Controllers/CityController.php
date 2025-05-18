<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Models\City;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\LogErrorAndRedirectTrait;

class CityController extends Controller
{
    use LogErrorAndRedirectTrait;
    
    /**
     * Display a listing of the cities.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $cities = City::orderBy('order', 'asc')->paginate(10);
            
            return view('admin.cities.index', compact('cities'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in city page: ');
            return abort(500);
        }
    }

    /**
     * Show the form for creating a new city.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            return view('admin.cities.create');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in city create page: ');
            return abort(500);
        }
    }

    /**
     * Store a newly created city in storage.
     *
     * @param  \App\Http\Requests\StoreCityRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCityRequest $request)
    {
        try {
            $validated = $request->validated();
            
            // Handle image upload
            if ($request->hasFile('image')) {
                // Generate human-readable filename
                $ext = $request->file('image')->getClientOriginalExtension();
                $slug = Str::slug($validated['name_en']);
                $filename = "{$slug}-" . time() . ".{$ext}";

                // Upload to S3
                $path = $request->file('image')
                    ->storeAs('cities', $filename, 's3');

                $validated['image'] = Storage::disk('s3')->url($path);
            }
            
            // Set default order if not provided
            if (empty($validated['order'])) {
                $maxOrder = City::max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }
            
            City::create($validated);
            
            return redirect()->route('cities.index')
                ->with('success', 'City created successfully.');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error storing city: ');
            
            return redirect()->back()
                ->with('error', __('error.internal_error'))
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified city.
     *
     * @param  int  $city_id
     * @return \Illuminate\View\View
     */
    public function edit($city_id)
    {
        try {
            $city = City::findOrFail($city_id);
            
            return view('admin.cities.edit', compact('city'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in city edit page: ');
            
            return redirect()->route('cities.index')
                ->with('error', __('error.internal_error'));
        }
    }

    /**
     * Update the specified city in storage.
     *
     * @param  \App\Http\Requests\UpdateCityRequest  $request
     * @param  int  $city_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateCityRequest $request, $city_id)
    {
        try {
            // Fetch the city
            $city = City::findOrFail($city_id);
            
            // Get validated data
            $validated = $request->validated();
            
            // Handle image upload
            if ($request->hasFile('image')) {
                if ($city->image) {
                    $oldPath = parse_url($city->image, PHP_URL_PATH);
                    if ($oldPath) {
                        $oldKey = ltrim($oldPath, '/');
                        Storage::disk('s3')->delete($oldKey);
                    }
                }
                
                // Generate new filename
                $ext = $request->file('image')->getClientOriginalExtension();
                $slug = Str::slug($validated['name_en']);
                $filename = "{$slug}-" . time() . ".{$ext}";
                
                // Upload to S3
                $path = $request->file('image')
                    ->storeAs('cities', $filename, 's3');
                
                $validated['image'] = Storage::disk('s3')->url($path);
            }
            
            $city->update($validated);
            
            return redirect()->route('cities.index')
                ->with('success', 'City updated successfully.');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error updating city: ');
            
            return redirect()->back()
                ->with('error', __('error.internal_error'))
                ->withInput();
        }
    }

    /**
     * Remove the specified city from storage.
     *
     * @param  int  $city_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($city_id)
    {
        try {
            $city = City::findOrFail($city_id);
            
            // Delete image from S3 if exists
            if ($city->image) {
                $oldPath = parse_url($city->image, PHP_URL_PATH);
                if ($oldPath) {
                    $oldKey = ltrim($oldPath, '/');
                    Storage::disk('s3')->delete($oldKey);
                }
            }
            
            $city->delete();
            
            return redirect()->route('cities.index')
                ->with('success', 'City deleted successfully.');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error deleting city: ');
            
            return redirect()->back()
                ->with('error', __('error.internal_error'));
        }
    }
}