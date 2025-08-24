<?php
/**
 * Logout Controller - Uses new SessionManager for logout functionality
 */

// Enable CORS for credentials
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if ($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: http://localhost:3000');
    header('Access-Control-Allow-Credentials: true');
}
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../core/SessionManager.php';

try {
    $sessionManager = SessionManager::getInstance();
    
    // Destroy the current session
    $success = $sessionManager->destroySession();
    
    if ($success) {
        echo json_encode([
            "success" => true,
            "message" => "Logout successful"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No active session found"
        ]);
    }

} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error occurred during logout"
    ]);
}
?>
