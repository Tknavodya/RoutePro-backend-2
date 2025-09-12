<?php

require_once __DIR__ . '/User.php';

class Driver extends User {
    private $driver_id;
    private $phone;
    private $status;
    private $license_no;
    private $vehicle_type;
    private $experience;
    private $location;

    public function __construct(
        $name = null,
        $email = null,
        $password = null,
        $phone = null,
        $license_no = null,
        $vehicle_type = null,
        $experience = null,
        $location = null,
        $status = null
    ) {
        parent::__construct($name, $email, $password, 'driver', 0);
        $this->phone = $phone;
        $this->license_no = $license_no;
        $this->vehicle_type = $vehicle_type;
        $this->experience = $experience;
        $this->location = $location;
        $this->status = $status ?? 'nonavailable'; // Use null coalescing for default
    }

    // Getters
    public function getDriverId() { return $this->driver_id; }
    public function getPhone() { return $this->phone; }
    public function getStatus() { return $this->status; }
    public function getLicenseNo() { return $this->license_no; }
    public function getVehicleType() { return $this->vehicle_type; }
    public function getExperience() { return $this->experience; }
    public function getLocation() { return $this->location; }

    // Setters
    public function setDriverId($driver_id) { $this->driver_id = $driver_id; }
    public function setPhone($phone) { $this->phone = $phone; }
    public function setStatus($status) { $this->status = $status; }
    public function setLicenseNo($license_no) { $this->license_no = $license_no; }
    public function setVehicleType($vehicle_type) { $this->vehicle_type = $vehicle_type; }
    public function setExperience($experience) { $this->experience = $experience; }
    public function setLocation($location) { $this->location = $location; }

    // Implementation of abstract method from User class
    public function register($connection) {
        try {
            // Validate input
            $validation_rules = [
                'name' => ['required' => true, 'min' => 2, 'max' => 100],
                'email' => ['required' => true, 'email' => true],
                'password' => ['required' => true, 'min' => 6],
                'phone' => ['required' => true, 'min' => 10],
                'license_no' => ['required' => true],
                'vehicle_type' => ['required' => true],
                'experience' => ['required' => true]
            ];

            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'phone' => $this->phone,
                'license_no' => $this->license_no,
                'vehicle_type' => $this->vehicle_type,
                'experience' => $this->experience
            ];

            $errors = $this->validate($data, $validation_rules);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            // Check if email already exists
            if ($this->emailExists($connection)) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            $connection->beginTransaction();

            // Create user first
            if (!$this->createUser($connection)) {
                $connection->rollback();
                return ['success' => false, 'message' => 'Failed to create user account'];
            }

            // Create driver profile
            $sql = "INSERT INTO drivers (user_id, name, phone, license_no, vehicle_type, experience, location, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $this->id);
            $stmt->bindValue(2, $this->name);
            $stmt->bindValue(3, $this->phone);
            $stmt->bindValue(4, $this->license_no);
            $stmt->bindValue(5, $this->vehicle_type);
            $stmt->bindValue(6, $this->experience);
            $stmt->bindValue(7, $this->location);
            $stmt->bindValue(8, $this->status);

            if ($stmt->execute()) {
                $this->driver_id = $connection->lastInsertId();
                $connection->commit();
                return ['success' => true, 'message' => 'Driver registered successfully', 'user_id' => $this->id];
            } else {
                $connection->rollback();
                return ['success' => false, 'message' => 'Failed to create driver profile'];
            }

        } catch (PDOException $e) {
            $connection->rollback();
            error_log("Driver registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed: Database error'];
        }
    }

    // Implementation of abstract method from User class
    public function getProfileData($connection) {
        try {
            $sql = "SELECT d.*, u.name, u.email, u.rating, u.created_at 
                    FROM drivers d 
                    JOIN users u ON d.user_id = u.id 
                    WHERE d.user_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $this->id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Driver profile fetch error: " . $e->getMessage());
            return false;
        }
    }

    // Driver-specific methods
    public function updateStatus($connection, $status) {
        try {
            $sql = "UPDATE drivers SET status = ? WHERE user_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $status);
            $stmt->bindValue(2, $this->id);
            
            if ($stmt->execute()) {
                $this->status = $status;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Driver status update error: " . $e->getMessage());
            return false;
        }
    }

    public function updateLocation($connection, $location) {
        try {
            $sql = "UPDATE drivers SET location = ? WHERE user_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $location);
            $stmt->bindValue(2, $this->id);
            
            if ($stmt->execute()) {
                $this->location = $location;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Driver location update error: " . $e->getMessage());
            return false;
        }
    }

    public static function getAvailableDrivers($connection) {
        try {
            $sql = "SELECT d.*, u.name, u.email, u.rating 
                    FROM drivers d 
                    JOIN users u ON d.user_id = u.id 
                    WHERE d.status = 'available'";
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Available drivers fetch error: " . $e->getMessage());
            return [];
        }
    }

    public static function getAllDrivers($connection) {
        try {
            $sql = "SELECT d.*, u.name as user_name, u.email, u.rating, u.created_at,
                           CASE 
                               WHEN d.photo IS NOT NULL AND d.photo != '' 
                               THEN CONCAT('http://localhost', d.photo)
                               ELSE 'http://localhost/RoutePro-backend(02)/public/images/defaults/default-driver.svg'
                           END as photo_url
                    FROM drivers d 
                    JOIN users u ON d.user_id = u.id 
                    ORDER BY u.created_at DESC";
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            
            $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the data for frontend consumption
            foreach ($drivers as &$driver) {
                $driver['id'] = $driver['user_id'];
                $driver['name'] = $driver['user_name'];
                unset($driver['user_name']);
                
                // Debug: Log the original photo value
                error_log("Driver {$driver['name']} photo field: " . ($driver['photo'] ?? 'NULL'));
                
                // Ensure photo_url is properly formatted with better fallback logic
                if ($driver['photo'] && !empty($driver['photo'])) {
                    $photoPath = $driver['photo'];
                    
                    // Handle different photo path formats
                    if (strpos($photoPath, '/RoutePro-backend(02)/public/uploads/drivers/') === 0) {
                        // Extract just the filename from the database path
                        $filename = basename($photoPath);
                        $localPath = __DIR__ . '/../../public/uploads/drivers/' . $filename;
                        
                        if (file_exists($localPath)) {
                            // Use the correct local path
                            $driver['photo_url'] = 'http://localhost/RoutePro-backend(02)/public/uploads/drivers/' . $filename;
                        } else {
                            error_log("Driver photo file not found: " . $localPath);
                            $driver['photo_url'] = 'https://ui-avatars.com/api/?name=' . urlencode($driver['name']) . '&background=4A90E2&color=fff&size=150';
                        }
                    } elseif (strpos($photoPath, '/') === 0) {
                        // If photo path starts with /, it's already a full path
                        $driver['photo_url'] = 'http://localhost' . $photoPath;
                    } else {
                        // If it's just a filename, add the uploads path
                        $localPath = __DIR__ . '/../../public/uploads/drivers/' . $photoPath;
                        if (file_exists($localPath)) {
                            $driver['photo_url'] = 'http://localhost/RoutePro-backend(02)/public/uploads/drivers/' . $photoPath;
                        } else {
                            error_log("Driver photo file not found: " . $localPath);
                            $driver['photo_url'] = 'https://ui-avatars.com/api/?name=' . urlencode($driver['name']) . '&background=4A90E2&color=fff&size=150';
                        }
                    }
                } else {
                    // Use a personalized avatar based on name
                    $driver['photo_url'] = 'https://ui-avatars.com/api/?name=' . urlencode($driver['name']) . '&background=4A90E2&color=fff&size=150';
                }
                
                // Debug: Log the final photo URL
                error_log("Driver {$driver['name']} final photo_url: " . $driver['photo_url']);
            }
            
            return $drivers;
        } catch (PDOException $e) {
            error_log("All drivers fetch error: " . $e->getMessage());
            return [];
        }
    }
}
