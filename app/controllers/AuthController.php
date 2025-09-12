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
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../models/Driver.php';
require_once __DIR__ . '/../models/Guide.php';
require_once __DIR__ . '/../models/Traveller.php';
require_once __DIR__ . '/../models/Admin.php';

class AuthController extends Controller
{
    private $connection;

    public function __construct()
    {
        try {
            $this->connection = new PDO("mysql:host=localhost;dbname=route_pro_db", "root", "");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            $this->sendResponse(['success' => false, 'message' => 'Database connection failed'], 500);
        }
    }

    public function login()
    {
        try {
            $input = $this->getInput();

            // Old validation required role as well. Now only require email and password.
            if (!isset($input['email']) || !isset($input['password'])) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Email and password are required'
                ], 400);
                return;
            }

            $email = trim($input['email']);
            $password = $input['password'];

            // If role is provided, use it, else auto-detect by trying each role
            $loggedInUser = null;

            if (isset($input['role']) && !empty($input['role'])) {
                $user = $this->createUserByRole(
                    $input['role'],
                    null,
                    $email,
                    $password
                );

                if (!$user) {
                    $this->sendResponse([
                        'success' => false,
                        'message' => 'Invalid user role'
                    ], 400);
                    return;
                }

                $loggedInUser = $user->login($this->connection);
            } else {
                // Try each user type until one succeeds
                $roles = ['driver', 'guide', 'traveller', 'admin'];
                foreach ($roles as $role) {
                    $user = $this->createUserByRole($role, null, $email, $password);
                    if ($user) {
                        $candidate = $user->login($this->connection);
                        if ($candidate) {
                            $loggedInUser = $candidate;
                            break;
                        }
                    }
                }
            }

            if ($loggedInUser) {
                // Use SessionManager to create proper session
                $sessionManager = SessionManager::getInstance();
                $sessionResult = $sessionManager->createSession(
                    $loggedInUser->getId(),
                    $loggedInUser->getEmail(),
                    $loggedInUser->getRole(),
                    $loggedInUser->getName()
                );

                if ($sessionResult['success']) {
                    // Get profile data
                    $profileData = $loggedInUser->getProfileData($this->connection);

                    $this->sendResponse([
                        'success' => true,
                        'message' => 'Login successful',
                        // backward-compatible top-level fields
                        'userId' => $loggedInUser->getId(),
                        'role'   => $loggedInUser->getRole(),
                        'name'   => $loggedInUser->getName(),
                        'email'  => $loggedInUser->getEmail(),
                        'rating' => $loggedInUser->getRating(),
                        // nested user object
                        'user' => [
                            'id' => $loggedInUser->getId(),
                            'name' => $loggedInUser->getName(),
                            'email' => $loggedInUser->getEmail(),
                            'role' => $loggedInUser->getRole(),
                            'rating' => $loggedInUser->getRating(),
                            'profile' => $profileData
                        ],
                        'session' => [
                            'token' => $sessionResult['session_token'],
                            'expires_at' => $sessionResult['expires_at']
                        ]
                    ]);
                } else {
                    $this->sendResponse([
                        'success' => false,
                        'message' => 'Failed to create session'
                    ], 500);
                }
            } else {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ], 401);
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Login failed: Server error'
            ], 500);
        }
    }

    public function register()
    {
        try {
            // Clear any output buffer to ensure clean JSON response
            if (ob_get_level()) {
                ob_clean();
            }

            error_log("Registration request received");
            $input = $this->getInput();
            error_log("Input data: " . json_encode($input));

            if (!isset($input['role'])) {
                error_log("Missing role in input");
                $this->sendResponse([
                    'success' => false,
                    'message' => 'User role is required'
                ], 400);
                return;
            }

            $user = $this->createUserForRegistration($input);
            error_log("User object created: " . ($user ? "Success" : "Failed"));

            if (!$user) {
                error_log("Failed to create user object");
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Invalid user role or missing required fields'
                ], 400);
                return;
            }

            $result = $user->register($this->connection);

            if ($result['success']) {
                $this->sendResponse($result, 201);
            } else {
                $this->sendResponse($result, 400);
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->sendResponse([
                'success' => false,
                'message' => 'Registration failed: Server error'
            ], 500);
        }
    }

    public function logout()
    {
        $sessionManager = SessionManager::getInstance();
        $result = $sessionManager->destroySession();

        $this->sendResponse([
            'success' => $result['success'],
            'message' => $result['message']
        ]);
    }

    public function profile()
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            $this->sendResponse([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
            return;
        }

        try {
            $user = $this->createUserByRole($_SESSION['user_role']);
            $user->setId($_SESSION['user_id']);

            $profileData = $user->getProfileData($this->connection);

            if ($profileData) {
                $this->sendResponse([
                    'success' => true,
                    'profile' => $profileData
                ]);
            } else {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Profile not found'
                ], 404);
            }
        } catch (Exception $e) {
            error_log("Profile fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch profile'
            ], 500);
        }
    }

    private function createUserByRole($role, $name = null, $email = null, $password = null)
    {
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

    private function createUserForRegistration($input)
    {
        switch ($input['role']) {
            case 'driver':
                return new Driver(
                    $input['name'] ?? null,
                    $input['email'] ?? null,
                    $input['password'] ?? null,
                    $input['phone'] ?? null,
                    $input['license_no'] ?? null,
                    $input['vehicle_type'] ?? null,
                    $input['experience'] ?? null,
                    $input['location'] ?? null
                );

            case 'guide':
                return new Guide(
                    $input['name'] ?? null,
                    $input['email'] ?? null,
                    $input['password'] ?? null,
                    $input['phone'] ?? null,
                    $input['nic'] ?? null,
                    $input['license_no'] ?? null,
                    $input['experience'] ?? null,
                    $input['location'] ?? null,
                    $input['languages'] ?? null
                );

            case 'traveller':
                return new Traveller(
                    $input['name'] ?? null,
                    $input['email'] ?? null,
                    $input['password'] ?? null,
                    $input['phone'] ?? null
                );

            case 'admin':
                return new Admin(
                    $input['name'] ?? null,
                    $input['email'] ?? null,
                    $input['password'] ?? null,
                    $input['department'] ?? null,
                    $input['permissions'] ?? null
                );

            default:
                return null;
        }
    }
}
