<?php

require_once __DIR__ . '/User.php';

class Guide extends User {
    private $guide_id;
    private $phone;
    private $status;
    private $nic;
    private $license_no;
    private $experience;
    private $location;
    private $languages;

    public function __construct(
        $name = null,
        $email = null,
        $password = null,
        $phone = null,
        $nic = null,
        $license_no = null,
        $experience = null,
        $location = null,
        $languages = null,
        $status = 'nonavailable'
    ) {
        parent::__construct($name, $email, $password, 'guide', 0);
        $this->phone = $phone;
        $this->nic = $nic;
        $this->license_no = $license_no;
        $this->experience = $experience;
        $this->location = $location;
        $this->languages = $languages;
        $this->status = $status;
    }

    // Getters
    public function getGuideId() { return $this->guide_id; }
    public function getPhone() { return $this->phone; }
    public function getNIC() { return $this->nic; }
    public function getLicenseNo() { return $this->license_no; }
    public function getExperience() { return $this->experience; }
    public function getLocation() { return $this->location; }
    public function getLanguages() { return $this->languages; }
    public function getStatus() { return $this->status; }

    // Setters
    public function setGuideId($guide_id) { $this->guide_id = $guide_id; }
    public function setPhone($phone) { $this->phone = $phone; }
    public function setNIC($nic) { $this->nic = $nic; }
    public function setLicenseNo($license_no) { $this->license_no = $license_no; }
    public function setExperience($experience) { $this->experience = $experience; }
    public function setLocation($location) { $this->location = $location; }
    public function setLanguages($languages) { $this->languages = $languages; }
    public function setStatus($status) { $this->status = $status; }

    // Implementation of abstract method from User class
    public function register($connection) {
        try {
            // Validate input
            $validation_rules = [
                'name' => ['required' => true, 'min' => 2, 'max' => 100],
                'email' => ['required' => true, 'email' => true],
                'password' => ['required' => true, 'min' => 6],
                'phone' => ['required' => true, 'min' => 10],
                'nic' => ['required' => true],
                'license_no' => ['required' => true],
                'experience' => ['required' => true],
                'languages' => ['required' => true]
            ];

            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'phone' => $this->phone,
                'nic' => $this->nic,
                'license_no' => $this->license_no,
                'experience' => $this->experience,
                'languages' => $this->languages
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

            // Create guide profile
            $sql = "INSERT INTO guides (user_id, name, phone, nic, license_no, experience, location, languages, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $this->id);
            $stmt->bindValue(2, $this->name);
            $stmt->bindValue(3, $this->phone);
            $stmt->bindValue(4, $this->nic);
            $stmt->bindValue(5, $this->license_no);
            $stmt->bindValue(6, $this->experience);
            $stmt->bindValue(7, $this->location);
            $stmt->bindValue(8, $this->languages);
            $stmt->bindValue(9, $this->status);

            if ($stmt->execute()) {
                $this->guide_id = $connection->lastInsertId();
                $connection->commit();
                return ['success' => true, 'message' => 'Guide registered successfully', 'user_id' => $this->id];
            } else {
                $connection->rollback();
                return ['success' => false, 'message' => 'Failed to create guide profile'];
            }

        } catch (PDOException $e) {
            $connection->rollback();
            error_log("Guide registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed: Database error'];
        }
    }

    // Implementation of abstract method from User class
    public function getProfileData($connection) {
        try {
            $sql = "SELECT g.*, u.name, u.email, u.rating, u.created_at 
                    FROM guides g 
                    JOIN users u ON g.user_id = u.id 
                    WHERE g.user_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $this->id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Guide profile fetch error: " . $e->getMessage());
            return false;
        }
    }

    // Guide-specific methods
    public function updateStatus($connection, $status) {
        try {
            $sql = "UPDATE guides SET status = ? WHERE user_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $status);
            $stmt->bindValue(2, $this->id);
            
            if ($stmt->execute()) {
                $this->status = $status;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Guide status update error: " . $e->getMessage());
            return false;
        }
    }

    public function updateLocation($connection, $location) {
        try {
            $sql = "UPDATE guides SET location = ? WHERE user_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $location);
            $stmt->bindValue(2, $this->id);
            
            if ($stmt->execute()) {
                $this->location = $location;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Guide location update error: " . $e->getMessage());
            return false;
        }
    }

    public static function getAvailableGuides($connection) {
        try {
            $sql = "SELECT g.*, u.name, u.email, u.rating 
                    FROM guides g 
                    JOIN users u ON g.user_id = u.id 
                    WHERE g.status = 'available'";
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Available guides fetch error: " . $e->getMessage());
            return [];
        }
    }

    public static function getGuidesByLanguage($connection, $language) {
        try {
            $sql = "SELECT g.*, u.name, u.email, u.rating 
                    FROM guides g 
                    JOIN users u ON g.user_id = u.id 
                    WHERE g.languages LIKE ? AND g.status = 'available'";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, "%$language%");
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Guides by language fetch error: " . $e->getMessage());
            return [];
        }
    }
}

