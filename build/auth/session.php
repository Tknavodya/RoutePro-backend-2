<?php
// Add CORS headers
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
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../app/core/SessionManager.php';

try {
    $sessionManager = SessionManager::getInstance();
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // Check current session status
        $currentUser = $sessionManager->getCurrentUser();
        
        if ($currentUser) {
            // Get additional profile data based on role
            $connection = new PDO("mysql:host=localhost;dbname=route_pro_db", "root", "newpassword");
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $profileData = null;
            
            if ($currentUser['role'] === 'driver') {
                $stmt = $connection->prepare("SELECT * FROM drivers WHERE email = ?");
                $stmt->execute([$currentUser['email']]);
                $profileData = $stmt->fetch(PDO::FETCH_ASSOC);
            } elseif ($currentUser['role'] === 'guide') {
                $stmt = $connection->prepare("SELECT * FROM guides WHERE email = ?");
                $stmt->execute([$currentUser['email']]);
                $profileData = $stmt->fetch(PDO::FETCH_ASSOC);
            } elseif ($currentUser['role'] === 'traveller') {
                $stmt = $connection->prepare("SELECT * FROM travellers WHERE email = ?");
                $stmt->execute([$currentUser['email']]);
                $profileData = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            echo json_encode([
                'success' => true,
                'logged_in' => true,
                'user' => [
                    'id' => $currentUser['user_id'],
                    'name' => $currentUser['name'],
                    'email' => $currentUser['email'],
                    'role' => $currentUser['role'],
                    'photo' => $profileData['photo'] ?? null
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'logged_in' => false,
                'user' => null
            ]);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        // Logout - destroy session
        $result = $sessionManager->destroySession();
        echo json_encode([
            'success' => $result['success'],
            'message' => $result['message']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Session API error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error'
    ]);
}
?>
