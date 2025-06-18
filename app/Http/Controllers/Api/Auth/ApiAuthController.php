<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ApiAuthController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;

    /**
     * The static OTP code to use during development
     * 
     * @var string
     */
    protected $staticOtp = '1234';

    /**
     * OTP expiry time in minutes
     * 
     * @var int
     */
    protected $otpExpiryMinutes = 10;

    public function register(Request $request)
    {
        /**
         * First step in registration process - store user data and send OTP with security token.
         *
         * This method validates user input, creates a new unverified user record,
         * generates an OTP code with a security token, and returns success message.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => [
                'required','string','max:20',
                Rule::unique('users')->whereNotNull('phone_verified_at'),
            ],
            'city_id'  => 'required|integer|exists:cities,id', 
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // Look for an existing *unverified* user
            $user = User::where('phone', $validated['phone'])
                        ->whereNull('phone_verified_at')
                        ->first();

            // Generate a security token that will be required during verification
            $securityToken = Str::random(64);
            
            $userData = [
                'otp_code'       => Hash::make($this->staticOtp),
                'security_token' => $securityToken,
                'otp_expires_at' => Carbon::now()->addMinutes($this->otpExpiryMinutes),
                'name'           => $validated['name'],
                'city_id'        => $validated['city_id'],
                'password'       => Hash::make($validated['password']),
            ];

            if ($user) {
                // update the unverified user
                $user->update($userData);
            } else {
                // create brand‑new record
                $user = User::create(array_merge(
                    ['phone' => $validated['phone']],
                    $userData
                ));
            }

            // In production you'd dispatch an SMS job here with just the OTP
            return $this->successResponse(true, [
                'message'        => 'OTP has been sent to your phone number',
                'security_token' => $securityToken,
                'expires_in'     => "{$this->otpExpiryMinutes} minutes",
            ], null, 200);

        } catch (\Exception $e) {
            $this->logException($e, [
                'request' => $request->except('password', 'password_confirmation'),
            ]);

            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        /**
         * Verify OTP with security token to complete registration process.
         *
         * This method validates the OTP and security token, verifies them against stored data,
         * completes user registration upon successful verification, and returns auth token.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        $data = $request->validate([
            'phone'          => 'required|string|max:20',
            'otp'            => 'required|string|size:4',
            'security_token' => 'required|string',
        ]);
        try {
            $user = User::where('phone', $request->phone)
                        ->whereNull('phone_verified_at')
                        ->first();

            if (!$user) {
                return $this->errorResponse(__('auth.user_not_found'), 404);
            }

            // Check if OTP is expired
            if (Carbon::now()->isAfter($user->otp_expires_at)) {
                return $this->errorResponse(__('auth.otp_expired'), 400);
            }

            // 1) first match the hashed OTP
            if (! Hash::check($data['otp'], $user->otp_code)) {
                return $this->errorResponse(__('auth.invalid_verification'), 400);
            }

            // 2) then guard+compare the token
            if (! $user->security_token ||
                ! hash_equals($user->security_token, $data['security_token'])
            ) {
                return $this->errorResponse(__('auth.invalid_verification'), 400);
            }

            // Mark phone as verified
            $user->update([
                'phone_verified_at' => Carbon::now(),
                'otp_code'          => null,
                'security_token'    => null,
                'otp_expires_at'    => null,
            ]);

            // Create token
            $token = $user->createToken('API Token')->plainTextToken;

            $responseData = [
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => new UserResource($user),
            ];

            return $this->successResponse(true, $responseData, null, 200);
        } catch (\Exception $e) {
            $this->logException($e, [
                'request' => $request->except(['otp', 'security_token']),
            ]);

            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    public function resendOtp(Request $request)
    {
        /**
         * Resend OTP code to user with validation of existing security token
         * and generation of a new security token.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        $data = $request->validate([
            'phone'          => 'required|string|max:20',
            'security_token' => 'required|string', // Require the original security token
        ]);
        try {
            $user = User::where('phone', $data['phone'])
                        ->whereNull('phone_verified_at')
                        ->first();
    
            if (!$user) {
                return $this->errorResponse(__('auth.user_not_found'), 404);
            }
    
            // Verify the security token matches
            if (!$user->security_token || !hash_equals($user->security_token, $data['security_token'])) {
                return $this->errorResponse(__('auth.invalid_token'), 400);
            }
    
            // Generate a new security token
            $newSecurityToken = Str::random(64);
            
            // Update OTP code, security token and expiry
            $user->update([
                'otp_code'       => Hash::make($this->staticOtp),
                'security_token' => $newSecurityToken,
                'otp_expires_at' => Carbon::now()->addMinutes($this->otpExpiryMinutes),
            ]);
    
            // In production, you would send only the OTP via SMS here
            $responseData = [
                'message'        => 'OTP has been resent to your phone number',
                'security_token' => $newSecurityToken, // Return the new token
                'expires_in'     => $this->otpExpiryMinutes . ' minutes',
            ];
    
            return $this->successResponse(true, $responseData, null, 200);
        } catch (\Exception $e) {
            $this->logException($e, [
                'request' => $request->except('security_token'),
            ]);
    
            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    public function login(Request $request)
    {
        /**
         * Login for API authentication.
         *
         * This method authenticates the user using their phone and password,
         * generates an API token upon successful authentication, and returns the
         * token along with the user data.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            $request->validate([
                'phone' => 'required|string',
                'password' => 'required|string',
            ]);

            // Check if the user exists and is verified
            $user = User::where('phone', $request->phone)
                        ->whereNotNull('phone_verified_at')
                        ->with('city')
                        ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->errorResponse(__('auth.failed'), 422);
            }

            // Prevent login if status != 'active'
            if ($user->status !== 'active') {
                return $this->errorResponse(__('auth.inactive_account'), 403);
            }

            $token = $user->createToken('API Token')->plainTextToken;

            $responseData = [
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => new UserResource($user),
            ];

            return $this->successResponse(true, $responseData, null, 200);
        } catch (\Exception $e) {
            $this->logException($e, [
                'request' => $request->all(),
                'user_id' => Auth::id(),
            ]);

            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    public function logout(Request $request)
    {
        /**
         * Logout for API authentication.
         *
         * This method revokes the current access token of the authenticated user,
         * effectively logging them out.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            $currentToken = $request->user()->currentAccessToken();
            if ($currentToken) {
                $currentToken->delete();
            }

            return $this->successResponse(true, __('auth.logged_out_successfully'), null, 200);
        } catch (\Exception $e) {
            $this->logException($e, [
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }
}