<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreFeatureRequest;
use App\Http\Requests\Dashboard\UpdateFeatureRequest;
use App\Models\Feature;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\LogErrorAndRedirectTrait;

class FeatureController extends Controller
{
    use LogErrorAndRedirectTrait;
    
    /**
     * Display a listing of the features.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $features = Feature::orderBy('order', 'asc')->paginate(10);
            
            return view('admin.features.index', compact('features'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in feature page: ');
            return abort(500);
        }
    }

    /**
     * Show the form for creating a new feature.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            return view('admin.features.create');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in feature create page: ');
            return abort(500);
        }
    }

    /**
     * Store a newly created feature in storage.
     *
     * @param  \App\Http\Requests\StoreFeatureRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreFeatureRequest $request)
    {
        try {
            $validated = $request->validated();
            
            // Handle icon upload
            if ($request->hasFile('icon')) {
                // Generate human-readable filename
                $ext = $request->file('icon')->getClientOriginalExtension();
                $slug = Str::slug($validated['name_en']);
                $filename = "{$slug}-" . time() . ".{$ext}";

                // Upload to S3
                $path = $request->file('icon')
                    ->storeAs('features', $filename, 's3');

                $validated['icon'] = Storage::disk('s3')->url($path);
            }
            
            // Set default order if not provided
            if (empty($validated['order'])) {
                $maxOrder = Feature::max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }
            
            Feature::create($validated);
            
            return redirect()->route('dashboard.features.index')
                ->with('success', 'Feature created successfully.');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error storing feature: ');
            
            return redirect()->back()
                ->with('error', 'Internal server error. Please try again later.')
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified feature.
     *
     * @param  int  $feature_id
     * @return \Illuminate\View\View
     */
    public function edit($feature_id)
    {
        try {
            $feature = Feature::findOrFail($feature_id);
            
            return view('admin.features.edit', compact('feature'));
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error in feature edit page: ');
            
            return redirect()->route('dashboard.features.index')
                ->with('error', 'Internal server error. Please try again later.');
        }
    }

    /**
     * Update the specified feature in storage.
     *
     * @param  \App\Http\Requests\UpdateFeatureRequest  $request
     * @param  int  $feature_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateFeatureRequest $request, $feature_id)
    {
        try {
            // Fetch the feature
            $feature = Feature::findOrFail($feature_id);
            
            // Get validated data
            $validated = $request->validated();
            
            // Handle icon upload
            if ($request->hasFile('icon')) {
                if ($feature->icon) {
                    $oldPath = parse_url($feature->icon, PHP_URL_PATH);
                    if ($oldPath) {
                        $oldKey = ltrim($oldPath, '/');
                        Storage::disk('s3')->delete($oldKey);
                    }
                }
                
                // Generate new filename
                $ext = $request->file('icon')->getClientOriginalExtension();
                $slug = Str::slug($validated['name_en']);
                $filename = "{$slug}-" . time() . ".{$ext}";
                
                // Upload to S3
                $path = $request->file('icon')
                    ->storeAs('features', $filename, 's3');
                
                $validated['icon'] = Storage::disk('s3')->url($path);
            }
            
            $feature->update($validated);
            
            return redirect()->route('dashboard.features.index')
                ->with('success', 'Feature updated successfully.');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error updating feature: ');
            
            return redirect()->back()
                ->with('error', 'Internal server error. Please try again later.')
                ->withInput();
        }
    }

    /**
     * Remove the specified feature from storage.
     *
     * @param  int  $feature_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($feature_id)
    {
        try {
            $feature = Feature::findOrFail($feature_id);
            
            // Delete icon from S3 if exists
            if ($feature->icon) {
                $oldPath = parse_url($feature->icon, PHP_URL_PATH);
                if ($oldPath) {
                    $oldKey = ltrim($oldPath, '/');
                    Storage::disk('s3')->delete($oldKey);
                }
            }
            
            $feature->delete();
            
            return redirect()->route('dashboard.features.index')
                ->with('success', 'Feature deleted successfully.');
        } catch (\Exception $e) {
            $this->logErrorAndRedirect($e, 'Error deleting feature: ');
            
            return redirect()->back()
                ->with('error', 'Internal server error. Please try again later.');
        }
    }
}