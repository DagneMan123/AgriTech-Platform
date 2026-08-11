<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get all system settings
     */
    public function index()
    {
        $settings = Setting::all()->mapWithKeys(fn ($setting) => [
            $setting->key => $setting->value,
        ]);

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Get setting by key
     */
    public function show(string $key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $setting,
        ]);
    }

    /**
     * Update or create setting
     */
    public function update(Request $request, string $key)
    {
        $validated = $request->validate([
            'value' => 'required',
        ]);

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $validated['value']]
        );

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully',
            'data' => $setting,
        ]);
    }

    /**
     * Update multiple settings
     */
    public function updateMultiple(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Get platform settings
     */
    public function platformSettings()
    {
        $settings = [
            'platform_name' => Setting::where('key', 'platform_name')->value('value') ?? 'AgriConnect',
            'platform_logo' => Setting::where('key', 'platform_logo')->value('value'),
            'currency' => Setting::where('key', 'currency')->value('value') ?? 'USD',
            'timezone' => Setting::where('key', 'timezone')->value('value') ?? 'UTC',
            'language' => Setting::where('key', 'language')->value('value') ?? 'en',
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Get payment settings
     */
    public function paymentSettings()
    {
        $settings = [
            'payment_gateway' => Setting::where('key', 'payment_gateway')->value('value'),
            'transaction_fee' => Setting::where('key', 'transaction_fee')->value('value') ?? 2.5,
            'minimum_transaction' => Setting::where('key', 'minimum_transaction')->value('value') ?? 1,
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Get email settings
     */
    public function emailSettings()
    {
        $settings = [
            'mail_driver' => Setting::where('key', 'mail_driver')->value('value'),
            'mail_from_address' => Setting::where('key', 'mail_from_address')->value('value'),
            'mail_from_name' => Setting::where('key', 'mail_from_name')->value('value'),
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
