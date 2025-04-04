<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Exception;

class GoogleLoginController extends Controller
{
    /**
     * Handle the Google login process via a POST request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function googleLogin(Request $request): JsonResponse
    {
        // Validate that the access_token is provided
        $request->validate([
            'access_token' => 'required|string',
            'device_token' => 'nullable|string',
        ]);

        try {
            // Validate Google token by calling Google API
            $googleResponse = Http::get("https://www.googleapis.com/oauth2/v1/userinfo", [
                'alt' => 'json',
                'access_token' => $request->access_token,
            ]);

            if ($googleResponse->failed()) {
                return response()->json([
                    'status' => 'error',
                    'status_code' => 400,
                    'message' => 'Invalid Google access token provided',
                ], 400);
            }

            $googleUserData = $googleResponse->json();

            // Check if user exists based on google_id or email
            $user = User::where('google_id', $googleUserData['id'])
                ->orWhere('email', $googleUserData['email'])
                ->first();

            if (!$user) {
                // If no user exists, create a new one
                $user = User::create([
                    'google_id' => $googleUserData['id'],
                    'name' => $googleUserData['name'] ?? 'Google User',
                    'first_name' => $googleUserData['given_name'] ?? null,
                    'last_name' => $googleUserData['family_name'] ?? null,
                    'email' => $googleUserData['email'],
                    'image' => $googleUserData['picture'] ?? null,
                    'password' => bcrypt(uniqid()), // Set a random password
                    'device_token' => $request->device_token,
                ]);
            } else {
                // Update google_id if not set
                $user->update([
                    // 'google_id' => $user->google_id ?? $googleUserData['id'],
                    // 'device_token' => $request->device_token,
                    'google_id' => $googleUserData['id'],
                    'device_token' => $request->device_token,
                ]);
            }

            // Log the user in
            Auth::login($user);

            // Generate token using Sanctum
            $token = $user->createToken('GoogleLoginToken')->plainTextToken;

            return response()->json([
                'status' => 'ok',
                'status_code' => 200,
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'message' => 'Unable to login using Google. Please try again.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}