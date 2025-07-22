<?php

namespace App\Http\Controllers\Api\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use App\Http\Resources\CityResource;
use App\Models\Area;
use Illuminate\Http\Request;
use App\Http\Resources\AreaResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

class ApiCityController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;


    public function index()
    {
        /**
         * List all published cities ordered by order column with farm counts.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            // Cache cities with farm counts for 1 hour 
            $cities = Cache::remember('cities_list_with_counts', 3600, function () {
                return City::published()
                    ->ordered()
                    ->withCount(['farms', 'areas']) // Count both farms and areas
                    ->get();
            });

            return $this->successResponse(true, CityResource::collection($cities), null, 200);
        } catch (\Exception $e) {
            $this->logException($e);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    public function basic()
    {
        /**
         * List all published cities with basic info including coordinates for selection purposes.
         * Perfect for dropdowns, forms, etc.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            // Cache basic cities for 1 hour 
            $cities = Cache::remember('cities_basic_list_with_coordinates', 3600, function () {
                return City::published()
                        ->ordered()
                        ->select(['id', 'name_en', 'name_ar', 'latitude', 'longitude'])
                        ->get()
                        ->map(function ($city) {
                            return [
                                'id' => $city->id,
                                'name_en' => $city->name_en,
                                'name_ar' => $city->name_ar,
                                'latitude' => $city->latitude,
                                'longitude' => $city->longitude,
                                'coordinates' => $city->coordinates, 
                                'has_coordinates' => $city->hasCoordinates(),
                            ];
                        });
            });
    
            return $this->successResponse(true, $cities, null, 200);
        } catch (\Exception $e) {
            $this->logException($e);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }


    public function getAreasByCity(Request $request, $cityId)
    {
        /**
         * Get published, ordered areas by city.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  int  $cityId
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            // Cache areas by city for 1 hour 
            $areas = Cache::remember("areas_by_city_{$cityId}", 3600, function () use ($cityId) {
                $city = City::published()->findOrFail($cityId);
                return $city->publishedAreas()->get();
            });

            return $this->successResponse(true, AreaResource::collection($areas), null, 200);
        }catch (\Exception $e) {
            $this->logException($e);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
}