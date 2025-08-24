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
require_once __DIR__ . '/../models/Driver.php';

class DriverController extends Controller {
    private $connection;

    public function __construct() {
        try {
            $this->connection = new PDO("mysql:host=localhost;dbname=route_pro_db", "root", "newpassword");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            $this->sendResponse(['success' => false, 'message' => 'Database connection failed'], 500);
        }
    }

    public function updateStatus() {
        $this->requireRole(['driver', 'admin']);
        
        try {
            $input = $this->getInput();
            
            if (!isset($input['status'])) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Status is required'
                ], 400);
                return;
            }

            $currentUser = $this->getCurrentUser();
            $driver = new Driver();
            $driver->setId($currentUser['user_id']);
            
            if ($driver->updateStatus($this->connection, $input['status'])) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Status updated successfully'
                ]);
            } else {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Failed to update status'
                ], 500);
            }

        } catch (Exception $e) {
            error_log("Status update error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Status update failed'
            ], 500);
        }
    }

    public function updateLocation() {
        $this->requireRole(['driver', 'admin']);
        
        try {
            $input = $this->getInput();
            
            if (!isset($input['location'])) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Location is required'
                ], 400);
                return;
            }

            $currentUser = $this->getCurrentUser();
            $driver = new Driver();
            $driver->setId($currentUser['user_id']);
            
            if ($driver->updateLocation($this->connection, $input['location'])) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Location updated successfully'
                ]);
            } else {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Failed to update location'
                ], 500);
            }

        } catch (Exception $e) {
            error_log("Location update error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Location update failed'
            ], 500);
        }
    }

    public function getAvailableDrivers() {
        try {
            $drivers = Driver::getAvailableDrivers($this->connection);
            
            $this->sendResponse([
                'success' => true,
                'drivers' => $drivers
            ]);

        } catch (Exception $e) {
            error_log("Available drivers fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch available drivers'
            ], 500);
        }
    }

    public function getProfile() {
        // Check if email parameter is provided for email-based lookup
        $email = $_GET['email'] ?? null;
        
        // Only require role authentication if no email is provided (session-based lookup)
        if (!$email) {
            $this->requireRole(['driver', 'admin']);
        }
        
        try {
            if ($email) {
                // Email-based lookup
                $sql = "SELECT u.id as user_id, u.name as user_name, u.email, 
                               d.name, d.phone, d.license_no, d.vehicle_type, 
                               d.experience, d.status, d.location
                        FROM users u 
                        JOIN drivers d ON u.id = d.user_id 
                        WHERE u.email = ? AND u.role = 'driver'";
                
                $stmt = $this->connection->prepare($sql);
                $stmt->execute([$email]);
                $driverData = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // Session-based lookup (existing functionality)
                $currentUser = $this->getCurrentUser();
                $userId = $currentUser['user_id'];
                
                $sql = "SELECT u.name as user_name, u.email, 
                               d.name, d.phone, d.license_no, d.vehicle_type, 
                               d.experience, d.status, d.location
                        FROM users u 
                        JOIN drivers d ON u.id = d.user_id 
                        WHERE u.id = ?";
                
                $stmt = $this->connection->prepare($sql);
                $stmt->execute([$userId]);
                $driverData = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$driverData) {
                $errorMessage = $email ? "No driver found with email: $email" : "Driver not found";
                $this->sendResponse([
                    'success' => false,
                    'message' => $errorMessage
                ], 404);
                return;
            }
            
            // Use driver name if available, otherwise use user name
            $finalData = [
                'name' => $driverData['name'] ?: $driverData['user_name'],
                'phone' => $driverData['phone'] ?: 'Not provided',
                'email' => $driverData['email'],
                'license_no' => $driverData['license_no'] ?: 'Not provided',
                'vehicle_type' => $driverData['vehicle_type'] ?: 'Not specified',
                'experience' => $driverData['experience'] ?: '0',
                'status' => $driverData['status'] ?: 'nonavailable',
                'location' => $driverData['location'] ?: 'Not specified'
            ];
            
            $this->sendResponse([
                'success' => true,
                'data' => $finalData
            ]);
            
        } catch (Exception $e) {
            error_log("Get driver profile error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to get driver profile'
            ], 500);
        }
    }

    public function updateProfile() {
        $this->requireRole(['driver', 'admin']);
        
        try {
            $input = $this->getInput();
            
            // Validate required fields
            $required = ['name', 'phone', 'license_no', 'vehicle_type', 'experience'];
            $missing = $this->validateRequired($input, $required);
            
            if (!empty($missing)) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Missing required fields: ' . implode(', ', $missing)
                ], 400);
                return;
            }

            $sql = "UPDATE drivers SET 
                    name = ?, phone = ?, license_no = ?, 
                    vehicle_type = ?, experience = ?, location = ?
                    WHERE user_id = ?";
            
            $currentUser = $this->getCurrentUser();
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(1, $input['name']);
            $stmt->bindValue(2, $input['phone']);
            $stmt->bindValue(3, $input['license_no']);
            $stmt->bindValue(4, $input['vehicle_type']);
            $stmt->bindValue(5, $input['experience']);
            $stmt->bindValue(6, $input['location'] ?? null);
            $stmt->bindValue(7, $currentUser['user_id']);
            
            if ($stmt->execute()) {
                // Also update user table
                $user_sql = "UPDATE users SET name = ? WHERE id = ?";
                $user_stmt = $this->connection->prepare($user_sql);
                $user_stmt->bindValue(1, $input['name']);
                $user_stmt->bindValue(2, $currentUser['user_id']);
                $user_stmt->execute();
                
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]);
            } else {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Failed to update profile'
                ], 500);
            }

        } catch (Exception $e) {
            error_log("Driver profile update error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Profile update failed'
            ], 500);
        }
    }
}