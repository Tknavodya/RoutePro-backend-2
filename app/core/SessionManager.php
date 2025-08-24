<?php

class SessionManager {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        try {
            $this->connection = new PDO("mysql:host=localhost;dbname=route_pro_db", "root", "newpassword");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Session Manager DB connection error: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Create a new session for user login
     */
    public function createSession($userId, $email, $role, $name = '') {
        try {
            // Generate session token
            $sessionToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Store session in database
            $sql = "INSERT INTO user_sessions (user_id, session_token, expires_at, created_at) 
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                    session_token = VALUES(session_token),
                    expires_at = VALUES(expires_at),
                    created_at = NOW()";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$userId, $sessionToken, $expiresAt]);
            
            // Set PHP session variables
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $role;
            $_SESSION['user_name'] = $name;
            $_SESSION['session_token'] = $sessionToken;
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            return [
                'success' => true,
                'session_token' => $sessionToken,
                'user_id' => $userId,
                'email' => $email,
                'role' => $role,
                'name' => $name,
                'expires_at' => $expiresAt
            ];
            
        } catch (Exception $e) {
            error_log("Session creation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create session'];
        }
    }
    
    /**
     * Validate current session
     */
    public function validateSession() {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            return ['valid' => false, 'message' => 'No active session'];
        }
        
        if (!isset($_SESSION['session_token'])) {
            return ['valid' => false, 'message' => 'No session token'];
        }
        
        try {
            // Check if session exists in database and is not expired
            $sql = "SELECT u.id, u.email, u.role, u.name, s.expires_at 
                    FROM user_sessions s 
                    JOIN users u ON s.user_id = u.id 
                    WHERE s.session_token = ? AND s.expires_at > NOW()";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$_SESSION['session_token']]);
            $sessionData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sessionData) {
                $this->destroySession();
                return ['valid' => false, 'message' => 'Session expired or invalid'];
            }
            
            // Update session variables if needed
            $_SESSION['user_id'] = $sessionData['id'];
            $_SESSION['user_email'] = $sessionData['email'];
            $_SESSION['user_role'] = $sessionData['role'];
            $_SESSION['user_name'] = $sessionData['name'];
            
            return [
                'valid' => true,
                'user_id' => $sessionData['id'],
                'email' => $sessionData['email'],
                'role' => $sessionData['role'],
                'name' => $sessionData['name']
            ];
            
        } catch (Exception $e) {
            error_log("Session validation error: " . $e->getMessage());
            return ['valid' => false, 'message' => 'Session validation failed'];
        }
    }
    
    /**
     * Destroy current session
     */
    public function destroySession() {
        try {
            // Remove from database if token exists
            if (isset($_SESSION['session_token'])) {
                $sql = "DELETE FROM user_sessions WHERE session_token = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->execute([$_SESSION['session_token']]);
            }
            
            // Clear PHP session
            session_unset();
            session_destroy();
            
            return ['success' => true, 'message' => 'Session destroyed'];
            
        } catch (Exception $e) {
            error_log("Session destruction error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to destroy session'];
        }
    }
    
    /**
     * Get current user info from session
     */
    public function getCurrentUser() {
        $validation = $this->validateSession();
        if (!$validation['valid']) {
            return null;
        }
        
        return [
            'user_id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'name' => $_SESSION['user_name']
        ];
    }
    
    /**
     * Check if user has required role
     */
    public function hasRole($requiredRoles) {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return false;
        }
        
        if (is_string($requiredRoles)) {
            $requiredRoles = [$requiredRoles];
        }
        
        return in_array($currentUser['role'], $requiredRoles);
    }
    
    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions() {
        try {
            $sql = "DELETE FROM user_sessions WHERE expires_at < NOW()";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Expired sessions cleaned up'];
            
        } catch (Exception $e) {
            error_log("Session cleanup error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to cleanup sessions'];
        }
    }
}
?>
