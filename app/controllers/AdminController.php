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
require_once __DIR__ . '/../models/Admin.php';

class AdminController extends Controller
{
    private $connection;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->connection = new PDO("mysql:host=localhost;dbname=route_pro_db", "root", "");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            $this->sendResponse(['success' => false, 'message' => 'Database connection failed'], 500);
        }
    }

    public function getAllUsers()
    {
        $this->requireRole(['admin']);

        try {
            $admin = new Admin();
            $admin->setId($_SESSION['user_id']);

            $users = $admin->getAllUsers($this->connection);

            $this->sendResponse([
                'success' => true,
                'users' => $users
            ]);
        } catch (Exception $e) {
            error_log("All users fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch users'
            ], 500);
        }
    }

    public function getUsersByRole()
    {
        $this->requireRole(['admin']);

        try {
            $input = $this->getInput();

            if (!isset($input['role'])) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Role is required'
                ], 400);
                return;
            }

            $admin = new Admin();
            $admin->setId($_SESSION['user_id']);

            $users = $admin->getUsersByRole($this->connection, $input['role']);

            $this->sendResponse([
                'success' => true,
                'users' => $users
            ]);
        } catch (Exception $e) {
            error_log("Users by role fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch users by role'
            ], 500);
        }
    }

    public function deleteUser()
    {
        $this->requireRole(['admin']);

        try {
            $input = $this->getInput();

            if (!isset($input['user_id'])) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
                return;
            }

            $admin = new Admin();
            $admin->setId($_SESSION['user_id']);

            if ($admin->deleteUser($this->connection, $input['user_id'])) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
            } else {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Failed to delete user'
                ], 500);
            }
        } catch (Exception $e) {
            error_log("User deletion error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'User deletion failed'
            ], 500);
        }
    }

    public function updateUserRole()
    {
        $this->requireRole(['admin']);

        try {
            $input = $this->getInput();

            if (!isset($input['user_id']) || !isset($input['new_role'])) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'User ID and new role are required'
                ], 400);
                return;
            }

            $admin = new Admin();
            $admin->setId($_SESSION['user_id']);

            if ($admin->updateUserRole($this->connection, $input['user_id'], $input['new_role'])) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'User role updated successfully'
                ]);
            } else {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Failed to update user role'
                ], 500);
            }
        } catch (Exception $e) {
            error_log("User role update error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'User role update failed'
            ], 500);
        }
    }

    public function getSystemStats()
    {
        $this->requireRole(['admin']);

        try {
            $admin = new Admin();
            $admin->setId($_SESSION['user_id']);

            $stats = $admin->getSystemStats($this->connection);

            $this->sendResponse([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            error_log("System stats fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch system statistics'
            ], 500);
        }
    }

    public function getProfile()
    {
        $this->requireRole(['admin']);

        try {
            $admin = new Admin();
            $admin->setId($_SESSION['user_id']);

            $profileData = $admin->getProfileData($this->connection);

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
            error_log("Admin profile fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch profile'
            ], 500);
        }
    }
}
