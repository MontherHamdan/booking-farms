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
            // active scope to get only active farms
            $query = Farm::active();
            
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
            $query = Farm::active();
            
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
    
            $query = Farm::active();
            
            // Apply search filter
            $this->applySearchFilter($query, $request);
            
            $relationships = $this->getFarmRelationships();
            $farms = $query->with($relationships)->paginate($perPage);
            
            // Handle search history for authenticated users and get the record
            $searchHistory = $this->handleSearchHistory($searchQuery);
            
            // Load farm images
            $this->loadFarmImages($farms);
            
            // Create combined response object
            $farmCollection = new FarmCollection($farms);
            $farmData = $farmCollection->toArray($request);
            
            // Build the response object
            $responseObj = [
                'farms' => $farmData,
                'search_history' => $searchHistory
            ];
            
            return $this->successResponse(true, $responseObj, null, 200);

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
}