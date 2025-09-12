<?php

// Add CORS headers at the beginning of the file
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if ($origin) {
    if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/i', $origin)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    }
} else {
    header('Access-Control-Allow-Origin: http://localhost:3000');
    header('Access-Control-Allow-Credentials: true');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Traveller.php';

class TravellerController extends Controller
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
        $this->requireRole(['traveller', 'admin']);

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
            $traveller = new Traveller();
            $traveller->setId($currentUser['user_id']);

            if ($traveller->updateStatus($this->connection, $input['status'])) {
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

    public function updateLocation()
    {
        $this->requireRole(['traveller', 'admin']);

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
            $traveller = new Traveller();
            $traveller->setId($currentUser['user_id']);

            if ($traveller->updateLocation($this->connection, $input['location'])) {
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

    public function getAvailableTravellers()
    {
        try {
            $travellers = Traveller::getAvailableTravellers($this->connection);

            $this->sendResponse([
                'success' => true,
                'travellers' => $travellers
            ]);
        } catch (Exception $e) {
            error_log("Available travellers fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch available travellers'
            ], 500);
        }
    }

    public function getProfile()
    {
        // Check if email parameter is provided for email-based lookup
        $email = $_GET['email'] ?? null;

        error_log("🔍 TravellerController::getProfile() called with email: " . ($email ?: 'none'));

        // Only require role authentication if no email is provided (session-based lookup)
        if (!$email) {
            $this->requireRole(['traveller', 'admin']);
        }

        try {
            if ($email) {
                error_log("📧 Email-based lookup for: " . $email);
                // Email-based lookup - handle the case where photo column might not exist
                try {
                    $sql = "SELECT u.id as user_id, u.name as user_name, u.email, 
                                   t.name, t.phone, t.created_at, t.photo
                            FROM users u 
                            JOIN travellers t ON u.id = t.user_id 
                            WHERE u.email = ? AND u.role = 'traveller'";

                    $stmt = $this->connection->prepare($sql);
                    $stmt->execute([$email]);
                    $travellerData = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Unknown column') !== false && strpos($e->getMessage(), 'photo') !== false) {
                        error_log("⚠️ Photo column doesn't exist, adding it...");

                        // Add photo column
                        $addColumnSql = "ALTER TABLE travellers ADD COLUMN photo VARCHAR(255) DEFAULT NULL";
                        $this->connection->exec($addColumnSql);

                        // Retry the query
                        $stmt = $this->connection->prepare($sql);
                        $stmt->execute([$email]);
                        $travellerData = $stmt->fetch(PDO::FETCH_ASSOC);
                    } else {
                        throw $e; // Re-throw if it's a different error
                    }
                }

                error_log("📊 Query result: " . json_encode($travellerData));
            } else {
                // Session-based lookup (existing functionality)
                $currentUser = $this->getCurrentUser();
                $userId = $currentUser['user_id'];

                try {
                    $sql = "SELECT u.name as user_name, u.email, 
                                   t.name, t.phone, t.created_at, t.photo
                            FROM users u 
                            JOIN travellers t ON u.id = t.user_id 
                            WHERE u.id = ?";

                    $stmt = $this->connection->prepare($sql);
                    $stmt->execute([$userId]);
                    $travellerData = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Unknown column') !== false && strpos($e->getMessage(), 'photo') !== false) {
                        error_log("⚠️ Photo column doesn't exist, adding it...");

                        // Add photo column
                        $addColumnSql = "ALTER TABLE travellers ADD COLUMN photo VARCHAR(255) DEFAULT NULL";
                        $this->connection->exec($addColumnSql);

                        // Retry the query
                        $stmt = $this->connection->prepare($sql);
                        $stmt->execute([$userId]);
                        $travellerData = $stmt->fetch(PDO::FETCH_ASSOC);
                    } else {
                        throw $e; // Re-throw if it's a different error
                    }
                }
            }

            if (!$travellerData) {
                $errorMessage = $email ? "No traveller found with email: $email" : "Traveller not found";
                error_log("❌ " . $errorMessage);
                $this->sendResponse([
                    'success' => false,
                    'message' => $errorMessage
                ], 404);
                return;
            }

            // Use traveller name if available, otherwise use user name
            $finalData = [
                'user_id' => isset($travellerData['user_id']) ? $travellerData['user_id'] : null,
                'name' => $travellerData['name'] ?: $travellerData['user_name'],
                'phone' => $travellerData['phone'] ?: 'Not provided',
                'email' => $travellerData['email'],
                'preferences' => 'No preferences set', // Default since column doesn't exist
                'status' => 'active', // Default since column doesn't exist
                'location' => 'Not specified', // Default since column doesn't exist
                'member_since' => $travellerData['created_at'] ?: 'Unknown',
                'photo' => isset($travellerData['photo']) ? $travellerData['photo'] : null
            ];

            error_log("✅ Final data being sent: " . json_encode($finalData));

            $this->sendResponse([
                'success' => true,
                'data' => $finalData
            ]);
        } catch (Exception $e) {
            error_log("Get traveller profile error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to get traveller profile'
            ], 500);
        }
    }

    public function updateProfile()
    {
        // Allow session-based updates or email-based updates
        $email = $_GET['email'] ?? null;

        // Only require role authentication if no email is provided (session-based lookup)
        if (!$email) {
            $this->requireRole(['traveller', 'admin']);
        }

        try {
            $input = $this->getInput();
            error_log("🔄 Update profile input: " . json_encode($input));

            // Validate required fields
            $required = ['name', 'phone'];
            $missing = $this->validateRequired($input, $required);

            if (!empty($missing)) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Missing required fields: ' . implode(', ', $missing)
                ], 400);
                return;
            }

            if ($email) {
                // Email-based update - first find the user_id
                $find_sql = "SELECT u.id as user_id FROM users u WHERE u.email = ? AND u.role = 'traveller'";
                $find_stmt = $this->connection->prepare($find_sql);
                $find_stmt->execute([$email]);
                $user_data = $find_stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user_data) {
                    $this->sendResponse([
                        'success' => false,
                        'message' => 'Traveller not found'
                    ], 404);
                    return;
                }

                $user_id = $user_data['user_id'];
            } else {
                // Session-based update
                $currentUser = $this->getCurrentUser();
                $user_id = $currentUser['user_id'];
            }

            // Update travellers table (only name and phone exist in actual schema)
            $sql = "UPDATE travellers SET name = ?, phone = ? WHERE user_id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(1, $input['name']);
            $stmt->bindValue(2, $input['phone']);
            $stmt->bindValue(3, $user_id);

            if ($stmt->execute()) {
                // Also update user table name
                $user_sql = "UPDATE users SET name = ? WHERE id = ?";
                $user_stmt = $this->connection->prepare($user_sql);
                $user_stmt->bindValue(1, $input['name']);
                $user_stmt->bindValue(2, $user_id);
                $user_stmt->execute();

                error_log("✅ Profile updated successfully for user_id: " . $user_id);

                $this->sendResponse([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]);
            } else {
                error_log("❌ Failed to update travellers table");
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Failed to update profile'
                ], 500);
            }
        } catch (Exception $e) {
            error_log("❌ Traveller profile update error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Profile update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBookingHistory()
    {
        $this->requireRole(['traveller', 'admin']);

        try {
            // Get traveller data first
            $currentUser = $this->getCurrentUser();
            $travellerData = Traveller::getByUserId($this->connection, $currentUser['user_id']);

            if (!$travellerData) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Traveller profile not found'
                ], 404);
                return;
            }

            $traveller = new Traveller();
            $traveller->setTravellerId($travellerData['id']);

            $bookings = $traveller->getBookingHistory($this->connection);

            $this->sendResponse([
                'success' => true,
                'bookings' => $bookings
            ]);
        } catch (Exception $e) {
            error_log("Booking history fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch booking history'
            ], 500);
        }
    }

    public function createBooking()
    {
        $this->requireRole(['traveller', 'admin']);

        try {
            $input = $this->getInput();

            if (!isset($input['route_id'])) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Route ID is required'
                ], 400);
                return;
            }

            // Get traveller data
            $currentUser = $this->getCurrentUser();
            $travellerData = Traveller::getByUserId($this->connection, $currentUser['user_id']);

            if (!$travellerData) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Traveller profile not found'
                ], 404);
                return;
            }

            $traveller = new Traveller();
            $traveller->setTravellerId($travellerData['id']);

            $success = $traveller->createBooking(
                $this->connection,
                $input['route_id'],
                $input['driver_id'] ?? null,
                $input['guide_id'] ?? null
            );

            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Booking created successfully'
                ]);
            } else {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Failed to create booking'
                ], 500);
            }
        } catch (Exception $e) {
            error_log("Booking creation error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Booking creation failed'
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

            // Get traveller user_id from email
            $getUserSql = "SELECT u.id as user_id FROM users u WHERE u.email = ? AND u.role = 'traveller'";
            $getUserStmt = $this->connection->prepare($getUserSql);
            $getUserStmt->execute([$email]);
            $userData = $getUserStmt->fetch(PDO::FETCH_ASSOC);

            if (!$userData) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Traveller not found with email: ' . $email
                ], 404);
                return;
            }

            // Create unique filename
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueFileName = 'traveller_' . $userData['user_id'] . '_' . time() . '.' . $fileExtension;

            // Set upload path
            $uploadDir = __DIR__ . '/../../public/uploads/travellers/';
            $uploadPath = $uploadDir . $uniqueFileName;

            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Move uploaded file
            if (move_uploaded_file($uploadedFile['tmp_name'], $uploadPath)) {
                // Update database with photo path - but first check if travellers table has photo column
                $photoUrl = '/RoutePro-backend(02)/public/uploads/travellers/' . $uniqueFileName;

                // Check if photo column exists in travellers table
                $checkColumnSql = "SHOW COLUMNS FROM travellers LIKE 'photo'";
                $checkStmt = $this->connection->prepare($checkColumnSql);
                $checkStmt->execute();
                $photoColumnExists = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if (!$photoColumnExists) {
                    // Add photo column to travellers table
                    $addColumnSql = "ALTER TABLE travellers ADD COLUMN photo VARCHAR(255) DEFAULT NULL";
                    $this->connection->exec($addColumnSql);
                    error_log("✅ Added photo column to travellers table");
                }

                $updateSql = "UPDATE travellers SET photo = ? WHERE user_id = ?";
                $updateStmt = $this->connection->prepare($updateSql);
                $success = $updateStmt->execute([$photoUrl, $userData['user_id']]);

                if ($success) {
                    error_log("✅ Photo uploaded and database updated for traveller: " . $email);
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
            error_log("❌ Traveller photo upload error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Photo upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
