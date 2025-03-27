<?php
    /**
        * User Authentication and Session Management
        *
        * This file contains the Auth class for handling user login, account locking, 
        * and logging of login attempts.
        *
        * PHP version 8.2.12
        *
        * @category   Authentication
        * @package    Assignment2
        * @author     Dang Quang Thinh
        * @student-id 105551875
        * @version    1.0.0
    */

    session_start(); //Must do=))
    require_once 'settings.php'; //Import db model from settings.php

    /**
        * Class Auth
        *
        * Handles user authentication including login, account locking, and logging of attempts.
    */
    class Auth {
        /**
           * @var mysqli $conn Database connection instance.
        */
        private $conn;

        public $error = '';
        public $message = '';

        /**
           * Auth constructor.
           *
           * Initializes the database connection.
        */
        public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        }

        /**
           * Handle messages passed via GET parameters.
           *
           * Sets appropriate messages or errors based on URL parameters.
           *
           * @return void
        */
        public function handleMessages() {
            if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
                $this->message = "You have been successfully logged out.";
            } elseif (isset($_GET['manage']) && $_GET['manage'] === 'error') {
                $this->error = "Unauthorized access. Please login with an admin account.";
            } elseif (isset($_GET['apply']) && $_GET['apply'] === 'error') {
                $this->error = "Please login to submit a job application.";
            } elseif (isset($_GET['error']) && $_GET['error'] === 'invalid_user') {
                $this->error = "Invalid user. Please try to login with a valid account.";
            }
        }

        /**
           * Checks if a user is already logged in.
           *
           * If logged in, redirects the user to the dashboard.
           *
           * @return void
        */
        public function isUserLoggedIn() {
            if (isset($_SESSION['username'])) {
                header("Location: dashboard.php");
                exit();
            }
        }

        /**
           * Handles the login process.
           *
           * Escapes the email input, prepares and executes the query,
           * and calls the appropriate method based on the login result.
           *
           * @param string $email User email.
           * @param string $password User password.
           *
           * @return void
        */
        public function login($email, $password) {
            $email = mysqli_real_escape_string($this->conn, $email);
            
            $query = "SELECT id, username, email, password, is_active, role, locked_until, login_attempts 
                    FROM users WHERE email = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $this->handleLoginAttempt($user, $password);
            } else {
                $this->error = "Invalid email or password.";
            }
            $stmt->close();
        }

        /**
           * Processes the login attempt for a given user.
           *
           * Checks account lock status, verifies the password,
           * and calls the corresponding method for a successful or failed login.
           *
           * @param array $user User record from the database.
           * @param string $password User provided password.
           *
           * @return void
        */
        private function handleLoginAttempt($user, $password) {
            if ($this->isAccountLocked($user)) {
                return;
            }
            
            if (password_verify($password, $user['password'])) {
                $this->successfulLogin($user);
            } else {
                $this->failedLogin($user);
            }
        }

        /**
           * Checks if the user's account is locked or blocked.
           *
           * @param array $user User record from the database.
           *
           * @return bool True if the account is locked or blocked, false otherwise.
        */
        private function isAccountLocked($user) {
            if ($user['locked_until'] !== null && strtotime($user['locked_until']) > time()) {
                $wait_time = ceil((strtotime($user['locked_until']) - time()) / 60);
                $this->error = "Account is locked. Please try again in {$wait_time} minutes.";
                return true;
            } elseif ($user['is_active'] === 'Blocked') {
                $this->error = "Your account has been blocked. Please contact support.";
                return true;
            }
            return false;
        }

        /**
           * Processes a successful login.
           *
           * Resets login attempts, updates last login timestamp, sets session variables,
           * logs the successful login attempt, and redirects the user to the dashboard.
           *
           * @param array $user User record from the database.
           *
           * @return void
        */
        private function successfulLogin($user) {
            $update_query = "UPDATE users SET login_attempts = 0, last_login = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = strtolower($user['role']);

            $this->logLoginAttempt($user['id'], 1);

            header("Location: dashboard.php");
            exit();
        }

        /**
           * Processes a failed login attempt.
           *
           * Increments the login attempts, locks the account if attempts reach the limit,
           * logs the failed attempt, and commits the transaction.
           *
           * @param array $user User record from the database.
           *
           * @return void
        */
        private function failedLogin($user) {
            $attempts = $user['login_attempts'] + 1;
            try {
                $this->conn->begin_transaction();
                if ($attempts >= 3) {
                    // Lock the account if attempts reach the limit:>
                    $locked_until = date('Y-m-d H:i:s', strtotime('+30 minutes')); // Lock for 30 minutes=)
                    $update_query = "UPDATE users SET login_attempts = ?, locked_until = ?, is_active = 'Blocked' WHERE id = ?";
                    $this->executeUpdate($update_query, "isi", [$attempts, $locked_until, $user['id']]);
                    $this->error = "Account has been locked for 30 minutes due to too many failed attempts.";
                } else {
                    $update_query = "UPDATE users SET login_attempts = ? WHERE id = ?";
                    $this->executeUpdate($update_query, "ii", [$attempts, $user['id']]);
                    $this->error = "Invalid email or password. Attempts remaining: " . (3 - $attempts);
                }
                $this->logLoginAttempt($user['id'], 0);
                $this->conn->commit();
            } catch (Exception $e) {
                $this->conn->rollback();
                error_log("Login error: " . $e->getMessage());
                $this->error = "An error occurred. Please try again later.";
            }
        }

        /**
           * Executes an update query with prepared statements.
           *
           * @param string $query SQL query string.
           * @param string $types A string that contains one or more characters which specify the types for the corresponding bind variables.\n    'i' for integer, 'd' for double, 's' for string, and 'b' for blob.\n",
           * @param array $params Array of parameters to bind to the query.
           *
           * @return void
        */
        private function executeUpdate($query, $types, $params) {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            }

            /**
             * Logs a login attempt to the database.
             *
             * @param int $user_id ID of the user.
             * @param int $success Indicator if the login attempt was successful (1) or failed (0).
             *
             * @return void
            */
            private function logLoginAttempt($user_id, $success) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $log_query = "INSERT INTO login_attempts (user_id, ip_address, successful) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($log_query);
            $stmt->bind_param("isi", $user_id, $ip, $success);
            $stmt->execute();
        }
    }

    $auth = new Auth();
    $auth->handleMessages();
    $auth->isUserLoggedIn();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $auth->login($_POST['email'], $_POST['password']);
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
        <h2><img src="styles/images/light_logo.png" alt="Logo Image">Welcome Back! Please Log In</h2>

        <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="auth-btn">Login</button>
        </form>

        <?php if (!empty($auth->message)) echo "<p class='success'>{$auth->message}</p>"; ?>
        <?php if (!empty($auth->error)) echo "<p class='error'>{$auth->error}</p>"; ?>

        <p>New here? <a href="signup.php">Create an account</a></p>
    </main>
</body>
</html>
