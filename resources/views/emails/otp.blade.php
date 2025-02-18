<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            font-size: 24px;
            color: #2d3748;
            margin-bottom: 20px;
            text-align: center;
        }
        .otp-code {
            background-color: #f7fafc;
            border-radius: 4px;
            padding: 15px;
            font-size: 32px;
            text-align: center;
            letter-spacing: 5px;
            margin: 20px 0;
            color: #4a5568;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="header">OTP Verification</h1>

        <p>Hello {{ $user->name }},</p>

        <p>Your OTP verification code is:</p>

        <div class="otp-code">{{ $otp }}</div>

        <p>This code will expire in 10 minutes.</p>

        <p>If you didn't request this code, please ignore this email.</p>

        <div class="footer">
            Thanks,<br>
            {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
