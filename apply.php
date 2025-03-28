<?php
    session_start(); // Start the session for application and auth check

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?apply=error");
        exit();
    }

    // Get stored form data from session, very important:vv
    $form_data = $_SESSION['form_data'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="styles/style_apply.css">
    <link rel="stylesheet" href="styles/style_index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Application Form</title>
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
        <div class="divider-container">
            <div class="hr"></div>
        </div>

        <h1>Job Application Form</h1>
        <p>Complete this form to be a part of us!</p>

        <!-- Application Form action="https://mercury.swin.edu.au/it000000/formtest.php" -->
        <form novalidate=”novalidate”  method="POST" action="process_eoi.php">
            <!-- Personal Information Section -->
            <section class="form-section">
                <h2>Personal Information</h2>
                <div class="form-grid">
                    <div>
                        <label for="job_ref_num">Job Reference Number:</label>
                        <input type="text" id="job_ref_num" name="job_ref_num" placeholder="5 alphanumeric characters" maxlength="5" pattern="^[a-zA-Z0-9]{5}$" required
                            value="<?php echo isset($form_data['job_ref_num']) ? htmlspecialchars($form_data['job_ref_num']) : ''; ?>">
                    </div>
                    <div>
                        <label for="dob">Date of Birth:</label>
                        <input type="text" id="dob" name="dob" required
                            value="<?php echo isset($form_data['dob']) ? htmlspecialchars($form_data['dob']) : ''; ?>">
                    </div>
                    <div>
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Maximum: 20 alpha letters" maxlength="20" pattern="^[a-zA-Z\s]{}$" required
                            value="<?php echo isset($form_data['first_name']) ? htmlspecialchars($form_data['first_name']) : ''; ?>">
                    </div>
                    <div>
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Maximum: 20 alpha letters" maxlength="20" pattern="^[a-zA-Z\s]{}$" required
                            value="<?php echo isset($form_data['last_name']) ? htmlspecialchars($form_data['last_name']) : ''; ?>">
                    </div>
                    <div>
                        <fieldset>
                            <legend>Gender:</legend>
                            <!-- Adds the checked attr to the input tag based on the form data stored in the session.  -->
                            <label>
                                <input type="radio" id="male" name="gender" value="Male" 
                                    <?php echo isset($form_data['gender']) && $form_data['gender'] == 'Male' ? 'checked' : ''; ?> 
                                    required> Male
                            </label>
                            <label>
                                <input type="radio" id="female" name="gender" value="Female" 
                                    <?php echo isset($form_data['gender']) && $form_data['gender'] == 'Female' ? 'checked' : ''; ?> 
                                    required> Female
                            </label>
                            <label>
                                <input type="radio" id="other" name="gender" value="Other" 
                                    <?php echo isset($form_data['gender']) && $form_data['gender'] == 'Other' ? 'checked' : ''; ?> 
                                    required> Other
                            </label>
                        </fieldset>
                    </div>
                </div>
            </section>

            <!-- Contact Information Section -->
            <section class="form-section">
                <h2>Contact Information</h2>
                <div class="form-grid">
                    <div>
                        <label for="address">Street Address:</label>
                        <input type="text" id="address" name="address" maxlength="40" placeholder="Maximum: 40 letters" pattern="^[a-zA-Z0-9\s]{1,40}$" required
                            value="<?php echo isset($form_data['address']) ? htmlspecialchars($form_data['address']) : ''; ?>">
                    </div>
                    <div>
                        <label for="town">Suburb/Town:</label>
                        <input type="text" id="town" name="town" maxlength="40" placeholder="Maximum: 40 letters" pattern="^[a-zA-Z\s]{1,40}$" required
                            value="<?php echo isset($form_data['town']) ? htmlspecialchars($form_data['town']) : ''; ?>">
                    </div>
                    <div>
                        <!-- Same as above, adds the checked attr to the input tag based on the form data stored in the session.  -->
                        <label for="state">State:</label>
                        <select id="state" name="state" required>
                            <option value="">Select your State</option>
                            <option value="VIC" <?php echo isset($form_data['state']) && $form_data['state'] == 'VIC' ? 'selected' : ''; ?>>VIC</option>
                            <option value="NSW" <?php echo isset($form_data['state']) && $form_data['state'] == 'NSW' ? 'selected' : ''; ?>>NSW</option>
                            <option value="QLD" <?php echo isset($form_data['state']) && $form_data['state'] == 'QLD' ? 'selected' : ''; ?>>QLD</option>
                            <option value="NT" <?php echo isset($form_data['state']) && $form_data['state'] == 'NT' ? 'selected' : ''; ?>>NT</option>
                            <option value="WA" <?php echo isset($form_data['state']) && $form_data['state'] == 'WA' ? 'selected' : ''; ?>>WA</option>
                            <option value="SA" <?php echo isset($form_data['state']) && $form_data['state'] == 'SA' ? 'selected' : ''; ?>>SA</option>
                            <option value="TAS" <?php echo isset($form_data['state']) && $form_data['state'] == 'TAS' ? 'selected' : ''; ?>>TAS</option>
                            <option value="ACT" <?php echo isset($form_data['state']) && $form_data['state'] == 'ACT' ? 'selected' : ''; ?>>ACT</option>
                        </select>
                    </div>
                    <div>
                        <label for="postcode">Postcode:</label>
                        <input type="text" id="postcode" name="postcode" maxlength="4" placeholder="4 digits only" pattern="^[0-9]{4}$" required
                            value="<?php echo isset($form_data['postcode']) ? htmlspecialchars($form_data['postcode']) : ''; ?>">
                    </div>
                    <div>
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" placeholder="ex:123@gmail.com" pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" required
                            value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                    </div>
                    <div>
                        <label for="phone_num">Phone Number:</label>
                        <input type="tel" id="phone_num" maxlength="12" name="phone_num" placeholder="8 to 12 digits, or spaces" pattern="^[0-9\s]{8,12}$" required
                            value="<?php echo isset($form_data['phone_num']) ? htmlspecialchars($form_data['phone_num']) : ''; ?>">
                    </div>
                </div>
            </section>

            <!-- Skills Section -->
            <section class="form-section">
                <h2>Skills</h2>
                <div class="form-grid">
                    <div>
                        <label>Which skills do you own:</label>
                        <div class="skills-list">
                            <label>
                                <input type="checkbox" name="skills[]" value="problem_solving"
                                    <?php echo isset($form_data['skills']) && in_array('problem_solving', $form_data['skills']) ? 'checked' : ''; ?>
                                    >Problem-solving skills
                            </label>
                            <label>
                                <input type="checkbox" name="skills[]" value="leadership"
                                    <?php echo isset($form_data['skills']) && in_array('leadership', $form_data['skills']) ? 'checked' : ''; ?>
                                    >Leadership
                            </label>
                            <label>
                                <input type="checkbox" name="skills[]" value="communication"
                                    <?php echo isset($form_data['skills']) && in_array('communication', $form_data['skills']) ? 'checked' : ''; ?>
                                    >Communication
                            </label>
                            <label>
                                <input type="checkbox" name="skills[]" value="responsibility"
                                    <?php echo isset($form_data['skills']) && in_array('responsibility', $form_data['skills']) ? 'checked' : ''; ?>
                                    >Responsibility
                            </label>
                            <label>
                                <input type="checkbox" name="skills[]" value="others"
                                    <?php echo isset($form_data['skills']) && in_array('others', $form_data['skills']) ? 'checked' : ''; ?>
                                    >Other skills...
                            </label>
                        </div>
                        <!-- I dont want but this is best for better readability:vv-->
                        <textarea id="other_skills" name="other_skills" rows="4" maxlength="200" placeholder="Please specify other skills"
                            ><?php echo isset($form_data['other_skills']) ? htmlspecialchars($form_data['other_skills']) : ''; ?></textarea>
                    </div>
                </div>
            </section>

            <!-- Form Actions Section -->
            <div class="form-actions">
                <button type="submit" name="reset" value="none">Reset</button>
                <button type="submit">Apply Now</button>
            </div>
        </form>
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
