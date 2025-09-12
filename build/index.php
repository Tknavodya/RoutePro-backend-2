<?php

// Clear any output buffer and start fresh
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Enable CORS for all localhost ports and development environments
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Always set specific origin when credentials are involved
if ($origin) {
    // Allow any localhost/127.0.0.1 port for development
    if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/i', $origin)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    } else {
        // For other origins, still allow but be more permissive for development
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    }
} else {
    // No origin header, set a more permissive default for development
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: false');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');

// Handle preflight OPTIONS request immediately
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include required files
require_once __DIR__ . '/../app/controllers/DriverController.php';
require_once __DIR__ . '/../app/controllers/GuideController.php';
require_once __DIR__ . '/../app/controllers/TravellerController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';

// Get the request URI and method
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Debug logging
error_log("=== ROUTING DEBUG ===");
error_log("Request URI: " . $requestUri);
error_log("Request Method: " . $requestMethod);

// Remove query string and decode URI
$path = parse_url($requestUri, PHP_URL_PATH);
$path = urldecode($path);

error_log("Parsed path: " . $path);

// Remove base path if running in subdirectory - handle multiple possible base paths
$possibleBasePaths = [
    '/RoutePro-backend(02)/public',
    '/Project%201%2022/frontend829/Backend/public',
    '/frontend829/Backend/public'
];

$originalPath = $path;
foreach ($possibleBasePaths as $basePath) {
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
        error_log("Removed base path: " . $basePath . " -> " . $path);
        break;
    }
}

error_log("Final path for routing: " . $path);
error_log("====================");

// If no base path found, path is already clean (running with php -S)

// Route the request
try {
    switch ($path) {
        // Authentication routes
        case '/auth/login':
            if ($requestMethod === 'POST') {
                require_once __DIR__ . '/../app/controllers/Login.php';
                include __DIR__ . '/../app/controllers/Login.php';
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/auth/register':
            if ($requestMethod === 'POST') {
                require_once __DIR__ . '/../app/controllers/AuthController.php';
                $controller = new AuthController();
                $controller->register();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/auth/logout':
            if ($requestMethod === 'POST') {
                require_once __DIR__ . '/../app/controllers/Logout.php';
                include __DIR__ . '/../app/controllers/Logout.php';
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case '/auth/forgot-password':
            if ($requestMethod === 'POST') {
                require_once __DIR__ . '/../app/controllers/ForgotPasswordController.php';
                $controller = new ForgotPasswordController();
                $controller->sendOTP();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case '/auth/send-otp':
            if ($requestMethod === 'POST') {
                require_once __DIR__ . '/../app/controllers/ForgotPasswordController.php';
                $controller = new ForgotPasswordController();
                $controller->sendOTP();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case '/auth/verify-otp':
            if ($requestMethod === 'POST') {
                require_once __DIR__ . '/../app/controllers/ForgotPasswordController.php';
                $controller = new ForgotPasswordController();
                $controller->verifyOTP();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case '/auth/resend-otp':
            if ($requestMethod === 'POST') {
                require_once __DIR__ . '/../app/controllers/ForgotPasswordController.php';
                $controller = new ForgotPasswordController();
                $controller->resendOTP();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        case '/auth/reset-password':
            if ($requestMethod === 'POST') {
                require_once __DIR__ . '/../app/controllers/ForgotPasswordController.php';
                $controller = new ForgotPasswordController();
                $controller->resetPassword();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        // Driver routes
        case '/driver/profile':
            if ($requestMethod === 'GET') {
                $controller = new DriverController();
                $controller->getProfile();
            } elseif ($requestMethod === 'PUT') {
                $controller = new DriverController();
                $controller->updateProfile();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/driver/status':
            if ($requestMethod === 'PUT') {
                $controller = new DriverController();
                $controller->updateStatus();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/driver/photo':
            if ($requestMethod === 'POST') {
                $controller = new DriverController();
                $controller->uploadPhoto();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/driver/location':
            if ($requestMethod === 'PUT') {
                $controller = new DriverController();
                $controller->updateLocation();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/drivers':
            if ($requestMethod === 'GET') {
                $controller = new DriverController();
                $controller->getAllDrivers();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/drivers/available':
            if ($requestMethod === 'GET') {
                $controller = new DriverController();
                $controller->getAvailableDrivers();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        // Guide routes
        case '/guide/profile':
            if ($requestMethod === 'GET') {
                $controller = new GuideController();
                $controller->getProfile();
            } elseif ($requestMethod === 'PUT') {
                $controller = new GuideController();
                $controller->updateProfile();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/guide/status':
            if ($requestMethod === 'PUT') {
                $controller = new GuideController();
                $controller->updateStatus();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/guide/photo':
            if ($requestMethod === 'POST') {
                $controller = new GuideController();
                $controller->uploadPhoto();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/guide/location':
            if ($requestMethod === 'PUT') {
                $controller = new GuideController();
                $controller->updateLocation();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/guides/available':
            if ($requestMethod === 'GET') {
                $controller = new GuideController();
                $controller->getAvailableGuides();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/guides/by-language':
            if ($requestMethod === 'POST') {
                $controller = new GuideController();
                $controller->getGuidesByLanguage();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/guides':
            if ($requestMethod === 'GET') {
                $controller = new GuideController();
                $controller->getAllGuides();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/guides/find-duplicates':
            if ($requestMethod === 'GET') {
                $controller = new GuideController();
                $controller->findDuplicates();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/guides/remove-duplicates':
            if ($requestMethod === 'POST') {
                $controller = new GuideController();
                $controller->removeDuplicates();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/guides/cleanup-duplicates':
            if ($requestMethod === 'POST') {
                $controller = new GuideController();
                $controller->cleanupDuplicates();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        // Traveller routes
        case '/traveller/profile':
            if ($requestMethod === 'GET') {
                $controller = new TravellerController();
                $controller->getProfile();
            } elseif ($requestMethod === 'PUT') {
                $controller = new TravellerController();
                $controller->updateProfile();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/traveller/photo':
            if ($requestMethod === 'POST') {
                $controller = new TravellerController();
                $controller->uploadPhoto();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/traveller/bookings':
            if ($requestMethod === 'GET') {
                $controller = new TravellerController();
                $controller->getBookingHistory();
            } elseif ($requestMethod === 'POST') {
                $controller = new TravellerController();
                $controller->createBooking();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/travellers/available':
            if ($requestMethod === 'GET') {
                $controller = new TravellerController();
                $controller->getAvailableTravellers();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        // Admin routes
        case '/admin/users':
            if ($requestMethod === 'GET') {
                $controller = new AdminController();
                $controller->getAllUsers();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/admin/users/by-role':
            if ($requestMethod === 'POST') {
                $controller = new AdminController();
                $controller->getUsersByRole();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/admin/users/delete':
            if ($requestMethod === 'DELETE') {
                $controller = new AdminController();
                $controller->deleteUser();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/admin/users/update-role':
            if ($requestMethod === 'PUT') {
                $controller = new AdminController();
                $controller->updateUserRole();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/admin/stats':
            if ($requestMethod === 'GET') {
                $controller = new AdminController();
                $controller->getSystemStats();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case '/admin/profile':
            if ($requestMethod === 'GET') {
                $controller = new AdminController();
                $controller->getProfile();
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;

        // Default route
        case '/':
        case '':
            echo json_encode([
                'success' => true,
                'message' => 'RoutePro API v1.0',
                'endpoints' => [
                    'Authentication' => [
                        'POST /auth/login' => 'User login',
                        'POST /auth/register' => 'User registration',
                        'POST /auth/logout' => 'User logout',
                        'GET /auth/profile' => 'Get user profile'
                    ],
                    'Driver' => [
                        'GET /driver/profile' => 'Get driver profile',
                        'PUT /driver/profile' => 'Update driver profile',
                        'PUT /driver/status' => 'Update driver status',
                        'PUT /driver/location' => 'Update driver location',
                        'GET /drivers' => 'Get all registered drivers',
                        'GET /drivers/available' => 'Get available drivers'
                    ],
                    'Guide' => [
                        'GET /guide/profile' => 'Get guide profile',
                        'PUT /guide/profile' => 'Update guide profile',
                        'PUT /guide/status' => 'Update guide status',
                        'PUT /guide/location' => 'Update guide location',
                        'GET /guides/available' => 'Get available guides',
                        'POST /guides/by-language' => 'Get guides by language'
                    ],
                    'Traveller' => [
                        'GET /traveller/profile' => 'Get traveller profile',
                        'PUT /traveller/profile' => 'Update traveller profile',
                        'GET /traveller/bookings' => 'Get booking history',
                        'POST /traveller/bookings' => 'Create new booking',
                        'GET /travellers/available' => 'Get available travellers'
                    ],
                    'Admin' => [
                        'GET /admin/users' => 'Get all users',
                        'POST /admin/users/by-role' => 'Get users by role',
                        'DELETE /admin/users/delete' => 'Delete user',
                        'PUT /admin/users/update-role' => 'Update user role',
                        'GET /admin/stats' => 'Get system statistics',
                        'GET /admin/profile' => 'Get admin profile'
                    ]
                ]
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    error_log("Router error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

// Autoload controllers/models
spl_autoload_register(function ($className) {
    if (file_exists("../app/controllers/$className.php")) {
        require_once "../app/controllers/$className.php";
    } elseif (file_exists("../app/models/$className.php")) {
        require_once "../app/models/$className.php";
    }
});
