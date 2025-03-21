<?php
    require_once 'settings.php';
    /**
        * EOI Management Handler.

        * Handles actions related to EOIs (Expressions of Interest), including filtering, 
        * showing more or fewer EOIs, deleting EOIs, updating EOI status, and resetting filters.

        * Author: Dang Quang Thinh
        * Date: 20/02/2025

        * This script processes form submissions that perform actions such as:
        * - Show more or less EOIs.
        * - Filter EOIs by job reference or applicant name.
        * - Delete EOIs based on job reference.
        * - Update the status of an EOI.
        * - Reset filters and EOI data.
    **/

    // just 4 debugging:vvv
    // error_reporting(E_ALL);
    // ini_set('display_errors', 1);

    // OOP for better look;))
    // docstring written by AI


    // Start the session if it hasn't been started yet, it maybe a little bit unnecessary:))
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


    class EOIManager {
        /**
            * Class EOIManager

            * This class manages the Expressions of Interest (EOI) in the system.
            * It provides methods to retrieve, filter, delete, and update EOIs.

            * Methods:
            * - getAllEOIs(int $limit, int $offset): array
            * - getEOIsByJobRef(string $jobRef): array
            * - getEOIsByName(string $firstName, string $lastName): array
            * - getTotalEOIs(): int
            * - deleteEOIsByJobRef(string $jobRef): bool
            * - updateEOIStatus(int $eoiNum, string $newStatus): bool
            * - isValidStatus(string $status): bool

            * Properties:
            * - private $conn: Database connection
            * - private const VALID_STATUSES: Valid status values for EOIs
        **/
        private $conn;
        private const VALID_STATUSES = ['new', 'current', 'final'];

        public function __construct() {
            $db = new Database();
            $this->conn = $db->getConnection();
        }

        /**
            * Get EOIs with pagination (limit and offset)

            * @param int $limit
            * @param int $offset
            * @return array
        **/
        public function getAllEOIs($limit, $offset): array {
            $query = "SELECT * FROM eoi ORDER BY submitTime DESC LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        /**
              * Get EOIs by Job Reference

              * @param string $jobRef
              * @return array
        **/
        public function getEOIsByJobRef(string $jobRef): array {
            $query = "SELECT * FROM eoi WHERE jobRef = ? ORDER BY submitTime DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $jobRef);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        /**
              * Get EOIs by Applicant Name

              * @param string $firstName
              * @param string $lastName
              * @return array
        **/
        public function getEOIsByName(string $firstName, string $lastName): array {
            $query = "SELECT * FROM eoi WHERE firstName = ? OR lastName = ?"; // OR for only one firstname, lastname or for both:vv
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ss", $firstName, $lastName);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        /**
              * Get total number of EOIs

              * @return int
        **/
        public function getTotalEOIs(): int {
            $result = $this->conn->query("SELECT COUNT(*) as total FROM eoi");
            return (int)$result->fetch_assoc()['total'];
        }

        /**
              * Delete EOIs by Job Reference

              * @param string $jobRef
              * @return bool
        **/
        public function deleteEOIsByJobRef(string $jobRef): bool {
            $query = "DELETE FROM eoi WHERE jobRef = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $jobRef);
            return $stmt->execute();
        }

        /**
              * Update EOI Status

              * @param int $eoiNum
              * @param string $newStatus
              * @return bool
        **/
        public function updateEOIStatus(int $eoiNum, string $newStatus): bool {
            $query = "UPDATE eoi SET status = ?, updateTime = CURRENT_TIMESTAMP WHERE EOINum = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $newStatus, $eoiNum);
            return $stmt->execute();
        }

        // status validation method
        public static function isValidStatus($status): bool {
            return in_array(trim($status), self::VALID_STATUSES);
        }

        public function __destruct() {
            $this->conn->close();
        }
    }


    class EOIManagement {
        /**
            * Show more EOIs by increasing the limit.
            * Updates the session limit for the number of EOIs displayed.
            * If the new limit exceeds the total number of EOIs, it sets the limit to the total.
        *
            * @param int $limit Current limit of EOIs displayed.
            * @param int $totalEOIs Total number of EOIs available.
            * @return void
        **/
        public static function showMore($limit, $totalEOIs) {
            if ($limit + 5 <= $totalEOIs) {
                $_SESSION['limit'] = $limit + 5;
            } else {
                $_SESSION['limit'] = $totalEOIs;
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    
        /**
            * Show less EOIs by decreasing the limit.
            * Updates the session limit for the number of EOIs displayed.
            * If the new limit is less than or equal to zero, it sets the limit to 5.
        *
            * @param int $limit Current limit of EOIs displayed.
            * @return void
        **/
        public static function showLess($limit) {
            if ($limit - 5 > 0) {
                $_SESSION['limit'] = $limit - 5;
            } else {
                $_SESSION['limit'] = 5;
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    
        /**
            * Reset the session data for a new update.
            * Clears the filter, results, and total EOIs from the session,
            * effectively resetting the view to show all EOIs.
        *
            * @return void
        **/
        public static function newUpdate() {
            unset($_SESSION['filter']);
            unset($_SESSION['results']);
            unset($_SESSION['totalEOIs']);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    
        /**
            * Filter EOIs by Job Reference.
            * Gets EOIs based on the provided job reference and updates
            * the session with the results and total count.
        *
            * @param EOIManager $manager Instance of EOIManager to interact with the database.
            * @param string $jobRef Job reference to filter EOIs.
            * @return void
        **/
        public static function filterByJobRef($manager, $jobRef) {
            $jobRef = htmlspecialchars($jobRef);
            $_SESSION['results'] = $manager->getEOIsByJobRef($jobRef);
            $_SESSION['totalEOIs'] = count($_SESSION['results']);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    
        /**
            * Filter EOIs by Applicant Name.
            * Gets EOIs based on the provided first and last name
            * and updates the session with the results and total count.
        *
            * @param EOIManager $manager Instance of EOIManager to interact with the database.
            * @param string $firstName First name of the applicant.
            * @param string $lastName Last name of the applicant.
            * @return void
        **/
        public static function filterByName($manager, $firstName, $lastName) {
            $firstName = htmlspecialchars($firstName);
            $lastName = htmlspecialchars($lastName);
            $_SESSION['results'] = $manager->getEOIsByName($firstName, $lastName);
            $_SESSION['totalEOIs'] = count($_SESSION['results']);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    
        /**
            * Delete EOIs by Job Reference.
            * Deletes EOIs associated with the provided job reference
            * and resets the session for a new update.
        *
            * @param EOIManager $manager Instance of EOIManager to interact with the database.
            * @param string $jobRefToDelete Job reference to delete EOIs.
            * @return void
        **/
        public static function deleteByJobRef($manager, $jobRefToDelete) {
            $jobRefToDelete = htmlspecialchars($jobRefToDelete);
            $manager->deleteEOIsByJobRef($jobRefToDelete);
            self::newUpdate();
        }
    
        /**
            * Update the status of an EOI.
            * Updates the status of an EOI identified by its number.
            * It validates the new status before updating and handles errors accordingly.
        *
            * @param EOIManager $manager Instance of EOIManager to interact with the database.
            * @param int $eoiNum EOI number to update.
            * @param string $newStatus New status to set for the EOI.
            * @return void
        **/
        public static function updateStatus($manager, $eoiNum, $newStatus) {
            if (!empty($eoiNum) && !empty($newStatus)) {
                if (EOIManager::isValidStatus(htmlspecialchars($newStatus))) {
                    $manager->updateEOIStatus((int)$eoiNum, $newStatus);
                    self::newUpdate(); // self is EOIManagement class:vv
                } else {
                    $_SESSION['error'] = "Invalid status value";
                }
            } else {
                $_SESSION['error'] = "Missing required fields";
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }


    $manager = new EOIManager();
    $limit = isset($_SESSION['limit']) ? $_SESSION['limit'] : 10;
    $offset = 0;
    
    $results = isset($_SESSION['results']) ? $_SESSION['results'] : $manager->getAllEOIs($limit, $offset);
    $totalEOIs = isset($_SESSION['totalEOIs']) ? $_SESSION['totalEOIs'] : $manager->getTotalEOIs();

    // Handle form submissions based on the action specified
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'showMore':
                EOIManagement::showMore($limit, $totalEOIs);
                break;
    
            case 'showLess':
                EOIManagement::showLess($limit);
                break;
    
            case 'newUpdate':
                EOIManagement::newUpdate();
                break;
    
            case 'filterByJobRef':
                $_SESSION['filter'] = "Job Reference: " . $_POST['jobRef'];
                EOIManagement::filterByJobRef($manager, $_POST['jobRef']);
                break;
    
            case 'filterByName':
                $_SESSION['filter'] = "Name: " . $_POST['firstName'] . ' ' . $_POST['lastName'];
                EOIManagement::filterByName($manager, $_POST['firstName'], $_POST['lastName']);
                break;
    
            case 'deleteByJobRef':
                EOIManagement::deleteByJobRef($manager, $_POST['jobRefToDelete']);
                break;
    
            case 'updateStatus':
                EOIManagement::updateStatus($manager, $_POST['eoiNum'], $_POST['newStatus']);
                break;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EOI Management</title>
    <link rel="stylesheet" href="styles/style_manage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="management-container">
        <h1>EOI Management System</h1>

        <div class="query-section">
            <div class="tableHeading-container">
                <h2>EOIs</h2>
                <form method="post" class="new_update_btn">
                    <input type="hidden" name="action" value="newUpdate">
                    <button type="submit"><i class="fas fa-sync"></i> New Update</button>
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
            <form method="post" class="delete-jobref">
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
