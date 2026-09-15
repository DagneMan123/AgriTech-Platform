<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use Illuminate\Console\Command;

class TestPasswordResetEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:password-reset-email {email} {--retry}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Test password reset email sending to any email address (internal or external)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $useRetry = $this->option('retry');

        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('Email Testing - AgriTech Platform');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->newLine();

        // Show email configuration
        $this->info('📧 Email Configuration:');
        $config = EmailService::testEmailConfiguration();
        
        if ($config['configured']) {
            $this->info('✅ Status: Properly Configured');
        } else {
            $this->error('❌ Status: Configuration Issues Found');
        }

        $this->info('   Mailer: ' . $config['configuration']['mailer']);
        $this->info('   Host: ' . $config['configuration']['host']);
        $this->info('   Port: ' . $config['configuration']['port']);
        $this->info('   Encryption: ' . $config['configuration']['encryption']);
        $this->info('   From: ' . $config['configuration']['from_address']);

        if (!empty($config['issues'])) {
            $this->newLine();
            $this->error('⚠️  Configuration Issues:');
            foreach ($config['issues'] as $issue) {
                $this->error('   - ' . $issue);
            }
            $this->newLine();
            return 1;
        }

        $this->newLine();
        $this->info('📨 Testing Email Delivery:');
        $this->info('   Recipient: ' . $email);
        
        if ($useRetry) {
            $this->info('   Retries: Enabled (will retry up to 3 times)');
        }

        $this->newLine();
        $this->line('Sending email...');
        $this->newLine();

        // Send test email with retry if requested
        if ($useRetry) {
            $result = EmailService::testEmailWithRetry($email, 3);
        } else {
            $result = EmailService::testEmailWithRetry($email, 1);
        }

        // Display result
        $this->newLine();
        if ($result['success']) {
            $this->info('═══════════════════════════════════════════════════════════════');
            $this->info('✅ EMAIL SENT SUCCESSFULLY');
            $this->info('═══════════════════════════════════════════════════════════════');
            $this->newLine();
            $this->info('📍 Recipient: ' . $result['recipient']);
            $this->info('⏱️  Sent at: ' . now());
            $this->info('🔄 Attempt: ' . $result['attempt']);
            $this->newLine();
            $this->info('📋 Next Steps:');
            $this->info('   1. Check inbox at ' . $result['recipient']);
            $this->info('   2. Look in Spam/Promotions if not in Inbox');
            $this->info('   3. Click the "Reset Password" button in the email');
            $this->info('   4. You should be redirected to reset form');
            $this->newLine();

            return 0;
        } else {
            $this->error('═══════════════════════════════════════════════════════════════');
            $this->error('❌ EMAIL SEND FAILED');
            $this->error('═══════════════════════════════════════════════════════════════');
            $this->newLine();
            $this->error('Error: ' . $result['last_error']);
            $this->error('Attempts: ' . $result['attempts']);
            $this->newLine();
            $this->info('🔧 Troubleshooting:');
            $this->info('   1. Verify Gmail account credentials in .env');
            $this->info('   2. Use Gmail App Password (not regular password)');
            $this->info('      Go to: https://myaccount.google.com/apppasswords');
            $this->info('   3. Enable Less Secure Apps');
            $this->info('      Go to: https://myaccount.google.com/lesssecureapps');
            $this->info('   4. Check port 587 is not blocked');
            $this->info('   5. Try with --retry flag for automatic retries');
            $this->newLine();
            $this->info('📋 Debug Command:');
            $this->info('   php artisan test:password-reset-email ' . $email . ' --retry');
            $this->newLine();

            return 1;
        }
    }
}

