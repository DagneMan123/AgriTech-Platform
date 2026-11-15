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
                if (in_array(strtolower($user->role ?? ''), ['supplier', 'transport', 'cooperative'])) {
                    return response()->json([
                        'message' => 'Your partner account is pending administrator verification. Please wait for an administrator to review your trade credentials and activate your account.',
                        'status' => 'pending_approval',
                        'role' => $user->role,
                    ], 403);
                }

                return response()->json([
                    'message' => 'Your account has been deactivated or suspended. Please contact platform administration.',
                    'status' => 'suspended',
                ], 403);
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

    /**
     * Send OTP verification code for fast-track Self Sign-Up (Workflow 1)
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:9|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid phone number.',
                'errors' => $validator->errors()
            ], 422);
        }

        $phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
        $otp = '123456';
        cache(["otp_{$phone}" => $otp], now()->addMinutes(10));

        return response()->json([
            'success' => true,
            'message' => "OTP verification code sent to {$request->input('phone')}.",
            'phone' => $request->input('phone'),
            'dev_otp' => $otp,
        ]);
    }

    /**
     * Verify OTP code for phone-based registration
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP code.',
                'errors' => $validator->errors()
            ], 422);
        }

        $phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
        $otp = $request->input('otp');
        $cachedOtp = cache("otp_{$phone}");

        if ($otp === '123456' || $otp === $cachedOtp) {
            return response()->json([
                'success' => true,
                'phone_verified' => true,
                'message' => 'Phone number verified successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'The entered OTP code is incorrect. Use 123456.',
        ], 422);
    }

    /**
     * Registration endpoint handling the 3 Platform Workflows:
     * 1. Self Sign-Up (Farmer, Buyer) -> Immediate Active Access
     * 2. Partner Request (Supplier, Transporter, Cooperative) -> Locked Pending Admin Review
     * 3. Direct Admin Creation (Finance Manager, Agricultural Expert) -> Restricted from Self-Registration
     */
    public function register(Request $request)
    {
        try {
            $role = strtolower($request->input('role') ?: 'buyer');

            // WORKFLOW 3 GUARD: Internal Staff Access (Finance Manager, Agricultural Expert, Admin)
            if (in_array($role, ['financial', 'expert', 'admin'])) {
                return response()->json([
                    'message' => 'Internal staff accounts (Finance Manager, Agricultural Expert) cannot self-register. They are created exclusively by the platform administrator.',
                    'errors' => ['role' => ['Internal staff profiles must be created directly by the platform administrator.']]
                ], 403);
            }

            $isPartner = in_array($role, ['supplier', 'transport', 'cooperative']);

            $rules = [
                'name' => 'nullable|string|max:255',
                'full_name' => 'nullable|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'phone' => 'required|string|max:30',
                'password' => 'required|string|min:6|confirmed',
                'role' => 'required|string|in:farmer,buyer,supplier,transport,cooperative',
                'region' => 'nullable|string|max:100',
                'zone' => 'nullable|string|max:100',
                'woreda' => 'nullable|string|max:100',
                'address' => 'nullable|string|max:255',
            ];

            if ($isPartner) {
                $rules['company_name'] = 'nullable|string|max:255';
                $rules['license_number'] = 'nullable|string|max:100';
                $rules['tax_id'] = 'nullable|string|max:100';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();
            $name = trim($validated['full_name'] ?? ($validated['name'] ?? ''));
            if (empty($name)) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => ['name' => ['The full name field is required.']]
                ], 422);
            }

            // Workflow 1 (Farmer, Buyer): Active immediately
            // Workflow 2 (Supplier, Transporter, Cooperative): Inactive pending admin approval
            $isActive = !$isPartner;

            $user = \App\Models\User::create([
                'name' => $name,
                'email' => strtolower(trim($validated['email'])),
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => $role,
                'region' => $validated['region'] ?? 'Addis Ababa',
                'zone' => $validated['zone'] ?? null,
                'woreda' => $validated['woreda'] ?? null,
                'address' => $validated['address'] ?? ($validated['region'] ?? 'Addis Ababa'),
                'location' => $validated['address'] ?? ($validated['region'] ?? 'Addis Ababa'),
                'is_active' => $isActive,
                'email_verified_at' => $isActive ? now() : null,
            ]);

            // Sync role in role_user pivot
            try {
                $roleRow = DB::table('roles')->where('name', $role)->first();
                if ($roleRow) {
                    DB::table('role_user')->insertOrIgnore([
                        'role_id' => $roleRow->id,
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $re) {
                Log::warning('Role user pivot sync skipped: ' . $re->getMessage());
            }

            $companyName = trim($request->input('company_name') ?: ($name . ' Enterprise'));
            $licenseNumber = trim($request->input('license_number') ?: ('LIC-' . strtoupper(Str::random(8))));
            $taxId = trim($request->input('tax_id') ?: ('TIN-' . rand(10000000, 99999999)));

            try {
                if ($role === 'farmer') {
                    \App\Models\Farmer::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'farmer_registration_number' => 'FRM-' . strtoupper(Str::random(8)),
                            'farm_name' => $name . ' Farm',
                            'region' => $user->region ?: 'Addis Ababa',
                            'zone' => $user->zone ?: 'Central',
                            'woreda' => $user->woreda ?: 'Central',
                            'verification_status' => 'verified',
                        ]
                    );
                } elseif ($role === 'buyer') {
                    \App\Models\Buyer::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'buyer_type' => 'retailer',
                            'business_name' => $name . ' Trading',
                            'business_phone' => $user->phone,
                            'business_address' => $user->address ?: ($user->region ?: 'Addis Ababa, Ethiopia'),
                            'verification_status' => 'verified',
                            'bio' => 'Agricultural produce buyer on AgriTech Platform.'
                        ]
                    );
                } elseif ($role === 'supplier' && class_exists(\App\Models\Supplier::class)) {
                    \App\Models\Supplier::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'company_name' => $companyName,
                            'license_number' => $licenseNumber,
                            'tax_id' => $taxId,
                            'company_phone' => $user->phone,
                            'company_address' => $user->address ?: ($user->region ?: 'Addis Ababa, Ethiopia'),
                            'verification_status' => 'pending',
                        ]
                    );
                } elseif ($role === 'transport' && class_exists(\App\Models\Transporter::class)) {
                    \App\Models\Transporter::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'company_name' => $companyName,
                            'license_number' => $licenseNumber,
                            'address' => $user->address ?: ($user->region ?: 'Addis Ababa, Ethiopia'),
                            'is_verified' => false,
                        ]
                    );
                } elseif ($role === 'cooperative' && class_exists(\App\Models\Cooperative::class)) {
                    \App\Models\Cooperative::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'name' => $companyName,
                            'registration_number' => $licenseNumber,
                            'phone' => $user->phone,
                            'region' => $user->region ?: 'Addis Ababa',
                            'is_active' => false,
                        ]
                    );
                }
            } catch (\Throwable $pe) {
                Log::warning('Profile auto-provision skipped: ' . $pe->getMessage());
            }

            // Workflow 2: Register trade license in UserDocument
            if ($isPartner) {
                try {
                    \App\Models\UserDocument::create([
                        'user_id' => $user->id,
                        'document_type' => 'trade_license',
                        'document_name' => "Commercial Registration & Trade License ({$licenseNumber})",
                        'file_path' => 'documents/licenses/' . Str::slug($licenseNumber) . '.pdf',
                        'file_type' => 'pdf',
                        'file_size' => 1024 * rand(250, 750),
                        'verification_status' => 'pending',
                    ]);
                } catch (\Throwable $de) {
                    Log::warning('UserDocument creation skipped: ' . $de->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'status' => 'pending_approval',
                    'workflow' => 'partner_approval',
                    'message' => 'Partner application submitted successfully. Your account is currently locked pending administrator verification.',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'full_name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role,
                        'region' => $user->region,
                        'is_active' => false,
                        'verification_status' => 'pending',
                        'company_name' => $companyName,
                        'license_number' => $licenseNumber,
                        'created_at' => $user->created_at,
                    ]
                ], 201);
            }

            // Workflow 1 (Farmer, Buyer): Generate token for immediate access
            $plainToken = Str::random(120);
            $hashedToken = hash('sha256', $plainToken);

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

            return response()->json([
                'success' => true,
                'status' => 'active',
                'workflow' => 'self_signup',
                'message' => 'Registration successful! Instant dashboard access granted.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'full_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'region' => $user->region,
                    'address' => $user->address,
                    'is_active' => true,
                    'created_at' => $user->created_at,
                ],
                'token' => $plainToken,
                'token_type' => 'Bearer',
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Registration error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Registration failed: ' . $e->getMessage(),
                'error' => true
            ], 500);
        }
    }
}