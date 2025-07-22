<?php

namespace App\Http\Controllers\Api\FarmOwner;

use App\Http\Controllers\Controller;
use App\Http\Requests\FarmOwner\StoreFarmRequest;
use App\Http\Resources\ShowFarmResource;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use App\Traits\FarmImageTrait;
use App\Traits\FarmPricingTrait;
use App\Traits\FarmHelperTrait;
use App\Traits\FarmStoreTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Resources\FarmCollection;
use Illuminate\Validation\ValidationException; 

class ApiFarmController extends Controller
{
    use JsonResponseTrait, 
        ExceptionLoggerTrait, 
        FarmImageTrait, 
        FarmPricingTrait,
        FarmHelperTrait,
        FarmStoreTrait;
    
    /**
     * Display a listing of farms for the authenticated farm owner.
     * By default shows only active farms, use status filter to see others.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get authenticated user (guaranteed to exist due to auth:sanctum middleware)
            $user = Auth::user();

            $query = Farm::query();
            
            // Filter farms by the authenticated user (farm owner)
            $query->where('user_id', $user->id);
            
            // Apply status filtering (default to active farms only)
            $status = $request->input('status', 'active');
            if (in_array($status, ['pending', 'active', 'rejected', 'disabled'])) {
                $query->where('status', $status);
            } elseif ($status === 'all') {
                // Show all statuses
            } else {
                $query->where('status', 'active'); // Default fallback
            }
            
            // Apply name filtering if provided
            if ($request->filled('name')) {
                $searchName = $request->input('name');
                $query->where(function ($q) use ($searchName) {
                    $q->where('name_en', 'LIKE', "%{$searchName}%")
                    ->orWhere('name_ar', 'LIKE', "%{$searchName}%");
                });
            }
            
            // Order by created_at desc to show newest farms first
            $query->orderBy('created_at', 'desc');
            
            $relationships = $this->getFarmRelationships();
            $farms = $query->with($relationships)->paginate($request->per_page ?? 10);
            
            // Load farm images
            $this->loadFarmImages($farms);
            
            return $this->successResponse(true, new FarmCollection($farms), null, 200);
            
        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'fetch owner farms', 
                'user_id' => Auth::id(),
                'filters' => $request->only(['name', 'per_page', 'status'])
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Display the specified farm.
     */
    public function show($farmId): JsonResponse
    {
        try {
            $farm = Farm::where('id', $farmId)
                       ->where('user_id', Auth::id())
                       ->first();

            if (!$farm) {
                return $this->errorResponse(__('farm.not_found'), 404);
            }

            $relationships = $this->getFarmRelationships();
            $farm->load($relationships);
            
            return $this->successResponse(true, new ShowFarmResource($farm), null, 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'show farm', 'id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }    
    }

    /**
     * Store farm data step by step
     */
    public function store(StoreFarmRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $step = $request->input('step', 1);
            $farmId = $request->input('farm_id'); // For updating existing draft
            
            // Get or create farm
            $farm = $farmId ? Farm::findOrFail($farmId) : new Farm(['user_id' => Auth::id()]);
            
            // Ensure user owns the farm (for updates)
            if ($farm->exists && $farm->user_id !== Auth::id()) {
                return $this->errorResponse(__('farm.unauthorized'), 403);
            }
            
            switch ($step) {
                case 1:
                    $this->handleStep1($farm, $request);
                    break;
                case 2:
                    $this->handleStep2($farm, $request);
                    break;
                case 3:
                    $this->handleStep3($farm, $request);
                    break;
                case 4:
                    $this->handleStep4($farm, $request);
                    break;
                case 5:
                    $this->handleStep5($farm, $request);
                    break;
                default:
                    return $this->errorResponse(__('farm.invalid_step'), 400);
            }
            
            // Update step progress
            $farm->current_step = max($farm->current_step ?? 1, $step);
            
            // Keep status as 'pending' - admin will review when all steps completed
            if (!$farm->exists) {
                $farm->status = 'pending'; // Set on first save
            }
            
            $farm->save();
            
            DB::commit();
            
            // Load relationships for response
            $farm->load(['city', 'area', 'features', 'images', 'pricing', 'offers']);
            
            return $this->successResponse(true, [
                'message'    => __('farm.step_saved'),
                'farm' => new ShowFarmResource($farm),
                'current_step' => $farm->current_step,
                'status' => $farm->status,
                'is_ready_for_review' => $farm->current_step >= 5,
                'next_step' => $step < 5 ? $step + 1 : null
            ], null, 200);
            
        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->errorResponse($e->errors(), 422);
        } catch (Exception $e) {
            DB::rollBack();
            $this->logException($e, ['action' => 'store farm step', 'step' => $step]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Remove the specified farm from storage.
     */
    public function destroy($farmId): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $farm = Farm::where('id', $farmId)
                       ->where('user_id', Auth::id())
                       ->first();

            if (!$farm) {
                return $this->errorResponse(__('farm.not_found'), 404);
            }
            
            // Delete farm images from storage
            foreach ($farm->images as $image) {
                $this->deleteImageFromS3($image->image_path);
            }
            
            // The farm, feature relationships, images, pricing, and offers will be deleted due to cascading constraints
            $farm->delete();
            
            DB::commit();
            
            return $this->successResponse(true, __('farm.deleted'), null, 200);
            
        } catch (Exception $e) {
            DB::rollBack();
            $this->logException($e, ['action' => 'delete farm', 'farm_id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    // ──────────────────────────────────Image Management────────────────────────────────────────

    /**
     * Upload main image for farm
     */
    public function uploadMainImage(Request $request, $farmId): JsonResponse
    {
        try {
            $request->validate([
                'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $farm = Farm::where('id', $farmId)
                       ->where('user_id', Auth::id())
                       ->first();

            if (!$farm) {
                return $this->errorResponse(__('farm.not_found'), 404);
            }

            // Upload new main image (don't set as main yet - that happens in Step 3)
            $mainImageUrl = $this->uploadFarmImage($request->file('main_image'), $farm, 'main');
            
            $image = $farm->images()->create([
                'image_path' => $mainImageUrl,
                'is_main' => false, // Will be set to true in Step 3
            ]);

            return $this->successResponse(true, [
                'image' => [
                    'message'    => __('farm.main_image_uploaded'),
                    'id' => $image->id,
                    'image_path' => $image->image_path,
                ]
            ], null, 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'upload main image', 'farm_id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Upload gallery images for farm
     */
    public function uploadGalleryImages(Request $request, $farmId): JsonResponse
    {
        try {
            $request->validate([
                'images' => 'required|array|max:10',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $farm = Farm::where('id', $farmId)
                       ->where('user_id', Auth::id())
                       ->first();

            if (!$farm) {
                return $this->errorResponse(__('farm.not_found'), 404);
            }

            $uploadedImages = [];
            
            foreach ($request->file('images') as $index => $image) {
                try {
                    $imageUrl = $this->uploadFarmImage($image, $farm, $index);
                    
                    $farmImage = $farm->images()->create([
                        'image_path' => $imageUrl,
                        'is_main' => false,
                    ]);

                    $uploadedImages[] = [
                        'id' => $farmImage->id,
                        'image_path' => $farmImage->image_path,
                    ];

                } catch (Exception $e) {
                    \Log::warning("Failed to upload gallery image {$index}", [
                        'farm_id' => $farmId,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with other images
                }
            }

            return $this->successResponse(true, [
                'message'    => __('farm.gallery_images_uploaded'),
                'images' => $uploadedImages,
                'uploaded_count' => count($uploadedImages),
                'total_requested' => count($request->file('images'))
            ], null, 200);

        } catch (Exception $e) {
            $this->logException($e, ['action' => 'upload gallery images', 'farm_id' => $farmId]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    /**
     * Delete specific farm image
     */
    public function deleteImage(Request $request, $farmId, $imageId): JsonResponse
    {
        try {
            $farm = Farm::where('id', $farmId)
                       ->where('user_id', Auth::id())
                       ->first();

            if (!$farm) {
                return $this->errorResponse(__('farm.not_found'), 404);
            }

            $image = $farm->images()->where('id', $imageId)->first();
            
            if (!$image) {
                return $this->errorResponse(__('farm.image_not_found'), 404);
            }

            // Delete from S3
            $this->deleteImageFromS3($image->image_path);
            
            // Delete from database
            $image->delete();

            return $this->successResponse(true, __('farm.image_deleted'), null, 200);

        } catch (Exception $e) {
            $this->logException($e, [
                'action' => 'delete farm image', 
                'farm_id' => $farmId, 
                'image_id' => $imageId
            ]);
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
}