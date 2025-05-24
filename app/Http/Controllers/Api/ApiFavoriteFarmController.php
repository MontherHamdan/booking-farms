<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FarmCollection;
use App\Models\Farm;
use App\Models\FavoriteFarm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;

class ApiFavoriteFarmController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;

    /**
     * Display a listing of user's favorite farms.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
    
            // Get favorite farms via a paginator—even if none exist, this returns an empty paginator
            $farms = Farm::whereIn('id', function($q) use ($userId) {
                    $q->select('farm_id')
                      ->from('favorite_farms')
                      ->where('user_id', $userId);
                })
                ->with(['city', 'features', 'pricing', 'offers'])
                ->paginate($request->per_page ?? 15);
    
            // load images exactly like ApiFarmController
            $farms->getCollection()->transform(function ($farm) {
                $mainImage     = $farm->images()->where('is_main', true)->get();
                $nonMainImages = $farm->images()->where('is_main', false)->limit(4)->get();
                $farm->setRelation('images', $mainImage->concat($nonMainImages));
                return $farm;
            });
    
            return $this->successResponse(
                true,
                new FarmCollection($farms),
                null,
                200
            );
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'fetch favorite farms', 'user_id' => auth()->id()]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
    

    /**
     * Toggle farm favorite status (add/remove from favorites).
     */
    public function toggle($farm_id): JsonResponse
    {
        try {
            $userId = auth()->id();

            $farm = Farm::findOrFail($farm_id);
            
            // Check if farm is already in favorites
            $existingFavorite = FavoriteFarm::where('user_id', $userId)
                ->where('farm_id', $farm->id)
                ->first();

            if ($existingFavorite) {
                // Remove from favorites
                $existingFavorite->delete();
                $isFavorite = false;
            } else {
                // Add to favorites
                FavoriteFarm::create([
                    'user_id' => $userId,
                    'farm_id' => $farm->id,
                ]);
                $isFavorite = true;
            }

            return $this->successResponse(
                true,
                [
                    'farm_id' => $farm->id,
                    'is_favorite' => $isFavorite,
                ],
                null,
                200
            );

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'toggle farm favorite', 
                'user_id' => auth()->id(), 
                'farm_id' => $farm->id
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
}