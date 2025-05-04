<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\JsonResponseTrait;
use App\Traits\ExceptionLoggerTrait;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ApiAuthController extends Controller
{
    use JsonResponseTrait, ExceptionLoggerTrait;

    public function register(Request $request)
    {
        /**
         * Register a new user for API authentication.
         *
         * This method validates user input, creates a new user record,
         * generates an API token, and returns the token along with the user data.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'required|string|max:20',
                'city' => 'required|string|max:100',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
                'city' => $validatedData['city'],
                'password' => Hash::make($validatedData['password']),
            ]);

            $token = $user->createToken('API Token')->plainTextToken;

            $responseData = [
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => new UserResource($user),
            ];

            return $this->successResponse(true, $responseData, __('auth.registered_successfully'), 201);
        } catch (\Exception $e) {
            $this->logException($e, [
                'request' => $request->except('password', 'password_confirmation'),
            ]);

            return $this->errorResponse(__('error.internal_error'), 500);
        }
    }

    public function login(Request $request)
    {
        /**
         * Login for API authentication.
         *
         * This method authenticates the user using their username and password,
         * generates an API token upon successful authentication, and returns the
         * token along with the user data.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        try {
            $request->validate([
                'email' => 'required|string',
                'password' => 'required|string',
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                return $this->errorResponse(__('auth.failed'), 422);
            }

            $user = Auth::user();
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