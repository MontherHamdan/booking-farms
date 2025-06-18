<?php

namespace App\Http\Controllers\Api\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use Illuminate\Http\JsonResponse;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use Exception;
use Illuminate\Support\Facades\Cache;

class ApiFeatureController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;

    public function index()
    {
        /**
         * List all features ordered by order column.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            // Cache features for 1 hour 
            $features = Cache::remember('features_list', 3600, function () {
                return Feature::orderBy('order')->get();
            });

            return $this->successResponse(true, FeatureResource::collection($features), null, 200);
        } catch (\Exception $e) {
            $this->logException($e);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
}
