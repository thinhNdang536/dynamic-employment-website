<?php
    /**
        * User Management System
        *
        * This file contains the UserManager class, responsible for managing user accounts
        * in the JobsTime system. It includes functionality for retrieving user lists,
        * searching users, and toggling user status.
        *
        * PHP version 8.2.12
        *
        * @category   UserManagement
        * @package    JobsTime
        * @author     Dang Quang Thinh
        * @student-id 105551875
        * @version    1.0.0
    */

    session_start(); //Must do=))
    require_once 'settings.php'; //Import db model from settings.php

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Refresh user role each time the page is loaded, kinda waste resource:vv but necessary
    if (!refreshUserRole($_SESSION['user_id'])) {
        session_destroy();
        header("Location: login.php?error=invalid_user");
        exit();
    }

    // Check if user is admin, for access control=))
    if (strtolower($_SESSION['role']) !== 'admin') {
        header("Location: login.php?manage=error");
        exit();
    }

    /**
        * Class UserManager
        *
        * Manages user-related operations, including retrieving users, 
        * searching users by username/email, and toggling user status.
    */
    class UserManager {
        private $conn;

        /**
            * Constructor initializes database connection.
        */
        public function __construct() {
            $db = new Database();
            $this->conn = $db->getConnection();
        }

        /**
            * Retrieves a paginated list of users.
            *
            * @param int $limit  Number of users per page (default: 10).
            * @param int $offset Offset for pagination (default: 0).
            * @return array Associative array of users.
        */
        public function getAllUsers($limit = 10, $offset = 0) {
            $query = "SELECT id, username, email, created_at, last_login, is_active 
                    FROM users 
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        /**
            * Toggles the activation status of a user.
            *
            * @param int $userId The ID of the user.
            * @return bool Returns true if update is successful, false otherwise.
        */
        public function toggleUserStatus($userId) {
            $query = "UPDATE users 
                    SET is_active = CASE 
                        WHEN is_active = 'Active' THEN 'Blocked'
                        ELSE 'Active'
                    END 
                    WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $userId);
            return $stmt->execute();
        }

        /**
            * Searches for users by username or email.
            *
            * @param string $term The search term (username or email).
            * @return array Associative array of matching users.
        */
        public function searchUsers($term) {
            $term = "%$term%";
            $query = "SELECT id, username, email, created_at, last_login, is_active 
                    FROM users 
                    WHERE username LIKE ? OR email LIKE ?
                    ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ss", $term, $term);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Init the database connection and user model
    $manager = new UserManager();
    $users = [];
    $message = '';
    $error = '';

    // Handle actions, e.g., login, register, toggle user status, search users
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'toggle_status':
                if (!empty($_POST['user_id']) && $manager->toggleUserStatus($_POST['user_id'])) {
                    $message = "User status updated successfully.";
                } else {
                    $error = "Failed to update user status.";
                }
                break;
            
            case 'search':
                if (!empty($_POST['search_term'])) {
                    $users = $manager->searchUsers($_POST['search_term']);
                }
                break;
        }
    }

    // Get all users if no search performed:vv at worst case, we have to fetch all users from the db
    if (empty($users)) {
        $users = $manager->getAllUsers();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - JobsTime</title>
    <link rel="stylesheet" href="styles/style_dashboard.css">
    <style>
        .user-management {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #242424;
            border-radius: 10px;
        }

        .search-bar {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
        }

        .search-bar input {
            flex: 1;
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid #444;
            background-color: #333;
            color: #fff;
        }

        .search-btn {
            padding: 0.5rem 1rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .users-table th,
        .users-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #444;
        }

        .users-table th {
            background-color: #333;
            color: #007bff;
        }

        .toggle-status {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .status-active {
            background-color: #28a745;
            color: white;
        }

        .status-blocked {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="user-management">
        <h2>User Management</h2>

        <?php if ($message): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" class="search-bar">
            <input type="hidden" name="action" value="search">
            <input type="text" name="search_term" placeholder="Search by username or email...">
            <button type="submit" class="search-btn">Search</button>
        </form>

        <table class="users-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Created At</th>
                    <th>Last Login</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td><?php echo $user['last_login'] ? htmlspecialchars($user['last_login']) : 'Never'; ?></td>
                        <td><?php echo htmlspecialchars($user['is_active']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="toggle-status <?php echo $user['is_active'] === 'Active' ? 'status-active' : 'status-blocked'; ?>">
                                    <?php echo $user['is_active'] === 'Active' ? 'Block' : 'Activate'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>