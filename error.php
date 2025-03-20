<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        /* CSS style for dark theme error page */
        :root {
            --bg-primary: #121212;
            --bg-secondary: #1e1e1e;
            --bg-tertiary: #2c2c2c;
            --text-primary: #e0e0e0;
            --text-secondary: #b0b0b0;
            --accent-red: #ff4d4d;
            --accent-blue: #007bff;
            --accent-blue-hover: #0056b3;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            /* min-height: 100vh; */
            display: flex;
            /* align-items: center; */
            justify-content: center;
            padding: 1rem;
        }

        .error-container {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 800px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        h2 {
            color: var(--accent-red);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
        }

        .error-list {
            background-color: var(--bg-tertiary);
            border-left: 4px solid var(--accent-red);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 0 8px 8px 0;
        }

        .error-list li {
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
            list-style-type: none;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .error-list li::before {
            content: "•";
            color: var(--accent-red);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: var(--accent-blue);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .back-link:hover {
            background-color: var(--accent-blue-hover);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <?php
        // Start the session if it hasn't been started yet, it maybe a little bit unnecessary:))
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Redirect if there are no errors deteched (aka redirecting to apply.php on page refreshing)
        if (!isset($_SESSION['errors']) || empty($_SESSION['errors'])) {
            header('Location: index.html');
            exit();
        }
    ?>
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