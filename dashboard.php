<?php
session_start();
require_once 'settings.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Refresh user role from database
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    $_SESSION['role'] = $user['role'];
} else {
    // If user no longer exists in database, log them out
    session_destroy();
    header("Location: login.php?error=invalid_user");
    exit();
}

// Check if user is admin for management features
$isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';

// Add EOI summary functionality
class EOISummary {
    private $conn;
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    
    public function getTotalEOIs() {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM eoi");
        return $result->fetch_assoc()['total'];
    }
    
    public function getStatusCounts() {
        // Default counts
        $counts = [
            'new' => 0,
            'current' => 0,
            'final' => 0
        ];
        
        $query = "SELECT LOWER(status) as status, COUNT(*) as count FROM eoi GROUP BY status";
        $result = $this->conn->query($query);
        
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $status = strtolower($row['status']); // Convert to lowercase for consistent comparison:vvv i hate this
                if (array_key_exists($status, $counts)) {
                    $counts[$status] = (int)$row['count'];
                }
            }
        }
        
        return $counts;
    }
    
    public function getRecentEOIs() {
        $query = "SELECT * FROM eoi ORDER BY submitTime DESC LIMIT 5";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getTotalUsers() {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM users");
        return $result->fetch_assoc()['total'];
    }
    
    public function getUserStatusCounts() {
        $counts = [
            'active' => 0,
            'blocked' => 0
        ];
        
        $query = "SELECT LOWER(is_active) as status, COUNT(*) as count FROM users GROUP BY is_active";
        $result = $this->conn->query($query);
        
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $status = strtolower($row['status']);
                $counts[$status] = (int)$row['count'];
            }
        }
        
        return $counts;
    }
    
    public function getRecentUsers() {
        $query = "SELECT username, email, created_at, is_active FROM users ORDER BY created_at DESC LIMIT 5";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getUserEOIs($userId) {
        $query = "SELECT e.* FROM eoi e 
                 INNER JOIN users u ON JSON_CONTAINS(u.eoiNums, CAST(e.EOInum AS JSON), '$')
                 WHERE u.id = ? 
                 ORDER BY e.submitTime DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getJobSummary() {
        $summary = [
            'total' => 0,
            'active' => 0
        ];
        
        $result = $this->conn->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN active = true THEN 1 ELSE 0 END) as active
            FROM jobs");
        
        if ($result) {
            $row = $result->fetch_assoc();
            $summary['total'] = (int)$row['total'];
            $summary['active'] = (int)$row['active'];
        }
        
        return $summary;
    }
}

$summary = new EOISummary();

// Load data based on user role
if ($isAdmin) {
    $totalEOIs = $summary->getTotalEOIs();
    $statusCounts = $summary->getStatusCounts();
    $recentEOIs = $summary->getRecentEOIs();
    $totalUsers = $summary->getTotalUsers();
    $userStatusCounts = $summary->getUserStatusCounts();
    $recentUsers = $summary->getRecentUsers();
    $jobSummary = $summary->getJobSummary();
} else {
    $userEOIs = $summary->getUserEOIs($_SESSION['user_id']);
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
            <div class="eoi-summary">
                <h2>EOI Management Summary</h2>
                
                <div class="summary-cards">
                    <div class="summary-card">
                        <h3>Total EOIs</h3>
                        <p class="count"><?php echo $totalEOIs; ?></p>
                    </div>
                    
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
                
                <div class="manage-link">
                    <a href="manage_user.php" class="manage-btn">Manage Users</a>
                </div>
            </div>

            <div class="eoi-summary user-summary">
                <h2>Job Listings Summary</h2>
                
                <div class="summary-cards">
                    <div class="summary-card">
                        <h3>Total Jobs</h3>
                        <p class="count"><?php echo $jobSummary['total']; ?></p>
                    </div>
                    
                    <div class="summary-card">
                        <h3>Status Breakdown</h3>
                        <table class="status-table">
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                            </tr>
                            <tr>
                                <td>Active</td>
                                <td><?php echo $jobSummary['active']; ?></td>
                            </tr>
                            <tr>
                                <td>Inactive</td>
                                <td><?php echo $jobSummary['total'] - $jobSummary['active']; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
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
