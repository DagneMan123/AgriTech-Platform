<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\Farmer;
use App\Models\Buyer;
use App\Models\Supplier;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validated();
                $role = strtolower($validated['role']);

                // 1. Prepare user data with all required fields
                $userData = [
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'password' => Hash::make($validated['password']),
                    'role' => $role,
                    'address' => $validated['address'] ?? null,
                    'location' => $validated['address'] ?? null,
                    'region' => $validated['region'] ?? null,
                    'is_active' => true,
                ];

                // 2. Create user
                $user = User::create($userData);

                // 3. Automatically create the respective role profile
                try {
                    $this->createRoleProfile($user, $role);
                } catch (\Exception $profileError) {
                    Log::error('Error creating role profile: ' . $profileError->getMessage(), [
                        'user_id' => $user->id,
                        'role' => $role,
                    ]);
                    // Don't fail registration if profile creation fails - it can be created later
                }

                // 4. Generate API token
                $token = $user->createToken('api-token')->plainTextToken;

                return response()->json([
                    'message' => 'User registered successfully',
                    'user' => $user->only(['id', 'name', 'email', 'phone', 'role', 'location', 'region', 'address']),
                    'token' => $token,
                ], 201);
            });
        } catch (QueryException $e) {
            $errorMessage = $e->getMessage();
            $message = 'A database error occurred during registration';

            // Check for specific constraint violations
            if (strpos($errorMessage, 'duplicate') !== false || strpos($errorMessage, 'Duplicate') !== false) {
                if (strpos($errorMessage, 'users_email_unique') !== false || strpos($errorMessage, 'email') !== false) {
                    $message = 'Email address is already registered';
                } elseif (strpos($errorMessage, 'users_phone_unique') !== false || strpos($errorMessage, 'phone') !== false) {
                    $message = 'Phone number is already registered';
                }
            } elseif (strpos($errorMessage, '23503') !== false) {
                // Foreign key constraint violation
                $message = 'Database constraint error. Please contact support if this persists.';
            } elseif (strpos($errorMessage, '23502') !== false) {
                // NOT NULL constraint violation
                $message = 'Missing required field. Please provide all required information.';
            }

            Log::error('Registration database error: ' . $errorMessage, [
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? null,
                'exception' => $e->getCode(),
            ]);

            return response()->json([
                'message' => $message,
                'errors' => ['registration' => [$message]]
            ], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Registration validation error: ', $e->errors());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'An error occurred during registration. Please try again later.',
                'errors' => ['registration' => ['An unexpected error occurred']]
            ], 500);
        }
    }

    /**
     * Create role-specific profile for the user safely
     */
    private function createRoleProfile(User $user, string $role)
    {
        $timestamp = time();

        try {
            match ($role) {
                'farmer' => Farmer::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'farmer_registration_number' => 'FRM-' . $user->id . '-' . $timestamp,
                        'farm_name' => $user->name . ' Farm',
                        'region' => $user->region ?? 'Not Specified',
                        'zone' => 'Not Specified',
                        'woreda' => 'Not Specified',
                    ]
                ),
                'buyer' => Buyer::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'buyer_type' => 'individual',
                        'business_name' => $user->name,
                        'business_address' => $user->address ?? 'Not specified',
                    ]
                ),
                'supplier' => Supplier::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'company_name' => $user->name,
                        'company_address' => $user->address ?? 'Not specified',
                        'contact_person' => $user->name,
                    ]
                ),
                'transport' => \App\Models\Transport::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'company_name' => $user->name,
                        'contact_person' => $user->name,
                        'emergency_contact' => $user->phone,
                    ]
                ),
                'expert' => \App\Models\Expert::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'specialization' => 'General Agriculture',
                    ]
                ),
                'financial' => \App\Models\Financial::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'institution_name' => $user->name,
                    ]
                ),
                'cooperative' => \App\Models\Cooperative::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'cooperative_name' => $user->name,
                        'region' => $user->region ?? 'Not specified',
                        'location' => $user->location ?? 'Not specified',
                    ]
                ),
                'admin' => null,
                default => null,
            };
        } catch (QueryException $e) {
            Log::warning('Failed to create role profile for user: ' . $user->id, [
                'role' => $role,
                'error' => $e->getMessage(),
            ]);
            // Silently fail - profile can be created later
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

            // Select only necessary columns for faster query
            $user = User::select('id', 'name', 'email', 'phone', 'role', 'password', 'is_active', 'location', 'region')
                ->where('email', $request->email)
                ->first();

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

            // Update last_login_at asynchronously (non-blocking)
            try {
                $user->update(['last_login_at' => now()]);
            } catch (\Exception $e) {
                // Log error but don't fail login
                \Illuminate\Support\Facades\Log::warning('Could not update last_login_at: ' . $e->getMessage());
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => $user->only(['id', 'name', 'email', 'phone', 'role', 'location', 'region', 'is_active']),
                'token' => $token,
            ], 200);
        } catch (QueryException $e) {
            Log::error('Login database error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred during login. Please try again later.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred during login. Please try again later.',
            ], 500);
        }
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

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

    public function forgotPassword(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);

            // Select only email column for fast lookup
            $user = User::select('id', 'name', 'email')->where('email', $request->email)->first();

            // For security, always return success message (don't reveal if email exists)
            $response = [
                'message' => 'If an account exists with this email, a password reset link has been sent.'
            ];

            if ($user) {
                // Generate a password reset token
                $resetToken = Str::random(60);
                
                // Store the reset token in the password_resets table with a 60-minute expiration
                DB::table('password_resets')->updateOrInsert(
                    ['email' => $user->email],
                    [
                        'token' => Hash::make($resetToken),
                        'created_at' => now(),
                    ]
                );

                // Get the frontend URL from environment or use default
                $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
                
                // Create the reset link with token
                $resetLink = $frontendUrl . '/reset-password?token=' . $resetToken . '&email=' . urlencode($user->email);

                // Send email asynchronously (non-blocking) using queue
                Mail::queue(
                    new PasswordResetMail(
                        $user->name,
                        $user->email,
                        $resetToken,
                        $resetLink
                    )
                );

                Log::info('Password reset request processed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }

            // Return immediately (don't wait for email queue)
            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('Forgot password error: ' . $e->getMessage(), [
                'email' => $request->email ?? null,
            ]);

            // Return success anyway for security
            return response()->json([
                'message' => 'If an account exists with this email, a password reset link has been sent.'
            ], 200);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Invalid email address.',
                    'errors' => ['email' => ['No account found with this email.']]
                ], 422);
            }

            // Check if reset token exists and is valid (within 60 minutes)
            $passwordReset = DB::table('password_resets')
                ->where('email', $request->email)
                ->first();

            if (!$passwordReset) {
                return response()->json([
                    'message' => 'Invalid or expired password reset token.',
                    'errors' => ['token' => ['Password reset token has expired or is invalid.']]
                ], 422);
            }

            // Verify the token
            if (!Hash::check($request->token, $passwordReset->token)) {
                return response()->json([
                    'message' => 'Invalid password reset token.',
                    'errors' => ['token' => ['The password reset token is invalid.']]
                ], 422);
            }

            // Check if token has expired (60 minutes)
            $tokenExpiredAt = strtotime($passwordReset->created_at) + (60 * 60); // 60 minutes
            if (time() > $tokenExpiredAt) {
                // Delete expired token
                DB::table('password_resets')->where('email', $request->email)->delete();
                
                return response()->json([
                    'message' => 'Password reset token has expired.',
                    'errors' => ['token' => ['Password reset token has expired. Please request a new one.']]
                ], 422);
            }

            // Update password
            $user->update(['password' => Hash::make($request->password)]);

            // Delete the used token
            DB::table('password_resets')->where('email', $request->email)->delete();

            Log::info('Password reset successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'timestamp' => now(),
            ]);

            return response()->json([
                'message' => 'Password has been reset successfully. You can now log in with your new password.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Password reset validation error', [
                'email' => $request->email ?? null,
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage(), [
                'email' => $request->email ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'An error occurred while resetting your password. Please try again later.',
            ], 500);
        }
    }
}
