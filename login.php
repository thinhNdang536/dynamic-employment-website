<?php
session_start();
require_once 'settings.php';

// Handle messages
$error = '';
$message = '';

if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $message = "You have been successfully logged out.";
} elseif (isset($_GET['manage']) && $_GET['manage'] === 'error') {
    $error = "Unauthorized access. Please login with an admin account.";
} elseif (isset($_GET['apply']) && $_GET['apply'] === 'error') {
    $error = "Please login to submit a job application.";
}

// Check if the user is already logged in
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error = '';
    $message = '';
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Check if account exists
    $query = "SELECT id, username, email, password, is_active, role, locked_until, login_attempts 
              FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Check if account is locked
        if ($user['locked_until'] !== null && strtotime($user['locked_until']) > time()) {
            $wait_time = ceil((strtotime($user['locked_until']) - time()) / 60);
            $error = "Account is locked. Please try again in {$wait_time} minutes.";
        }
        // Check if account is blocked
        elseif ($user['is_active'] === 'Blocked') {
            $error = "Your account has been blocked. Please contact support.";
        }
        // Verify password
        elseif (password_verify($password, $user['password'])) {
            // Reset login attempts on successful login
            $update_query = "UPDATE users SET login_attempts = 0, last_login = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = strtolower($user['role']);
            
            // Log successful attempt
            $ip = $_SERVER['REMOTE_ADDR'];
            $log_query = "INSERT INTO login_attempts (user_id, ip_address) VALUES (?, ?)";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bind_param("is", $user['id'], $ip);
            $log_stmt->execute();
            
            header("Location: dashboard.php");
            exit();
        } else {
            // Increment login attempts
            $attempts = $user['login_attempts'] + 1;
            
            try {
                $conn->begin_transaction();
                
                if ($attempts >= 3) {
                    // Block account and set lock time
                    $locked_until = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                    $update_query = "UPDATE users SET 
                                    login_attempts = ?,
                                    locked_until = ?,
                                    is_active = 'Blocked'
                                    WHERE id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    if ($update_stmt === false) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
                    $update_stmt->bind_param("isi", $attempts, $locked_until, $user['id']);
                    if (!$update_stmt->execute()) {
                        throw new Exception("Execute failed: " . $update_stmt->error);
                    }
                    
                    $error = "Account has been locked for 30 minutes due to too many failed attempts.";
                } else {
                    // Just increment attempts
                    $update_query = "UPDATE users SET login_attempts = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    if ($update_stmt === false) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
                    $update_stmt->bind_param("ii", $attempts, $user['id']);
                    if (!$update_stmt->execute()) {
                        throw new Exception("Execute failed: " . $update_stmt->error);
                    }
                    
                    $error = "Invalid email or password. Attempts remaining: " . (3 - $attempts);
                }
                
                // Log failed attempt
                $ip = $_SERVER['REMOTE_ADDR'];
                $log_query = "INSERT INTO login_attempts (user_id, ip_address, successful) VALUES (?, ?, 0)";
                $log_stmt = $conn->prepare($log_query);
                if ($log_stmt === false) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                $log_stmt->bind_param("is", $user['id'], $ip);
                if (!$log_stmt->execute()) {
                    throw new Exception("Execute failed: " . $log_stmt->error);
                }
                
                $conn->commit();
                
            } catch (Exception $e) {
                $conn->rollback();
                error_log("Login error: " . $e->getMessage());
                $error = "An error occurred. Please try again later.";
            }
        }
    } else {
        $error = "Invalid email or password.";
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/style_login.css">
    <title>JobsTime - Secure Login</title>
</head>
<body>
    <main class="main-container">
        <a href="index.php" class="go-back-btn">←</a>
        <h2><img src="assets/img/home/light_logo.png" alt="Logo Image">Welcome Back! Please Log In</h2>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="auth-btn">Login</button>
        </form>

        <?php if (!empty($message)): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <p>New here? <a href="signup.php">Create an account</a></p>
    </main>
</body>
</html>
