<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateAvatarRequest;

class ApiUserController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;

    public function profile(Request $request)
    {
        /**
         * Get authenticated user profile.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            $user = $request->user();
            
            return $this->successResponse(true, [
                'user' => new UserResource($user),
            ], null, 200);
        } catch (\Exception $e) {
            $this->logException($e, [
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        /**
         * Update authenticated user profile.
         *
         * This method allows users to update their profile information including
         * name, city, password, and avatar. Avatar is uploaded to S3 storage.
         *
         * @param \Illuminate\Http\UpdateProfileRequest $request
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            $user = $request->user();
        
            // Only the validated fields are present now:
            $data = $request->safe()->except(['current_password', 'password_confirmation']);
        
            // Handle password if present:
            if ($request->filled('password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return $this->errorResponse(__('auth.current_password_incorrect'), 400);
                }
                $data['password'] = Hash::make($request->password);
            }
        
            $user->update($data);
        
            return $this->successResponse(true, [
                'message' => __('auth.profile_updated_successfully'),
                'user'    => new UserResource($user->fresh()),
            ], null, 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse($e->errors(), 422);
        } catch (\Exception $e) {
            $this->logException($e, [
                'request' => $request->except(['password', 'current_password', 'password_confirmation']),
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    public function updateAvatar(UpdateAvatarRequest $request)
    {
        /**
         * Update user avatar only.
         *
         * This method allows users to update only their avatar image.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            $user = Auth::user();
            
            // Delete old avatar if present
            if ($user->avatar) {
                $oldPath = parse_url($user->avatar, PHP_URL_PATH);
                if ($oldPath) {
                    Storage::disk('s3')->delete(ltrim($oldPath, '/'));
                }
            }

            $ext = $request->file('avatar')->getClientOriginalExtension();
            $slug = Str::slug($user->name);
            $filename = "user-{$user->id}-{$slug}-" . time() . ".{$ext}";

            // Upload to S3 under 'avatars/' folder
            $path = $request->file('avatar')
                ->storeAs('avatars', $filename, 's3');

            $avatarUrl = Storage::disk('s3')->url($path);

            // Update user avatar
            $user->update(['avatar' => $avatarUrl]);

            return $this->successResponse(true, [
                'message'    => __('auth.avatar_updated_successfully'),
                'avatar_url' => $avatarUrl,
                'user'       => new UserResource($user->fresh()),
            ], null, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse($e->errors(), 422);
        } catch (\Exception $e) {
            $this->logException($e, [
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    public function deleteAvatar(Request $request)
    {
        /**
         * Delete user avatar.
         *
         * This method allows users to remove their avatar image.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            $user = $request->user();
            
            if (!$user->avatar) {
                return $this->errorResponse(__('auth.no_avatar_to_delete'), 400);
            }

            // Delete avatar from S3
            $oldPath = parse_url($user->avatar, PHP_URL_PATH);
            if ($oldPath) {
                Storage::disk('s3')->delete(ltrim($oldPath, '/'));
            }

            // Remove avatar from user record
            $user->update(['avatar' => null]);

            return $this->successResponse(true, [
                'message' => __('auth.avatar_deleted_successfully'),
                'user'    => new UserResource($user->fresh()),
            ], null, 200);

        } catch (\Exception $e) {
            $this->logException($e, [
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
}