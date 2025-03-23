<?php
    session_start();
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
    <main>
        <h1>Join Us:</h1>
        <div class="job-container">
            <!-- Job Listing: Marketing Manager -->
            <div class="job-listing">
                <img src="images/job/makerting_manager.jpg" width="200" height="200" alt="Marketing Manager">
                <section>
                    <h2>Marketing Manager (A1234)</h2>
                    <h4>Reports to: Marketing Director</h4>
                    <p class="job-description">We are looking for Marketing Managers that are responsible for developing, implementing, and executing strategic marketing plans for an entire organization. You should have good analytical skills to forecast and identify trends and challenges.</p>
                </section>
                <aside>
                    <section>
                        <h2 class="salary">Salary Range:</h2>
                        <p>$80,000 - $100,000</p>
                    </section>
                    <section>
                        <h3>Requirements:</h3>
                        <h4 class="requirement">Essentials:</h4>
                        <ol>
                            <li>Bachelor's degree in Marketing</li>
                            <li>7 years of marketing experience</li>
                            <li>Strong analytical skills</li>
                        </ol>
                        <h4 class="requirement">Preferable:</h4>
                        <ol>
                            <li>Digital marketing experience</li>
                            <li>Google Analytics knowledge</li>
                            <li>Project management skills</li>
                        </ol>
                    </section>
                    <section>
                        <h3>Key Responsibilities:</h3>
                        <ul>
                            <li>Create marketing strategies</li>
                            <li>Analyze market trends</li>
                            <li>Manage digital campaigns</li>
                        </ul>
                    </section>
                </aside>                                
                <a href="apply.php" class="apply-button">Apply Now</a>
            </div>

            <!-- Job Listing: Business Analyst -->
            <div class="job-listing">
                <img src="images/job/business_analyst.jpg" width="200" height="200" alt="Business Analyst">
                <section>
                    <h2>Business Analyst (B1234)</h2>
                    <h4>Reports to: Business Director</h4>
                    <p class="job-description">We are seeking a skilled and detail-oriented Business Analyst to join our team. The Business Analyst will be responsible for analyzing business processes, identifying areas for improvement, and developing strategies to enhance efficiency and productivity.</p>
                </section>
                <aside>
                    <section>
                        <h2 class="salary">Salary Range:</h2>
                        <p>$70,000 - $90,000</p>
                    </section>
                    <section>
                        <h3>Requirements:</h3>
                        <h4 class="requirement">Essentials:</h4>
                        <ol>
                            <li>Bachelor's degree in Business</li>
                            <li>2 years of analysis experience</li>
                            <li>Problem-solving skills</li>
                        </ol>
                        <h4 class="requirement">Preferable:</h4>
                        <ol>
                            <li>Agile experience</li>
                            <li>SQL skills</li>
                            <li>Data analysis knowledge</li>
                        </ol>
                    </section>
                    <section>
                        <h3>Key Responsibilities:</h3>
                        <ul>
                            <li>Analyze business processes</li>
                            <li>Gather requirements from stakeholders</li>
                            <li>Provide actionable insights</li>
                        </ul>
                    </section>
                </aside>                
                <a href="apply.php" class="apply-button">Apply Now</a>
            </div>

            <!-- Job Listing: Software Engineer -->
            <div class="job-listing">
                <img src="images/job/software_engineer.jpg" width="200" height="200" alt="Software Engineer">
                <section>
                    <h2>Software Engineer (C1234)</h2>
                    <h4>Reports to: Software Director</h4>
                    <p class="job-description">We are looking for programmers to design new software for companies, improve existing programs, and provide quality assurance for any upcoming initiatives developed in code.</p>
                </section>
                <aside>
                    <section>
                        <h2 class="salary">Salary Range:</h2>
                        <p>$90,000 - $120,000</p>
                    </section>
                    <section>
                        <h3>Requirements:</h3>
                        <h4 class="requirement">Essentials:</h4>
                        <ol>
                            <li>Bachelor's in Computer Science</li>
                            <li>2 years of development experience</li>
                            <li>Proficient in Java, Python, or C#</li>
                        </ol>
                        <h4 class="requirement">Preferable:</h4>
                        <ol>
                            <li>Version control skills</li>
                            <li>Cloud tech knowledge</li>
                            <li>Agile experience</li>
                        </ol>
                    </section>
                    <section>
                        <h3>Key Responsibilities:</h3>
                        <ul>
                            <li>Develop software solutions</li>
                            <li>Write efficient code</li>
                            <li>Collaborate on requirements</li>
                        </ul>
                    </section>
                </aside>                
                <a href="apply.php" class="apply-button">Apply Now</a>
            </div>

            <!-- Job Listing: Graphic Designer -->
            <div class="job-listing">
                <img src="images/job/graphic_designer.jpg" width="200" height="200" alt="Graphic Designer">
                <section>
                    <h2>Graphic Designer (D1234)</h2>
                    <h4>Reports to: Graphic Director</h4>
                    <p class="job-description">We are looking for a creative Graphic Designer to join our team. You will be responsible for creating visual concepts that communicate ideas that inspire, inform, or captivate consumers.</p>
                </section>
                <aside>
                    <section>
                        <h2 class="salary">Salary Range:</h2>
                        <p>$60,000 - $80,000</p>
                    </section>
                    <section>
                        <h3>Requirements:</h3>
                        <h4 class="requirement">Essentials:</h4>
                        <ol>
                            <li>Bachelor's in Graphic Design</li>
                            <li>3 years of design experience</li>
                            <li>Adobe Creative Suite skills</li>
                        </ol>
                        <h4 class="requirement">Preferable:</h4>
                        <ol>
                            <li>UI/UX design skills</li>
                            <li>Strong portfolio</li>
                            <li>Web design knowledge</li>
                        </ol>
                    </section>
                    <section>
                        <h3>Key Responsibilities:</h3>
                        <ul>
                            <li>Design print and digital media</li>
                            <li>Follow brand guidelines</li>
                            <li>Collaborate with marketing</li>
                        </ul>
                    </section>
                </aside>
                <a href="apply.php" class="apply-button">Apply Now</a>
            </div>

            <!-- Job Listing: Data Analyst -->
            <div class="job-listing">
                <img src="images/job/data_analyst.jpg" width="200" height="200" alt="Data Analyst">
                <section>
                    <h2>Data Analyst (E1234)</h2>
                    <h4>Reports to: Data Director</h4>
                    <p class="job-description">We are looking for a Data Analyst to help us make data-driven decisions. You will be responsible for collecting, processing, and analyzing data to help the company improve its operations.</p>
                </section>
                <aside>
                    <section>
                        <h2 class="salary">Salary Range:</h2>
                        <p>$75,000 - $95,000</p>
                    </section>
                    <section>
                        <h3>Requirements:</h3>
                        <h4 class="requirement">Essentials:</h4>
                        <ol>
                            <li>Bachelor's in Data Science</li>
                            <li>2 years of analysis experience</li>
                            <li>SQL & visualization skills</li>
                        </ol>
                        <h4 class="requirement">Preferable:</h4>
                        <ol>
                            <li>Machine learning knowledge</li>
                            <li>Python or R skills</li>
                            <li>Data warehousing experience</li>
                        </ol>
                    </section>
                    <section>
                        <h3>Key Responsibilities:</h3>
                        <ul>
                            <li>Analyze data trends</li>
                            <li>Create reports & dashboards</li>
                            <li>Ensure data consistency</li>
                        </ul>
                    </section>
                </aside>
                <a href="apply.php" class="apply-button">Apply Now</a>
            </div>

            <!-- Job Listing: Network Engineer -->
            <div class="job-listing">
                <img src="images/job/network_engineer.jpg" width="200" height="200" alt="Network Engineer">
                <section>
                    <h2>Network Engineer (F1234)</h2>
                    <h4>Reports to: Network Director</h4>
                    <p class="job-description">We are looking for a Network Engineer to design, implement, and maintain our network infrastructure. You will ensure the integrity of high availability network infrastructure to provide maximum performance.</p>
                </section>
                <aside>
                    <section>
                        <h2 class="salary">Salary Range:</h2>
                        <p>$80,000 - $100,000</p>
                    </section>
                    <section>
                        <h3>Requirements:</h3>
                        <h4 class="requirement">Essentials:</h4>
                        <ol>
                            <li>Bachelor's in Computer Science</li>
                            <li>3 years of network engineering</li>
                            <li>Knowledge of network protocols</li>
                        </ol>
                        <h4 class="requirement">Preferable:</h4>
                        <ol>
                            <li>Firewall & VPN experience</li>
                            <li>CCNA certification</li>
                            <li>Network monitoring skills</li>
                        </ol>
                    </section>
                    <section>
                        <h3>Key Responsibilities:</h3>
                        <ul>
                            <li>Design network infrastructure</li>
                            <li>Monitor network performance</li>
                            <li>Ensure network security</li>
                        </ul>
                    </section>
                </aside>
                <a href="apply.php" class="apply-button">Apply Now</a>
            </div>

            <!-- Job Listing: HR Manager -->
            <div class="job-listing">
                <img src="images/job/hr_manager.jpg" width="200" height="200" alt="HR Manager">
                <section>
                    <h2>HR Manager (G1234)</h2>
                    <h4>Reports to: HR Director</h4>
                    <p class="job-description">We are looking for an HR Manager to oversee all aspects of human resources practices and processes. You will support business needs and ensure the proper implementation of company strategy and objectives.</p>
                </section>
                <aside>
                    <section>
                        <h2 class="salary">Salary Range:</h2>
                        <p>$70,000 - $90,000</p>
                    </section>
                    <section>
                        <h3>Requirements:</h3>
                        <h4>Essentials:</h4>
                        <ol>
                            <li>Bachelor's in HR or related field</li>
                            <li>5 years in HR management</li>
                            <li>Knowledge of labor laws</li>
                        </ol>
                        <h4>Preferable:</h4>
                        <ol>
                            <li>Experience with HRIS</li>
                            <li>Certifications in HR management</li>
                            <li>Experience in conflict resolution</li>
                        </ol>
                    </section>
                    <section>
                        <h3>Key Responsibilities:</h3>
                        <ul>
                            <li>Manage HR operations</li>
                            <li>Implement HR strategies</li>
                            <li>Ensure legal compliance</li>
                        </ul>
                    </section>
                </aside>
                <a href="apply.php" class="apply-button">Apply Now</a>
            </div>

            <!-- Job Listing: Digital Marketer -->
            <div class="job-listing">
                <img src="images/job/digital_marketer.jpg" width="200" height="200" alt="Digital Marketer">
                <section>
                    <h2>Digital Marketer (H1234)</h2>
                    <h4>Reports to: Digital Director</h4>
                    <p class="job-description">We are seeking a Digital Marketer to develop, implement, and manage marketing campaigns that promote our products and services. You will enhance brand awareness within the digital space.</p>
                </section>
                <aside>
                    <section>
                        <h2 class="salary">Salary Range:</h2>
                        <p>$60,000 - $80,000</p>
                    </section>
                    <section>
                        <h3>Requirements:</h3>
                        <h4>Essentials:</h4>
                        <ol>
                            <li>Bachelor's in Marketing or related field</li>
                            <li>3 years in digital marketing</li>
                            <li>Proficiency in Google Analytics and SEO tools</li>
                        </ol>
                        <h4>Preferable:</h4>
                        <ol>
                            <li>Experience with paid ads</li>
                            <li>Knowledge of content marketing</li>
                            <li>Familiarity with email marketing tools</li>
                        </ol>
                    </section>
                    <section>
                        <h3>Key Responsibilities:</h3>
                        <ul>
                            <li>Manage online marketing campaigns</li>
                            <li>Analyze campaign performance</li>
                            <li>Enhance brand presence online</li>
                        </ul>
                    </section>
                </aside>
                <a href="apply.php" class="apply-button">Apply Now</a>
            </div>
        </div>
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
