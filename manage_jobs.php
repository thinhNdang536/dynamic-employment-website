<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management - JobsTime</title>
    <link rel="stylesheet" href="styles/style_dashboard.css">
</head>
<body>
    <?php
        session_start();
        require_once 'settings.php';

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

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

        class JobManager {
            public $conn;
            
            public function __construct() {
                $db = new Database();
                $this->conn = $db->getConnection();
                $this->conn->autocommit(false);
            }
            
            public function getAllJobs() {
                $query = "SELECT *, 
                        CASE 
                            WHEN active = 1 THEN 'Active'
                            ELSE 'Inactive'
                        END as status 
                        FROM jobs 
                        ORDER BY title";
                $result = $this->conn->query($query);
                return $result->fetch_all(MYSQLI_ASSOC);
            }
            
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
                    
                    // Commit the transaction
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
            
            public function getJobSummary() {
                return [
                    'total' => $this->conn->query("SELECT COUNT(*) as count FROM jobs")->fetch_assoc()['count'],
                    'active' => $this->conn->query("SELECT COUNT(*) as count FROM jobs WHERE active = true")->fetch_assoc()['count']
                ];
            }

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

            public function updateJob($jobRef, $data) {
                try {
                    // $this->conn->begin_transaction();
                    
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
                    
                    // Fix: Use correct number of 's' and 'd' for types
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
                    
                    // $this->conn->commit();
                    return true;
                    
                } catch (Exception $e) {
                    error_log("Update job error: " . $e->getMessage());
                    $this->conn->rollback();
                    return false;
                }
            }

            public function deleteJob($jobRef) {
                $query = "DELETE FROM jobs WHERE jobRef = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("s", $jobRef);
                return $stmt->execute();
            }

            public function getJob($jobRef) {
                $query = "SELECT * FROM jobs WHERE jobRef = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("s", $jobRef);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc();
            }
            
            public function __destruct() {
                // Ensure connection is closed properly
                if ($this->conn) {
                    $this->conn->close();
                }
            }
        }

        $manager = new JobManager();
        $jobs = $manager->getAllJobs();
        $message = '';
        $error = '';
        $editJob = null;

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
                        'salaryMin' => (float)$_POST['salaryMin'],
                        'salaryMax' => (float)$_POST['salaryMax'],
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
                        $editJob = $manager->getJob($_POST['jobRef']); // Keep form open with data
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
                        // Force refresh of jobs list from database
                        $jobs = $manager->getAllJobs();
                    } else {
                        $error = "Failed to update job status.";
                    }
                    break;

                case 'cancel':
                    $editJob = null;
                    // Refresh the jobs list
                    $jobs = $manager->getAllJobs();
                    break;
            }
        }
    ?>
    <div class="management-container">
        <h1>Job Management</h1>
        
        <?php if ($message): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <div class="job-form-container">
            <?php if (!$editJob): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="show_form">
                    <button type="submit" name="create" class="add-job-btn">Add New Job</button>
                </form>
            <?php endif; ?>
            
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
                        <form method="POST">
                            <!-- Action for submitting the job -->
                            <input type="hidden" name="action" value="<?php echo $editJob ? 'update' : 'create'; ?>">
                            
                            <!-- Include the job reference if it's an update -->
                            <?php if ($editJob): ?>
                                <input type="hidden" name="jobRef" value="<?php echo htmlspecialchars($editJob['jobRef']); ?>">
                            <?php endif; ?>
                            
                            <!-- Submit Button (either Update or Create) -->
                            <button type="submit" class="save-btn">
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

    <script>
    function toggleJobForm() {
        const form = document.getElementById('jobForm');
        form.classList.toggle('show');
    }

    function resetForm() {
        const form = document.getElementById('jobForm');
        form.reset();
        form.classList.remove('show');
        form.action.value = 'create';
    }
    </script>
</body>
</html>