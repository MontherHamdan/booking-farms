<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFarmRequest;
use App\Http\Requests\UpdateFarmRequest;
use App\Http\Resources\FarmCollection;
use App\Http\Resources\FarmResource;
use App\Http\Resources\IndexShowFarmResource;
use App\Models\Farm;
use App\Models\FarmImage;
use App\Models\FarmPricing;
use App\Models\FarmOffer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use App\Traits\FarmPricingTrait;
use App\Traits\FarmFiltersTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


class ApiFarmController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait, FarmPricingTrait, FarmFiltersTrait;

    /**
     * Display a listing of farms.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Farm::query();
            
            // Apply only has_offer filter for GET method
            $this->applyOfferFilter($query, $request);
            
            $relationships = ['pricing', 'city', 'user', 'features', 'images', 'offers'];

            // Check for authenticated user using Sanctum guard
            $user = Auth::guard('sanctum')->user();
            
            // Add user-specific favorites if authenticated
            if ($user) {
                $relationships['favoritedBy'] = function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                };
            }

            $farms = $query->with($relationships)->paginate($request->per_page ?? 10);      

            // After pagination, load the images separately for each farm
            $farms->getCollection()->transform(function ($farm) {
                // Get the main image
                $mainImage = $farm->images()->where('is_main', true)->get();
                
                // Get up to 4 non-main images
                $nonMainImages = $farm->images()->where('is_main', false)->limit(4)->get();
                
                // Combine the collections
                $farm->setRelation('images', $mainImage->concat($nonMainImages));
                
                return $farm;
            });
            
            return $this->successResponse(true, new FarmCollection($farms), null, 200);
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'fetch farms']);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Filter farms with advanced criteria (POST method)
     * Includes all available filters
     */
    public function filter(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $request->validate([
                'city_id' => 'nullable|array',
                'city_id.*' => 'integer|exists:cities,id',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'has_offer' => 'nullable|boolean',
                'available_time' => 'nullable|array',
                'available_time.*' => 'string|in:day_use,night,full_day',
                'date' => 'nullable|date_format:Y-m-d|after_or_equal:today',
                'start_date' => 'nullable|date_format:Y-m-d|after_or_equal:today',
                'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $query = Farm::query();
            
            // Apply all filters using the trait
            $this->applyFarmFilters($query, $request);
            
            $farms = $query->with(['pricing', 'city', 'user', 'features', 'images', 'offers'])->paginate($request->per_page ?? 10);
            
            // After pagination, load the images separately for each farm
            $farms->getCollection()->transform(function ($farm) {
                // Get the main image
                $mainImage = $farm->images()->where('is_main', true)->get();
                
                // Get up to 4 non-main images
                $nonMainImages = $farm->images()->where('is_main', false)->limit(4)->get();
                
                // Combine the collections
                $farm->setRelation('images', $mainImage->concat($nonMainImages));
                
                return $farm;
            });
            
            return $this->successResponse(true, new FarmCollection($farms), null, 200);
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'filter farms', 'filters' => $request->all()]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Store a newly created farm in storage.
     */
    public function store(StoreFarmRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {

            // Prepare farm data with only non-null values
            $farmData = [
                'user_id' => auth()->id(),
            ];

            // Add fields only if they are provided
            if ($request->filled('city_id')) {
                $farmData['city_id'] = $request->city_id;
            }
            if ($request->filled('name_ar')) {
                $farmData['name_ar'] = $request->name_ar;
            }
            if ($request->filled('name_en')) {
                $farmData['name_en'] = $request->name_en;
            }
            if ($request->filled('description_ar')) {
                $farmData['description_ar'] = $request->description_ar;
            }
            if ($request->filled('description_en')) {
                $farmData['description_en'] = $request->description_en;
            }
            if ($request->filled('passengers_count')) {
                $farmData['passengers_count'] = $request->passengers_count;
            }
            if ($request->filled('not_available_dates')) {
                $farmData['not_available_dates'] = $request->not_available_dates;
            }

            // 1) Create the Farm
            $farm = Farm::create($farmData);

            // 2) Attach features
            if ($request->filled('features')) {
                // If features array is provided directly
                $farm->features()->attach($request->features);
            } elseif ($request->filled('features_string')) {
                // If features are provided as comma-separated string
                $featuresArray = array_filter(explode(',', $request->features_string));
                if (!empty($featuresArray)) {
                    $farm->features()->attach($featuresArray);
                }
            }

            // 3) Create pricing data
            $this->createFarmPricing($farm, $request);

            // 4) Create offer if provided
            if ($request->filled('offer')) {
                $offerData = $request->offer;
                $farm->offers()->create([
                    'percentage' => $offerData['percentage'],
                    'start_date' => $offerData['start_date'],
                    'end_date' => $offerData['end_date'],
                    'is_active' => $offerData['is_active'] ?? true,
                ]);
            }

            // 5) Upload main_image
            if ($request->hasFile('main_image')) {
                $mainFile = $request->file('main_image');
                $ext = $mainFile->getClientOriginalExtension();
                
                // Use farm name if available, otherwise use farm ID
                $farmName = $farm->name_en ?: $farm->name_ar ?: "farm-{$farm->id}";
                $slug = Str::slug($farmName);
                $filename = "{$slug}-main-" . time() . ".{$ext}";
                $path = $mainFile->storeAs('farms', $filename, 's3');
                $url = Storage::disk('s3')->url($path);

                $farm->images()->create([
                    'image_path' => $url,
                    'is_main' => true,
                ]);
            }

            // 6) Upload any gallery images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $ext = $image->getClientOriginalExtension();
                    
                    // Use farm name if available, otherwise use farm ID
                    $farmName = $farm->name_en ?: $farm->name_ar ?: "farm-{$farm->id}";
                    $slug = Str::slug($farmName);
                    $filename = "{$slug}-{$index}-" . time() . ".{$ext}";
                    $path = $image->storeAs('farms', $filename, 's3');
                    $url = Storage::disk('s3')->url($path);

                    $farm->images()->create([
                        'image_path' => $url,
                        'is_main' => false,
                    ]);
                }
            }

            DB::commit();

            $farm->load(['city', 'features', 'images', 'pricing', 'offers']);
            return response()->json([
                'status' => 'success',
                'data' => new FarmResource($farm),
                'message' => 'Farm created successfully',
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            $this->logException($e, ['action' => 'store farms']);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Display the specified farm.
     */
    public function show(Farm $farm): JsonResponse
    {
        try {
            $farm->load(['city', 'features', 'images', 'user', 'pricing', 'offers']);
            
            return $this->successResponse(true, new IndexShowFarmResource($farm), null, 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'show farm', 'id' => $farm->id]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }    
    }

    /**
     * Update the specified farm in storage.
     */
    public function update(UpdateFarmRequest $request, Farm $farm): JsonResponse
    {
        DB::beginTransaction();
        try {
            // 1) Update main farm attributes - only update provided fields
            $updateData = [];
            
            if ($request->has('city_id')) {
                $updateData['city_id'] = $request->city_id;
            }
            if ($request->has('name_ar')) {
                $updateData['name_ar'] = $request->name_ar;
            }
            if ($request->has('name_en')) {
                $updateData['name_en'] = $request->name_en;
            }
            if ($request->has('description_ar')) {
                $updateData['description_ar'] = $request->description_ar;
            }
            if ($request->has('description_en')) {
                $updateData['description_en'] = $request->description_en;
            }
            if ($request->has('passengers_count')) {
                $updateData['passengers_count'] = $request->passengers_count;
            }
            if ($request->has('not_available_dates')) {
                $updateData['not_available_dates'] = $request->not_available_dates;
            }

            if (!empty($updateData)) {
                $farm->update($updateData);
            }

            // 2) Sync features if sent
            if ($request->filled('features')) {
                $farm->features()->sync($request->features);
            }

            // 3) Update pricing data
            $this->updateFarmPricing($farm, $request);

            // 4) Handle offer updates
            if ($request->filled('delete_current_offer') && $request->delete_current_offer) {
                // Delete current offers
                $farm->offers()->delete();
            } elseif ($request->filled('offer')) {
                // Delete existing offers and create new one
                $farm->offers()->delete();
                
                $offerData = $request->offer;
                $farm->offers()->create([
                    'percentage' => $offerData['percentage'],
                    'start_date' => $offerData['start_date'],
                    'end_date' => $offerData['end_date'],
                    'is_active' => $offerData['is_active'] ?? true,
                ]);
            }

            // 5) Replace or add main_image
            if ($request->hasFile('main_image')) {
                // delete old main
                $oldMain = $farm->images()->where('is_main', true)->first();
                if ($oldMain) {
                    $oldKey = ltrim(parse_url($oldMain->image_path, PHP_URL_PATH), '/');
                    Storage::disk('s3')->delete($oldKey);
                    $oldMain->delete();
                }

                // upload new main
                $mainFile = $request->file('main_image');
                $ext = $mainFile->getClientOriginalExtension();
                
                // Use farm name if available, otherwise use farm ID
                $farmName = $farm->name_en ?: $farm->name_ar ?: "farm-{$farm->id}";
                $slug = Str::slug($farmName);
                $filename = "{$slug}-main-" . time() . ".{$ext}";
                $path = $mainFile->storeAs('farms', $filename, 's3');
                $url = Storage::disk('s3')->url($path);

                $farm->images()->create([
                    'image_path' => $url,
                    'is_main' => true,
                ]);
            }

            // 6) Handle new gallery images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $ext = $image->getClientOriginalExtension();
                    
                    // Use farm name if available, otherwise use farm ID
                    $farmName = $farm->name_en ?: $farm->name_ar ?: "farm-{$farm->id}";
                    $slug = Str::slug($farmName);
                    $filename = "{$slug}-{$index}-" . time() . ".{$ext}";
                    $path = $image->storeAs('farms', $filename, 's3');
                    $url = Storage::disk('s3')->url($path);

                    $farm->images()->create([
                        'image_path' => $url,
                        'is_main' => false,
                    ]);
                }
            }

            // 7) Delete any flagged images
            if ($request->filled('delete_image_ids')) {
                $toDelete = $farm->images()->whereIn('id', $request->delete_image_ids)->get();
                foreach ($toDelete as $img) {
                    $key = ltrim(parse_url($img->image_path, PHP_URL_PATH), '/');
                    Storage::disk('s3')->delete($key);
                }
                $farm->images()->whereIn('id', $request->delete_image_ids)->delete();
            }

            DB::commit();

            $farm->load(['city', 'features', 'images', 'pricing', 'offers']);
            return response()->json([
                'status' => 'success',
                'data' => new FarmResource($farm),
                'message' => 'Farm updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update farm: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified farm from storage.
     */
    public function destroy(Farm $farm): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            // Delete farm images from storage
            foreach ($farm->images as $image) {
                if (Storage::disk('s3')->exists($image->image_path)) {
                    $key = ltrim(parse_url($image->image_path, PHP_URL_PATH), '/');
                    Storage::disk('s3')->delete($key);
                }
            }
            
            // The farm, feature relationships, images, pricing, and offers will be deleted due to cascading constraints
            $farm->delete();
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Farm deleted successfully',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete farm: ' . $e->getMessage(),
            ], 500);
        }
    }
}