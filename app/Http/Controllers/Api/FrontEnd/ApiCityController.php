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

class ApiCityController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;


    public function index()
    {
        /**
         * List all published cities ordered by order column.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            $cities = City::published()->ordered()->get();

            return $this->successResponse(true, CityResource::collection($cities), null, 200);
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
            $city = City::published()->findOrFail($cityId);

            $areas = $city->publishedAreas()->get();

            return $this->successResponse(true, AreaResource::collection($areas), null, 200);

        } catch (ModelNotFoundException $e) {
            // City not found or not published
            return $this->errorResponse(__('error.city_not_found'), 404);
        } catch (\Exception $e) {
            $this->logException($e);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
}