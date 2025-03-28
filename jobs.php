<?php
require_once 'settings.php';

// Create a new database connection
$db = new Database();
$conn = $db->getConnection();

// Prepare SQL query to fetch all jobs ordered by title
$query = "SELECT jobRef, title, reportTo, description, salaryMin, salaryMax, essentialReqs, preferableReqs, responsibilities, image, active FROM jobs ORDER BY title";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Job Listings</title>
  <link rel="stylesheet" href="styles/style_jobs.css">
  <link rel="stylesheet" href="styles/style_index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
  <!-- HEADER SECTION (Include your header markup) -->
  <header class="header">
    <!-- Your header content goes here -->
  </header>

  <!-- Main Content Section -->
  <main>
    <h1>Job Listings</h1>
    <div class="jobs-container">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($job = $result->fetch_assoc()): ?>
          <div class="job-listing">
            <img src="<?php echo htmlspecialchars($job['image']); ?>" alt="<?php echo htmlspecialchars($job['title']); ?>" width="200" height="200">
            <section>
              <h2><?php echo htmlspecialchars($job['title']); ?> (<?php echo htmlspecialchars($job['jobRef']); ?>)</h2>
              <h4>Reports to: <?php echo htmlspecialchars($job['reportTo']); ?></h4>
              <p class="job-description"><?php echo htmlspecialchars($job['description']); ?></p>
            </section>
            <aside>
              <section>
                <h2 class="salary">Salary Range:</h2>
                <p>$<?php echo number_format($job['salaryMin']); ?> - $<?php echo number_format($job['salaryMax']); ?></p>
              </section>
              <section>
                <h3>Requirements:</h3>
                <h4 class="requirement">Essentials:</h4>
                <p><?php echo nl2br(htmlspecialchars($job['essentialReqs'])); ?></p>
                <h4 class="requirement">Preferable:</h4>
                <p><?php echo nl2br(htmlspecialchars($job['preferableReqs'])); ?></p>
              </section>
              <section>
                <h3>Key Responsibilities:</h3>
                <p><?php echo nl2br(htmlspecialchars($job['responsibilities'])); ?></p>
              </section>
              <p><strong>Status:</strong> <?php echo $job['active'] ? 'Active' : 'Inactive'; ?></p>
            </aside>
            <a href="apply.php" class="apply-button">Apply Now</a>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No jobs found.</p>
      <?php endif; ?>
    </div>
  </main>

  <!-- FOOTER SECTION (Include your footer markup) -->
  <footer class="footer">
    <!-- Your footer content goes here -->
  </footer>
</body>
</html>

<?php
$conn->close();
?>
