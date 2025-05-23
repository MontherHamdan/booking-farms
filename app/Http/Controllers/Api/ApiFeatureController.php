<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use Illuminate\Http\JsonResponse;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use Exception;

class ApiFeatureController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;

    public function index(): JsonResponse
    {
        try {
            $features = Feature::orderBy('order', 'asc')->get();
    
            $resourceCollection = FeatureResource::collection($features);
    
            return $this->successResponse(true, $resourceCollection, null, 200);
        } catch (Exception $e) {
            $this->logException($e, ['action' => 'fetch features']);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
}
