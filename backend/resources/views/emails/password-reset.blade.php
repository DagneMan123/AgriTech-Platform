<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - AgriTech Platform</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            background-color: #f8f9fa;
            line-height: 1.6;
            color: #2c3e50;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2d5016 0%, #4CAF50 100%);
            padding: 50px 30px;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.95;
            font-weight: 500;
        }

        .content {
            padding: 45px 35px;
        }

        .greeting {
            font-size: 16px;
            margin-bottom: 24px;
            color: #2c3e50;
            font-weight: 500;
        }

        .greeting strong {
            color: #2d5016;
            font-weight: 600;
        }

        .message {
            font-size: 15px;
            color: #555;
            line-height: 1.8;
            margin-bottom: 32px;
        }

        .action-section {
            text-align: center;
            margin: 45px 0;
            padding: 30px 0;
            border-top: 1px solid #e8ecf1;
            border-bottom: 1px solid #e8ecf1;
        }

        .action-text {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #4CAF50 0%, #3d8b40 100%);
            color: white;
            padding: 16px 48px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
            border: none;
            cursor: pointer;
        }

        .reset-button:hover {
            background: linear-gradient(135deg, #45a049 0%, #388e3c 100%);
            box-shadow: 0 6px 16px rgba(76, 175, 80, 0.4);
            transform: translateY(-2px);
        }

        .copy-link {
            margin-top: 25px;
            padding: 20px;
            background-color: #f8f9fa;
            border: 1px dashed #d1d5db;
            border-radius: 6px;
        }

        .copy-link-label {
            font-size: 12px;
            color: #6b7280;
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .copy-link-value {
            word-break: break-all;
            font-size: 12px;
            color: #4CAF50;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            line-height: 1.5;
        }

        .expiry-notice {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.05) 0%, rgba(245, 158, 11, 0.05) 100%);
            border: 1px solid #fcd34d;
            border-radius: 6px;
            padding: 16px;
            margin: 30px 0;
        }

        .expiry-notice strong {
            color: #92400e;
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .expiry-notice p {
            font-size: 14px;
            color: #78350f;
            line-height: 1.6;
        }

        .security-info {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%);
            border: 1px solid #86efac;
            border-radius: 6px;
            padding: 18px;
            margin: 30px 0;
        }

        .security-info strong {
            display: block;
            margin-bottom: 12px;
            color: #15803d;
            font-weight: 600;
            font-size: 14px;
        }

        .security-tips {
            font-size: 13px;
            color: #166534;
            line-height: 1.8;
        }

        .security-tips ul {
            margin: 10px 0 0 20px;
            padding: 0;
        }

        .security-tips li {
            margin-bottom: 8px;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 30px 35px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e8ecf1;
        }

        .footer-links {
            margin-bottom: 15px;
        }

        .footer a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 500;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .footer-divider {
            display: inline-block;
            margin: 0 8px;
            color: #d1d5db;
        }

        .copyright {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
        }

        .automated-notice {
            margin-top: 10px;
            font-size: 11px;
            color: #9ca3af;
            font-style: italic;
        }

        @media (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }

            .header {
                padding: 40px 20px;
            }

            .footer {
                padding: 20px;
            }

            .reset-button {
                padding: 14px 36px;
                font-size: 15px;
            }

            .header h1 {
                font-size: 24px;
            }
        }

        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1a1a1a;
                color: #e0e0e0;
            }

            .container {
                background-color: #2a2a2a;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            }

            .content {
                color: #e0e0e0;
            }

            .greeting {
                color: #f0f0f0;
            }

            .message {
                color: #b0b0b0;
            }

            .copy-link {
                background-color: #3a3a3a;
                border-color: #4a4a4a;
            }

            .footer {
                background-color: #2a2a2a;
                border-color: #4a4a4a;
                color: #9a9a9a;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="header">
            <h1>🌾 AgriTech Platform</h1>
            <p>Password Reset Request</p>
        </div>


        <div class="content">
            <div class="greeting">
                Hello <strong>{{ $userName }}</strong>,
            </div>

            <div class="message">
                We received a password reset request for your AgriTech Platform account. If you did not request this, please ignore this email and your account will remain secure.
            </div>


            <div class="action-section">
                <p class="action-text">Click the button below to reset your password:</p>
                <a href="{{ $resetLink }}" class="reset-button">Reset Password</a>

                <div class="copy-link">
                    <span class="copy-link-label">Or copy this link:</span>
                    <div class="copy-link-value">{{ $resetLink }}</div>
                </div>
            </div>


            <div class="expiry-notice">
                <strong>⏱️ Link Expiration</strong>
                <p>This password reset link expires in <strong>60 minutes</strong>. If it expires, visit the "Forgot Password" page to request a new one.</p>
            </div>


            <div class="security-info">
                <strong>🔒 Security Recommendations</strong>
                <div class="security-tips">
                    <ul>
                        <li>Create a strong, unique password you haven't used before</li>
                        <li>Never share your password with anyone</li>
                        <li>Use a combination of uppercase, lowercase, numbers, and special characters</li>
                        <li>After resetting, log out from all other devices for security</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-links">
                <a href="{{ env('FRONTEND_URL', 'https://agritech.com') }}">AgriTech Platform</a>
                <span class="footer-divider">•</span>
                <a href="{{ env('FRONTEND_URL', 'https://agritech.com') }}/privacy">Privacy Policy</a>
                <span class="footer-divider">•</span>
                <a href="{{ env('FRONTEND_URL', 'https://agritech.com') }}/terms">Terms of Service</a>
            </div>
            <div class="copyright">
                <p>© {{ date('Y') }} AgriTech Platform. All rights reserved.</p>
            </div>
            <div class="automated-notice">
                Questions? Contact our support team. This is an automated email, please do not reply directly.
            </div>
        </div>
    </div>
</body>

</html>