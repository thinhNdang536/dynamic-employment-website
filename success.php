<?php
    session_start();

    // Prevent direct access
    if (!isset($_SESSION['eoi_number'])) {
        header('Location: apply.php');
        exit();
    }

    $eoi_number = $_SESSION['eoi_number'];
    unset($_SESSION['eoi_number']); // Clear after use
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Successful</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212; /* Dark background */
            color: #e0e0e0; /* Light text for readability */
            margin: 0;
            padding: 0;
        }

        .success-container {
            max-width: 600px;
            margin: 4rem auto;
            padding: 2rem;
            background-color: #1e1e1e; /* Darker container background */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); /* Deeper shadow for depth */
            text-align: center;
            transition: transform 0.2s; /* Smooth scaling effect */
        }

        .success-container:hover {
            transform: scale(1.02); /* Slightly enlarge on hover */
        }

        h1 {
            font-size: 2rem; /* Larger font size for the heading */
            color: #ffffff; /* White color for the heading */
            margin-bottom: 1rem; /* Space below the heading */
        }

        .eoi-number {
            font-size: 1.5rem;
            color: #76ff03; /* Bright green for the EOI number */
            margin: 1rem 0;
            padding: 0.5rem;
            background-color: #2e2e2e; /* Darker background for the EOI number */
            border-radius: 4px;
            border: 1px solid #76ff03; /* Border to define the EOI number box */
        }

        p {
            font-size: 1rem; /* Standard font size for paragraphs */
            line-height: 1.5; /* Improved line height for readability */
            margin: 0.5rem 0; /* Space between paragraphs */
        }

        .btn {
            display: inline-block; /* Make the link behave like a button */
            padding: 0.75rem 1.5rem; /* Padding for the button */
            margin-top: 1rem; /* Space above the button */
            background-color: #76ff03; /* Bright green background for the button */
            color: #121212; /* Dark text color for contrast */
            text-decoration: none; /* Remove underline */
            border-radius: 4px; /* Rounded corners */
            transition: background-color 0.3s, transform 0.2s; /* Smooth transition for hover effects */
        }

        .btn:hover {
            background-color: #64dd17; /* Slightly darker green on hover */
            transform: translateY(-2px); /* Slight lift effect on hover */
        }

        .btn:active {
            transform: translateY(0); /* Reset lift effect when clicked */
        }
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
        <a href="index.html" class="btn">Return to Home</a>
    </div>
</body>
</html>