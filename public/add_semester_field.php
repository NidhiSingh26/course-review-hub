<?php

declare(strict_types=1);

// Migration script to add semester_taken field to reviews table
// Access this at: http://localhost:8000/add_semester_field.php

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../config/config.php';

$envHost = getenv('DB_HOST') ?: '127.0.0.1';
$envName = getenv('DB_NAME') ?: 'course_review_hub';
$envUser = getenv('DB_USER') ?: 'root';
$envPass = getenv('DB_PASS') ?: 'Fine2955';

$messages = [];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['run'])) {
	try {
		$pdo = new PDO(
			"mysql:host={$envHost};dbname={$envName};charset=utf8mb4",
			$envUser,
			$envPass,
			[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
		);

		$messages[] = "✓ Connected to database";

		// Check if column already exists
		$stmt = $pdo->query("SHOW COLUMNS FROM reviews LIKE 'semester_taken'");
		if ($stmt->fetch()) {
			$messages[] = "⚠ Column 'semester_taken' already exists. Skipping.";
		} else {
			// Add semester_taken column
			$pdo->exec("ALTER TABLE reviews ADD COLUMN semester_taken VARCHAR(50) NULL AFTER comment");
			$messages[] = "✓ Added 'semester_taken' column to reviews table";
		}

		$messages[] = "==========================================";
		$messages[] = "✓ Migration completed successfully!";
		$messages[] = "==========================================";

	} catch (PDOException $e) {
		$error = $e->getMessage();
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Add Semester Field</title>
	<style><?php 
	$cssFile = __DIR__ . '/../assets/styles.css';
	if (file_exists($cssFile)) {
		echo file_get_contents($cssFile);
	}
	?></style>
</head>
<body>
	<header class="site-header">
		<div class="container">
			<a class="logo" href="/index.php">Course Review Hub</a>
			<nav class="nav">
				<a href="/index.php">Courses</a>
				<a href="/login.php">Login</a>
			</nav>
		</div>
	</header>
	<main class="container">
		<div class="card">
			<h1>Add Semester Field to Reviews</h1>

			<?php if ($error): ?>
				<div class="error-message">
					<strong>Error:</strong> <?= htmlspecialchars($error) ?>
				</div>
			<?php elseif (!empty($messages)): ?>
				<div class="success-message">
					<strong>Success!</strong>
				</div>
				<div style="background: #f9fafb; padding: 16px; border-radius: 8px; margin: 16px 0; font-family: monospace; font-size: 14px;">
					<?php foreach ($messages as $msg): ?>
						<p><?= htmlspecialchars($msg) ?></p>
					<?php endforeach; ?>
				</div>
				<p><a href="/index.php">← Go to Homepage</a></p>
			<?php else: ?>
				<p>This script will add a <code>semester_taken</code> field to the reviews table.</p>
				<form method="post">
					<button type="submit" class="button-primary">Add Field</button>
				</form>
			<?php endif; ?>
		</div>
	</main>
</body>
</html>

