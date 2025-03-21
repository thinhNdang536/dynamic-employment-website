<?php
// Database connection details
$servername = 'fygq1.h.filess.io';
$username = 'ass2_quitefunme';
$password = '7c5a9a9e241335fc981ca841b4838100c1fb207e';
$database = 'ass2_quitefunme';
$port = 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all jobs (for now you have only Marketing Manager)
$sql = "SELECT * FROM jobs";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Listings</title>
    <link rel="stylesheet" href="ass2jobs.css">
</head>
<body>
    <h1>Join Us:</h1>
    <div class="job-container">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="job-listing">
                    <img src="<?= htmlspecialchars($row['image']) ?>" width="200" height="200" alt="<?= htmlspecialchars($row['title']) ?>">
                    <h2><?= htmlspecialchars($row['title']) ?></h2>
                    <p><?= htmlspecialchars($row['description']) ?></p>
                    
                    <h2>Salary Range:</h2>
                    <p>$<?= number_format($row['salaryMin']) ?> - $<?= number_format($row['salaryMax']) ?></p>

                    <h3>Requirements:</h3>
                    <ol>
                        <?php foreach (explode(',', $row['essentialReqs']) as $requirement): ?>
                            <li><?= htmlspecialchars(trim($requirement)) ?></li>
                        <?php endforeach; ?>
                    </ol>

                    <h3>responsibilities:</h3>
                    <ul>
                        <?php foreach (explode(',', $row['responsibilities']) as $benefit): ?>
                            <li><?= htmlspecialchars(trim($benefit)) ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <button><a href="apply.html">Apply Now</a></button>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No job listings found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>
