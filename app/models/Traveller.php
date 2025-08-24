<?php

require_once __DIR__ . '/User.php';

class Traveller extends User {
    private $traveller_id;
    private $phone;

    public function __construct(
        $name = null,
        $email = null,
        $password = null,
        $phone = null
    ) {
        parent::__construct($name, $email, $password, 'traveller', 0);
        $this->phone = $phone;
    }

    // Getters
    public function getTravellerId() { return $this->traveller_id; }
    public function getPhone() { return $this->phone; }

    // Setters
    public function setTravellerId($traveller_id) { $this->traveller_id = $traveller_id; }
    public function setPhone($phone) { $this->phone = $phone; }

    // Implementation of abstract method from User class
    public function register($connection) {
        try {
            // Validate input
            $validation_rules = [
                'name' => ['required' => true, 'min' => 2, 'max' => 100],
                'email' => ['required' => true, 'email' => true],
                'password' => ['required' => true, 'min' => 6],
                'phone' => ['required' => true, 'min' => 10]
            ];

            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'phone' => $this->phone
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

            // Create traveller profile
            $sql = "INSERT INTO travellers (user_id, name, phone) VALUES (?, ?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $this->id);
            $stmt->bindValue(2, $this->name);
            $stmt->bindValue(3, $this->phone);

            if ($stmt->execute()) {
                $this->traveller_id = $connection->lastInsertId();
                $connection->commit();
                return ['success' => true, 'message' => 'Traveller registered successfully', 'user_id' => $this->id];
            } else {
                $connection->rollback();
                return ['success' => false, 'message' => 'Failed to create traveller profile'];
            }

        } catch (PDOException $e) {
            $connection->rollback();
            error_log("Traveller registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed: Database error'];
        }
    }

    // Implementation of abstract method from User class
    public function getProfileData($connection) {
        try {
            $sql = "SELECT t.*, u.name, u.email, u.rating, u.created_at 
                    FROM travellers t 
                    JOIN users u ON t.user_id = u.id 
                    WHERE t.user_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $this->id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Traveller profile fetch error: " . $e->getMessage());
            return false;
        }
    }

    // Traveller-specific methods
    public function getBookingHistory($connection) {
        try {
            $sql = "SELECT b.*, r.route_name, r.start_location, r.end_location 
                    FROM bookings b 
                    JOIN routes r ON b.route_id = r.id 
                    WHERE b.traveller_id = ? 
                    ORDER BY b.created_at DESC";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $this->traveller_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Booking history fetch error: " . $e->getMessage());
            return [];
        }
    }

    public function createBooking($connection, $route_id, $driver_id = null, $guide_id = null) {
        try {
            $sql = "INSERT INTO bookings (traveller_id, route_id, driver_id, guide_id, status, created_at) 
                    VALUES (?, ?, ?, ?, 'pending', NOW())";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $this->traveller_id);
            $stmt->bindValue(2, $route_id);
            $stmt->bindValue(3, $driver_id);
            $stmt->bindValue(4, $guide_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Booking creation error: " . $e->getMessage());
            return false;
        }
    }

    public static function getByUserId($connection, $user_id) {
        try {
            $sql = "SELECT * FROM travellers WHERE user_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $user_id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Traveller fetch error: " . $e->getMessage());
            return false;
        }
    }

    public static function getAvailableTravellers($connection) {
        try {
            $sql = "SELECT u.id as user_id, u.name as user_name, u.email, 
                           t.traveller_id, t.name, t.phone, t.preferences, 
                           t.status, t.location, t.created_at
                    FROM users u 
                    JOIN travellers t ON u.id = t.user_id 
                    WHERE u.role = 'traveller'
                    ORDER BY t.created_at DESC";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            
            $travellers = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $travellers[] = [
                    'user_id' => $row['user_id'],
                    'traveller_id' => $row['traveller_id'],
                    'name' => $row['name'] ?: $row['user_name'],
                    'email' => $row['email'],
                    'phone' => $row['phone'] ?: 'Not provided',
                    'preferences' => $row['preferences'] ?: 'No preferences set',
                    'status' => $row['status'] ?: 'active',
                    'location' => $row['location'] ?: 'Not specified',
                    'member_since' => $row['created_at']
                ];
            }
            
            return $travellers;
        } catch (PDOException $e) {
            error_log("Available travellers fetch error: " . $e->getMessage());
            return [];
        }
    }
}
