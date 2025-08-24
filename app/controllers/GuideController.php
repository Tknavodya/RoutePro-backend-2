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
            $this->connection = new PDO("mysql:host=localhost;dbname=route_pro_db", "root", "newpassword");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            $this->sendResponse(['success' => false, 'message' => 'Database connection failed'], 500);
        }
    }

    public function updateStatus() {
        $this->requireRole(['guide', 'admin']);
        
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

        } catch (Exception $e) {
            error_log("Status update error: " . $e->getMessage());
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
                $sql = "SELECT u.id as user_id, u.name as user_name, u.email, 
                               g.name, g.phone, g.nic, g.license_no, 
                               g.experience, g.status, g.location, g.languages
                        FROM users u 
                        JOIN guides g ON u.id = g.user_id 
                        WHERE u.email = ? AND u.role = 'guide'";
                
                $stmt = $this->connection->prepare($sql);
                $stmt->execute([$email]);
                $guideData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$guideData) {
                    $this->sendResponse([
                        'success' => false,
                        'message' => "No guide found with email: $email"
                    ], 404);
                    return;
                }
                
                // Format data for response
                $finalData = [
                    'name' => $guideData['name'] ?: $guideData['user_name'],
                    'phone' => $guideData['phone'] ?: 'Not provided',
                    'email' => $guideData['email'],
                    'nic' => $guideData['nic'] ?: 'Not provided',
                    'license_no' => $guideData['license_no'] ?: 'Not provided',
                    'experience' => $guideData['experience'] ?: '0',
                    'status' => $guideData['status'] ?: 'nonavailable',
                    'location' => $guideData['location'] ?: 'Not specified',
                    'languages' => $guideData['languages'] ?: 'Not specified'
                ];
                
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
        $this->requireRole(['guide', 'admin']);
        
        try {
            $input = $this->getInput();
            
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

        } catch (Exception $e) {
            error_log("Guide profile update error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Profile update failed'
            ], 500);
        }
    }
}
