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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #333;
        }

        .greeting strong {
            color: #4CAF50;
        }

        .message {
            font-size: 15px;
            color: #666;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .action-section {
            text-align: center;
            margin: 40px 0;
        }

        .reset-button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 14px 40px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .reset-button:hover {
            background-color: #45a049;
        }

        .token-section {
            background-color: #f9f9f9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 30px 0;
            border-radius: 4px;
        }

        .token-section p {
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
        }

        .token {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            word-break: break-all;
            color: #333;
        }

        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            font-size: 13px;
            margin: 30px 0;
            line-height: 1.6;
        }

        .footer {
            background-color: #f5f5f5;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #e5e5e5;
        }

        .footer a {
            color: #4CAF50;
            text-decoration: none;
        }

        .divider {
            height: 1px;
            background-color: #e5e5e5;
            margin: 20px 0;
        }

        .info-box {
            background-color: #e8f5e9;
            border: 1px solid #4CAF50;
            padding: 15px;
            border-radius: 4px;
            font-size: 13px;
            color: #2e7d32;
            margin: 20px 0;
        }

        .info-box strong {
            display: block;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🌾 AgriTech Platform</h1>
            <p>Password Reset Request</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hello <strong>{{ $userName }}</strong>,
            </div>

            <div class="message">
                We received a request to reset the password for your AgriTech Platform account. 
                If you didn't make this request, you can safely ignore this email. Your account remains secure.
            </div>

            <!-- Action Section -->
            <div class="action-section">
                <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                    Click the button below to reset your password:
                </p>
                <a href="{{ $resetLink }}" class="reset-button">Reset Password</a>
            </div>

            <div style="text-align: center; font-size: 12px; color: #999; margin-top: 15px;">
                Or copy and paste this link in your browser:
            </div>
            <div style="word-break: break-all; text-align: center; font-size: 11px; color: #4CAF50; margin-top: 10px;">
                {{ $resetLink }}
            </div>

            <!-- Token Info -->
            <div class="token-section">
                <p><strong>Reset Token:</strong></p>
                <div class="token">{{ $resetToken }}</div>
                <p style="margin-top: 10px; font-size: 12px;">
                    You'll need this token when resetting your password if clicking the link doesn't work.
                </p>
            </div>

            <!-- Important Notice -->
            <div class="warning">
                <strong>⏱️ Important:</strong> This password reset link expires in 60 minutes. 
                If the link expires, you can request a new one by visiting the forgot password page.
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <strong>Need Help?</strong>
                If you didn't request this password reset or need assistance, 
                please contact our support team at support@agritech.com or reply to this email.
            </div>

            <div class="divider"></div>

            <div style="font-size: 13px; color: #666; line-height: 1.8;">
                <p><strong>Tips for account security:</strong></p>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li>Use a strong, unique password</li>
                    <li>Never share your password with anyone</li>
                    <li>Enable two-factor authentication if available</li>
                    <li>Log out from other devices after resetting your password</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                AgriTech Platform | 
                <a href="https://agritech.com/privacy">Privacy Policy</a> | 
                <a href="https://agritech.com/terms">Terms of Service</a>
            </p>
            <p style="margin-top: 10px;">
                © {{ date('Y') }} AgriTech Platform. All rights reserved.
            </p>
            <p style="margin-top: 8px; color: #ccc;">
                This is an automated message. Please do not reply directly to this email.
            </p>
        </div>
    </div>
</body>
</html>
