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
    <link rel="stylesheet" href="styles/style_index.css">
    <link rel="stylesheet" href="styles/style_manage_user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- HEADER SECTION -->
    <header class="header">
        <!-- Logo Section -->
        <div class="header-logo">
            <p>JOBS</p>
            <img src="styles/images/logo.png" alt="Logo Image">
            <p>TIME</p>
        </div>
        
        <!-- Navigation and Auth Container -->
        <div class="nav-auth-container">
            <!-- Navigation Bar -->
            <nav class="nav-bar">
                <a href="index.php" class="nav-item">
                    <p class="nav-main-item">Home</p>
                    <p class="nav-sub-item">Main page</p>
                </a>
                <a href="about.php" class="nav-item">
                    <p class="nav-main-item">About</p>
                    <p class="nav-sub-item">More information</p>
                </a>
                <a href="jobs.php" class="nav-item">
                    <p class="nav-main-item">Jobs</p>
                    <p class="nav-sub-item">Find opportunities</p>
                </a>
                <a href="apply.php" class="nav-item">
                    <p class="nav-main-item">Apply</p>
                    <p class="nav-sub-item">Send applications</p>
                </a>
                <a href="enhancements.php" class="nav-item" id="last-item">
                    <p class="nav-main-item">Enhancements</p>
                    <p class="nav-sub-item">Feedback and Suggestions</p>
                </a>
                    <a href="phpenhancements.php" class="nav-item" id="last-item">
                    <p class="nav-main-item">PHP Enhancements</p>
                    <p class="nav-sub-item">Feedback and Suggestions</p>
                </a>
            </nav>

            <!-- Auth Buttons -->
            <div class="auth-buttons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="auth-btn account-btn">
                        <i class="fas fa-user-circle"></i>
                        Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="user-management">
        <h2>User Management</h2>

        <!-- Display message if found -->
        <?php if ($message): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Display error if found -->
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

    <!-- FOOTER SECTION -->
    <footer class="footer">
        <!-- Copyright Area -->
        <div class="footer-content">
            <p class="copyright">
                Copyright &copy; 2018, All Right Reserved
                <a href="mailto:105551875@student.swin.edu.au" class="link">(Our student email)</a>
            </p>

            <!-- Footer Menu Links -->
            <div class="footer-menu">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="jobs.php">Job</a></li>
                    <li><a href="apply.php">Apply</a></li>
                    <li><a href="enhancements.php">Enhancements</a></li>
                    <li><a href="phpenhancements.php">PHP Enhancements</a></li>
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>