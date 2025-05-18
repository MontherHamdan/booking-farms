<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use App\Http\Resources\CityResource;

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
}