<?php

// Email configuration
class EmailConfig {
    // SMTP Configuration
    public static $SMTP_HOST = 'smtp.gmail.com';
    public static $SMTP_PORT = 587;
    public static $SMTP_USERNAME = 'pubuduharshana222@gmail.com'; // Your Gmail address
    public static $SMTP_PASSWORD = 'ltxs cbkc gcem hnlr'; // Your App Password (not regular password)
    public static $SMTP_ENCRYPTION = 'tls';
    
    // From email settings
    public static $FROM_EMAIL = 'pubuduharshana222@gmail.com'; // Your Gmail address
    public static $FROM_NAME = 'RoutePro - Travel Guide Platform';
    
    // OTP Email settings
    public static $OTP_SUBJECT = 'Your RoutePro Password Reset OTP';
    
    public static function getOTPEmailTemplate($otp, $email) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Password Reset OTP</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #84cc16, #eab308); padding: 30px; text-align: center; }
                .header h1 { color: white; margin: 0; font-size: 28px; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
                .content { padding: 40px; }
                .otp-box { background: linear-gradient(135deg, #22c55e, #84cc16); color: white; padding: 20px; border-radius: 10px; text-align: center; margin: 20px 0; }
                .otp-code { font-size: 36px; font-weight: bold; letter-spacing: 8px; margin: 10px 0; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; border-top: 1px solid #eee; }
                .warning { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 5px; padding: 15px; margin: 20px 0; color: #92400e; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🌟 RoutePro</h1>
                    <p style='color: white; margin: 10px 0 0 0; opacity: 0.9;'>Your Travel Guide Platform</p>
                </div>
                <div class='content'>
                    <h2 style='color: #333; margin-bottom: 20px;'>Password Reset Request</h2>
                    <p style='color: #555; line-height: 1.6;'>Hello,</p>
                    <p style='color: #555; line-height: 1.6;'>We received a request to reset your password for your RoutePro account. Use the OTP below to reset your password:</p>
                    
                    <div class='otp-box'>
                        <p style='margin: 0; font-size: 18px;'>Your OTP Code:</p>
                        <div class='otp-code'>{$otp}</div>
                        <p style='margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;'>Valid for 5 minutes</p>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Security Notice:</strong>
                        <ul style='margin: 10px 0; padding-left: 20px;'>
                            <li>Never share this OTP with anyone</li>
                            <li>RoutePro will never ask for your OTP via phone or email</li>
                            <li>This OTP expires in 5 minutes</li>
                        </ul>
                    </div>
                    
                    <p style='color: #555; line-height: 1.6;'>If you didn't request this password reset, please ignore this email. Your account remains secure.</p>
                </div>
                <div class='footer'>
                    <p>© 2025 RoutePro - Your trusted travel guide platform</p>
                    <p style='font-size: 12px; margin-top: 10px;'>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>
