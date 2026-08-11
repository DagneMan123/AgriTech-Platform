<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        try {
            $validated = $request->validated();

            // Create user using mass assignment
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'location' => $validated['address'],
                'region' => $validated['region'] ?? null,
                'is_active' => true,
            ]);

            // Generate API token
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user->only(['id', 'name', 'email', 'phone', 'role', 'location', 'region']),
                'token' => $token,
            ], 201);

        } catch (QueryException $e) {
            // Handle database-specific errors
            $message = 'A database error occurred during registration';
            $errorMessage = $e->getMessage();
            
            if (strpos($errorMessage, 'Duplicate') !== false || 
                strpos($errorMessage, 'duplicate') !== false) {
                if (strpos($errorMessage, 'email') !== false) {
                    $message = 'Email address is already registered';
                } elseif (strpos($errorMessage, 'phone') !== false) {
                    $message = 'Phone number is already registered';
                } else {
                    $message = 'Email or phone number already registered';
                }
            } elseif (strpos($errorMessage, 'Undefined column') !== false ||
                      strpos($errorMessage, 'undefined column') !== false) {
                $message = 'Database configuration error. Please contact support.';
            }
            
            \Log::error('Registration database error: ' . $e->getMessage());
            return response()->json([
                'message' => $message,
                'errors' => ['registration' => [$message]]
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'An error occurred during registration. Please try again later.',
                'errors' => ['registration' => ['An unexpected error occurred']]
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ], [
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'password.required' => 'Password is required.',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'The provided credentials are incorrect.',
                    'errors' => ['email' => ['The provided credentials are incorrect.']]
                ], 422);
            }

            if (!$user->is_active) {
                return response()->json([
                    'message' => 'This account has been suspended.',
                    'errors' => ['email' => ['This account has been suspended.']]
                ], 403);
            }

            $user->update(['last_login_at' => now()]);

            // Generate API token
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => $user->only(['id', 'name', 'email', 'phone', 'role', 'location', 'region', 'is_active']),
                'token' => $token,
            ], 200);

        } catch (QueryException $e) {
            \Log::error('Login database error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred during login. Please try again later.',
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'An error occurred during login. Please try again later.',
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $request->user()->id,
            'location' => 'sometimes|string|max:255',
            'region' => 'sometimes|string|max:255',
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();
        $data = $request->only(['name', 'phone', 'location', 'region']);

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $data['profile_image'] = $path;
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password changed successfully']);
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset link sent to email'])
            : response()->json(['message' => 'Unable to send reset link'], 400);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->update(['password' => Hash::make($password)]);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successfully'])
            : response()->json(['message' => 'Invalid reset token'], 400);
    }
}
