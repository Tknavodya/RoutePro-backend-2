<?php

require_once __DIR__ . '/User.php';

class Admin extends User {
    private $admin_id;
    private $department;
    private $permissions;

    public function __construct(
        $name = null,
        $email = null,
        $password = null,
        $department = null,
        $permissions = null
    ) {
        parent::__construct($name, $email, $password, 'admin', 5);
        $this->department = $department;
        $this->permissions = $permissions;
    }

    // Getters
    public function getAdminId() { return $this->admin_id; }
    public function getDepartment() { return $this->department; }
    public function getPermissions() { return $this->permissions; }

    // Setters
    public function setAdminId($admin_id) { $this->admin_id = $admin_id; }
    public function setDepartment($department) { $this->department = $department; }
    public function setPermissions($permissions) { $this->permissions = $permissions; }

    // Implementation of abstract method from User class
    public function register($connection) {
        try {
            // Validate input
            $validation_rules = [
                'name' => ['required' => true, 'min' => 2, 'max' => 100],
                'email' => ['required' => true, 'email' => true],
                'password' => ['required' => true, 'min' => 8],
                'department' => ['required' => true]
            ];

            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'department' => $this->department
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

            // Create admin profile
            $sql = "INSERT INTO admins (user_id, name, department, permissions) VALUES (?, ?, ?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $this->id);
            $stmt->bindValue(2, $this->name);
            $stmt->bindValue(3, $this->department);
            $stmt->bindValue(4, $this->permissions ?? 'basic');

            if ($stmt->execute()) {
                $this->admin_id = $connection->lastInsertId();
                $connection->commit();
                return ['success' => true, 'message' => 'Admin registered successfully', 'user_id' => $this->id];
            } else {
                $connection->rollback();
                return ['success' => false, 'message' => 'Failed to create admin profile'];
            }

        } catch (PDOException $e) {
            $connection->rollback();
            error_log("Admin registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed: Database error'];
        }
    }

    // Implementation of abstract method from User class
    public function getProfileData($connection) {
        try {
            $sql = "SELECT a.*, u.name, u.email, u.rating, u.created_at 
                    FROM admins a 
                    JOIN users u ON a.user_id = u.id 
                    WHERE a.user_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $this->id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Admin profile fetch error: " . $e->getMessage());
            return false;
        }
    }

    // Admin-specific methods
    public function getAllUsers($connection) {
        try {
            $sql = "SELECT id, name, email, role, rating, created_at FROM users ORDER BY created_at DESC";
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("All users fetch error: " . $e->getMessage());
            return [];
        }
    }

    public function getUsersByRole($connection, $role) {
        try {
            $sql = "SELECT id, name, email, role, rating, created_at FROM users WHERE role = ? ORDER BY created_at DESC";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $role);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Users by role fetch error: " . $e->getMessage());
            return [];
        }
    }

    public function deleteUser($connection, $user_id) {
        try {
            $connection->beginTransaction();

            // Delete from role-specific tables first
            $role_sql = "SELECT role FROM users WHERE id = ?";
            $role_stmt = $connection->prepare($role_sql);
            $role_stmt->bindValue(1, $user_id);
            $role_stmt->execute();
            $role_result = $role_stmt->fetch(PDO::FETCH_ASSOC);

            if ($role_result) {
                switch ($role_result['role']) {
                    case 'driver':
                        $delete_sql = "DELETE FROM drivers WHERE user_id = ?";
                        break;
                    case 'guide':
                        $delete_sql = "DELETE FROM guides WHERE user_id = ?";
                        break;
                    case 'traveller':
                        $delete_sql = "DELETE FROM travellers WHERE user_id = ?";
                        break;
                    case 'admin':
                        $delete_sql = "DELETE FROM admins WHERE user_id = ?";
                        break;
                }

                if (isset($delete_sql)) {
                    $stmt = $connection->prepare($delete_sql);
                    $stmt->bindValue(1, $user_id);
                    $stmt->execute();
                }
            }

            // Delete from users table
            $user_sql = "DELETE FROM users WHERE id = ?";
            $user_stmt = $connection->prepare($user_sql);
            $user_stmt->bindValue(1, $user_id);
            $success = $user_stmt->execute();

            $connection->commit();
            return $success;

        } catch (PDOException $e) {
            $connection->rollback();
            error_log("User deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function updateUserRole($connection, $user_id, $new_role) {
        try {
            $sql = "UPDATE users SET role = ? WHERE id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(1, $new_role);
            $stmt->bindValue(2, $user_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("User role update error: " . $e->getMessage());
            return false;
        }
    }

    public function getSystemStats($connection) {
        try {
            $stats = [];
            
            // Count users by role
            $role_sql = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
            $role_stmt = $connection->prepare($role_sql);
            $role_stmt->execute();
            $stats['user_counts'] = $role_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Total bookings
            $booking_sql = "SELECT COUNT(*) as total_bookings FROM bookings";
            $booking_stmt = $connection->prepare($booking_sql);
            $booking_stmt->execute();
            $stats['total_bookings'] = $booking_stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'];
            
            // Active drivers and guides
            $active_sql = "SELECT 
                            (SELECT COUNT(*) FROM drivers WHERE status = 'available') as active_drivers,
                            (SELECT COUNT(*) FROM guides WHERE status = 'available') as active_guides";
            $active_stmt = $connection->prepare($active_sql);
            $active_stmt->execute();
            $active_data = $active_stmt->fetch(PDO::FETCH_ASSOC);
            $stats['active_drivers'] = $active_data['active_drivers'];
            $stats['active_guides'] = $active_data['active_guides'];
            
            return $stats;
        } catch (PDOException $e) {
            error_log("System stats fetch error: " . $e->getMessage());
            return [];
        }
    }
}
