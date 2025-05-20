<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset Code</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9fafb;
        }
        .header {
            background-color: #dbeafe;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .code-container {
            margin: 30px 0;
            text-align: center;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #1e3a8a;
            background-color: #f3f4f6;
            padding: 15px 25px;
            border-radius: 8px;
            display: inline-block;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Password Reset Code</h2>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>You've requested to reset your password. Use the verification code below to complete the process:</p>
            
            <div class="code-container">
                <div class="code">{{ $code }}</div>
            </div>
            
            <p>This code will expire in 15 minutes.</p>
            <p>If you didn't request this password reset, please ignore this email or contact support if you have concerns.</p>
            <p>Thank you,<br>Stock Manager Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Stock Manager. All rights reserved.
        </div>
    </div>
</body>
</html> 