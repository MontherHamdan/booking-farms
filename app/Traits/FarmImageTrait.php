<?php

namespace App\Traits;
use Illuminate\Support\Facades\Auth;

trait FarmImageTrait
{
    /**
     * Load and organize farm images (main + up to 4 non-main)
     */
    private function loadFarmImages($farms)
    {
        return $farms->getCollection()->transform(function ($farm) {
            // Get the main image
            $mainImage = $farm->images()->where('is_main', true)->get();
            
            // Get up to 4 non-main images
            $nonMainImages = $farm->images()->where('is_main', false)->limit(4)->get();
            
            // Combine the collections
            $farm->setRelation('images', $mainImage->concat($nonMainImages));
            
            return $farm;
        });
    }

    /**
     * Get farm relationships including user-specific favorites
     */
    private function getFarmRelationships($includeUserFavorites = true): array
    {
        $relationships = ['pricing', 'city', 'area', 'user', 'features', 'images', 'offers', 'ratings'];

        if ($includeUserFavorites) {
            $user = Auth::guard('sanctum')->user();
            
            if ($user) {
                $relationships['favoritedBy'] = function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                };
            }
        }

        return $relationships;
    }
}