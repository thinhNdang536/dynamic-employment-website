<?php
// Author: Dang Quang Thinh & Nguyen Cong Quang Minh
// Student ID: 105551875 & ID: 105680177

// Clear, Well-structure, Optimized, Maintainable

class Database {
    /* I give class for more maintainable code */
    // Define vars
    private $host;
    private $user;
    private $pwd;
    private $sql_db;
    private $port;
    public $conn;

    public function __construct() {
        /* This func will always execute first when class is called */
        // Load db config
        $this->host = 'fygq1.h.filess.io';
        $this->user = 'ass2_quitefunme';
        $this->pwd = '7c5a9a9e241335fc981ca841b4838100c1fb207e';
        $this->sql_db = 'ass2_quitefunme';
        $this->port = '3307';

        // Init the connection
        $this->connect();
    }

    private function connect() {
        /* Create connection to db and check whether success or not */
        $this->conn = mysqli_connect($this->host, $this->user, $this->pwd, $this->sql_db, $this->port);

        // Check the connection to db
        if (mysqli_connect_errno()) {
            throw new Exception("Failed to connect to db. Please try again later.");
        }
    }

    public function getConnection() {
        if ($this->conn === null) {
            $this->conn = new mysqli($this->host, $this->user, $this->pwd, $this->sql_db);
            if ($this->conn->connect_error) {
                die("Connection failed: " . $this->conn->connect_error);
            }
            // Disable auto-commit
            $this->conn->autocommit(false);
        }
        return $this->conn;
    }
}

// Example for usage
// $db = new Database();
// $conn = $db->getConnection();

// Add this function after the Database class
function refreshUserRole($userId) {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}
?>