<?php
    session_start(); //Must do=))
    require_once 'settings.php';

    /**
        * Expressions of Interest (EOI) Management
        *
        * This file contains the EOIManager class for managing Expressions of Interest (EOIs)
        * in the system. It provides methods for retrieving, filtering, updating, and paginating
        * EOI records in the database. The class also includes functionality for filtering EOIs by 
        * job reference or applicant name and managing the status of EOIs.
        *
        * PHP version 8.2.12
        *
        * @category   Management
        * @package    Assignment2
        * @author     Dang Quang Thinh
        * @student-id 105551875
        * @version    1.0.0
    */

    /**
        * EOIManager Class
        *
        * This class manages Expressions of Interest (EOIs) including
        * retrieving, filtering, and updating EOI records in the database.
        *
        * It provides methods for pagination, filtering by job reference
        * or applicant name, and managing EOI statuses.
    */
    class EOIManager {
        private $conn;
        private $limits;

        public function __construct() {
            $db = new Database();
            $this->conn = $db->getConnection();
        }

        /**
            * Get EOIs with pagination (limit and offset)
            *
            * Retrieves all EOIs with specified limit and offset for pagination.
            *
            * @param int $limit The number of EOIs to retrieve
            * @param int $offset The offset for pagination
            * @return array An array of EOI records
        */
        public function getAllEOIs($limit, $offset): array {
            $query = "SELECT * FROM eoi ORDER BY submitTime DESC LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function getEOIsByJobRef($jobRef): array {
            $query = "SELECT * FROM eoi WHERE jobRef = ? ORDER BY submitTime DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $jobRef);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function getEOIsByName($firstName, $lastName): array {
            $query = "SELECT * FROM eoi WHERE firstName = ? OR lastName = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ss", $firstName, $lastName);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        /**
               * Get total number of EOIs
               *
               * Retrieves the total count of EOIs in the database.
               *
               * @return int The total number of EOIs
        */
        public function getTotalEOIs(): int {
            $result = $this->conn->query("SELECT COUNT(*) as total FROM eoi");
            return (int)$result->fetch_assoc()['total'];
        }

        public function deleteEOIsByJobRef($jobRef): bool {
            $query = "DELETE FROM eoi WHERE jobRef = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $jobRef);
            return $stmt->execute();
        }

        public function updateEOIStatus($eoiNum, $newStatus): bool {
            $query = "UPDATE eoi SET status = ? WHERE EOInum = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $newStatus, $eoiNum);
            return $stmt->execute();
        }

        public function __destruct() {
            $this->conn->close();
        }
    }

    // Check if user is logged in
    // if (!isset($_SESSION['user_id'])) {
    //     header("Location: login.php?manage=invalid_user");
    //     exit();
    // }

    // Refresh user role
    if (!refreshUserRole($_SESSION['user_id'])) {
        session_destroy();
        header("Location: login.php?error=invalid_user");
        exit();
    }

    // Check if user is admin
    if (strtolower($_SESSION['role']) !== 'admin') {
        header("Location: login.php?manage=error");
        exit();
    }

    $manager = new EOIManager();
    $limit = isset($_SESSION['limit']) ? $_SESSION['limit'] : 10;
    $offset = 0;

    $totalEOIs = $manager->getTotalEOIs();
    $results = $manager->getAllEOIs($limit, $offset);

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'showMore':
                if ($limit + 5 <= $totalEOIs) {
                    $_SESSION['limit'] = $limit + 5;
                } else {
                    $_SESSION['limit'] = $totalEOIs;
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;

            case 'showLess':
                if ($limit - 5 > 0) {
                    $_SESSION['limit'] = $limit - 5;
                } else {
                    $_SESSION['limit'] = 5;
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;

            case 'newUpdate':
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            
            case 'filterByJobRef':
                $jobRef = $_POST['jobRef'];
                $results = $manager->getEOIsByJobRef($jobRef);
                $totalEOIs = count($results);

            case 'filterByName':
                $firstName = $_POST['firstName'];
                $lastName = $_POST['lastName'];
                $results = $manager->getEOIsByName($firstName, $lastName);
                $totalEOIs = count($results);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EOI Management</title>
    <link rel="stylesheet" href="styles/style_index.css">
    <link rel="stylesheet" href="styles/style_manage.css">
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

    <div class="management-container">
        <h1>EOI Management System</h1>

        <div class="query-section">
            <div class="tableHeading-container">
                <h2>EOI Records</h2>
                
                <div class="sort-controls">
                    <form method="post" class="sort-form">
                        <input type="hidden" name="action" value="sort">
                        <select name="sortField" onchange="this.form.submit()">
                            <option value="">Sort By...</option>
                            <option value="EOInum" <?php echo isset($_SESSION['sortField']) && $_SESSION['sortField'] === 'EOInum' ? 'selected' : ''; ?>>EOI Number</option>
                            <option value="jobRef" <?php echo isset($_SESSION['sortField']) && $_SESSION['sortField'] === 'jobRef' ? 'selected' : ''; ?>>Job Reference</option>
                            <option value="firstName" <?php echo isset($_SESSION['sortField']) && $_SESSION['sortField'] === 'firstName' ? 'selected' : ''; ?>>First Name</option>
                            <option value="submitTime" <?php echo isset($_SESSION['sortField']) && $_SESSION['sortField'] === 'submitTime' ? 'selected' : ''; ?>>Submit Time</option>
                            <option value="status" <?php echo isset($_SESSION['sortField']) && $_SESSION['sortField'] === 'status' ? 'selected' : ''; ?>>Status</option>
                        </select>
                    </form>
                </div>
                
                <form method="post" class="update-form">
                    <input type="hidden" name="action" value="update">
                    <button type="submit"><i class="fas fa-sync"></i> Update</button>
                </form>
            </div>
            <table class="results-table">
                <thead>
                    <tr>
                        <th>EOI #</th>
                        <th>Job Ref</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Submit Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $row): ?>
                        <tr>
                        <td><?php echo htmlspecialchars($row['EOInum']); ?></td> <!-- EOI Number -->
                        <td><?php echo htmlspecialchars($row['jobRef']); ?></td> <!-- Job Reference -->
                        <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?></td> <!-- Applicant Name -->
                        <td><?php echo htmlspecialchars($row['email']); ?></td> <!-- Email -->
                        <td><?php echo htmlspecialchars($row['phoneNum']); ?></td> <!-- Phone Number -->
                        <td><?php echo htmlspecialchars($row['status']); ?></td> <!-- Status -->
                        <td><?php echo htmlspecialchars($row['submitTime']); ?></td> <!-- Submit Time -->
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No EOIs found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

             
            <!-- Filter criteria -->
            <?php if (isset($_SESSION['filter'])): ?>
                <div class="filter-message">
                    <p>Showing results for:
                    <?php 
                        echo $_SESSION['filter'];
                    ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="button-group">
                <?php if ($limit < $totalEOIs && !isset($_POST['action'])): ?>
                    <form method="post" class="see_more_btn">
                        <input type="hidden" name="action" value="showMore">
                        <button type="submit"><i class="fas fa-plus"></i> See More (<?php echo $totalEOIs - $limit; ?>)</button>
                    </form>
                <?php endif; ?>
                
                <?php if ($limit > 5 && !isset($_POST['action'])): ?>
                    <form method="post" class="see_more_btn">
                        <input type="hidden" name="action" value="showLess">
                        <button type="submit"><i class="fas fa-minus"></i> See Less</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="filter-section">
            <h2>Filter EOIs</h2>
            <!-- Filter by Job Reference -->
            <form method="post" class="filter-jobref">
                <input type="text" name="jobRef" placeholder="Enter Job Reference" required>
                <button type="submit" name="action" value="filterByJobRef">Filter by Job Reference Number</button>
            </form>

            <!-- Filter by Name -->
            <form method="post" class="filter-applicant">
                <input type="text" name="firstName" placeholder="First Name">
                <input type="text" name="lastName" placeholder="Last Name">
                <button type="submit" name="action" value="filterByName">Filter by Name</button>
            </form>

            <!-- Delete by Job Reference -->
            <form method="post" class="delete-jobref"  onsubmit="return confirm('Are you sure you want to delete all EOIs related to this Job Reference?');">
                <input type="text" name="jobRefToDelete" placeholder="Enter Job Reference to Delete" required>
                <button type="submit" name="action" value="deleteByJobRef">Delete EOIs</button>
            </form>

            <!-- Update Status -->
            <form method="post" class="update-status" onsubmit="return confirm('Are you sure you want to update this EOI status?');">
                <div class="form-group">
                    <label for="eoiNum">EOI Number:</label>
                    <input type="number" id="eoiNum" name="eoiNum" required min="1">
                </div>
                <div class="form-group">
                    <label for="newStatus">New Status:</label>
                    <select id="newStatus" name="newStatus" required>
                        <option value="">Select Status</option>
                        <option value="new">New</option>
                        <option value="current">Current</option>
                        <option value="final">Final</option>
                    </select>
                </div>
                <button type="submit" name="action" value="updateStatus">
                    <i class="fas fa-sync-alt"></i> Update Status
                </button>
            </form>
        </div>
    </div>
</body>
</html>
