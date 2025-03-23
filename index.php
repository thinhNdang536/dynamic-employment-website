<?php
    session_start();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="styles/style_index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>JobsTime - Dashboard</title>
  </head>
  <body>
    <!-- HEADER SECTION -->
    <header class="header">
        <!-- Logo Section -->
        <div class="header-logo">
            <p>JOBS</p>
            <img src="assets/img/home/logo.png" alt="Logo Image">
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

    <!-- BANNER SECTION -->
    <div class="banner-container">
        <div class="banner-content">
            <p class="banner-sub-title">Position Seeker</p>
            <p class="banner-main-title">Decide Your Future - Discover Your Dream Job.</p>
            <p class="banner-description">
                Unlock your potential and shape your future with the perfect job. Explore a wide range of career opportunities, discover your dream role, and take the first step toward a fulfilling career today.
            </p>
            <div class="banner-buttons">
                <a href="jobs.php" class="banner-btn-primary">Get Started</a>
                <a href="about.php" class="banner-btn-success">Learn more</a>
            </div>
        </div>
        <img src="assets/img/home/banner.jpeg" alt="Banner Image">
    </div>

    <!-- HORIZONTAL LINE DIVIDER -->
    <div class="divider-container">
        <div class="hr"></div>
    </div>

    <!-- CONTENT SECTION -->
    <main class="main-container">
        <!-- INTRO SECTION -->
        <div class="intro-container">
            <img src="assets/img/home/img_01.jpg" alt="Intro Image">
            <div class="intro-content-container">
                <div class="intro-content-animation">
                    <p class="intro-title">Elevate Your Hiring Prospects</p>
                    <p class="intro-description">
                        Enhance your career trajectory with our expert job search strategies, tailored to unlock opportunities in Sai Gon City.
                    </p>
                </div>
            </div>
        </div>

        <!-- HORIZONTAL LINE DIVIDER -->
        <div class="divider-container">
            <div class="hr"></div>
        </div>

        <!-- MENU SECTION -->
        <div class="menu-container">
            <div class="menu-header">
                <p class="header-title">Refine Job Search Skills</p>
                <p class="header-description">Enhance your career prospects with tailored job search strategies, designed to streamline and focus your employment journey.</p>
            </div>
        
            <!-- MENU ROWS -->
            <div class="menu-row-container">
                <div class="menu-description-container" id="indexpicture">
                    <a href="jobs.php" class="menu-link">
                        <img src="assets/img/home/img_02.jpg" alt="Personalized Resume Optimization">
                        <p class="card-title">Personalized Resume Optimization</p>
                        <p class="card-description">Enhance your resume with tailored strategies that highlight strengths and attract employers.</p>
                    </a>
                </div>
                <div class="menu-description-container second-row-container">
                    <a href="jobs.php" class="menu-link">
                        <img src="assets/img/home/img_03.jpg" alt="Interview Mastery Sessions">
                        <p class="card-title">Interview Mastery Sessions</p>
                        <p class="card-description">Gain confidence and excel at interviews with our expert-led practice and feedback sessions.</p>
                    </a>
                </div>
            </div> 
            <div class="menu-row-container second-column-container">
                <div class="menu-description-container">
                    <a href="jobs.php" class="menu-link">
                        <img src="assets/img/home/img_04.jpg" alt="Personalized Resume Optimization">
                        <p class="card-title">Targeted Job Market Insights</p>
                        <p class="card-description">Stay ahead with access to exclusive job market trends and insights specific to your industry.</p>
                    </a>
                </div>
                <div class="menu-description-container second-row-container">
                    <a href="jobs.php" class="menu-link">
                        <img src="assets/img/home/img_05.jpg" alt="Interview Mastery Sessions">
                        <p class="card-title">Strategic Skill Development Plans</p>
                        <p class="card-description">Advance your career with customized plans to acquire in-demand skills and achieve goals.</p>
                    </a>
                </div>
            </div>
        </div>
        <a href="https://youtu.be/5mZKol24ddY">Youtube Video</a>
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
