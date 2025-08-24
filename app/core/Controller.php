<?php

require_once __DIR__ . '/SessionManager.php';

class Controller {
    
    public function model($modelName) {
        require_once __DIR__ . "/../models/$modelName.php";
        return new $modelName();
    }

    protected function getInput() {
        $input = file_get_contents("php://input");
        return json_decode($input, true);
    }

    protected function sendResponse($data, $statusCode = 200) {
        // Clear any output buffer to ensure clean JSON response
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Add CORS headers - more permissive for development
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        
        if ($origin) {
            // Allow any localhost or 127.0.0.1 with any port
            if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/i', $origin)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Credentials: true');
            } else {
                // For other origins, still allow but without credentials for security
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Credentials: false');
            }
        } else {
            // Default fallback
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Credentials: false');
        }
        
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");
        header("Content-Type: application/json; charset=utf-8");
        
        http_response_code($statusCode);
        
        $jsonResponse = json_encode($data);
        if ($jsonResponse === false) {
            // JSON encoding failed
            error_log("JSON encoding failed: " . json_last_error_msg());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        } else {
            echo $jsonResponse;
        }
        exit();
    }

    protected function validateRequired($data, $fields) {
        $missing = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        return $missing;
    }

    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    protected function isAuthenticated() {
        $sessionManager = SessionManager::getInstance();
        return $sessionManager->validateSession();
    }

    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->sendResponse([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }
    }

    protected function requireRole($allowedRoles) {
        $this->requireAuth();
        
        $sessionManager = SessionManager::getInstance();
        $currentUser = $sessionManager->getCurrentUser();
        
        if (!$currentUser || !in_array($currentUser['role'], $allowedRoles)) {
            $this->sendResponse([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403);
        }
    }

    protected function getCurrentUser() {
        $sessionManager = SessionManager::getInstance();
        return $sessionManager->getCurrentUser();
    }

    protected function createSession($userId, $role) {
        $sessionManager = SessionManager::getInstance();
        return $sessionManager->createSession($userId, $role);
    }

    protected function destroySession() {
        $sessionManager = SessionManager::getInstance();
        return $sessionManager->destroySession();
    }
}
