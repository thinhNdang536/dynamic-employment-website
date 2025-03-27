<?php
    /**
        * Dashboard for EOIs, Users and Job Summaries
        *
        * This file provides a dashboard view for managing Expressions of Interest (EOIs),
        * user accounts, and job listings. It retrieves and displays summary data based on the
        * current user's role. Admin users see management summaries, while non-admin users see
        * their personal application details.
        *
        * PHP version 8.2.12
        *
        * @category   Management
        * @package    Assignment2
        * @author     Dang Quang Thinh
        * @student-id 105551875
        * @version    1.0.0
    */

    session_start(); //Must do=))
    require_once 'settings.php'; //Import db model from settings.php

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?error=invalid_user");
        exit();
    }


    /**
        * Refresh the user's role from the database and check if the user is an admin.
        *
        * This function retrieves the user's role using the user id stored in the session.
        * If the user is found, it updates the session with the user's role. If not,
        * it destroys the session and redirects to the login page with an error.
        * Finally, it returns true if the user's role is 'admin' (case-insensitive), or false otherwise.
        *
        * @return bool True if the user is an admin, false otherwise.
    */
    function refreshUserRoleAndCheckAdmin() {
        // Create a new Database instance and get the connection
        $db = new Database();
        $conn = $db->getConnection();
        
        // Prepare and execute the query to fetch the user's role
        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the user is found and update the session with the role
        if ($user = $result->fetch_assoc()) {
            $_SESSION['role'] = $user['role'];
        } else {
            // User not found, destroy session and redirect to login with error message
            // I don't like unauthorized access:vv
            session_destroy();
            header("Location: login.php?error=invalid_user");
            exit();
        }

        // My db have limited connection so...=))
        $stmt->close();
        $conn->close();
        
        // Determine if the user is an admin (case-insensitive)
        $isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';
        return $isAdmin;
    }

    // Check if user is admin for management features
    $isAdmin = refreshUserRoleAndCheckAdmin();


    /**
        * SummaryBase Class
        *
        * This abstract base class provides a common database connection for summary-related
        * operations. All summary classes should extend this class.
        * I love OOP=)), it kinda makes my code look like a work of art:>
    */
    abstract class SummaryBase {
        /**
            * @var mysqli Database connection instance.
        */
        protected $conn;
        
        /**
            * Constructor.
            * Initializes the database connection.
        */
        public function __construct() {
            $db = new Database();
            $this->conn = $db->getConnection();
        }
    }


    /**
           * EOISummary Class
           *
           * This class provides various methods to retrieve summary statistics and recent data
           * for Expressions of Interest (EOIs), users, and job listings. It is used by the
           * dashboard to display management summaries for admin users as well as personal EOI
           * data for non-admin users.
    */
    class EOISummary extends SummaryBase {
        /**
            * Get total count of EOIs.
            *
            * @return int The total number of EOIs.
        */
        public function getTotalEOIs(): int {
            $result = $this->conn->query("SELECT COUNT(*) as total FROM eoi");
            return (int)$result->fetch_assoc()['total'];
        }
        
        /**
            * Get count of EOIs based on status.
            *
            * Returns an associative array with keys 'new', 'current', and 'final'.
            *
            * @return array Associative array of EOI counts by status.
        */
        public function getStatusCounts(): array {
            $counts = ['new' => 0, 'current' => 0, 'final' => 0];
            $query = "SELECT LOWER(status) as status, COUNT(*) as count FROM eoi GROUP BY status";
            $result = $this->conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $status = strtolower($row['status']);
                if (array_key_exists($status, $counts)) {
                    $counts[$status] = (int)$row['count'];
                }
            }
            return $counts;
        }
        
        /**
            * Get recent EOIs.
            *
            * Retrieves the 5 most recent EOIs based on submission time.
            *
            * @return array An array of recent EOI records.
        */
        public function getRecentEOIs(): array {
            $query = "SELECT * FROM eoi ORDER BY submitTime DESC LIMIT 5";
            $result = $this->conn->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        
        /**
            * Get EOIs submitted by a specific user.
            *
            * @param int $userId The ID of the user.
            * @return array An array of EOI records for the specified user.
        */
        public function getUserEOIs($userId): array {
            $query = "SELECT * FROM eoi WHERE user_id = ? ORDER BY submitTime DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }
    
    /**
        * UserSummary Class
        *
        * This class handles summary-related operations for users, such as retrieving
        * the total number of users, counting users by active status, and fetching recent users.
    */
    class UserSummary extends SummaryBase {
        /**
            * Get total count of users.
            *
            * @return int The total number of users.
        */
        public function getTotalUsers(): int {
            $result = $this->conn->query("SELECT COUNT(*) as total FROM users");
            return (int)$result->fetch_assoc()['total'];
        }
        
        /**
            * Get user count based on active status.
            *
            * Returns an associative array with keys 'active' and 'blocked'.
            *
            * @return array Associative array with user counts by status.
        */
        public function getUserStatusCounts(): array {
            $counts = ['active' => 0, 'blocked' => 0];
            $query = "SELECT LOWER(is_active) as status, COUNT(*) as count FROM users GROUP BY is_active";
            $result = $this->conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $status = strtolower($row['status']);
                $counts[$status] = (int)$row['count'];
            }
            return $counts;
        }
        
        /**
            * Get recent users.
            *
            * Retrieves the 5 most recently registered users.
            *
            * @return array An array of recent user records.
        */
        public function getRecentUsers(): array {
            $query = "SELECT username, email, created_at, is_active FROM users ORDER BY created_at DESC LIMIT 5";
            $result = $this->conn->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }
    
    /**
        * JobSummary Class
        *
        * This class handles summary-related operations for job listings,
        * such as retrieving the total number of jobs and the number of active jobs.
        *
        * @category Management
        * @package  Assignment2
        * @author   Dang Quang Thinh
        * @version  1.0.0
    */
    class JobSummary extends SummaryBase {
        /**
            * Get job summary.
            *
            * Retrieves an associative array with the total number of jobs and the count of active jobs.
            *
            * @return array Associative array with keys 'total' and 'active'.
        */
        public function getJobSummary(): array {
            $summary = ['total' => 0, 'active' => 0];
            $query = "SELECT COUNT(*) as total, SUM(CASE WHEN active = true THEN 1 ELSE 0 END) as active FROM jobs";
            $result = $this->conn->query($query);
            if ($result) {
                $row = $result->fetch_assoc();
                $summary['total'] = (int)$row['total'];
                $summary['active'] = (int)$row['active'];
            }
            return $summary;
        }
    }
    
    // Init instance of summaries
    $eoiSummary = new EOISummary();
    $userSummary = new UserSummary();
    $jobSummary = new JobSummary();
    
    if ($isAdmin) {
        $totalEOIs = $eoiSummary->getTotalEOIs();
        $statusCounts = $eoiSummary->getStatusCounts();
        $recentEOIs = $eoiSummary->getRecentEOIs();

        $totalUsers = $userSummary->getTotalUsers();
        $userStatusCounts = $userSummary->getUserStatusCounts();
        $recentUsers = $userSummary->getRecentUsers();

        $jobSummaryData = $jobSummary->getJobSummary();
    } else {
        // For non-admin users, load personal EOIs=))
        $userEOIs = $eoiSummary->getUserEOIs($_SESSION['user_id']);
    }
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="styles/style_dashboard.css">
    <title>JobsTime - Dashboard</title>
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
            </nav>

            <!-- Auth Buttons -->
            <div class="auth-buttons">
                <?php if(isset($_SESSION['username'])): ?>
                    <a href="logout.php" class="auth-btn">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Add EOI Management Summary Section -->
    <main class="dashboard-content">
        <?php if ($isAdmin): ?>
            <!-- Admin View -->
            <!-- EOI Management Summary Section -->
            <div class="eoi-summary">
                <h2>EOI Management Summary</h2>
                
                <div class="summary-cards">
                    <div class="summary-card">
                        <h3>Total EOIs</h3>
                        <p class="count"><?php echo $totalEOIs; ?></p>
                    </div>
                    
                    <!-- EOI status summary cards here -->
                    <div class="summary-card">
                        <h3>Status Breakdown</h3>
                        <table class="status-table">
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                            </tr>
                            <tr>
                                <td>New</td>
                                <td><?php echo $statusCounts['new'] ?? 0; ?></td>
                            </tr>
                            <tr>
                                <td>Current</td>
                                <td><?php echo $statusCounts['current'] ?? 0; ?></td>
                            </tr>
                            <tr>
                                <td>Final</td>
                                <td><?php echo $statusCounts['final'] ?? 0; ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Most recent EOI summary here -->
                    <div class="summary-card">
                        <h3>Recent Applications</h3>
                        <table>
                            <tr>
                                <th>Job Ref</th>
                                <th>Name</th>
                                <th>Status</th>
                            </tr>
                            <?php foreach($recentEOIs as $eoi): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($eoi['jobRef']); ?></td>
                                <td><?php echo htmlspecialchars($eoi['firstName'] . ' ' . $eoi['lastName']); ?></td>
                                <td><?php echo htmlspecialchars($eoi['status']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
                
                <!-- For ultimate management=)) -->
                <div class="manage-link">
                    <a href="manage.php" class="manage-btn">Go to Full Management</a>
                </div>
            </div>

            <div class="eoi-summary user-summary">
                <h2>User Management Summary</h2>
                
                <div class="summary-cards">
                    <div class="summary-card">
                        <h3>Total Users</h3>
                        <p class="count"><?php echo $totalUsers; ?></p>
                    </div>
                    
                    <!-- User status summary here -->
                    <div class="summary-card">
                        <h3>Status Breakdown</h3>
                        <table class="status-table">
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                            </tr>
                            <tr>
                                <td>Active</td>
                                <td><?php echo $userStatusCounts['active'] ?? 0; ?></td>
                            </tr>
                            <tr>
                                <td>Blocked</td>
                                <td><?php echo $userStatusCounts['blocked'] ?? 0; ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Most recent user here -->
                    <div class="summary-card">
                        <h3>Recent Users</h3>
                        <table>
                            <tr>
                                <th>Email</th>
                                <th>Status</th>
                            </tr>
                            <?php foreach($recentUsers as $user): ?>
                            <tr>
                                <td class="email-cell" title="<?php echo htmlspecialchars($user['email']); ?>">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['is_active']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
                
                <!-- For ultimate user management, go to user management page=)) -->
                <div class="manage-link">
                    <a href="manage_user.php" class="manage-btn">Manage Users</a>
                </div>
            </div>

            <div class="eoi-summary user-summary">
                <h2>Job Listings Summary</h2>
                
                <div class="summary-cards">
                    <div class="summary-card">
                        <h3>Total Jobs</h3>
                        <p class="count"><?php echo $jobSummaryData['total']; ?></p>
                    </div>
                    
                    <!-- Same as above:vv -->
                    <div class="summary-card">
                        <h3>Status Breakdown</h3>
                        <table class="status-table">
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                            </tr>
                            <tr>
                                <td>Active</td>
                                <td><?php echo $jobSummaryData['active']; ?></td>
                            </tr>
                            <tr>
                                <td>Inactive</td>
                                <td><?php echo $jobSummaryData['total'] - $jobSummaryData['active']; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Same as above:vv -->
                <div class="manage-link">
                    <a href="manage_jobs.php" class="manage-btn">Manage Jobs</a>
                </div>
            </div>


        <?php else: ?>
            <!-- User View -->
            <div class="eoi-summary">
                <h2>My Applications</h2>
                
                <div class="summary-cards">
                    <div class="summary-card">
                        <h3>My EOIs</h3>
                        <?php if (empty($userEOIs)): ?>
                            <p class="no-eois">You haven't submitted any applications yet.</p>
                            <div class="apply-link">
                                <a href="apply.php" class="manage-btn">Submit an Application</a>
                            </div>
                        <?php else: ?>
                            <table>
                                <tr>
                                    <th>Job Reference</th>
                                    <th>Submit Date</th>
                                    <th>Status</th>
                                </tr>
                                <?php foreach($userEOIs as $eoi): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($eoi['jobRef']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($eoi['submitTime'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($eoi['status']); ?>">
                                            <?php echo htmlspecialchars($eoi['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

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
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>
