<?php
    /**
        * Database Connection and Settings Management
        *
        * This file contains the Database class for managing database connections
        * and helper functions for user role management.
        *
        * PHP version 8.2.12
        *
        * @category   Configuration
        * @package    Assignment2
        * @author     Dang Quang Thinh & Nguyen Cong Quang Minh
        * @student-id 105551875 & 105680177
        * @version    1.0.0

    */

    /**
        * Database Connection Handler Class
        *
        * Manages database connections and provides connection utilities

    */
    class Database {
        private $host;
        private $user;
        private $pwd;
        private $sql_db;
        private $port;
        
        /** @var mysqli Active database connection*/
        public $conn;

        /**
            * Constructor - Initializes database connection parameters
        */
        public function __construct() {
            // Load database configuration
            $this->host = 'fygq1.h.filess.io';
            $this->user = 'ass2_quitefunme';
            $this->pwd = '7c5a9a9e241335fc981ca841b4838100c1fb207e';
            $this->sql_db = 'ass2_quitefunme';
            $this->port = '3307';

            // Initialize the connection
            $this->connect();
        }

        /**
            * Establishes connection to the database
            * 
            * @throws Exception If connection fails
        */
        private function connect() {
            $this->conn = mysqli_connect($this->host, $this->user, $this->pwd, $this->sql_db, $this->port);

            if (mysqli_connect_errno()) {
                throw new Exception("Failed to connect to db. Please try again later.");
            }
        }

        /**
            * Gets or creates a database connection
            * 
            * @return mysqli Active database connection
        */
        public function getConnection() {
            if ($this->conn === null) {
                $this->conn = new mysqli($this->host, $this->user, $this->pwd, $this->sql_db);
                if ($this->conn->connect_error) {
                    die("Connection failed: " . $this->conn->connect_error);
                }
                $this->conn->autocommit(false);
            }
            return $this->conn;
        }
    }

    /**
        * Updates the user's role in the session
        * 
        * @param int $userId The ID of the user to refresh role for
        * @return bool True if role was refreshed successfully, false otherwise
    */
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
