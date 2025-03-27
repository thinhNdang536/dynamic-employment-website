<?php
    session_start(); // Start the session for get data

    // Prevent direct access
    if (!isset($_SESSION['eoi_number'])) {
        header('Location: apply.php');
        exit();
    }

    $eoi_number = $_SESSION['eoi_number']; // Must get eoi num for display
    unset($_SESSION['eoi_number']); // Clear after use
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Successful</title>
    <link rel="stylesheet" href="styles/style_success.css">
    <style>
    </style>
</head>
<body>
    <div class="success-container">
        <h1>Application Submitted Successfully!</h1>
        <p>Thank you for your application. Your EOI has been recorded.</p>
        <div class="eoi-number">
            Your EOI Number: <?php echo htmlspecialchars((string)$eoi_number); ?>
        </div>
        <p>Please save this number for future reference.</p>
        <a href="dashboard.php" class="btn">Return to Home</a>
    </div>
</body>
</html>