<?php

require_once __DIR__ . '/../core/Model.php';

abstract class User extends Model {
    protected $id;
    protected $name;
    protected $email;
    protected $password;
    protected $role;
    protected $rating;
    protected $created_at;
    protected $reset_token;
    protected $reset_token_expiry;

    // Constructor
    public function __construct(
        $name = null,
        $email = null,
        $password = null,
        $role = null,
        $rating = null
    ) {
        parent::__construct();
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->rating = $rating;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getRating() { return $this->rating; }
    public function getCreatedAt() { return $this->created_at; }
    public function getResetToken() { return $this->reset_token; }
    public function getResetTokenExpiry() { return $this->reset_token_expiry; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setName($name) { $this->name = $name; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { $this->password = $password; }
    public function setRole($role) { $this->role = $role; }
    public function setRating($rating) { $this->rating = $rating; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setResetToken($reset_token) { $this->reset_token = $reset_token; }
    public function setResetTokenExpiry($reset_token_expiry) { $this->reset_token_expiry = $reset_token_expiry; }

    // Abstract methods that child classes must implement
    abstract public function register($connection);
    abstract public function getProfileData($connection);

    // Common login method for all user types
    public function login($connection) {
        try {
            $sql = "SELECT id, name, email, password, role, rating, created_at
                    FROM users
                    WHERE email = ? AND role = ?";
           
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $this->email);
            $stmt->bindValue(2, $this->role);
            $stmt->execute();
           
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && password_verify($this->password, $row['password'])) {
                $this->setId($row['id']);
                $this->setName($row['name']);
                $this->setEmail($row['email']);
                $this->setRole($row['role']);
                $this->setRating($row['rating']);
                $this->setCreatedAt($row['created_at']);
               
                return $this;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Login query error: " . $e->getMessage());
            return null;
        }
    }

    // Common user creation method
    protected function createUser($connection) {
        try {
            $sql = "INSERT INTO users (name, email, password, role, rating) VALUES (?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($sql);
            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
            
            $stmt->bindValue(1, $this->name);
            $stmt->bindValue(2, $this->email);
            $stmt->bindValue(3, $hashedPassword);
            $stmt->bindValue(4, $this->role);
            $stmt->bindValue(5, $this->rating ?? 0);
            
            if ($stmt->execute()) {
                $this->id = $connection->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("User creation error: " . $e->getMessage());
            return false;
        }
    }

    // Check if email already exists
    protected function emailExists($connection) {
        try {
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $this->email);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (PDOException $e) {
            error_log("Email check error: " . $e->getMessage());
            return true; // Return true to be safe
        }
    }

    // Password reset functionality
    public function generateResetToken($connection) {
        try {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $sql = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $token);
            $stmt->bindValue(2, $expiry);
            $stmt->bindValue(3, $this->email);
            
            if ($stmt->execute()) {
                $this->reset_token = $token;
                $this->reset_token_expiry = $expiry;
                return $token;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Reset token generation error: " . $e->getMessage());
            return false;
        }
    }

    public function resetPassword($connection, $token, $newPassword) {
        try {
            $sql = "SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $token);
            $stmt->execute();
            
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?";
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(1, $hashedPassword);
                $stmt->bindValue(2, $token);
                return $stmt->execute();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            return false;
        }
    }
}