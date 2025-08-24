<?php
// DbConnector.php
class DbConnector {
    private $hostname = "localhost";
    private $dbname = "route_pro_db";
    private $dbuser = "root";
    private $dbpw = "newpassword";
    private $connection = null;

    public function getConnection() {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host=" . $this->hostname . ";dbname=" . $this->dbname . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                $this->connection = new PDO($dsn, $this->dbuser, $this->dbpw, $options);
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw new Exception("Database connection failed");
            }
        }
        return $this->connection;
    }

    public function closeConnection() {
        $this->connection = null;
    }
}
?>