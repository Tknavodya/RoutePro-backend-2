<?php

class AdminDashboardController
{
    private $connection;

    public function __construct()
    {
        try {
            $this->connection = new PDO("mysql:host=localhost;dbname=route_pro_db", "root", "");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        try {
            $stats = [
                'monthlyRevenue' => $this->getMonthlyRevenue(),
                'totalTrips' => $this->getTotalTrips(),
                'totalTravelers' => $this->getTotalTravelers(),
                'totalDriversGuides' => $this->getTotalDriversGuides(),
                'recentTrips' => $this->getRecentTrips(),
                'revenueTrend' => $this->getRevenueTrend()
            ];

            return [
                'success' => true,
                'data' => $stats
            ];
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics'
            ];
        }
    }

    /**
     * Send JSON response
     */
    private function sendResponse($data, $statusCode = 200)
    {
        // Add CORS headers
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

        if ($origin) {
            if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/i', $origin)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Credentials: true');
            } else {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Credentials: false');
            }
        } else {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Credentials: false');
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
        header('Content-Type: application/json; charset=utf-8');

        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }

    /**
     * Get monthly revenue
     */
    private function getMonthlyRevenue()
    {
        $currentMonth = date('Y-m');

        // Get revenue from bookings for current month
        $stmt = $this->connection->prepare("
            SELECT COALESCE(SUM(total_cost), 0) as monthly_revenue,
                   COUNT(*) as trip_count
            FROM booking 
            WHERE DATE_FORMAT(date, '%Y-%m') = ? 
            AND status IN ('confirmed', 'completed')
        ");
        $stmt->execute([$currentMonth]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get previous month for comparison
        $previousMonth = date('Y-m', strtotime('-1 month'));
        $stmt = $this->connection->prepare("
            SELECT COALESCE(SUM(total_cost), 0) as monthly_revenue
            FROM booking 
            WHERE DATE_FORMAT(date, '%Y-%m') = ? 
            AND status IN ('confirmed', 'completed')
        ");
        $stmt->execute([$previousMonth]);
        $previous = $stmt->fetch(PDO::FETCH_ASSOC);

        $currentRevenue = floatval($current['monthly_revenue']);
        $previousRevenue = floatval($previous['monthly_revenue']);

        $percentageChange = 0;
        if ($previousRevenue > 0) {
            $percentageChange = (($currentRevenue - $previousRevenue) / $previousRevenue) * 100;
        }

        return [
            'amount' => $currentRevenue,
            'formatted' => 'Rs. ' . number_format($currentRevenue, 0),
            'percentageChange' => round($percentageChange, 1),
            'tripCount' => intval($current['trip_count'])
        ];
    }

    /**
     * Get total trips count
     */
    private function getTotalTrips()
    {
        // Total trips from booking table
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total_trips,
                   COUNT(CASE WHEN DATE(date) = CURDATE() THEN 1 END) as today_trips
            FROM booking
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total' => intval($result['total_trips']),
            'today' => intval($result['today_trips'])
        ];
    }

    /**
     * Get total travelers count
     */
    private function getTotalTravelers()
    {
        // Count travelers from users table
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total_travelers,
                   COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as this_week
            FROM users 
            WHERE role = 'traveller'
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total' => intval($result['total_travelers']),
            'thisWeek' => intval($result['this_week'])
        ];
    }

    /**
     * Get total drivers and guides count
     */
    private function getTotalDriversGuides()
    {
        // Count drivers
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as driver_count
            FROM users 
            WHERE role = 'driver'
        ");
        $stmt->execute();
        $drivers = $stmt->fetch(PDO::FETCH_ASSOC);

        // Count guides
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as guide_count
            FROM users 
            WHERE role = 'guide'
        ");
        $stmt->execute();
        $guides = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalDrivers = intval($drivers['driver_count']);
        $totalGuides = intval($guides['guide_count']);

        return [
            'total' => $totalDrivers + $totalGuides,
            'drivers' => $totalDrivers,
            'guides' => $totalGuides
        ];
    }

    /**
     * Get recent trips
     */
    private function getRecentTrips()
    {
        $stmt = $this->connection->prepare("
            SELECT 
                b.booking_id,
                b.date,
                b.total_cost,
                b.status,
                u.name as traveler_name,
                r.start_location,
                r.end_location,
                d.name as driver_name,
                g.name as guide_name
            FROM booking b
            LEFT JOIN users u ON b.traveler_id = u.id
            LEFT JOIN routes r ON b.route_id = r.route_id
            LEFT JOIN drivers d ON b.driver_id = d.id
            LEFT JOIN guides g ON b.guide_id = g.id
            ORDER BY b.date DESC
            LIMIT 5
        ");
        $stmt->execute();
        $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($trip) {
            return [
                'id' => 'TR' . str_pad($trip['booking_id'], 3, '0', STR_PAD_LEFT),
                'traveler' => $trip['traveler_name'] ?? 'Unknown',
                'route' => ($trip['start_location'] ?? 'Unknown') . ' - ' . ($trip['end_location'] ?? 'Unknown'),
                'amount' => 'Rs. ' . number_format($trip['total_cost'], 0),
                'status' => $trip['status'],
                'date' => $trip['date'],
                'staff' => trim(($trip['driver_name'] ? 'Driver: ' . $trip['driver_name'] : '') .
                    ($trip['guide_name'] ? ($trip['driver_name'] ? ', ' : '') . 'Guide: ' . $trip['guide_name'] : ''))
            ];
        }, $trips);
    }

    /**
     * Get revenue trend for last 5 months
     */
    private function getRevenueTrend()
    {
        $months = [];
        for ($i = 4; $i >= 0; $i--) {
            $months[] = date('Y-m', strtotime("-$i month"));
        }

        $trend = [];
        foreach ($months as $month) {
            $stmt = $this->connection->prepare("
                SELECT COALESCE(SUM(total_cost), 0) as revenue
                FROM booking 
                WHERE DATE_FORMAT(date, '%Y-%m') = ? 
                AND status IN ('confirmed', 'completed')
            ");
            $stmt->execute([$month]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $trend[] = [
                'month' => date('M', strtotime($month)),
                'revenue' => floatval($result['revenue']),
                'formatted' => 'Rs. ' . number_format($result['revenue'] / 1000, 0) . 'K'
            ];
        }

        return $trend;
    }

    /**
     * Get all users by type (travelers, drivers, guides)
     */
    public function getUsers($userType = 'all', $search = '')
    {
        try {
            $users = [];

            if ($userType === 'all' || $userType === 'travelers') {
                $users['travelers'] = $this->getTravelers($search);
            }

            if ($userType === 'all' || $userType === 'drivers') {
                $users['drivers'] = $this->getDrivers($search);
            }

            if ($userType === 'all' || $userType === 'guides') {
                $users['guides'] = $this->getGuides($search);
            }

            return [
                'success' => true,
                'data' => $users
            ];
        } catch (Exception $e) {
            error_log("Get users error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch users'
            ];
        }
    }

    /**
     * Get travelers with additional data
     */
    private function getTravelers($search = '')
    {
        $searchCondition = '';
        $params = [];

        if (!empty($search)) {
            $searchCondition = " AND (u.name LIKE ? OR u.email LIKE ? OR t.phone LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }

        $stmt = $this->connection->prepare("
            SELECT 
                u.id,
                u.name,
                u.email,
                u.created_at as join_date,
                u.rating,
                t.phone,
                COALESCE(SUM(b.total_cost), 0) as total_spent,
                COUNT(b.booking_id) as total_trips
            FROM users u
            LEFT JOIN travellers t ON u.id = t.user_id
            LEFT JOIN booking b ON u.id = b.traveler_id AND b.status IN ('confirmed', 'completed')
            WHERE u.role = 'traveller'
            $searchCondition
            GROUP BY u.id, u.name, u.email, u.created_at, u.rating, t.phone
            ORDER BY u.created_at DESC
        ");

        $stmt->execute($params);
        $travelers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($traveler) {
            return [
                'id' => 'T' . str_pad($traveler['id'], 3, '0', STR_PAD_LEFT),
                'name' => $traveler['name'] ?? 'Unknown',
                'email' => $traveler['email'],
                'phone' => $traveler['phone'] ?? 'N/A',
                'rating' => floatval($traveler['rating']),
                'totalSpent' => floatval($traveler['total_spent']),
                'totalTrips' => intval($traveler['total_trips']),
                'joinDate' => $traveler['join_date'],
                'status' => 'active' // Default status
            ];
        }, $travelers);
    }

    /**
     * Get drivers with additional data
     */
    private function getDrivers($search = '')
    {
        $searchCondition = '';
        $params = [];

        if (!empty($search)) {
            $searchCondition = " AND (u.name LIKE ? OR u.email LIKE ? OR d.name LIKE ? OR d.phone LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }

        $stmt = $this->connection->prepare("
            SELECT 
                u.id,
                u.name,
                u.email,
                u.created_at as join_date,
                u.rating,
                d.phone,
                d.license_no,
                d.vehicle_type,
                d.status as driver_status,
                d.experience,
                d.location,
                COALESCE(SUM(b.total_cost), 0) as earnings,
                COUNT(b.booking_id) as total_trips
            FROM users u
            LEFT JOIN drivers d ON u.id = d.user_id
            LEFT JOIN booking b ON d.id = b.driver_id AND b.status IN ('confirmed', 'completed')
            WHERE u.role = 'driver'
            $searchCondition
            GROUP BY u.id, u.name, u.email, u.created_at, u.rating, d.phone, d.license_no, d.vehicle_type, d.status, d.experience, d.location
            ORDER BY u.created_at DESC
        ");

        $stmt->execute($params);
        $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($driver) {
            return [
                'id' => 'D' . str_pad($driver['id'], 3, '0', STR_PAD_LEFT),
                'name' => $driver['name'] ?? 'Unknown',
                'email' => $driver['email'],
                'phone' => $driver['phone'] ?? 'N/A',
                'license' => $driver['license_no'] ?? 'N/A',
                'vehicle' => $driver['vehicle_type'] ?? 'N/A',
                'rating' => floatval($driver['rating']),
                'earnings' => floatval($driver['earnings']),
                'totalTrips' => intval($driver['total_trips']),
                'status' => $driver['driver_status'] === 'available' ? 'Available' : 'Busy',
                'experience' => intval($driver['experience']),
                'location' => $driver['location'] ?? 'N/A',
                'joinDate' => $driver['join_date']
            ];
        }, $drivers);
    }

    /**
     * Get guides with additional data
     */
    private function getGuides($search = '')
    {
        $searchCondition = '';
        $params = [];

        if (!empty($search)) {
            $searchCondition = " AND (u.name LIKE ? OR u.email LIKE ? OR g.name LIKE ? OR g.phone LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }

        $stmt = $this->connection->prepare("
            SELECT 
                u.id,
                u.name,
                u.email,
                u.created_at as join_date,
                u.rating,
                g.phone,
                g.license_no,
                g.languages,
                g.status as guide_status,
                g.experience,
                g.location,
                COALESCE(SUM(b.total_cost), 0) as earnings,
                COUNT(b.booking_id) as total_trips
            FROM users u
            LEFT JOIN guides g ON u.id = g.user_id
            LEFT JOIN booking b ON g.id = b.guide_id AND b.status IN ('confirmed', 'completed')
            WHERE u.role = 'guide'
            $searchCondition
            GROUP BY u.id, u.name, u.email, u.created_at, u.rating, g.phone, g.license_no, g.languages, g.status, g.experience, g.location
            ORDER BY u.created_at DESC
        ");

        $stmt->execute($params);
        $guides = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($guide) {
            return [
                'id' => 'G' . str_pad($guide['id'], 3, '0', STR_PAD_LEFT),
                'name' => $guide['name'] ?? 'Unknown',
                'email' => $guide['email'],
                'phone' => $guide['phone'] ?? 'N/A',
                'license' => $guide['license_no'] ?? 'N/A',
                'languages' => $guide['languages'] ?? 'N/A',
                'rating' => floatval($guide['rating']),
                'earnings' => floatval($guide['earnings']),
                'totalTrips' => intval($guide['total_trips']),
                'status' => $guide['guide_status'] === 'available' ? 'Available' : 'Busy',
                'experience' => intval($guide['experience']),
                'location' => $guide['location'] ?? 'N/A',
                'joinDate' => $guide['join_date']
            ];
        }, $guides);
    }

    /**
     * Update user rating
     */
    public function updateUserRating($userId, $rating)
    {
        try {
            $stmt = $this->connection->prepare("
                UPDATE users 
                SET rating = ? 
                WHERE id = ?
            ");
            $stmt->execute([$rating, $userId]);

            return [
                'success' => true,
                'message' => 'Rating updated successfully'
            ];
        } catch (Exception $e) {
            error_log("Update rating error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update rating'
            ];
        }
    }
}
