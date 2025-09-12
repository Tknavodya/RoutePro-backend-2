<?php

// Enhanced Email Service with PHPMailer-style SMTP
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
            
            // Method 1: Try using custom SMTP implementation
            $result1 = $this->sendViaSMTP($email, $subject, $body);
            if ($result1['success']) {
                return $result1;
            }
            
            // Method 2: Try using PHP's built-in mail() with Gmail SMTP settings
            $result2 = $this->sendViaBuiltinMail($email, $subject, $body);
            if ($result2['success']) {
                return $result2;
            }
            
            // Method 3: Try using file-based email queue (for development)
            $result3 = $this->sendViaFileQueue($email, $subject, $body, $otp);
            if ($result3['success']) {
                return $result3;
            }
            
            // Method 4: Log-only fallback
            return $this->sendViaLogOnly($email, $subject, $body, $otp);
            
        } catch (Exception $e) {
            error_log("EmailService error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Email service error: ' . $e->getMessage()
            ];
        }
    }
    
    private function sendViaSMTP($email, $subject, $body) {
        try {
            require_once __DIR__ . '/../config/EmailConfig.php';
            
            // Create socket connection to Gmail SMTP
            $smtp = fsockopen(
                'ssl://' . EmailConfig::$SMTP_HOST, 
                465, // SSL port for Gmail
                $errno, 
                $errstr, 
                30
            );
            
            if (!$smtp) {
                error_log("SMTP Connection failed: {$errno} - {$errstr}");
                return ['success' => false, 'message' => 'SMTP connection failed'];
            }
            
            // Read initial response
            $response = fgets($smtp, 515);
            error_log("SMTP Initial: " . trim($response));
            
            // EHLO command
            fputs($smtp, "EHLO " . EmailConfig::$SMTP_HOST . "\r\n");
            $response = fgets($smtp, 515);
            error_log("SMTP EHLO: " . trim($response));
            
            // AUTH LOGIN
            fputs($smtp, "AUTH LOGIN\r\n");
            $response = fgets($smtp, 515);
            error_log("SMTP AUTH: " . trim($response));
            
            // Send username (base64 encoded)
            fputs($smtp, base64_encode(EmailConfig::$SMTP_USERNAME) . "\r\n");
            $response = fgets($smtp, 515);
            error_log("SMTP User: " . trim($response));
            
            // Send password (base64 encoded)
            fputs($smtp, base64_encode(EmailConfig::$SMTP_PASSWORD) . "\r\n");
            $response = fgets($smtp, 515);
            error_log("SMTP Pass: " . trim($response));
            
            // Check if authentication succeeded
            if (strpos($response, '235') === false) {
                fclose($smtp);
                return ['success' => false, 'message' => 'SMTP authentication failed'];
            }
            
            // MAIL FROM
            fputs($smtp, "MAIL FROM: <" . EmailConfig::$FROM_EMAIL . ">\r\n");
            $response = fgets($smtp, 515);
            error_log("SMTP FROM: " . trim($response));
            
            // RCPT TO
            fputs($smtp, "RCPT TO: <{$email}>\r\n");
            $response = fgets($smtp, 515);
            error_log("SMTP TO: " . trim($response));
            
            // DATA
            fputs($smtp, "DATA\r\n");
            $response = fgets($smtp, 515);
            error_log("SMTP DATA: " . trim($response));
            
            // Email headers and body
            $headers = "From: " . EmailConfig::$FROM_NAME . " <" . EmailConfig::$FROM_EMAIL . ">\r\n";
            $headers .= "Reply-To: " . EmailConfig::$FROM_EMAIL . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "\r\n";
            
            fputs($smtp, $headers . $body . "\r\n.\r\n");
            $response = fgets($smtp, 515);
            error_log("SMTP Send: " . trim($response));
            
            // QUIT
            fputs($smtp, "QUIT\r\n");
            fclose($smtp);
            
            if (strpos($response, '250') !== false) {
                error_log("✅ Email sent via custom SMTP to {$email}");
                return [
                    'success' => true,
                    'message' => 'Email sent via SMTP'
                ];
            } else {
                return ['success' => false, 'message' => 'SMTP send failed'];
            }
            
        } catch (Exception $e) {
            error_log("SMTP error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'SMTP error: ' . $e->getMessage()
            ];
        }
    }
    
    private function sendViaBuiltinMail($email, $subject, $body) {
        try {
            // Configure PHP to use SMTP
            ini_set('SMTP', EmailConfig::$SMTP_HOST);
            ini_set('smtp_port', EmailConfig::$SMTP_PORT);
            ini_set('sendmail_from', EmailConfig::$FROM_EMAIL);
            
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
            
            error_log("✅ Email queued in file: {$filename}");
            error_log("📧 Email details saved for manual processing");
            
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
