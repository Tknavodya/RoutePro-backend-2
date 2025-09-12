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
require_once __DIR__ . '/../models/Guide.php';

class GuideController extends Controller {
    private $connection;

    public function __construct() {
        try {
            $this->connection = new PDO("mysql:host=localhost;dbname=route_pro_db", "root", "pubz");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            $this->sendResponse(['success' => false, 'message' => 'Database connection failed'], 500);
        }
    }

    public function updateStatus() {
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
                // Email-based update
                // First, get the user_id for this email
                $getUserSql = "SELECT u.id as user_id FROM users u WHERE u.email = ? AND u.role = 'guide'";
                $getUserStmt = $this->connection->prepare($getUserSql);
                $getUserStmt->execute([$email]);
                $userData = $getUserStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$userData) {
                    $this->sendResponse([
                        'success' => false,
                        'message' => 'Guide not found with email: ' . $email
                    ], 404);
                    return;
                }
                
                // Now update the guides table
                $updateSql = "UPDATE guides SET status = ? WHERE user_id = ?";
                $updateStmt = $this->connection->prepare($updateSql);
                $success = $updateStmt->execute([$input['status'], $userData['user_id']]);
                
                error_log("Guide status update attempt - Email: $email, User ID: {$userData['user_id']}, Status: {$input['status']}, Success: " . ($success ? 'true' : 'false') . ", Rows affected: " . $updateStmt->rowCount());
                
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
                $this->requireRole(['guide', 'admin']);
                $currentUser = $this->getCurrentUser();
                $guide = new Guide();
                $guide->setId($currentUser['user_id']);
                
                if ($guide->updateStatus($this->connection, $input['status'])) {
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
            error_log("Guide status update error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Status update failed'
            ], 500);
        }
    }

    public function updateLocation() {
        $this->requireRole(['guide', 'admin']);
        
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
            $guide = new Guide();
            $guide->setId($currentUser['user_id']);
            
            if ($guide->updateLocation($this->connection, $input['location'])) {
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

    public function getAvailableGuides() {
        try {
            $guides = Guide::getAvailableGuides($this->connection);
            
            $this->sendResponse([
                'success' => true,
                'guides' => $guides
            ]);

        } catch (Exception $e) {
            error_log("Available guides fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch available guides'
            ], 500);
        }
    }

    public function getAllGuides() {
        try {
            $guides = Guide::getAllGuides($this->connection);
            
            $this->sendResponse([
                'success' => true,
                'guides' => $guides
            ]);

        } catch (Exception $e) {
            error_log("All guides fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch guides'
            ], 500);
        }
    }

    public function findDuplicates() {
        try {
            $duplicates = Guide::findDuplicateGuides($this->connection);
            
            $this->sendResponse([
                'success' => true,
                'duplicates' => $duplicates,
                'message' => 'Found ' . count($duplicates) . ' users with duplicate guide records'
            ]);

        } catch (Exception $e) {
            error_log("Find duplicates error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to find duplicates'
            ], 500);
        }
    }

    public function removeDuplicates() {
        try {
            $deletedCount = Guide::removeDuplicateGuides($this->connection);
            
            if ($deletedCount !== false) {
                $this->sendResponse([
                    'success' => true,
                    'deleted_count' => $deletedCount,
                    'message' => "Removed $deletedCount duplicate guide records"
                ]);
            } else {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Failed to remove duplicates'
                ], 500);
            }

        } catch (Exception $e) {
            error_log("Remove duplicates error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to remove duplicates'
            ], 500);
        }
    }

    public function cleanupDuplicates() {
        try {
            // First, let's see how many duplicates we have
            $findDuplicatesSql = "SELECT user_id, COUNT(*) as count FROM guides GROUP BY user_id HAVING COUNT(*) > 1";
            $findStmt = $this->connection->prepare($findDuplicatesSql);
            $findStmt->execute();
            $duplicates = $findStmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Found " . count($duplicates) . " users with duplicate guide records");
            
            if (count($duplicates) > 0) {
                // Remove duplicates, keeping the most recent one (highest ID)
                $cleanupSql = "DELETE g1 FROM guides g1 
                               INNER JOIN guides g2 
                               WHERE g1.user_id = g2.user_id 
                               AND g1.id < g2.id";
                $cleanupStmt = $this->connection->prepare($cleanupSql);
                $cleanupResult = $cleanupStmt->execute();
                $deletedRows = $cleanupStmt->rowCount();
                
                error_log("Deleted $deletedRows duplicate guide records");
                
                $this->sendResponse([
                    'success' => true,
                    'message' => "Cleaned up $deletedRows duplicate records",
                    'duplicates_found' => count($duplicates),
                    'records_deleted' => $deletedRows
                ]);
            } else {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'No duplicate records found',
                    'duplicates_found' => 0,
                    'records_deleted' => 0
                ]);
            }

        } catch (Exception $e) {
            error_log("Cleanup duplicates error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to cleanup duplicates: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getGuidesByLanguage() {
        try {
            $input = $this->getInput();
            
            if (!isset($input['language'])) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Language is required'
                ], 400);
                return;
            }

            $guides = Guide::getGuidesByLanguage($this->connection, $input['language']);
            
            $this->sendResponse([
                'success' => true,
                'guides' => $guides
            ]);

        } catch (Exception $e) {
            error_log("Guides by language fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch guides by language'
            ], 500);
        }
    }

    public function getProfile() {
        // Check if email parameter is provided for email-based lookup
        $email = $_GET['email'] ?? null;
        
        // Only require role authentication if no email is provided (session-based lookup)
        if (!$email) {
            $this->requireRole(['guide', 'admin']);
        }
        
        try {
            if ($email) {
                // Email-based lookup
                // First check if user exists
                $userSql = "SELECT u.id as user_id, u.name as user_name, u.email 
                           FROM users u 
                           WHERE u.email = ? AND u.role = 'guide'";
                
                $userStmt = $this->connection->prepare($userSql);
                $userStmt->execute([$email]);
                $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$userData) {
                    $this->sendResponse([
                        'success' => false,
                        'message' => "No guide found with email: $email"
                    ], 404);
                    return;
                }
                
                // Now try to get guide details
                $sql = "SELECT g.name, g.phone, g.nic, g.license_no, 
                               g.experience, g.status, g.location, g.languages, g.photo
                        FROM guides g 
                        WHERE g.user_id = ?";
                
                $stmt = $this->connection->prepare($sql);
                $stmt->execute([$userData['user_id']]);
                $guideData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // If no guide record exists, create default data
                if (!$guideData) {
                    error_log("No guide record found for user_id: " . $userData['user_id'] . ", creating default response");
                    $finalData = [
                        'name' => $userData['user_name'],
                        'phone' => 'Not provided',
                        'email' => $userData['email'],
                        'nic' => 'Not provided',
                        'license_no' => 'Not provided',
                        'experience' => '0',
                        'status' => 'nonavailable',
                        'location' => 'Not specified',
                        'languages' => 'Not specified',
                        'photo' => null
                    ];
                } else {
                    // Format data for response
                    $finalData = [
                        'name' => $guideData['name'] ?: $userData['user_name'],
                        'phone' => $guideData['phone'] ?: 'Not provided',
                        'email' => $userData['email'],
                        'nic' => $guideData['nic'] ?: 'Not provided',
                        'license_no' => $guideData['license_no'] ?: 'Not provided',
                        'experience' => $guideData['experience'] ?: '0',
                        'status' => $guideData['status'] ?: 'nonavailable',
                        'location' => $guideData['location'] ?: 'Not specified',
                        'languages' => $guideData['languages'] ?: 'Not specified',
                        'photo' => $guideData['photo'] ?: null
                    ];
                }
                
                $this->sendResponse([
                    'success' => true,
                    'data' => $finalData
                ]);
                
            } else {
                // Session-based lookup (existing functionality)
                $currentUser = $this->getCurrentUser();
                $guide = new Guide();
                $guide->setId($currentUser['user_id']);
                
                $profileData = $guide->getProfileData($this->connection);
                
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
            }

        } catch (Exception $e) {
            error_log("Guide profile fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch profile'
            ], 500);
        }
    }

    public function updateProfile() {
        try {
            $input = $this->getInput();
            
            // Check if email is provided for email-based update
            $email = $input['email'] ?? null;
            
            if ($email) {
                // Email-based update (similar to DriverController)
                error_log("Guide profile update via email: " . $email);
                
                // Validate required fields for email-based update
                $required = ['name'];
                $missing = [];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty(trim($input[$field]))) {
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

                // First, get the user_id for this email
                $getUserSql = "SELECT u.id as user_id, u.name as user_name FROM users u WHERE u.email = ? AND u.role = 'guide'";
                $getUserStmt = $this->connection->prepare($getUserSql);
                $getUserStmt->execute([$email]);
                $userData = $getUserStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$userData) {
                    $this->sendResponse([
                        'success' => false,
                        'message' => 'Guide not found with email: ' . $email
                    ], 404);
                    return;
                }

                error_log("Found user for email $email: user_id = {$userData['user_id']}, name = {$userData['user_name']}");

                // Check if guide record exists
                $checkGuideSql = "SELECT user_id FROM guides WHERE user_id = ?";
                $checkGuideStmt = $this->connection->prepare($checkGuideSql);
                $checkGuideStmt->execute([$userData['user_id']]);
                $guideExists = $checkGuideStmt->fetch(PDO::FETCH_ASSOC);

                if (!$guideExists) {
                    // Create new guide record
                    $insertSql = "INSERT INTO guides (user_id, name, phone, nic, license_no, experience, location, languages, status) 
                                  VALUES (?, ?, '', '', '', '0', '', '', 'available')";
                    $insertStmt = $this->connection->prepare($insertSql);
                    $insertResult = $insertStmt->execute([$userData['user_id'], $input['name'] ?? $userData['user_name']]);
                    
                    if (!$insertResult) {
                        error_log("Failed to create guide record for user_id: " . $userData['user_id']);
                        $this->sendResponse([
                            'success' => false,
                            'message' => 'Failed to create guide record'
                        ], 500);
                        return;
                    }
                    
                    error_log("Created new guide record for user_id: " . $userData['user_id']);
                } else {
                    error_log("Guide record already exists for user_id: " . $userData['user_id']);
                }

                // Build update query dynamically based on provided fields
                $updateFields = [];
                $updateValues = [];
                
                // Allowed fields for guide profile update
                $allowedFields = ['name', 'phone', 'nic', 'license_no', 'experience', 'location', 'languages'];
                
                foreach ($allowedFields as $field) {
                    if (isset($input[$field]) && $input[$field] !== '') {
                        $updateFields[] = "$field = ?";
                        $updateValues[] = $input[$field];
                    }
                }
                
                // If no fields to update, just return success
                if (empty($updateFields)) {
                    $this->sendResponse([
                        'success' => true,
                        'message' => 'Guide record verified, no additional fields to update'
                    ]);
                    return;
                }

                // Update guides table - make sure we only update one record
                $updateValues[] = $userData['user_id']; // Add user_id for WHERE clause
                $sql = "UPDATE guides SET " . implode(', ', $updateFields) . " WHERE user_id = ? LIMIT 1";
                
                error_log("Guide update SQL: " . $sql);
                error_log("Guide update values: " . json_encode($updateValues));
                
                $stmt = $this->connection->prepare($sql);
                $success = $stmt->execute($updateValues);
                $rowsAffected = $stmt->rowCount();
                
                error_log("Guide update result - Success: " . ($success ? 'YES' : 'NO') . ", Rows affected: " . $rowsAffected);
                
                // Always return success if SQL executed successfully, even if no rows changed
                if ($success) {
                    // If name is being updated, also update the users table
                    if (isset($input['name'])) {
                        $userUpdateSql = "UPDATE users SET name = ? WHERE id = ? LIMIT 1";
                        $userUpdateStmt = $this->connection->prepare($userUpdateSql);
                        $userUpdateStmt->execute([$input['name'], $userData['user_id']]);
                        error_log("Updated user table for guide: " . $input['name']);
                    }
                    
                    $this->sendResponse([
                        'success' => true,
                        'message' => 'Profile updated successfully',
                        'data' => [
                            'updated_fields' => array_keys(array_filter($input, function($v, $k) use ($allowedFields) {
                                return in_array($k, $allowedFields) && $v !== '';
                            }, ARRAY_FILTER_USE_BOTH)),
                            'rows_affected' => $rowsAffected,
                            'user_id' => $userData['user_id']
                        ]
                    ]);
                } else {
                    error_log("Guide SQL execution failed for user_id: " . $userData['user_id']);
                    $this->sendResponse([
                        'success' => false,
                        'message' => 'Database update failed'
                    ], 500);
                }
                
            } else {
                // Session-based update (existing functionality)
                $this->requireRole(['guide', 'admin']);
                
                // Validate required fields
                $required = ['name', 'phone', 'nic', 'license_no', 'experience', 'languages'];
                $missing = $this->validateRequired($input, $required);
                
                if (!empty($missing)) {
                    $this->sendResponse([
                        'success' => false,
                        'message' => 'Missing required fields: ' . implode(', ', $missing)
                    ], 400);
                    return;
                }

                $sql = "UPDATE guides SET 
                        name = ?, phone = ?, nic = ?, license_no = ?, 
                        experience = ?, location = ?, languages = ?
                        WHERE user_id = ?";
                
                $currentUser = $this->getCurrentUser();
                
                $stmt = $this->connection->prepare($sql);
                $stmt->bindValue(1, $input['name']);
                $stmt->bindValue(2, $input['phone']);
                $stmt->bindValue(3, $input['nic']);
                $stmt->bindValue(4, $input['license_no']);
                $stmt->bindValue(5, $input['experience']);
                $stmt->bindValue(6, $input['location'] ?? null);
                $stmt->bindValue(7, $input['languages']);
                $stmt->bindValue(8, $currentUser['user_id']);
                
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
            error_log("Guide profile update error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Profile update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadPhoto() {
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

            // Get guide user_id from email
            $getUserSql = "SELECT u.id as user_id FROM users u WHERE u.email = ? AND u.role = 'guide'";
            $getUserStmt = $this->connection->prepare($getUserSql);
            $getUserStmt->execute([$email]);
            $userData = $getUserStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userData) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'Guide not found with email: ' . $email
                ], 404);
                return;
            }

            // Create unique filename
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueFileName = 'guide_' . $userData['user_id'] . '_' . time() . '.' . $fileExtension;
            
            // Set upload path
            $uploadDir = __DIR__ . '/../../public/uploads/guides/';
            $uploadPath = $uploadDir . $uniqueFileName;
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Move uploaded file
            if (move_uploaded_file($uploadedFile['tmp_name'], $uploadPath)) {
                // Update database with photo path
                $photoUrl = '/RoutePro-backend(02)/public/uploads/guides/' . $uniqueFileName;
                
                // Check if guide record exists, create if it doesn't
                $checkGuideSql = "SELECT user_id FROM guides WHERE user_id = ?";
                $checkGuideStmt = $this->connection->prepare($checkGuideSql);
                $checkGuideStmt->execute([$userData['user_id']]);
                $guideExists = $checkGuideStmt->fetch(PDO::FETCH_ASSOC);

                if (!$guideExists) {
                    // Create initial guide record if it doesn't exist
                    $insertSql = "INSERT INTO guides (user_id, name, phone, nic, license_no, experience, location, languages, status, photo) 
                                  VALUES (?, 'Guide User', '', '', '', '0', '', '', 'available', ?)";
                    $insertStmt = $this->connection->prepare($insertSql);
                    $insertStmt->execute([$userData['user_id'], $photoUrl]);
                    error_log("Created new guide record with photo for user_id: " . $userData['user_id']);
                    $success = true;
                } else {
                    // Update existing record
                    $updateSql = "UPDATE guides SET photo = ? WHERE user_id = ?";
                    $updateStmt = $this->connection->prepare($updateSql);
                    $success = $updateStmt->execute([$photoUrl, $userData['user_id']]);
                }
                
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
