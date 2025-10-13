<?php

namespace App\Http\Controllers\Api\FarmOwner;

use App\Http\Controllers\Controller;
use App\Services\FarmOwnerApplicationService;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class ApiFarmOwnerApplicationController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;

    protected $applicationService;

    public function __construct(FarmOwnerApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    /**
     * Submit farm owner application
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();

            // Check if already a farm owner
            if ($user->isFarmOwner()) {
                return $this->errorResponse('You are already a farm owner', 400);
            }

            // Validate ID image if provided
            $request->validate([
                'id_image' => 'nullable|image|mimes:jpeg,jpg,png|max:5120', // 5MB max
            ]);

            $idImageUrl = null;

            // Upload ID image to S3 if provided
            if ($request->hasFile('id_image')) {
                $ext = $request->file('id_image')->getClientOriginalExtension();
                $slug = Str::slug($user->name);
                $filename = "farm-owner-{$userId}-{$slug}-" . time() . ".{$ext}";

                // Upload to S3 under 'farm-owners/id-images/' folder
                $path = $request->file('id_image')
                    ->storeAs('farm-owners/id-images', $filename, 's3');

                $idImageUrl = Storage::disk('s3')->url($path);
            }

            // Submit application
            $result = $this->applicationService->submitApplication($userId, $idImageUrl);

            return $this->successResponse(true, [
                'message' => $result['message'],
                'application_status' => $this->applicationService->getApplicationStatus($userId),
            ], null, 201);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'submit farm owner application',
                'user_id' => Auth::id(),
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Get application status
     */
    public function show(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $status = $this->applicationService->getApplicationStatus($userId);

            return $this->successResponse(true, $status, null, 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'get application status',
                'user_id' => Auth::id(),
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Upload/Update ID image
     */
    public function uploadIdImage(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();

            // Must be a farm owner
            if (!$user->isFarmOwner()) {
                return $this->errorResponse('You must be a farm owner to upload ID image', 403);
            }

            // Validate ID image
            $request->validate([
                'id_image' => 'required|image|mimes:jpeg,jpg,png|max:5120', // 5MB max
            ]);

            // Delete old ID image from S3 if exists
            $application = $user->farmOwnerApplication;
            if ($application && $application->id_image) {
                $oldPath = parse_url($application->id_image, PHP_URL_PATH);
                if ($oldPath) {
                    Storage::disk('s3')->delete(ltrim($oldPath, '/'));
                }
            }

            // Upload new ID image to S3
            $ext = $request->file('id_image')->getClientOriginalExtension();
            $slug = Str::slug($user->name);
            $filename = "farm-owner-{$userId}-{$slug}-" . time() . ".{$ext}";

            // Upload to S3 under 'farm-owners/id-images/' folder
            $path = $request->file('id_image')
                ->storeAs('farm-owners/id-images', $filename, 's3');

            $idImageUrl = Storage::disk('s3')->url($path);

            // Update application
            $result = $this->applicationService->uploadIdImage($userId, $idImageUrl);

            return $this->successResponse(true, [
                'message' => $result['message'],
                'application_status' => $this->applicationService->getApplicationStatus($userId),
            ], null, 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'upload ID image',
                'user_id' => Auth::id(),
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
}