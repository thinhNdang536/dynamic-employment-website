<?php
    /**
        * Job Management and Administration
        *
        * This file contains the JobManager class for managing job records within the JobsTime system.
        * It includes functionality for retrieving all jobs, toggling job statuses, creating, updating,
        * and deleting jobs, as well as summarizing job statistics.
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
    require_once 'settings.php'; //Import model from settings.php

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
        * JobManager Class
        *
        * This class handles the management of job records including:
        * - Retrieving a list of all jobs.
        * - Toggling the active/inactive status of a job.
        * - Summarizing job statistics.
        * - Creating, updating, and deleting job records.
    */
    class JobManager {
        public $conn;
        
        public function __construct() {
            $db = new Database();
            $this->conn = $db->getConnection();
            $this->conn->autocommit(false);
        }
        
        /**
            * Retrieves all jobs from the database.
            *
            * @return array Returns an associative array of job records.
        */
        public function getAllJobs() {
            $query = "SELECT    *, 
                        CASE 
                            WHEN active = 1 THEN 'Active'
                            ELSE 'Inactive'
                        END as status 
                    FROM jobs 
                    ORDER BY title";
            $result = $this->conn->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        
        /**
            * Toggles the status of a job (active/inactive).
            *
            * This method uses a transaction to ensure data integrity. It checks if the job exists,
            * retrieves the current status, and then updates it accordingly.
            *
            * @param string $jobRef The reference identifier for the job.
            *
            * @return bool Returns true on success or false on failure.
        */
        public function toggleJobStatus($jobRef) {
            try {
                $this->conn->begin_transaction();
                
                // First check if job exists and get current status
                $checkQuery = "SELECT active FROM jobs WHERE jobRef = ? FOR UPDATE";
                $checkStmt = $this->conn->prepare($checkQuery);
                if (!$checkStmt) {
                    throw new Exception("Failed to prepare check query: " . $this->conn->error);
                }
                
                $checkStmt->bind_param("s", $jobRef);
                $checkStmt->execute();
                $result = $checkStmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception("Job not found");
                }
                
                $job = $result->fetch_assoc();
                $newStatus = $job['active'] ? 0 : 1;
                
                // Update the status
                $updateQuery = "UPDATE jobs SET active = ? WHERE jobRef = ?";
                $updateStmt = $this->conn->prepare($updateQuery);
                if (!$updateStmt) {
                    throw new Exception("Failed to prepare update query: " . $this->conn->error);
                }
                
                $updateStmt->bind_param("is", $newStatus, $jobRef);
                if (!$updateStmt->execute()) {
                    throw new Exception("Failed to update status: " . $updateStmt->error);
                }
                
                // Commit the transaction, very important for changes:vvv
                $this->conn->commit();
                return true;
                
            } catch (Exception $e) {
                $this->conn->rollback();
                error_log("Toggle status error: " . $e->getMessage());
                return false;
            } finally {
                if (isset($checkStmt)) $checkStmt->close();
                if (isset($updateStmt)) $updateStmt->close();
            }
        }
        
        /**
            * Provides a summary of job statistics.
            *
            * @return array Returns an associative array containing the total number of jobs and the count of active jobs.
        */
        public function getJobSummary() {
            return [
                'total' => $this->conn->query("SELECT COUNT(*) as count FROM jobs")->fetch_assoc()['count'],
                'active' => $this->conn->query("SELECT COUNT(*) as count FROM jobs WHERE active = true")->fetch_assoc()['count']
            ];
        }

        /**
            * Creates a new job record in the database.
            *
            * Always creates a new job as inactive initially.
            *
            * @param array $data An associative array containing job data.
            *
            * @return bool Returns true on successful creation, false otherwise.
        */
        public function createJob($data) {
            try {
                $this->conn->begin_transaction();
                
                // Always start as inactive
                $active = 0;
                
                $query = "INSERT INTO jobs (jobRef, title, reportTo, description, salaryMin, salaryMax, 
                                                essentialReqs, preferableReqs, responsibilities, image, active) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("ssssddssssi", 
                    $data['jobRef'],
                    $data['title'],
                    $data['reportTo'],
                    $data['description'],
                    $data['salaryMin'],
                    $data['salaryMax'],
                    $data['essentialReqs'],
                    $data['preferableReqs'],
                    $data['responsibilities'],
                    $data['image'],
                    $active
                );
                
                $success = $stmt->execute();
                
                if ($success) {
                    $this->conn->commit();
                    return true;
                } else {
                    $this->conn->rollback();
                    return false;
                }
            } catch (Exception $e) {
                $this->conn->rollback();
                return false;
            }
        }

        /**
            * Updates an existing job record.
            *
            * @param string $jobRef The reference identifier of the job to update.
            * @param array  $data   An associative array containing updated job data.
            *
            * @return bool Returns true if the job was updated successfully, false otherwise.
        */
        public function updateJob($jobRef, $data) {
            try {
                $query = "UPDATE jobs SET 
                            title = ?, 
                            reportTo = ?, 
                            description = ?, 
                            salaryMin = ?, 
                            salaryMax = ?, 
                            essentialReqs = ?, 
                            preferableReqs = ?, 
                            responsibilities = ?, 
                            image = ? 
                        WHERE jobRef = ?";
                
                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    error_log("Prepare failed: " . $this->conn->error);
                    return false;
                }
                
                $stmt->bind_param("sssddsssss",
                    $data['title'],
                    $data['reportTo'],
                    $data['description'],
                    $data['salaryMin'],
                    $data['salaryMax'],
                    $data['essentialReqs'],
                    $data['preferableReqs'],
                    $data['responsibilities'],
                    $data['image'],
                    $jobRef
                );
                
                if (!$stmt->execute()) {
                    error_log("Execute failed: " . $stmt->error);
                    $this->conn->rollback();
                    return false;
                }
                
                if ($stmt->affected_rows === 0) {
                    error_log("No rows affected. JobRef: " . $jobRef);
                    $this->conn->rollback();
                    return false;
                }
                
                $this->conn->commit(); // dont never forget to commit:vvv
                return true;
                
            } catch (Exception $e) {
                error_log("Update job error: " . $e->getMessage());
                $this->conn->rollback();
                return false;
            }
        }

        /**
            * Deletes a job record.
            *
            * @param string $jobRef The reference identifier of the job to delete.
            *
            * @return bool Returns true if the deletion was successful, false otherwise.
        */
        public function deleteJob($jobRef) {
            try {
                $query = "DELETE FROM jobs WHERE jobRef = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("s", $jobRef);

                $success = $stmt->execute();
                if ($success) {
                    $this->conn->commit();
                    return true;
                } else {
                    $this->conn->rollback();
                    return false;
                }
                return true;
            } catch (Exception $e) {
                error_log("Update job error: " . $e->getMessage());
                $this->conn->rollback();
                return false;
            }
        }

        /**
            * Retrieves a single job record.
            *
            * @param string $jobRef The reference identifier of the job.
            *
            * @return array|null Returns an associative array of the job record or null if not found.
        */
        public function getJob($jobRef) {
            $query = "SELECT    * FROM jobs WHERE jobRef = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $jobRef);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }
        
        /**
            * Destructor.
            *
            * Ensures the database connection is closed properly.
            *
            * @return void
        */
        public function __destruct() {
            if ($this->conn) {
                $this->conn->close();
            }
        }
    }

    // Init db and create necessary vars
    $manager = new JobManager();
    $jobs = $manager->getAllJobs();
    $message = '';
    $error = '';
    $editJob = null;

    // Handle form submission, such as adding or editing a job;>
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($_POST['action']) {
            case 'create':
                $data = [
                    'jobRef' => $_POST['jobRef'],
                    'title' => $_POST['title'],
                    'reportTo' => $_POST['reportTo'],
                    'description' => $_POST['description'],
                    'salaryMin' => $_POST['salaryMin'],
                    'salaryMax' => $_POST['salaryMax'],
                    'essentialReqs' => json_encode(explode("\n", trim($_POST['essentialReqs']))),
                    'preferableReqs' => json_encode(explode("\n", trim($_POST['preferableReqs']))),
                    'responsibilities' => json_encode(explode("\n", trim($_POST['responsibilities']))),
                    'image' => $_POST['image'],
                    'active' => 1
                ];
                
                if ($manager->createJob($data)) {
                    $message = "Job created successfully.";
                    $jobs = $manager->getAllJobs();
                } else {
                    $error = "Failed to create job.";
                }
                break;

            case 'update':
                $data = [
                    'title' => trim($_POST['title']),
                    'reportTo' => trim($_POST['reportTo']),
                    'description' => trim($_POST['description']),
                    'salaryMin' => (float)$_POST['salaryMin'], // just for ensuring no error:vv
                    'salaryMax' => (float)$_POST['salaryMax'], // just for ensuring no error:vv
                    'essentialReqs' => json_encode(array_filter(explode("\n", trim($_POST['essentialReqs'])), 'strlen')),
                    'preferableReqs' => json_encode(array_filter(explode("\n", trim($_POST['preferableReqs'])), 'strlen')),
                    'responsibilities' => json_encode(array_filter(explode("\n", trim($_POST['responsibilities'])), 'strlen')),
                    'image' => trim($_POST['image'])
                ];
                
                error_log("Attempting to update job: " . $_POST['jobRef']);
                
                if ($manager->updateJob($_POST['jobRef'], $data)) {
                    $message = "Job updated successfully.";
                    $editJob = null; // Clear edit mode
                    $jobs = $manager->getAllJobs(); // Refresh list
                } else {
                    $error = "Failed to update job. Please check the error log.";
                    $editJob = $manager->getJob($_POST['jobRef']); // Keep form open with data:v
                }
                break;

            case 'delete':
                if ($manager->deleteJob($_POST['jobRef'])) {
                    $message = "Job deleted successfully.";
                    $jobs = $manager->getAllJobs();
                } else {
                    $error = "Failed to delete job.";
                }
                break;

            case 'edit':
                $editJob = $manager->getJob($_POST['jobRef']);
                break;
                
            case 'toggle_status':
                if ($manager->toggleJobStatus($_POST['jobRef'])) {
                    $message = "Job status updated successfully.";
                    $jobs = $manager->getAllJobs();
                } else {
                    $error = "Failed to update job status.";
                }
                break;

            case 'cancel':
                $editJob = null;
                $jobs = $manager->getAllJobs();
                break;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management - JobsTime</title>
    <link rel="stylesheet" href="styles/style_index.css">
    <link rel="stylesheet" href="styles/style_dashboard.css">
    <link rel="stylesheet" href="styles/style_manage_job.css">
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
        <h1>Job Management</h1>
        
        <!-- Display message if found -->
        <?php if ($message): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        
        <!-- Display error if found -->
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <div class="job-form-container">
            <!-- Create job btn -->
            <?php if (!$editJob): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="show_form">
                    <button type="submit" name="create" class="add-job-btn">Add New Job</button>
                </form>
            <?php endif; ?>
            
            <!-- Kinda complicated:vv, but for easy understanding. If editJob is set, show job data to update form, else show blank form inputs -->
            <?php if ($editJob || isset($_POST['action']) && $_POST['action'] === 'show_form'): ?>
                <form method="POST" class="job-form show">
                    <input type="hidden" name="action" value="<?php echo $editJob ? 'update' : 'create'; ?>">
                    
                    <div class="form-group">
                        <label for="jobRef">Job Reference:</label>
                        <input type="text" id="jobRef" name="jobRef" value="<?php echo $editJob ? htmlspecialchars($editJob['jobRef']) : ''; ?>" 
                               <?php echo $editJob ? 'readonly' : ''; ?> required>
                    </div>

                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title" value="<?php echo $editJob ? htmlspecialchars($editJob['title']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="reportTo">Reports To:</label>
                        <input type="text" id="reportTo" name="reportTo" value="<?php echo $editJob ? htmlspecialchars($editJob['reportTo']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" required><?php echo $editJob ? htmlspecialchars($editJob['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="salaryMin">Minimum Salary:</label>
                            <input type="number" id="salaryMin" name="salaryMin" value="<?php echo $editJob ? htmlspecialchars($editJob['salaryMin']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="salaryMax">Maximum Salary:</label>
                            <input type="number" id="salaryMax" name="salaryMax" value="<?php echo $editJob ? htmlspecialchars($editJob['salaryMax']) : ''; ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="essentialReqs">Essential Requirements (one per line):</label>
                        <textarea id="essentialReqs" name="essentialReqs" required><?php echo $editJob ? implode("\n", json_decode($editJob['essentialReqs'], true)) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="preferableReqs">Preferable Requirements (one per line):</label>
                        <textarea id="preferableReqs" name="preferableReqs" required><?php echo $editJob ? implode("\n", json_decode($editJob['preferableReqs'], true)) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="responsibilities">Responsibilities (one per line):</label>
                        <textarea id="responsibilities" name="responsibilities" required><?php echo $editJob ? implode("\n", json_decode($editJob['responsibilities'], true)) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Image Path:</label>
                        <input type="text" id="image" name="image" value="<?php echo $editJob ? htmlspecialchars($editJob['image']) : ''; ?>" required>
                    </div>

                    <div class="form-buttons">
                        <!-- If editing a job, display the update btn, else just create btn=)) -->
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $editJob ? 'update' : 'create'; ?>">
                            <?php if ($editJob): ?>
                                <input type="hidden" name="jobRef" value="<?php echo htmlspecialchars($editJob['jobRef']); ?>">
                            <?php endif; ?>
                            <button type="submit" class="save-btn" >
                                <?php echo $editJob ? 'Update' : 'Create'; ?> Job
                            </button>
                        </form>

                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="cancel">
                            <?php if ($editJob): ?>
                                <input type="hidden" name="jobRef" value="<?php echo htmlspecialchars($editJob['jobRef']); ?>">
                            <?php endif; ?>
                            <button type="submit" class="cancel-btn">Cancel</button>
                        </form>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <table class="jobs-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Title</th>
                    <th>Salary Range</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($job['jobRef']); ?></td>
                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                        <td>$<?php echo number_format($job['salaryMin']); ?> - $<?php echo number_format($job['salaryMax']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $job['active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $job['active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="jobRef" value="<?php echo htmlspecialchars($job['jobRef']); ?>">
                                    <button type="submit" class="action-btn <?php echo $job['active'] ? 'deactivate' : 'activate'; ?>">
                                        <?php echo $job['active'] ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>

                                <!-- Edit is update job:vv -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="jobRef" value="<?php echo htmlspecialchars($job['jobRef']); ?>">
                                    <button type="submit" class="action-btn edit">Edit</button>
                                </form>

                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this job?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="jobRef" value="<?php echo htmlspecialchars($job['jobRef']); ?>">
                                    <button type="submit" class="action-btn delete">Delete</button>
                                </form>
                            </div>
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
