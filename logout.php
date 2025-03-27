<?php
    session_start();

    // Clear all session variables if no user data will stay in session:vvv
    $_SESSION = array();

    // Destroy the session cookie, so the user will not be logged in anymore=))
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }

    // Destroy the session
    session_destroy();

    // Redirect to login page with a logout message
    header("Location: login.php?logout=success");
    exit();
?>