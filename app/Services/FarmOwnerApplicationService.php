<?php

namespace App\Services;

use App\Models\FarmOwnerApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class FarmOwnerApplicationService
{
    /**
     * Submit farm owner application
     * User gets farm_owner role immediately
     */
    public function submitApplication(int $userId, ?string $idImageUrl = null): array
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($userId);

            // Check if user already has an application
            if ($user->farmOwnerApplication) {
                throw new Exception('User already has a farm owner application');
            }

            // Create application
            $application = FarmOwnerApplication::create([
                'user_id' => $userId,
                'id_image' => $idImageUrl, // Store S3 full URL
                'id_verification_status' => FarmOwnerApplication::STATUS_PENDING,
                'applied_at' => now(),
            ]);

            // Assign farm_owner role immediately
            $user->assignRole('farm_owner');

            DB::commit();

            Log::info('Farm owner application submitted', [
                'user_id' => $userId,
                'has_id_image' => !empty($idImageUrl),
            ]);

            return [
                'success' => true,
                'application' => $application,
                'message' => 'You are now a farm owner! ' . ($idImageUrl ? 'ID image uploaded for verification.' : 'You can upload your ID image later.'),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            // Delete uploaded image from S3 if exists
            if ($idImageUrl) {
                $path = parse_url($idImageUrl, PHP_URL_PATH);
                if ($path) {
                    Storage::disk('s3')->delete(ltrim($path, '/'));
                }
            }

            Log::error('Failed to submit farm owner application', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Upload or update ID image
     */
    public function uploadIdImage(int $userId, string $idImageUrl): array
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($userId);
            $application = $user->farmOwnerApplication;

            if (!$application) {
                throw new Exception('Farm owner application not found');
            }

            // Note: Old image deletion is handled in the controller before calling this

            // Update with new image URL
            $application->update([
                'id_image' => $idImageUrl, // Store S3 full URL
                'id_verification_status' => FarmOwnerApplication::STATUS_PENDING, // Reset to pending
                'verified_at' => null,
            ]);

            DB::commit();

            Log::info('Farm owner ID image uploaded', [
                'user_id' => $userId,
                'application_id' => $application->id,
            ]);

            return [
                'success' => true,
                'application' => $application,
                'message' => 'ID image uploaded successfully. Pending verification.',
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            // Delete uploaded image from S3 if exists
            if ($idImageUrl) {
                $path = parse_url($idImageUrl, PHP_URL_PATH);
                if ($path) {
                    Storage::disk('s3')->delete(ltrim($path, '/'));
                }
            }

            Log::error('Failed to upload ID image', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Get application status
     */
    public function getApplicationStatus(int $userId): array
    {
        $user = User::findOrFail($userId);
        $application = $user->farmOwnerApplication;

        if (!$application) {
            return [
                'has_application' => false,
                'is_farm_owner' => false,
            ];
        }

        return [
            'has_application' => true,
            'is_farm_owner' => $user->isFarmOwner(),
            'id_image' => [
                'uploaded' => $application->hasIdImage(),
                'status' => $application->id_verification_status,
                'url' => $application->id_image, // Already full S3 URL
            ],
            'applied_at' => $application->applied_at,
            'verified_at' => $application->verified_at,
        ];
    }

    /**
     * Verify ID image (Admin function)
     */
    public function verifyIdImage(int $applicationId): array
    {
        try {
            DB::beginTransaction();

            $application = FarmOwnerApplication::findOrFail($applicationId);

            if (!$application->hasIdImage()) {
                throw new Exception('No ID image to verify');
            }

            $application->update([
                'id_verification_status' => FarmOwnerApplication::STATUS_VERIFIED,
                'verified_at' => now(),
            ]);

            DB::commit();

            Log::info('Farm owner ID verified', [
                'application_id' => $applicationId,
                'user_id' => $application->user_id,
            ]);

            return [
                'success' => true,
                'message' => 'ID image verified successfully',
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to verify ID image', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Get all pending applications (Admin function)
     */
    public function getPendingApplications()
    {
        return FarmOwnerApplication::with('user')
            ->withIdImage()
            ->pending()
            ->orderBy('applied_at', 'desc')
            ->get();
    }

    /**
     * Delete ID image
     */
    public function deleteIdImage(int $userId): array
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($userId);
            $application = $user->farmOwnerApplication;

            if (!$application || !$application->hasIdImage()) {
                throw new Exception('No ID image to delete');
            }

            // Note: S3 deletion is handled in the controller before calling this

            // Update application
            $application->update([
                'id_image' => null,
                'id_verification_status' => FarmOwnerApplication::STATUS_PENDING,
                'verified_at' => null,
            ]);

            DB::commit();

            Log::info('Farm owner ID image deleted', [
                'user_id' => $userId,
                'application_id' => $application->id,
            ]);

            return [
                'success' => true,
                'message' => 'ID image deleted successfully',
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete ID image', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}