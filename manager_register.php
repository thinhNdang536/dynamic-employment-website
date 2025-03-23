<?php
session_start();
require_once 'settings.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: login.php?manage=error");
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get and sanitize input
    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 5) {
        $errors[] = "Username must be at least 5 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Strict password rules for managers
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
    
    // Check if username or email already exists
    $check_query = "SELECT username, email FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['username'] === $username) {
                $errors[] = "Username already exists";
            }
            if ($row['email'] === $email) {
                $errors[] = "Email already exists";
            }
        }
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin';
        
        $insert_query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
        
        if ($stmt->execute()) {
            $success = "Manager account created successfully!";
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
    
    $stmt->close();
    $conn->close();
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
        <h2><img src="assets/img/home/light_logo.png" alt="Logo Image">Create Manager Account</h2>

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