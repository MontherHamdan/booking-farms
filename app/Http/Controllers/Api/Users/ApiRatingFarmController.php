<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Farm;
use App\Models\FarmRating;
use App\Http\Requests\User\StoreFarmRatingRequest;
use Illuminate\Http\JsonResponse;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use Exception;
use App\Http\Requests\User\GetFarmRatingsRequest;
use App\Http\Resources\RatingCollection;

class ApiRatingFarmController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;

    /**
     * Add a rating for a farm.
     */
    public function storeRating(StoreFarmRatingRequest $request, $farmId): JsonResponse
    {
        try {
            $farm = Farm::find($farmId);
    
            if (!$farm) {
                return $this->errorResponse(__('farm.already_rated'), 409);
            }
    
            $userId = auth()->id();
    
            if ($farm->ratings()->where('user_id', $userId)->exists()) {
                return $this->errorResponse(__('farm.already_rated'), 409);
            }
    
            $rating = $farm->ratings()->create([
                'user_id' => $userId,
                'rating' => $request->rating,
                'review' => $request->review,
            ]);
    
            $rating->load('user:id,name,avatar');

            $responseData=[
                'message' => __('farm.rating_created_success'),
                'rating' => $rating,
                'farm_average_rating' => $farm->average_rating,
                'farm_total_ratings' => $farm->total_ratings,
            ];
    
            return $this->successResponse(true, $responseData, null, 201);
    
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'store farm rating', 'farm_id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * update a rating for a farm.
     */
    public function updateRating(StoreFarmRatingRequest $request, $farmId): JsonResponse
    {

        try {
            $farm = Farm::find($farmId);

            if (!$farm) {
                return $this->errorResponse(__('farm.not_found', ['id' => $farmId]), 404);
            }

            $userId = auth()->id();

            $existingRating = $farm->ratings()->where('user_id', $userId)->first();

            if (!$existingRating) {
                return $this->errorResponse(__('farm.no_existing_rating'), 404);
            }

            $existingRating->update([
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            $existingRating->load('user:id,name,avatar');

            return $this->successResponse(true, [
                'message' => __('farm.rating_updated_success'),
                'rating' => $existingRating,
                'farm_average_rating' => $farm->average_rating,
                'farm_total_ratings' => $farm->total_ratings,
            ], null, 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'update farm rating', 'farm_id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get all ratings for a specific farm using get method with filters in request body.
     */
    public function getRatings(GetFarmRatingsRequest $request, $farmId): JsonResponse
    {
        try {
            $farm = Farm::find($farmId);
            if (! $farm) {
                return $this->errorResponse(__('farm.not_found', ['id' => $farmId]), 404);
            }
    
            // Get validated filters (with defaults)
            $perPage    = $request->input('per_page', 15);
            $sortBy     = $request->input('sort_by', 'newest');
            $starFilter = $request->input('star_filter');
    
            $query = $farm->ratings()->with('user:id,name,avatar');
    
            // Apply star filter if provided
            if ($starFilter) {
                $query->where('rating', '>=', $starFilter)
                      ->where('rating', '<', $starFilter + 1);
            }
    
            // Apply sorting
            switch ($sortBy) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'highest_rating':
                    $query->orderBy('rating', 'desc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
            }
    
            // Paginate
            $ratingsPaginator = $query->paginate($perPage);
    
            // Build farm stats
            $farmStats = [
                'average_rating'   => $farm->average_rating,
                'total_ratings'    => $farm->total_ratings,
                'rating_breakdown' => $farm->rating_breakdown,
            ];
    
            // Return a ResourceCollection wrapped in your successResponse
            return $this->successResponse(true, [
                'farm_stats' => $farmStats,
                'ratings'    => new RatingCollection($ratingsPaginator),
            ], null, 200);
    
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'get farm ratings', 'farm_id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Delete a user's rating for a farm.
     */
    public function deleteRating($farmId): JsonResponse
    {
        try {
            $farm = Farm::find($farmId);
            
            if (!$farm) {
                return $this->errorResponse(__('farm.not_found', ['id' => $farmId]), 404);
            }

            $userId = auth()->id();
            $rating = $farm->ratings()->where('user_id', $userId)->first();
            
            if (!$rating) {
                return $this->errorResponse(__('farm.not_yet_rated'), 404);
            }

            $rating->delete();

            return $this->successResponse(true, [
                'message' => __('farm.rating_deleted_success'),     
                'farm_average_rating' => $farm->average_rating,
                'farm_total_ratings' => $farm->total_ratings,
            ], null, 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'delete farm rating', 'farm_id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get the current user's rating for a specific farm.
     */
    public function getUserRating($farmId): JsonResponse
    {
        try {
            $farm = Farm::find($farmId);
            
            if (!$farm) {
                return $this->errorResponse(__('farm.not_found', ['id' => $farmId]), 404);
            }

            $userId = auth()->id();
            $rating = $farm->ratings()->where('user_id', $userId)->with('user:id,name,avatar')->first();
            
            if (!$rating) {
                return $this->successResponse(true, [
                    'has_rated' => false,
                    'rating' => null,
                ], null, 200);
            }

            return $this->successResponse(true, [
                'has_rated' => true,
                'rating' => $rating,
            ], null, 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'get user farm rating', 'farm_id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
}
