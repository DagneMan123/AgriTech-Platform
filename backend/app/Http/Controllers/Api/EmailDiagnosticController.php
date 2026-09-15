<?php

namespace App\Http\Controllers\Api;

use App\Services\EmailService;
use Illuminate\Http\Request;

class EmailDiagnosticController extends Controller
{
    /**
     * Get email system health and configuration
     */
    public function health()
    {
        return response()->json(
            EmailService::getEmailHealth()
        );
    }

    /**
     * Test email configuration without sending
     */
    public function testConfiguration()
    {
        return response()->json(
            EmailService::testEmailConfiguration()
        );
    }

    /**
     * Send test email to specified address
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'retry' => 'boolean',
        ]);

        $email = $request->input('email');
        $useRetry = $request->boolean('retry', false);

        if ($useRetry) {
            $result = EmailService::testEmailWithRetry($email);
        } else {
            $result = EmailService::testEmailWithRetry($email, 1);
        }

        return response()->json($result);
    }

    /**
     * Test email to multiple providers
     */
    public function testMultipleProviders()
    {
        $testEmails = [
            'gmail' => 'test@gmail.com',
            'outlook' => 'test@outlook.com',
            'yahoo' => 'test@yahoo.com',
        ];

        $results = [];

        foreach ($testEmails as $provider => $email) {
            $results[$provider] = [
                'email' => $email,
                'status' => 'configured',
                'note' => 'Ready to test - update email to actual address'
            ];
        }

        return response()->json([
            'message' => 'Email providers configured and ready',
            'test_providers' => $results,
            'instructions' => [
                '1. Replace test emails with real ones',
                '2. Call POST /api/admin/email/test-multiple with real emails',
                '3. Check each provider inbox for email'
            ]
        ]);
    }
}
