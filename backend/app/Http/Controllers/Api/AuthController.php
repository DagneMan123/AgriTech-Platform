<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            $user = DB::table('users')
                ->where('email', $validated['email'])
                ->first(['id', 'name', 'email', 'phone', 'role', 'password', 'is_active']);

            if (!$user) {
                return response()->json([
                    'message' => 'Invalid email or password',
                    'errors' => ['email' => ['Invalid credentials']]
                ], 422);
            }

            if (!Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'message' => 'Invalid email or password',
                    'errors' => ['password' => ['Invalid credentials']]
                ], 422);
            }

            if (!$user->is_active) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['is_active' => true, 'updated_at' => now()]);
            }

            $plainToken = Str::random(120);
            $hashedToken = hash('sha256', $plainToken);

            try {
                DB::table('personal_access_tokens')->insert([
                    'tokenable_type' => 'App\\Models\\User',
                    'tokenable_id' => $user->id,
                    'name' => 'api-token',
                    'token' => $hashedToken,
                    'abilities' => json_encode(['*']),
                    'last_used_at' => null,
                    'expires_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $tokenError) {
                Log::error('Token insertion failed', [
                    'message' => $tokenError->getMessage(),
                    'user_id' => $user->id,
                    'token_hash' => substr($hashedToken, 0, 10) . '...'
                ]);
                return response()->json([
                    'message' => 'Failed to create session token',
                    'error' => true
                ], 500);
            }

            return response()->json([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                ],
                'token' => $plainToken,
                'token_type' => 'Bearer',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Login validation failed', ['email' => $request->email]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Login error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Server error: ' . $e->getMessage(),
                'error' => true
            ], 500);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return response()->json($user, 200);
        } catch (\Exception $e) {
            Log::error('Me endpoint error', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching user'], 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return response()->json($user, 200);
        } catch (\Exception $e) {
            Log::error('Profile endpoint error', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching profile'], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'location' => 'sometimes|string|max:255',
                'region' => 'sometimes|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            DB::table('users')
                ->where('id', $user->id)
                ->update(array_merge($validated, ['updated_at' => now()]));

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => array_merge((array)$user, $validated)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Update profile error', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Error updating profile'], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            $userData = DB::table('users')->where('id', $user->id)->first();

            if (!Hash::check($validated['current_password'], $userData->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'password' => Hash::make($validated['password']),
                    'updated_at' => now()
                ]);

            return response()->json(['message' => 'Password changed successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Change password error', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Error changing password'], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), ['email' => 'required|email']);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            $user = DB::table('users')
                ->where('email', $validated['email'])
                ->first(['id', 'name', 'email']);

            if ($user) {
                $resetToken = Str::random(60);

                DB::table('password_resets')->updateOrInsert(
                    ['email' => $user->email],
                    [
                        'token' => Hash::make($resetToken),
                        'created_at' => now(),
                    ]
                );

                Log::info('Password reset requested', ['email' => $user->email]);
            }

            return response()->json([
                'message' => 'If email exists, a password reset link has been sent'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Forgot password error', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Error processing request'], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            $user = DB::table('users')->where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json(['message' => 'Invalid email'], 422);
            }

            $reset = DB::table('password_resets')
                ->where('email', $validated['email'])
                ->first();

            if (!$reset || !Hash::check($validated['token'], $reset->token)) {
                return response()->json(['message' => 'Invalid token'], 422);
            }

            if (strtotime($reset->created_at) + 3600 < time()) {
                DB::table('password_resets')->where('email', $validated['email'])->delete();
                return response()->json(['message' => 'Token expired'], 422);
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'password' => Hash::make($validated['password']),
                    'updated_at' => now()
                ]);

            DB::table('password_resets')->where('email', $validated['email'])->delete();

            return response()->json(['message' => 'Password reset successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Reset password error', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Error resetting password'], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $token = $request->bearerToken();
            if ($token) {
                $hashedToken = hash('sha256', $token);
                DB::table('personal_access_tokens')
                    ->where('token', $hashedToken)
                    ->delete();
            }

            return response()->json(['message' => 'Logged out successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Logout error', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Error logging out'], 500);
        }
    }

    public function register(Request $request)
    {
        return response()->json([
            'message' => 'Registration endpoint coming soon',
            'status' => 'pending'
        ], 501);
    }
}