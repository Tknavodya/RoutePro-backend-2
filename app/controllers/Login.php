<?php
/**
 * Login Controller - Updated to work with new MVC inheritance architecture
 * Maintains backward compatibility while using the new User inheritance structure
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

// Include new models with inheritance
require_once __DIR__ . '/../models/Driver.php';
require_once __DIR__ . '/../models/Guide.php';
require_once __DIR__ . '/../models/Traveller.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../core/SessionManager.php';

// Get SessionManager instance
$sessionManager = SessionManager::getInstance();

try {
    // Get JSON input
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    // Validate input
    if (!isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Email and password are required."
        ]);
        exit;
    }

    $email = trim($data['email']);
    $password = $data['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Invalid email format."
        ]);
        exit;
    }

    // Create PDO connection
    try {
        $connection = new PDO("mysql:host=localhost;dbname=route_pro_db", "root", "pubz");
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        throw new Exception("Database connection failed");
    }

    // If role is provided, use it directly
    if (isset($data['role'])) {
        $user = createUserByRole($data['role'], null, $email, $password);
        if ($user) {
            $loggedInUser = $user->login($connection);
            if ($loggedInUser) {
                handleSuccessfulLogin($loggedInUser);
                exit;
            }
        }
    } else {
        // Try each user type if role not specified (for backward compatibility)
        $roles = ['driver', 'guide', 'traveller', 'admin'];
        
        foreach ($roles as $role) {
            $user = createUserByRole($role, null, $email, $password);
            if ($user) {
                $loggedInUser = $user->login($connection);
                if ($loggedInUser) {
                    handleSuccessfulLogin($loggedInUser);
                    exit;
                }
            }
        }
    }

    // If we reach here, login failed
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "error" => "Invalid email or password."
    ]);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error occurred. Please try again later."
    ]);
}

/**
 * Create user object based on role using inheritance
 */
function createUserByRole($role, $name = null, $email = null, $password = null) {
    switch ($role) {
        case 'driver':
            return new Driver($name, $email, $password);
        case 'guide':
            return new Guide($name, $email, $password);
        case 'traveller':
            return new Traveller($name, $email, $password);
        case 'admin':
            return new Admin($name, $email, $password);
        default:
            return null;
    }
}

/**
 * Handle successful login response
 */
function handleSuccessfulLogin($loggedInUser) {
    global $sessionManager;
    
    // Create session using SessionManager with all required parameters
    $sessionResult = $sessionManager->createSession(
        $loggedInUser->getId(), 
        $loggedInUser->getEmail(),
        $loggedInUser->getRole(),
        $loggedInUser->getName()
    );

    if ($sessionResult['success']) {
        // Return success response with token
        echo json_encode([
            "success" => true,
            "userId" => $loggedInUser->getId(),
            "role" => $loggedInUser->getRole(),
            "name" => $loggedInUser->getName(),
            "email" => $loggedInUser->getEmail(),
            "rating" => $loggedInUser->getRating(),
            "token" => $sessionResult['session_token'],
            "message" => "Login successful"
        ]);
    } else {
        // Session creation failed
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "error" => "Failed to create session: " . ($sessionResult['message'] ?? 'Unknown error')
        ]);
    }
}
?>