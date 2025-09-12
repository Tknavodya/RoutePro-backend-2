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

class DriverController extends Controller
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

    public function updateStatus()
    {
        try {
            $input = $this->getInput();

            if (!isset($input['status'])) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Status is required'
                ], 400);
                return;
            }

            // Check if email is provided for email-based update
            $email = $input['email'] ?? null;

            if ($email) {
                // Email-based update - using same approach as getProfile
                // First, get the user_id for this email
                $getUserSql = "SELECT u.id as user_id FROM users u WHERE u.email = ? AND u.role = 'driver'";
                $getUserStmt = $this->connection->prepare($getUserSql);
                $getUserStmt->execute([$email]);
                $userData = $getUserStmt->fetch(PDO::FETCH_ASSOC);

                if (!$userData) {
                    $this->sendResponse([
                        'success' => false,
                        'message' => 'Driver not found with email: ' . $email
                    ], 404);
                    return;
                }

                // Now update the drivers table
                $updateSql = "UPDATE drivers SET status = ? WHERE user_id = ?";
                $updateStmt = $this->connection->prepare($updateSql);
                $success = $updateStmt->execute([$input['status'], $userData['user_id']]);

                error_log("Status update attempt - Email: $email, User ID: {$userData['user_id']}, Status: {$input['status']}, Success: " . ($success ? 'true' : 'false') . ", Rows affected: " . $updateStmt->rowCount());

                if ($success && $updateStmt->rowCount() > 0) {
                    $this->sendResponse([
                        'success' => true,
                        'message' => 'Status updated successfully'
                    ]);
                } else {
                    $this->sendResponse([
                        'success' => false,
                        'message' => 'Status update failed. Rows affected: ' . $updateStmt->rowCount()
                    ], 500);
                }
            } else {
                // Session-based update (existing functionality)
                $this->requireRole(['driver', 'admin']);
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
            }
        } catch (Exception $e) {
            error_log("Status update error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Status update failed'
            ], 500);
        }
    }

    public function updateLocation()
    {
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

    public function getAvailableDrivers()
    {
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

    public function getAllDrivers()
    {
        try {
            $drivers = Driver::getAllDrivers($this->connection);

            $this->sendResponse([
                'success' => true,
                'drivers' => $drivers,
                'total' => count($drivers)
            ]);
        } catch (Exception $e) {
            error_log("All drivers fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch drivers'
            ], 500);
        }
    }

    public function getProfile()
    {
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
                               d.experience, d.status, d.location, d.photo
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
                               d.experience, d.status, d.location, d.photo
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
                'location' => $driverData['location'] ?: 'Not specified',
                'photo' => $driverData['photo'] ?: null
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

    public function updateProfile()
    {
        try {
            $input = $this->getInput();

            // Check if email is provided for email-based update
            $email = $input['email'] ?? null;

            if ($email) {
                // Email-based update
                // First, get the user_id for this email
                $getUserSql = "SELECT u.id as user_id FROM users u WHERE u.email = ? AND u.role = 'driver'";
                $getUserStmt = $this->connection->prepare($getUserSql);
                $getUserStmt->execute([$email]);
                $userData = $getUserStmt->fetch(PDO::FETCH_ASSOC);

                if (!$userData) {
                    $this->sendResponse([
                        'success' => false,
                        'message' => 'Driver not found with email: ' . $email
                    ], 404);
                    return;
                }

                $user_id = $userData['user_id'];

                // Validate required fields for email-based update
                $required = ['name', 'phone', 'vehicle_type', 'experience'];
                $missing = [];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || trim($input[$field]) === '') {
                        $missing[] = $field;
                    }
                }

                if (!empty($missing)) {
                    $this->sendResponse([
                        'success' => false,
                        'message' => 'Missing required fields: ' . implode(', ', $missing)
                    ], 400);
                    return;
                }

                // Update drivers table
                $sql = "UPDATE drivers SET 
                        name = ?, phone = ?, license_no = ?, 
                        vehicle_type = ?, experience = ?, location = ?
                        WHERE user_id = ?";

                $stmt = $this->connection->prepare($sql);
                $success = $stmt->execute([
                    $input['name'],
                    $input['phone'],
                    $input['license_no'] ?? '',
                    $input['vehicle_type'],
                    $input['experience'],
                    $input['location'] ?? '',
                    $user_id
                ]);

                if ($success) {
                    // Also update user table name
                    $user_sql = "UPDATE users SET name = ? WHERE id = ?";
                    $user_stmt = $this->connection->prepare($user_sql);
                    $user_stmt->execute([$input['name'], $user_id]);

                    error_log("Driver profile updated successfully for email: $email");

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
            } else {
                // Session-based update (existing functionality)
                $this->requireRole(['driver', 'admin']);

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
            }
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Profile update failed'
            ], 500);
        }
    }

    public function uploadPhoto()
    {
        try {
            // Check if email is provided
            $email = $_POST['email'] ?? null;

            if (!$email) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Email is required'
                ], 400);
                return;
            }

            // Check if file was uploaded
            if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'No photo uploaded or upload error'
                ], 400);
                return;
            }

            $uploadedFile = $_FILES['photo'];
            $fileSize = $uploadedFile['size'];
            $fileType = $uploadedFile['type'];
            $fileName = $uploadedFile['name'];

            // Validate file size (max 5MB)
            if ($fileSize > 5 * 1024 * 1024) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'File size too large. Maximum 5MB allowed.'
                ], 400);
                return;
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($fileType, $allowedTypes)) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.'
                ], 400);
                return;
            }

            // Get driver user_id from email
            $getUserSql = "SELECT u.id as user_id FROM users u WHERE u.email = ? AND u.role = 'driver'";
            $getUserStmt = $this->connection->prepare($getUserSql);
            $getUserStmt->execute([$email]);
            $userData = $getUserStmt->fetch(PDO::FETCH_ASSOC);

            if (!$userData) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Driver not found with email: ' . $email
                ], 404);
                return;
            }

            // Create unique filename
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueFileName = 'driver_' . $userData['user_id'] . '_' . time() . '.' . $fileExtension;

            // Set upload path
            $uploadDir = __DIR__ . '/../../public/uploads/drivers/';
            $uploadPath = $uploadDir . $uniqueFileName;

            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Move uploaded file
            if (move_uploaded_file($uploadedFile['tmp_name'], $uploadPath)) {
                // Update database with photo path
                $photoUrl = '/RoutePro-backend(02)/public/uploads/drivers/' . $uniqueFileName;

                $updateSql = "UPDATE drivers SET photo = ? WHERE user_id = ?";
                $updateStmt = $this->connection->prepare($updateSql);
                $success = $updateStmt->execute([$photoUrl, $userData['user_id']]);

                if ($success) {
                    $this->sendResponse([
                        'success' => true,
                        'message' => 'Photo uploaded successfully',
                        'data' => [
                            'photo_url' => 'http://localhost' . $photoUrl,
                            'file_name' => $uniqueFileName
                        ]
                    ]);
                } else {
                    // Delete uploaded file if database update fails
                    unlink($uploadPath);
                    $this->sendResponse([
                        'success' => false,
                        'message' => 'Failed to update database'
                    ], 500);
                }
            } else {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Failed to upload file'
                ], 500);
            }
        } catch (Exception $e) {
            error_log("Photo upload error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Photo upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
