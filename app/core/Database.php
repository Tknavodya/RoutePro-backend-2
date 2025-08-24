<?php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "newpassword";
    private $dbname = "route_pro_db";

    public $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        if ($this->conn->connect_error) {
            die("DB Error: " . $this->conn->connect_error);
        }
    }
}
