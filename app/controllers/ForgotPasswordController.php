<?php

// Add CORS headers at the beginning of the file
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if ($origin) {
    if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/i', $origin)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    }
} else {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: false');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/DbConnector.php';

class ForgotPasswordController extends Controller {
    private $connection;

    public function __construct() {
        try {
            $dbConnector = new DbConnector();
            $this->connection = $dbConnector->getConnection();
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            $this->sendResponse(['success' => false, 'message' => 'Database connection failed'], 500);
        }
    }

    public function sendOTP() {
        try {
            $input = $this->getInput();
            
            if (!isset($input['email']) || empty($input['email'])) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Email is required'
                ], 400);
                return;
            }

            $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Invalid email format'
                ], 400);
                return;
            }

            // Check if user exists
            $sql = "SELECT id, name FROM users WHERE email = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // Don't reveal if email exists or not for security
                $this->sendResponse([
                    'success' => true,
                    'message' => 'If an account with that email exists, an OTP has been sent.'
                ]);
                return;
            }

            // Generate 6-digit OTP
            $otp = sprintf("%06d", mt_rand(100000, 999999));
            $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            // Store OTP
            $this->storeOTP($user['id'], $email, $otp, $expires);

            // Send OTP email
            require_once __DIR__ . '/../services/EmailService.php';
            $emailService = new EmailService();
            $emailResult = $emailService->sendOTPEmail($email, $otp);

            if ($emailResult['success']) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'OTP has been sent to your email.',
                    'debug_otp' => $otp, // Remove this in production
                    'email_status' => 'sent'
                ]);
            } else {
                // Still respond success for security (don't reveal if email exists)
                // But log the error
                error_log("Failed to send OTP email to {$email}: " . $emailResult['message']);
                $this->sendResponse([
                    'success' => true,
                    'message' => 'If an account with that email exists, an OTP has been sent.',
                    'debug_otp' => $otp, // Remove this in production - keeping for development
                    'email_status' => 'failed',
                    'email_error' => $emailResult['message']
                ]);
            }

        } catch (Exception $e) {
            error_log("Send OTP error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'An error occurred while sending OTP'
            ], 500);
        }
    }

    public function verifyOTP() {
        try {
            $input = $this->getInput();
            
            if (!isset($input['email']) || !isset($input['otp'])) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Email and OTP are required'
                ], 400);
                return;
            }

            $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
            $otp = $input['otp'];

            if (!$email || strlen($otp) !== 6 || !ctype_digit($otp)) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Invalid email or OTP format'
                ], 400);
                return;
            }

            // Verify OTP
            $currentTime = date('Y-m-d H:i:s');
            $sql = "SELECT user_id FROM password_resets WHERE email = ? AND otp = ? AND expires_at > ? AND used = 0";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$email, $otp, $currentTime]);
            $otpRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$otpRecord) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Invalid or expired OTP'
                ], 400);
                return;
            }

            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            // Update the record with token and mark OTP as used
            $sql = "UPDATE password_resets SET token = ?, expires_at = ?, otp_verified = 1 WHERE email = ? AND otp = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$token, $expires, $email, $otp]);

            $this->sendResponse([
                'success' => true,
                'message' => 'OTP verified successfully',
                'token' => $token
            ]);

        } catch (Exception $e) {
            error_log("Verify OTP error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'An error occurred while verifying OTP'
            ], 500);
        }
    }

    public function resendOTP() {
        try {
            $input = $this->getInput();
            
            if (!isset($input['email']) || empty($input['email'])) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Email is required'
                ], 400);
                return;
            }

            $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Invalid email format'
                ], 400);
                return;
            }

            // Check if user exists
            $sql = "SELECT id, name FROM users WHERE email = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'If an account with that email exists, a new OTP has been sent.'
                ]);
                return;
            }

            // Generate new 6-digit OTP
            $otp = sprintf("%06d", mt_rand(100000, 999999));
            $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            // Invalidate previous OTPs and store new one
            $this->storeOTP($user['id'], $email, $otp, $expires);

            // In production, send actual email here
            error_log("New OTP for {$email}: {$otp}");

            $this->sendResponse([
                'success' => true,
                'message' => 'New OTP has been sent to your email.',
                'debug_otp' => $otp // Remove this in production
            ]);

        } catch (Exception $e) {
            error_log("Resend OTP error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'An error occurred while resending OTP'
            ], 500);
        }
    }

    public function resetPassword() {
        try {
            $input = $this->getInput();
            
            if (!isset($input['token']) || !isset($input['password']) || !isset($input['confirmPassword'])) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Token, password, and confirm password are required'
                ], 400);
                return;
            }

            if ($input['password'] !== $input['confirmPassword']) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Passwords do not match'
                ], 400);
                return;
            }

            // Validate password strength
            if (strlen($input['password']) < 8) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Password must be at least 8 characters long'
                ], 400);
                return;
            }

            // Verify reset token
            $currentTime = date('Y-m-d H:i:s');
            $sql = "SELECT user_id FROM password_resets WHERE token = ? AND expires_at > ? AND used = 0";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$input['token'], $currentTime]);
            $resetRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$resetRecord) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Invalid or expired reset token'
                ], 400);
                return;
            }

            // Update user password
            $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$hashedPassword, $resetRecord['user_id']]);

            // Mark token as used
            $sql = "UPDATE password_resets SET used = 1 WHERE token = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$input['token']]);

            $this->sendResponse([
                'success' => true,
                'message' => 'Password has been reset successfully'
            ]);

        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'An error occurred while resetting your password'
            ], 500);
        }
    }

    private function storeResetToken($userId, $token, $expires) {
        // First, invalidate any existing tokens for this user
        $sql = "UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$userId]);

        // Insert new token
        $sql = "INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$userId, $token, $expires]);
    }

    private function storeOTP($userId, $email, $otp, $expires) {
        try {
            // Invalidate any existing OTPs for this email
            $sql = "UPDATE password_resets SET used = 1 WHERE email = ? AND used = 0";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$email]);

            // Insert new OTP
            $sql = "INSERT INTO password_resets (user_id, email, otp, expires_at, created_at, used, otp_verified) VALUES (?, ?, ?, ?, NOW(), 0, 0)";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$userId, $email, $otp, $expires]);
        } catch (Exception $e) {
            throw new Exception("Failed to store OTP: " . $e->getMessage());
        }
    }
}
?>
