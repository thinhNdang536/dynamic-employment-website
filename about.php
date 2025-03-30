<?php
    session_start(); // Just for check if user logged in or not:vv nevermind
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Custom Stylesheet -->
    <link href="styles/style_about.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/style_index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>About Us</title>
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

    <!-- MAIN CONTENT SECTION -->
    <main class="main-container">
        <!-- Group Information Section -->
        <div class="group-name-container">
            <h1>GENTLE GUYS</h1>
        </div>        

        <!-- Group Details Section -->
        <div class="detail-container">
            <div>
                <dl>
                    <dt>Group ID:</dt>
                    <dd>???</dd>
                </dl>
                <dl>
                    <dt>Tutor's Name:</dt>
                    <dd>Tristan Nguyen</dd>
                </dl>
                <dl>
                    <dt>Contact us at:</dt>
                    <dd><a href="mailto:moiphuong2911@gmail.com">moiphuong2911@gmail.com</a></dd>
                </dl>
            </div>
            <figure>
                <img src="images/about/banner.jpg" alt="group-photo">
            </figure>
        </div>

        <!-- Divider -->
        <div class="divider-container">
            <div class="hr"></div>
        </div>

        <!-- Members Contribution Section -->
        <div class="contributions">
            <h2>Members Contribution to this Project:</h2>
            <div class="card-slider">
                <!-- Card 1: Home Page Designer -->
                <div class="card" id="homepage-designer">
                    <h3 class="card-header">Dang Quang Thinh</h3>
                    <p class="card-body">(ID: 105551875)</p>
                    <aside>
                        <h4>Contributions:</h4>
                        <ul>
                            <li>Create an EOI table</li>
                            <li>Adding validated records to the EOI table</li>
                            <li>Help create a file to store database connection variables</li>
                        </ul>
                    </aside>
                </div>
        
                <!-- Card 2: About Page Designer -->
                <div class="card" id="about-us-designer">
                    <h3 class="card-header">Nguyen Ho Minh Phuong</h3>
                    <p class="card-body">(ID: 105723111)</p>
                    <aside>
                        <h4>Contributions:</h4>
                        <ul>
                            <li>Use PHP to reuse common elements in Web site</li>
                            <li>Update about page</li>
                            <li>Help enhancements</li>
                        </ul>
                    </aside>
                </div>
        
                <!-- Card 3: Job Page Designer -->
                <div class="card" id="job-description-designer">
                    <h3 class="card-header">Nguyen Bach Tung</h3>
                    <p class="card-body">(ID: 105555424)</p>
                    <aside>
                        <h4>Contributions:</h4>
                        <ul>
                            <li>HR manager queries</li>
                            <li>Jobs Description</li>
                        </ul>
                    </aside>
                </div>
        
                <!-- Card 4: Apply Page Designer -->
                <div class="card" id="form-application-designer">
                    <h3 class="card-header">Nguyen Cong Quang Minh</h3>
                    <p class="card-body">(ID: 105680177)</p>
                    <aside>
                        <h4>Contributions:</h4>
                        <ul>
                            <li>Help create a file to store database connection variables</li>
                            <li>Help enhancements</li>
                        </ul>
                    </aside>
                </div>
            </div>
        </div>

        <!-- Timetable Section -->
        <table>
            <thead>
                <tr>
                    <th colspan="5">Our Timetable</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td>Dang Quang Thinh</td>
                    <td>Nguyen Cong Quang Minh</td>
                    <td>Nguyen Ho Minh Phuong</td>
                    <td>Nguyen Bach Tung</td>
                </tr>
                <tr>
                    <td>Monday</td>
                    <td>10:00 - 14:00</td>
                    <td>13:00 - 18:00</td>
                    <td>08:00 - 12:00</td>
                    <td>15:00 - 20:00</td>
                </tr>
                <tr>
                    <td>Tuesday</td>
                    <td>11:00 - 16:00</td>
                    <td>No Schedule</td>
                    <td>No Schedule</td>
                    <td>09:00 - 13:00</td>
                </tr>
                <tr>
                    <td>Wednesday</td>
                    <td>14:00 - 19:00</td>
                    <td>16:00 - 21:00</td>
                    <td>10:00 - 15:00</td>
                    <td>18:00 - 22:00</td>
                </tr>
                <tr>
                    <td>Thursday</td>
                    <td>No Schedule</td>
                    <td>13:00 - 18:00</td>
                    <td>09:00 - 14:00</td>
                    <td>14:00 - 19:00</td>
                </tr>
                <tr>
                    <td>Friday</td>
                    <td>15:00 - 20:00</td>
                    <td>17:00 - 21:00</td>
                    <td>12:00 - 17:00</td>
                    <td>09:00 - 13:00</td>
                </tr>
                <tr>
                    <td>Saturday</td>
                    <td>13:00 - 18:00</td>
                    <td>14:00 - 19:00</td>
                    <td>10:00 - 15:00</td>
                    <td>16:00 - 20:00</td>
                </tr>
                <tr>
                    <td>Sunday</td>
                    <td>No Schedule</td>
                    <td>No Schedule</td>
                    <td>No Schedule</td>
                    <td>No Schedule</td>
                </tr>
            </tbody>
        </table>
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
