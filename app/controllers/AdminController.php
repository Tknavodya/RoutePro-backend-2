<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Admin.php';

class AdminController extends Controller {
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

    public function getAllUsers() {
        // Temporarily comment out auth check for testing
        // $this->requireRole(['admin']);
        
        try {
            $admin = new Admin();
            // $admin->setId($_SESSION['user_id']);
            
            // Get query parameters for filtering
            $type = $_GET['type'] ?? null;
            $search = $_GET['search'] ?? '';
            
            $users = $admin->getAllUsers($this->connection, $type, $search);
            
            $this->sendResponse([
                'success' => true,
                'users' => $users
            ]);

        } catch (Exception $e) {
            error_log("All users fetch error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch users'
            ], 500);
        }
    }

    public function getSystemStats() {
        try {
            // Get basic statistics
            $stats = [
                'monthlyRevenue' => [
                    'amount' => 0,
                    'formatted' => 'Rs. 0',
                    'percentageChange' => 0,
                    'tripCount' => 0
                ],
                'totalTrips' => [
                    'total' => 0,
                    'today' => 0
                ],
                'totalTravelers' => [
                    'total' => 0,
                    'thisWeek' => 0
                ],
                'totalDriversGuides' => [
                    'total' => 0,
                    'drivers' => 0,
                    'guides' => 0
                ],
                'recentTrips' => [],
                'revenueTrend' => []
            ];

            // Get user counts
            $userStmt = $this->connection->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
            while ($row = $userStmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row['role'] === 'traveller') {
                    $stats['totalTravelers']['total'] = $row['count'];
                } elseif ($row['role'] === 'driver') {
                    $stats['totalDriversGuides']['drivers'] = $row['count'];
                } elseif ($row['role'] === 'guide') {
                    $stats['totalDriversGuides']['guides'] = $row['count'];
                }
            }

            $stats['totalDriversGuides']['total'] = $stats['totalDriversGuides']['drivers'] + $stats['totalDriversGuides']['guides'];

            $this->sendResponse([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            error_log("System stats error: " . $e->getMessage());
            $this->sendResponse([
                'success' => false,
                'message' => 'Failed to fetch system statistics'
            ], 500);
        }
    }
}
