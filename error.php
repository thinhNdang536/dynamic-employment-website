<?php
    session_start(); // Start the session for get the error

    // Redirect if there are no errors deteched (aka redirecting to apply.php on page refreshing)
    if (!isset($_SESSION['errors']) || empty($_SESSION['errors'])) {
        header('Location: index.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="styles/style_error.css">
</head>
<body>
    <div class="error-container">
        <h2>⚠️ Please Correct the Following Errors</h2>
        <ul class="error-list">
            <?php
                // Display errors
                if (isset($_SESSION['errors'])) {
                    if (is_array($_SESSION['errors'])) {
                        foreach ($_SESSION['errors'] as $error) {
                            echo "<li>" . htmlspecialchars($error) . "</li>";
                        }
                    } else {
                        echo "<li>" . htmlspecialchars($_SESSION['errors']) . "</li>";
                    }
                }

                // Clear errors after displaying them for prevent loop, and redirect to apply.php on page refreshing
                // This is kinda important!
                unset($_SESSION['errors']);
            ?>
        </ul>
        <a href="apply.php" class="back-link">← Return to Form</a>
    </div>
</body>
</html>