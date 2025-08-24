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

class TravellerController extends Controller {
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

    public function updateLocation() {
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

    public function getAvailableTravellers() {
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

    public function getProfile() {
        // Check if email parameter is provided for email-based lookup
        $email = $_GET['email'] ?? null;
        
        // Only require role authentication if no email is provided (session-based lookup)
        if (!$email) {
            $this->requireRole(['traveller', 'admin']);
        }
        
        try {
            if ($email) {
                // Email-based lookup
                $sql = "SELECT u.id as user_id, u.name as user_name, u.email, 
                               t.name, t.phone, t.preferences, t.status, t.location, 
                               t.created_at
                        FROM users u 
                        JOIN travellers t ON u.id = t.user_id 
                        WHERE u.email = ? AND u.role = 'traveller'";
                
                $stmt = $this->connection->prepare($sql);
                $stmt->execute([$email]);
                $travellerData = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // Session-based lookup (existing functionality)
                $currentUser = $this->getCurrentUser();
                $userId = $currentUser['user_id'];
                
                $sql = "SELECT u.name as user_name, u.email, 
                               t.name, t.phone, t.preferences, t.status, t.location, 
                               t.created_at
                        FROM users u 
                        JOIN travellers t ON u.id = t.user_id 
                        WHERE u.id = ?";
                
                $stmt = $this->connection->prepare($sql);
                $stmt->execute([$userId]);
                $travellerData = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$travellerData) {
                $errorMessage = $email ? "No traveller found with email: $email" : "Traveller not found";
                $this->sendResponse([
                    'success' => false,
                    'message' => $errorMessage
                ], 404);
                return;
            }
            
            // Use traveller name if available, otherwise use user name
            $finalData = [
                'name' => $travellerData['name'] ?: $travellerData['user_name'],
                'phone' => $travellerData['phone'] ?: 'Not provided',
                'email' => $travellerData['email'],
                'preferences' => $travellerData['preferences'] ?: 'No preferences set',
                'status' => $travellerData['status'] ?: 'active',
                'location' => $travellerData['location'] ?: 'Not specified',
                'member_since' => $travellerData['created_at'] ?: 'Unknown'
            ];
            
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

    public function updateProfile() {
        $this->requireRole(['traveller', 'admin']);
        
        try {
            $input = $this->getInput();
            
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

            $sql = "UPDATE travellers SET 
                    name = ?, phone = ?, preferences = ?, 
                    location = ?
                    WHERE user_id = ?";
            
            $currentUser = $this->getCurrentUser();
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(1, $input['name']);
            $stmt->bindValue(2, $input['phone']);
            $stmt->bindValue(3, $input['preferences'] ?? null);
            $stmt->bindValue(4, $input['location'] ?? null);
            $stmt->bindValue(5, $currentUser['user_id']);
            
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
            error_log("Traveller profile update error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Profile update failed'
            ], 500);
        }
    }

    public function getBookingHistory() {
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

    public function createBooking() {
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
}
