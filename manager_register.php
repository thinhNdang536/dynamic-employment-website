<?php
    session_start(); //Must do=))
    require_once 'settings.php'; //Import db model from settings.php

    // Redirects non-admin users to login page. ONLY ADMIN CAN CREATE ADMIN=))
    if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
        header("Location: login.php?manage=error");
        exit();
    }

    $errors = []; // now i use global var, no array_merge. just don't know why:vv
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $db = new Database();
        $conn = $db->getConnection();
        
        $username = sanitizeInput($conn, $_POST['username']);
        $email = sanitizeInput($conn, $_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        validateUsername($username, $errors);
        validateEmail($email, $errors);
        validatePassword($password, $confirm_password, $errors);
        checkExistingUser($conn, $username, $email, $errors);
        
        if (empty($errors)) {
            createManagerAccount($conn, $username, $email, $password, $success, $errors);
        }
        
        $conn->close();
    }

    /**
        * Sanitizes user input to prevent SQL injection.
    */
    function sanitizeInput($conn, $input) {
        return trim(mysqli_real_escape_string($conn, $input));
    }

    /**
        * Validates the username.
    */
    function validateUsername($username, &$errors) {
        if (empty($username)) {
            $errors[] = "Username is required";
        } elseif (strlen($username) < 5) {
            $errors[] = "Username must be at least 5 characters";
        }
    }

    /**
        * Validates the email format.
    */
    function validateEmail($email, &$errors) {
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
    }

    /**
        * Validates the password according to security standards.
    */
    function validatePassword($password, $confirm_password, &$errors) {
        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 12) {
            $errors[] = "Password must be at least 12 characters";
        } elseif (!preg_match("/[A-Z]/", $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        } elseif (!preg_match("/[a-z]/", $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        } elseif (!preg_match("/[0-9]/", $password)) {
            $errors[] = "Password must contain at least one number";
        } elseif (!preg_match("/[!@#$%^&*()\-_=+{};:,<.>]/", $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
    }

    /**
        * Checks if the username or email already exists in the database.
    */
    function checkExistingUser($conn, $username, $email, &$errors) {
        $query = "SELECT username, email FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            if ($row['username'] === $username) {
                $errors[] = "Username already exists";
            }
            if ($row['email'] === $email) {
                $errors[] = "Email already exists";
            }
        }
        
        $stmt->close();
    }

    /**
        * Creates a new manager account if validation is successful.
    */
    function createManagerAccount($conn, $username, $email, $password, &$success, &$errors) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin';
        
        $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
        
        if ($stmt->execute()) {
            $success = "Manager account created successfully!";
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        
        $stmt->close();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Manager - JobsTime</title>
    <link rel="stylesheet" href="styles/style_login.css">
</head>
<body>
    <main class="main-container">
        <a href="dashboard.php" class="go-back-btn">←</a>
        <h2><img src="styles/images/light_logo.png" alt="Logo Image">Create Manager Account</h2>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <small>Must be at least 12 characters with uppercase, lowercase, number, and special character</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <?php if (!empty($errors)): ?>
                <?php foreach($errors as $error): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($success): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <button type="submit" class="auth-btn">Create Manager Account</button>
        </form>
    </main>
</body>
</html>