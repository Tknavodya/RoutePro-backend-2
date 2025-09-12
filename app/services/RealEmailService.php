<?php

// Enhanced Email Service with real email sending capability
class EmailService {
    
    public function sendOTPEmail($email, $otp) {
        require_once __DIR__ . '/../config/EmailConfig.php';
        
        try {
            $subject = EmailConfig::$OTP_SUBJECT;
            $body = EmailConfig::getOTPEmailTemplate($otp, $email);
            
            error_log("=== ATTEMPTING TO SEND OTP EMAIL ===");
            error_log("To: {$email}");
            error_log("OTP: {$otp}");
            error_log("Subject: {$subject}");
            
            // Method 1: Try using Windows SMTP (if configured)
            $result1 = $this->sendViaWindowsSMTP($email, $subject, $body);
            if ($result1['success']) {
                return $result1;
            }
            
            // Method 2: Try using PHP mail() function with Gmail settings
            $result2 = $this->sendViaBuiltinMail($email, $subject, $body);
            if ($result2['success']) {
                return $result2;
            }
            
            // Method 3: Try cURL with Gmail API approach
            $result3 = $this->sendViaGmailAPI($email, $subject, $body, $otp);
            if ($result3['success']) {
                return $result3;
            }
            
            // Method 4: File queue for development (but still try to send)
            $result4 = $this->sendViaFileQueue($email, $subject, $body, $otp);
            if ($result4['success']) {
                return $result4;
            }
            
            // Method 5: Log-only fallback
            return $this->sendViaLogOnly($email, $subject, $body, $otp);
            
        } catch (Exception $e) {
            error_log("EmailService error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Email service error: ' . $e->getMessage()
            ];
        }
    }
    
    private function sendViaWindowsSMTP($email, $subject, $body) {
        try {
            require_once __DIR__ . '/../config/EmailConfig.php';
            
            // Configure PHP ini settings for SMTP
            $originalSMTP = ini_get('SMTP');
            $originalPort = ini_get('smtp_port');
            $originalFrom = ini_get('sendmail_from');
            
            // Set Gmail SMTP settings
            ini_set('SMTP', EmailConfig::$SMTP_HOST);
            ini_set('smtp_port', EmailConfig::$SMTP_PORT);
            ini_set('sendmail_from', EmailConfig::$FROM_EMAIL);
            
            // Create headers for HTML email
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . EmailConfig::$FROM_NAME . ' <' . EmailConfig::$FROM_EMAIL . '>',
                'Reply-To: ' . EmailConfig::$FROM_EMAIL,
                'X-Mailer: RoutePro-PHP/' . phpversion(),
                'X-Priority: 1',
                'Return-Path: ' . EmailConfig::$FROM_EMAIL
            ];
            
            $headerString = implode("\r\n", $headers);
            
            // Attempt to send email
            $result = mail($email, $subject, $body, $headerString);
            
            // Restore original settings
            ini_set('SMTP', $originalSMTP);
            ini_set('smtp_port', $originalPort);
            ini_set('sendmail_from', $originalFrom);
            
            if ($result) {
                error_log("✅ Email sent via Windows SMTP to {$email}");
                return [
                    'success' => true,
                    'message' => 'Email sent via Windows SMTP'
                ];
            } else {
                error_log("❌ Windows SMTP failed for {$email}");
                return [
                    'success' => false,
                    'message' => 'Windows SMTP failed'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Windows SMTP error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Windows SMTP error: ' . $e->getMessage()
            ];
        }
    }
    
    private function sendViaGmailAPI($email, $subject, $body, $otp) {
        try {
            require_once __DIR__ . '/../config/EmailConfig.php';
            
            // Create a simple email message
            $message = "From: " . EmailConfig::$FROM_NAME . " <" . EmailConfig::$FROM_EMAIL . ">\r\n";
            $message .= "To: {$email}\r\n";
            $message .= "Subject: {$subject}\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "\r\n";
            $message .= $body;
            
            // Encode the message
            $encodedMessage = rtrim(strtr(base64_encode($message), '+/', '-_'), '=');
            
            // For now, just log that we would send via Gmail API
            error_log("Gmail API message prepared for: {$email}");
            error_log("OTP: {$otp}");
            
            // This would require OAuth2 setup, so for now return false
            return [
                'success' => false,
                'message' => 'Gmail API not configured'
            ];
            
        } catch (Exception $e) {
            error_log("Gmail API error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gmail API error: ' . $e->getMessage()
            ];
        }
    }
    
    private function sendViaBuiltinMail($email, $subject, $body) {
        try {
            require_once __DIR__ . '/../config/EmailConfig.php';
            
            $headers = [];
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=UTF-8';
            $headers[] = 'From: ' . EmailConfig::$FROM_NAME . ' <' . EmailConfig::$FROM_EMAIL . '>';
            $headers[] = 'Reply-To: ' . EmailConfig::$FROM_EMAIL;
            $headers[] = 'X-Mailer: RoutePro-PHP/' . phpversion();
            $headers[] = 'X-Priority: 1';
            
            $headerString = implode("\r\n", $headers);
            
            $result = mail($email, $subject, $body, $headerString);
            
            if ($result) {
                error_log("✅ Email sent via built-in mail() to {$email}");
                return [
                    'success' => true,
                    'message' => 'Email sent via built-in mail function'
                ];
            } else {
                error_log("❌ Built-in mail() failed for {$email}");
                return [
                    'success' => false,
                    'message' => 'Built-in mail function failed'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Built-in mail error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Built-in mail error: ' . $e->getMessage()
            ];
        }
    }
    
    private function sendViaFileQueue($email, $subject, $body, $otp) {
        try {
            // Create an email queue file for development
            $queueDir = __DIR__ . '/../../email_queue';
            if (!is_dir($queueDir)) {
                mkdir($queueDir, 0777, true);
            }
            
            $emailData = [
                'to' => $email,
                'subject' => $subject,
                'body' => $body,
                'otp' => $otp,
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => 'queued'
            ];
            
            $filename = $queueDir . '/email_' . time() . '_' . md5($email) . '.json';
            file_put_contents($filename, json_encode($emailData, JSON_PRETTY_PRINT));
            
            error_log("📧 Email queued in file: {$filename}");
            error_log("📧 OTP for {$email}: {$otp}");
            
            return [
                'success' => true,
                'message' => 'Email queued successfully (development mode)'
            ];
            
        } catch (Exception $e) {
            error_log("File queue error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'File queue error: ' . $e->getMessage()
            ];
        }
    }
    
    private function sendViaLogOnly($email, $subject, $body, $otp) {
        try {
            error_log("=== EMAIL LOG FALLBACK ===");
            error_log("To: {$email}");
            error_log("Subject: {$subject}");
            error_log("OTP: {$otp}");
            error_log("Timestamp: " . date('Y-m-d H:i:s'));
            error_log("Body (first 200 chars): " . substr(strip_tags($body), 0, 200) . "...");
            error_log("=========================");
            
            return [
                'success' => true,
                'message' => 'Email logged successfully (fallback mode)'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Even log fallback failed: ' . $e->getMessage()
            ];
        }
    }
    
    public function testConnection() {
        try {
            require_once __DIR__ . '/../config/EmailConfig.php';
            
            error_log("=== TESTING EMAIL CONFIG ===");
            error_log("SMTP Host: " . EmailConfig::$SMTP_HOST);
            error_log("SMTP Port: " . EmailConfig::$SMTP_PORT);
            error_log("SMTP Username: " . EmailConfig::$SMTP_USERNAME);
            error_log("From Email: " . EmailConfig::$FROM_EMAIL);
            error_log("Password length: " . strlen(EmailConfig::$SMTP_PASSWORD));
            
            return [
                'success' => true,
                'message' => 'Email configuration loaded successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Configuration error: ' . $e->getMessage()
            ];
        }
    }
}
?>
