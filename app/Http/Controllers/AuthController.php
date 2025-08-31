<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials',
                    'errors' => [
                        'email' => ['coba lagi.']
                    ]
                ], 401);
            }

            if (!$user->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account is inactive',
                    'errors' => [
                        'email' => ['coba aja']
                    ]
                ], 401);
            }

            // Delete existing tokens
            $user->tokens()->delete();

            // Create new token
            $token = $user->createToken('auth-token', ['*'], now()->addDays(7))->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'ruko_id' => $user->ruko_id,
                        'ruko' => $user->ruko,
                        'is_active' => $user->is_active
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'errors' => ['server' => ['Something went wrong. Please try again.']]
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|in:admin_pusat,admin_ruko',
                'ruko_id' => 'nullable|exists:rukos,id|required_if:role,admin_ruko'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'ruko_id' => $request->ruko_id,
                'is_active' => true
            ]);

            $token = $user->createToken('auth-token', ['*'], now()->addDays(7))->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'ruko_id' => $user->ruko_id,
                        'ruko' => $user->ruko,
                        'is_active' => $user->is_active
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'errors' => ['server' => ['Something went wrong. Please try again.']]
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Delete current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed',
                'errors' => ['server' => ['Something went wrong during logout.']]
            ], 500);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $request->user()->load('ruko');
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'ruko_id' => $user->ruko_id,
                        'ruko' => $user->ruko,
                        'is_active' => $user->is_active
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get user information',
                'errors' => ['server' => ['Something went wrong.']]
            ], 500);
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $user = $request->user();
            
            // Delete current token
            $request->user()->currentAccessToken()->delete();
            
            // Create new token
            $token = $user->createToken('auth-token', ['*'], now()->addDays(7))->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token refresh failed',
                'errors' => ['server' => ['Something went wrong.']]
            ], 500);
        }
    }
}