<?php

namespace App\Services;

use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailService
{
    /**
     * Send password reset email to user
     * Works with both internal and external email providers
     *
     * @param string $userName
     * @param string $userEmail
     * @param string $resetToken
     * @param string $resetLink
     * @return array
     */
    public static function sendPasswordResetEmail(
        string $userName,
        string $userEmail,
        string $resetToken,
        string $resetLink
    ): array {
        try {
            // Validate email format
            if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Invalid email format', ['email' => $userEmail]);
                return [
                    'success' => false,
                    'message' => 'Invalid email address format',
                    'error_code' => 'INVALID_EMAIL'
                ];
            }

            Log::info('Sending password reset email', [
                'user_name' => $userName,
                'user_email' => $userEmail,
                'reset_link' => $resetLink,
            ]);

            // Attempt to send email
            Mail::send(
                new PasswordResetMail(
                    $userName,
                    $userEmail,
                    $resetToken,
                    $resetLink
                )
            );

            // Log successful send
            Log::info('Password reset email sent successfully', [
                'user_email' => $userEmail,
                'mailer' => config('mail.mailer'),
                'from_address' => config('mail.from.address'),
                'timestamp' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Password reset email sent successfully',
                'recipient' => $userEmail,
                'timestamp' => now(),
            ];
        } catch (Exception $e) {
            // Log detailed error
            Log::error('Failed to send password reset email', [
                'user_email' => $userEmail,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'mailer_config' => [
                    'mailer' => config('mail.mailer'),
                    'host' => config('mail.host'),
                    'port' => config('mail.port'),
                    'encryption' => config('mail.encryption'),
                ],
            ]);

            // Return error response
            return [
                'success' => false,
                'message' => 'Failed to send password reset email',
                'error' => $e->getMessage(),
                'error_code' => 'EMAIL_SEND_FAILED',
                'timestamp' => now(),
            ];
        }
    }

    /**
     * Verify email connectivity
     * Tests if email system is properly configured
     *
     * @return array
     */
    public static function testEmailConfiguration(): array
    {
        $config = [
            'mailer' => config('mail.mailer'),
            'host' => config('mail.host'),
            'port' => config('mail.port'),
            'encryption' => config('mail.encryption'),
            'username' => config('mail.username'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'queue_enabled' => config('queue.default') !== 'sync',
        ];

        $issues = [];

        // Validate configuration
        if (!config('mail.mailer')) {
            $issues[] = 'Mail mailer not configured';
        }

        if (!config('mail.host')) {
            $issues[] = 'Mail host not configured';
        }

        if (!config('mail.port')) {
            $issues[] = 'Mail port not configured';
        }

        if (!config('mail.from.address')) {
            $issues[] = 'Mail from address not configured';
        }

        if (!config('mail.username')) {
            $issues[] = 'Mail username not configured';
        }

        // Check password is not empty
        $password = config('mail.password');
        if (empty($password)) {
            $issues[] = 'Mail password not configured';
        }

        return [
            'configured' => count($issues) === 0,
            'configuration' => $config,
            'issues' => $issues,
        ];
    }

    /**
     * Test email sending with retry logic
     * Attempts to send email with automatic retries
     *
     * @param string $testEmail
     * @param int $maxRetries
     * @return array
     */
    public static function testEmailWithRetry(string $testEmail, int $maxRetries = 3): array
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $maxRetries) {
            $attempt++;

            try {
                Log::info("Email test attempt {$attempt}/{$maxRetries}", ['email' => $testEmail]);

                $resetToken = \Illuminate\Support\Str::random(60);
                $resetLink = env('FRONTEND_URL', 'http://localhost:5173') . '/reset-password?token=' . $resetToken . '&email=' . urlencode($testEmail);

                Mail::send(
                    new PasswordResetMail(
                        'Test User',
                        $testEmail,
                        $resetToken,
                        $resetLink
                    )
                );

                Log::info("Email test succeeded on attempt {$attempt}");

                return [
                    'success' => true,
                    'message' => "Email sent successfully on attempt {$attempt}",
                    'recipient' => $testEmail,
                    'attempt' => $attempt,
                ];
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("Email test failed on attempt {$attempt}", [
                    'email' => $testEmail,
                    'error' => $lastError,
                ]);

                // Wait before retry (exponential backoff)
                if ($attempt < $maxRetries) {
                    $waitSeconds = pow(2, $attempt);
                    Log::info("Waiting {$waitSeconds} seconds before retry...");
                    sleep($waitSeconds);
                }
            }
        }

        Log::error("Email test failed after {$maxRetries} attempts", [
            'email' => $testEmail,
            'last_error' => $lastError,
        ]);

        return [
            'success' => false,
            'message' => "Failed to send email after {$maxRetries} attempts",
            'recipient' => $testEmail,
            'last_error' => $lastError,
            'attempts' => $maxRetries,
        ];
    }

    /**
     * Get email statistics and health check
     *
     * @return array
     */
    public static function getEmailHealth(): array
    {
        $config = self::testEmailConfiguration();

        return [
            'status' => $config['configured'] ? 'healthy' : 'unhealthy',
            'configuration' => $config['configuration'],
            'issues' => $config['issues'],
            'support_providers' => [
                'gmail' => 'smtp.gmail.com:587',
                'outlook' => 'smtp-mail.outlook.com:587',
                'yahoo' => 'smtp.mail.yahoo.com:587',
                'sendgrid' => 'smtp.sendgrid.net:587',
            ],
            'timestamp' => now(),
        ];
    }
}
