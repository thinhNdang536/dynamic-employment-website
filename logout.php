<?php
    // Start the session if it hasn't been started
    session_start();

    // Clear all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }

    // Destroy the session
    session_destroy();

    // Redirect to login page with a logout message
    header("Location: login.php?logout=success");
    exit();
?>