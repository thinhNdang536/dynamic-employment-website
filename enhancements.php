<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="styles/style_enhancements.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/style_index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Enhancements</title>
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

    <!-- CONTENT OF THE PAGE -->
    <div class="container">
        <!-- Overall Enhancements Section -->
        <div class="enhancements-section">
            <h2 class="enhancements-title">Overall Enhancements</h2>
            <p class="enhancements-description">
                We have made several enhancements to improve the user experience and visual appeal of the website. Here are the key improvements:
            </p>
            <ul class="enhancements-list">
                <li>Improved color scheme for better contrast and readability.</li>
                <li>Enhanced typography for clearer text presentation.</li>
                <li>Increased spacing for a cleaner layout and better organization.</li>
                <li>Interactive hover effects for navigation items and buttons.</li>
                <li>Modern box shadows for a more contemporary look.</li>
                <li>Smooth transitions for a more fluid user experience.</li>
            </ul>
        </div>

        <p id="ptitle">Enhancements</p>
        <div class="boxdesign">
            <!-- Box 1 -->
            <div class="box" id="indexpage">
                <h2 class="boxheader">Index Page</h2>
                <div class="box-info">
                    <ul>
                        <li><a href="index.html">Open Animation and Navigation Bar Animation</a></li>
                        <li><a href="index.html#indexpicture">Pictures Animation</a></li>
                    </ul>
                </div>
            </div>

            <!-- Box 2 -->
            <div class="box" id="aboutpage">
                <h2 class="boxheader">About Page</h2>
                <div class="box-info">
                    <ul>
                        <li><a href="about.html">Open Animation and Navigation Bar Animation</a></li>
                        <li><a href="about.html#homepage-designer">Cards animation</a></li>
                    </ul>
                </div>
            </div>

            <!-- Box 3 -->
            <div class="box" id="jobpage">
                <h2 class="boxheader">Job Page</h2>
                <div class="box-info">
                    <ul>
                        <li><a href="jobs.html">Open Animation and Navigation Bar Animation</a></li>
                        <li><a href="jobs.html#joblist">List Animation</a></li>
                    </ul>
                </div>
            </div>

            <!-- Box 4 -->
            <div class="box" id="apply">
                <h2 class="boxheader">Apply Page</h2>
                <div class="box-info">
                    <ul>
                        <li><a href="apply.html">Open Animation and Navigation Bar Animation</a></li>
                        <li><a href="apply.html#buttonani">Button Animation</a></li>
                    </ul>
                </div>
            </div>
        </div>
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
