<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * User Registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'status_code' => 422,
                'error_code' => 'validation_failed',
                'error_message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user', 
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'ok',
            'status_code' => 201,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 201);
    }

    /**
     * Seller Registration
     */
    public function registerSeller(Request $request)
    {
        // Get the authenticated user
        $currentUser = $request->user();

        // Ensure the user is authenticated as a normal user
        if (!$currentUser || $currentUser->role !== 'user') {
            return response()->json([
                'status' => 'error',
                'status_code' => 401,
                'error_code' => 'unauthorized',
                'error_message' => 'You must be a registered user first.'
            ], 401);
        }

        // Validate seller registration request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'status_code' => 422,
                'error_code' => 'validation_failed',
                'error_message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create new seller account
        $seller = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'seller',
        ]);

        // Generate a token for the seller
        $token = $seller->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'ok',
            'status_code' => 201,
            'message' => 'Seller account created successfully',
            'data' => [
                'user' => $seller,
                'token' => $token
            ]
        ], 201);
    }

    /**
     * Login User or Seller
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'status_code' => 422,
                'error_code' => 'validation_failed',
                'error_message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'status_code' => 401,
                'error_code' => 'invalid_credentials',
                'error_message' => 'Invalid email or password'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'role' => $user->role,
                'token' => $token
            ]
        ], 200);
    }

    /**
     * Logout User
     */
    public function logout(Request $request)
    {
        // Revoke all tokens of the authenticated user
        $request->user()->tokens()->delete();
    
        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'Successfully logged out'
        ], 200);
    }

    /**
     * Get User Profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'ok',
            'status_code' => 200,
            'message' => 'User profile retrieved successfully',
            'data' => [
                'user' => $request->user()
            ]
        ], 200);
    }
}
