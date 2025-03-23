<?php
require_once 'settings.php';

class JobListing {
    private $conn;
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    
    public function getAllJobs() {
        $query = "SELECT * FROM jobs ORDER BY title";
        $result = $this->conn->query($query);
        $jobs = $result->fetch_all(MYSQLI_ASSOC);
        
        // Debug logging
        foreach ($jobs as $job) {
            error_log("Job: " . $job['jobRef']);
            error_log("Essential Reqs: " . $job['essentialReqs']);
            error_log("Preferable Reqs: " . $job['preferableReqs']);
            error_log("Responsibilities: " . $job['responsibilities']);
        }
        
        return $jobs;
    }
}

$jobListing = new JobListing();
$jobs = $jobListing->getAllJobs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>
    <link rel="stylesheet" href="styles/style_jobs.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- HEADER SECTION -->
        <header class="header">
            <!-- Logo Section -->
            <div class="header-logo">
                <p>JOBS</p>
                <img src="assets/img/home/logo.png" alt="Logo Image">
                <p>IME</p>
            </div>
            
            <!-- Navigation Bar -->
            <nav class="nav-bar">
                <a href="index.html" class="nav-item">
                    <p class="nav-main-item">Home</p>
                    <p class="nav-sub-item">Main page</p>
                </a>
                <a href="about.html" class="nav-item">
                    <p class="nav-main-item">About</p>
                    <p class="nav-sub-item">More information</p>
                </a>
                <a href="jobs.html" class="nav-item">
                    <p class="nav-main-item">Jobs</p>
                    <p class="nav-sub-item">Find opportunities</p>
                </a>
                <a href="apply.html" class="nav-item">
                    <p class="nav-main-item">Apply</p>
                    <p class="nav-sub-item">Send applications</p>
                </a>
                <a href="enhancements.html" class="nav-item" id="last-item">
                    <p class="nav-main-item">Enhancements</p>
                    <p class="nav-sub-item">Feedback and Suggestions</p>
                </a>
            </nav>
        </header>

        <!-- Main Content Section -->
        <main class="main-content">
            <h1>Join Us:</h1>
            <?php if (empty($jobs)): ?>
                <div class="job-container">
                    <div class="job-grid">
                        <div class="job-listing no-jobs">
                            <!-- No jobs message -->
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="job-container">
                    <div class="job-grid">
                        <?php foreach ($jobs as $job): ?>
                            <div class="job-listing">
                                <img src="<?php echo 'assets/img/job/'. htmlspecialchars($job['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($job['title']); ?>">
                                <section class="job-header">
                                    <h2><?php echo htmlspecialchars($job['title']); ?></h2>
                                    <p class="job-ref">(<?php echo htmlspecialchars($job['jobRef']); ?>)</p>
                                    <h4>Reports to: <?php echo htmlspecialchars($job['reportTo']); ?></h4>
                                </section>
                                <!-- Rest of the job content -->
                            </div>
                        <?php endforeach; ?>
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
                        <li><a href="index.html">Home</a></li>
                        <li><a href="about.html">About</a></li>
                        <li><a href="jobs.html">Job</a></li>
                        <li><a href="apply.html">Apply</a></li>
                        <li><a href="enhancements.html">Enhancements</a></li>
                    </ul>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
