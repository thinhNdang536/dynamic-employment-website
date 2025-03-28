<?php
	/**
		* Job Listing
		*
		* This file contains the JobListing class for retrieving job records from the database.
		* It includes a method for fetching all jobs with a dynamic status label based on the
		* active field.
		*
		* PHP version 8.2.12
		*
		* @category Management
		* @package  Assignment2
		* @author   Dang Quang Thinh & Nguyen Bach Tung
		* @version  1.0.0
	*/

    session_start(); //Must do=))
    require_once 'settings.php'; //Import db model from settings.php

	/**
		* JobListing Class
		*
		* Handles the retrieval of job listings from the database. It provides methods for
		* fetching all jobs, including a dynamically generated status field indicating whether
		* a job is active or inactive.
	*/
	class JobListing {
		private $conn;
		
		public function __construct() {
			$db = new Database();
			$this->conn = $db->getConnection();
		}
		
		/**
			* Get all jobs.
			*
			* Retrieves all job records from the database. The query dynamically generates a 
			* "status" field based on whether the job is active (1) or not.
			*
			* @return array An associative array of job records.
     	*/
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
    <link rel="stylesheet" href="styles/style_index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                <?php else: ?>
                    <a href="login.php" class="auth-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                    <a href="signup.php" class="auth-btn">
                        <i class="fas fa-user-plus"></i>
                        Sign up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

	<!-- Main Content Section -->
	<main class="main-content">
		<h1>Join Us:</h1>
		<?php if (empty($jobs)): ?>
			<div class="job-listing no-jobs">
				<img src="assets/img/job/no-jobs.jpg" width="200" height="200" alt="No Jobs Available">
				<section>
					<h2>No Positions Available</h2>
					<h4>Status: Unavailable</h4>
					<p class="job-description">We currently don't have any open positions. Please check back later for new opportunities.</p>
				</section>
				<aside>
					<section>
						<h2 class="salary">Salary Range:</h2>
						<p>Not Available</p>
					</section>
					<section>
						<h3>Requirements:</h3>
						<h4 class="requirement">Essentials:</h4>
						<ol>
							<li>No requirements listed</li>
						</ol>
						<h4 class="requirement">Preferable:</h4>
						<ol>
							<li>No preferences listed</li>
						</ol>
					</section>
					<section>
						<h3>Key Responsibilities:</h3>
						<ul>
							<li>No responsibilities listed</li>
						</ul>
					</section>
				</aside>
				<button class="apply-button" disabled>No Positions Available</button>
			</div>
		<?php else: ?>
			<div class="jobs-container">
				<?php foreach ($jobs as $job): ?>
					<div class="job-listing">
						<img src="<?php echo 'images/jobs/'. htmlspecialchars($job['image']); ?>" 
							width="200" height="200" 
							alt="<?php echo htmlspecialchars($job['title']); ?>">
						<section>
							<h2><?php echo htmlspecialchars($job['title']); ?> (<?php echo htmlspecialchars($job['jobRef']); ?>)</h2>
							<section>
								<h3><strong>Status:</strong></h3>
								<p><?php echo $job['active'] ? 'Active' : 'Inactive'; ?></p>
							</section>
							<h4>Reports to: <?php echo htmlspecialchars($job['reportTo']); ?></h4>
							<p class="job-description"><?php echo htmlspecialchars($job['description']); ?></p>
						</section>
						<aside>
							<section>
								<h2 class="salary">Salary Range:</h2>
								<p>$<?php echo number_format($job['salaryMin']); ?> - $<?php echo number_format($job['salaryMax']); ?></p>
							</section>
							<section>
								<h3>Requirements:</h3>
								<h4 class="requirement">Essentials:</h4>
								<ol>
									<?php 
									$essentials = json_decode($job['essentialReqs'], true);
									if (is_array($essentials)):
										foreach ($essentials as $req): 
									?>
										<li><?php echo htmlspecialchars($req); ?></li>
									<?php 
										endforeach;
									endif;
									?>
								</ol>
								<h4 class="requirement">Preferable:</h4>
								<ol>
									<?php 
									$preferables = json_decode($job['preferableReqs'], true);
									if (is_array($preferables)):
										foreach ($preferables as $req): 
									?>
										<li><?php echo htmlspecialchars($req); ?></li>
									<?php 
										endforeach;
									endif;
									?>
								</ol>
							</section>
							<section>
								<h3>Key Responsibilities:</h3>
								<ul>
									<?php 
									$responsibilities = json_decode($job['responsibilities'], true);
									if (is_array($responsibilities)):
										foreach ($responsibilities as $resp): 
									?>
										<li><?php echo htmlspecialchars($resp); ?></li>
									<?php 
										endforeach;
									endif;
									?>
								</ul>
							</section>
						</aside>
						<a href="apply.php?ref=<?php echo urlencode($job['jobRef']); ?>" class="apply-button">Apply Now</a>
					</div>
				<?php endforeach; ?>
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
					<li><a href="phpenhancements.php">PHP Enhancements</a></li>
				</ul>
			</div>
		</div>
	</footer>
</body>
</html>
