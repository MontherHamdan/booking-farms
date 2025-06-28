<?php

namespace App\Http\Controllers\Api\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Requests\FarmOwner\StoreFarmRequest;
use App\Http\Requests\FarmOwner\UpdateFarmRequest;
use App\Http\Requests\FrontEnd\FilterFarmRequest;
use App\Http\Requests\FrontEnd\SearchFarmRequest;
use App\Http\Requests\FrontEnd\CalculatePriceRequest;
use App\Http\Resources\FarmCollection;
use App\Http\Resources\FarmResource;
use App\Http\Resources\ShowFarmResource;
use App\Models\Farm;
use App\Models\SearchHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use App\Traits\FarmPricingTrait;
use App\Traits\FarmFiltersTrait;
use App\Traits\FarmSearchTrait;
use App\Traits\FarmImageTrait;
use App\Traits\FarmHelperTrait;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ApiFarmController extends Controller
{
    use JsonResponseTrait, 
        ExceptionLoggerTrait, 
        FarmPricingTrait, 
        FarmFiltersTrait,
        FarmSearchTrait,
        FarmImageTrait,
        FarmHelperTrait;

    /**
     * Display a listing of farms.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Farm::query();
            
            // Apply only has_offer filter for GET method
            $this->applyOfferFilter($query, $request);
            
            $relationships = $this->getFarmRelationships();
            $farms = $query->with($relationships)->paginate($request->per_page ?? 10);
            
            // Load farm images
            $this->loadFarmImages($farms);
            
            return $this->successResponse(true, new FarmCollection($farms), null, 200);
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'fetch farms']);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    public function filter(FilterFarmRequest $request): JsonResponse
    {
        /**
         * Filter farms with advanced criteria (POST method)
         * Includes all available filters
        */
        try {
            $query = Farm::query();
            
            // Apply all filters using the trait
            $this->applyFarmFilters($query, $request);
            
            $relationships = $this->getFarmRelationships();
            $farms = $query->with($relationships)->paginate($request->per_page ?? 10);
            
            // Load farm images
            $this->loadFarmImages($farms);
            
            return $this->successResponse(true, new FarmCollection($farms), null, 200);
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'filter farms', 'filters' => $request->all()]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get dynamic filter fields for farms based on Accept-Language header
     */
    public function getFilterFields(Request $request): JsonResponse
    {
        $locale = app()->getLocale();
        $fields = $this->getFilterFieldsConfig($locale);
        
        return $this->successResponse(true, $fields, null, 200);
    }

    public function search(SearchFarmRequest $request): JsonResponse
    {
        /**
         * Search farms by name and description
        */
        try {
            $searchQuery = $request->input('query');
            $perPage = $request->input('per_page', 10);

            $query = Farm::query();
            
            // Apply search filter
            $this->applySearchFilter($query, $request);
            
            $relationships = $this->getFarmRelationships();
            $farms = $query->with($relationships)->paginate($perPage);
            
            // Handle search history for authenticated users
            $this->handleSearchHistory($searchQuery);
            
            // Load farm images
            $this->loadFarmImages($farms);
            
            return $this->successResponse(true, new FarmCollection($farms), null, 200);
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'search farms', 'query' => $request->input('query')]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }


    public function getSearchHistory(Request $request): JsonResponse
    {
        /**
         * Get search history for authenticated users
        */
        try {
            $validator = Validator::make($request->all(), [
                'per_page' => 'integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $user = auth('sanctum')->user();
            $perPage = $request->input('per_page', 10);

            $searchHistory = SearchHistory::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return $this->successResponse(true, $searchHistory, null, 200);
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'get search history', 'user_id' => auth('sanctum')->id()]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Delete specific search history item by ID
    */
    public function deleteSearchHistoryItem($historyId): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            
            // Find the search history item that belongs to the authenticated user
            $searchHistory = SearchHistory::where('user_id', $user->id)
                ->where('id', $historyId)
                ->first();
                
            if (!$searchHistory) {
                return $this->errorResponse(__('farm.history_not_found'), 404);
            }
            
            $searchHistory->delete();
            
            return $this->successResponse(true, __('farm.history_item_deleted'), null, 200);
            
        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'delete search history item', 
                'user_id' => auth('sanctum')->id(),
                'history_id' => $historyId
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Clear all search history for authenticated user
    */
    public function clearSearchHistory(): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            
            // Delete all search history for this user
            SearchHistory::where('user_id', $user->id)->delete();
            
            return $this->successResponse(true, __('farm.history_cleared'), null, 200);
            
        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'clear all search history', 
                'user_id' => auth('sanctum')->id()
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }


    public function show($farm_id): JsonResponse
    {
        /**
         * Display the specified farm.
        */
        try {
            $relationships = $this->getFarmRelationships();
            $farm = Farm::with($relationships)->find($farm_id);
    
            if (!$farm) {
                return $this->errorResponse(__('farm.not_found', ['id' => $farm_id]), 404);
            }
            
            return $this->successResponse(true, new ShowFarmResource($farm), null, 200);
    
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'show farm', 'id' => $farm_id]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }    
    }

    public function calculatePrice(CalculatePriceRequest $request, $farmId): JsonResponse
    {
        /**
         * Calculate farm price based on selected dates and price type.
         */
        try {
            // Fetch farm with pricing and offers
            $farm = Farm::with(['pricing', 'offers'])->find($farmId);
    
            if (!$farm) {
                return $this->errorResponse(__('farm.not_found', ['id' => $farmId]), 404);
            }
    
            $dates = $request->dates;
            $priceType = $request->price_type;
    
            // Get pricing for selected type
            $pricing = $farm->pricing()->where('price_type', $priceType)->first();
            if (!$pricing) {
                return $this->errorResponse(__('farm.pricing_not_available', ['price_type' => __('farm.price_types.' . $priceType)]), 400);
            }
    
            // Process dates based on price type
            $processedDates = $this->processDatesByPriceType($dates, $priceType);
    
            // Ensure none of the processed dates are blocked
            $unavailable = array_intersect($processedDates, $farm->not_available_dates ?? []);
            if ($unavailable) {
                return $this->errorResponse(__('farm.unavailable_dates', ['dates' => implode(', ', $unavailable)]), 400);
            }
    
            // Calculate subtotal before discount
            $subtotal = collect($processedDates)->sum(function ($date) use ($pricing) {
                $day = strtolower(Carbon::parse($date)->format('l'));
                return $pricing->{"{$day}_price"} ?? 0;
            });
    
            // Determine current offer percentage - always as float
            $offer = $farm->currentOffer;
            $percentage = $offer ? (float) $offer->percentage : 0.0;
    
            // Compute discount and final total
            $discountAmount = ($subtotal * $percentage) / 100;
            $total = $subtotal - $discountAmount;
    
            // Simplified response with offer details
            $data = [
                'price_before_offer' => $subtotal,
                'offer_percentage'   => $percentage, // Now always float
                'is_offer'           => $percentage > 0,
                'discount_amount'    => $discountAmount,
                'price_after_offer'  => $total,
            ];
    
            return $this->successResponse(true, $data, null, 200);
    
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'calculate farm price', 'farm_id' => $farmId]);
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