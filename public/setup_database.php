<?php

declare(strict_types=1);

// Database setup script (web-accessible)
// Access this at: http://localhost:8000/setup_database.php

header('Content-Type: text/html; charset=utf-8');

// Use the same config as the main application - read config values
$envHost = getenv('DB_HOST') ?: '127.0.0.1';
$envName = getenv('DB_NAME') ?: 'course_review_hub';
$envUser = getenv('DB_USER') ?: 'root';
$envPass = getenv('DB_PASS') ?: 'Fine2955'; // Match config.php default

$messages = [];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['run'])) {
	try {
		$messages[] = "Step 1: Connecting to MySQL server...";
		
		// Connect to MySQL without database
		$dsn = "mysql:host={$envHost};charset=utf8mb4";
		$pdo = new PDO($dsn, $envUser, $envPass, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		]);
		$messages[] = "✓ Connected successfully";
		
		// Create database
		$messages[] = "Step 2: Creating database '{$envName}'...";
		$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$envName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
		$messages[] = "✓ Database created/exists";
		
		// Select database
		$messages[] = "Step 3: Selecting database...";
		$pdo->exec("USE `{$envName}`");
		$messages[] = "✓ Database selected";
		
		// Create tables
		$messages[] = "Step 4: Creating tables...";
		
		$pdo->exec("CREATE TABLE IF NOT EXISTS users (
			id INT AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(100) NOT NULL,
			email VARCHAR(190) NOT NULL UNIQUE,
			password_hash VARCHAR(255) NOT NULL,
			role ENUM('user','admin') NOT NULL DEFAULT 'user',
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$messages[] = "✓ Table 'users' created";
		
		$pdo->exec("CREATE TABLE IF NOT EXISTS courses (
			id INT AUTO_INCREMENT PRIMARY KEY,
			code VARCHAR(32) NOT NULL UNIQUE,
			title VARCHAR(200) NOT NULL,
			description TEXT,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$messages[] = "✓ Table 'courses' created";
		
		$pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
			id INT AUTO_INCREMENT PRIMARY KEY,
			course_id INT NOT NULL,
			user_id INT NOT NULL,
			rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
			comment TEXT,
			semester_taken VARCHAR(50) NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP NULL DEFAULT NULL,
			FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
			FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$messages[] = "✓ Table 'reviews' created";
		
		$pdo->exec("CREATE TABLE IF NOT EXISTS tags (
			id INT AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(64) NOT NULL UNIQUE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$messages[] = "✓ Table 'tags' created";
		
		$pdo->exec("CREATE TABLE IF NOT EXISTS review_tags (
			review_id INT NOT NULL,
			tag_id INT NOT NULL,
			PRIMARY KEY (review_id, tag_id),
			FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
			FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$messages[] = "✓ Table 'review_tags' created";
		
		// Seed tags
		$messages[] = "Step 5: Seeding tags...";
		$tags = [
			'Heavy Reading',
			'Pop Quizzes',
			'Group Projects',
			'Attendance Matters',
			'Light Workload',
			'Project Based',
			'Exam Heavy'
		];
		
		$stmt = $pdo->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
		foreach ($tags as $tag) {
			$stmt->execute([$tag]);
		}
		$messages[] = "✓ Tags seeded";
		$messages[] = "==========================================";
		$messages[] = "✓ Database setup completed successfully!";
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
	<title>Database Setup</title>
	<style>
		body {
			font-family: system-ui, -apple-system, sans-serif;
			max-width: 800px;
			margin: 40px auto;
			padding: 20px;
			line-height: 1.6;
		}
		.card {
			background: white;
			border: 1px solid #e5e7eb;
			border-radius: 8px;
			padding: 24px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
		}
		.button-primary {
			background: #2563eb;
			color: white;
			border: none;
			padding: 10px 20px;
			border-radius: 6px;
			cursor: pointer;
			font-size: 16px;
		}
		.button-primary:hover {
			background: #1d4ed8;
		}
		.error {
			color: #b91c1c;
			background: #fee2e2;
			padding: 12px;
			border-radius: 6px;
			margin: 12px 0;
		}
		.success {
			color: #059669;
			background: #d1fae5;
			padding: 12px;
			border-radius: 6px;
			margin: 12px 0;
		}
		.messages {
			background: #f9fafb;
			padding: 16px;
			border-radius: 6px;
			margin: 16px 0;
			font-family: monospace;
			font-size: 14px;
		}
		.messages p {
			margin: 4px 0;
		}
		.config {
			background: #f3f4f6;
			padding: 12px;
			border-radius: 6px;
			margin: 12px 0;
			font-size: 14px;
		}
	</style>
</head>
<body>
	<div class="card">
		<h1>Database Setup</h1>
		
		<div class="config">
			<strong>Configuration:</strong><br>
			Host: <?= htmlspecialchars($envHost) ?><br>
			Database: <?= htmlspecialchars($envName) ?><br>
			User: <?= htmlspecialchars($envUser) ?><br>
		</div>
		
		<?php if ($error): ?>
			<div class="error">
				<strong>Error:</strong> <?= htmlspecialchars($error) ?><br><br>
				Please check:
				<ul>
					<li>MySQL server is running</li>
					<li>Database credentials are correct in <code>config/config.php</code></li>
					<li>User has permissions to create databases</li>
				</ul>
			</div>
		<?php elseif (!empty($messages)): ?>
			<div class="success">
				<strong>Setup Complete!</strong>
			</div>
			<div class="messages">
				<?php foreach ($messages as $msg): ?>
					<p><?= htmlspecialchars($msg) ?></p>
				<?php endforeach; ?>
			</div>
			<p><a href="/">← Go to Homepage</a></p>
		<?php else: ?>
			<p>Click the button below to set up the database. This will:</p>
			<ul>
				<li>Create the database if it doesn't exist</li>
				<li>Create all required tables</li>
				<li>Seed initial tag data</li>
			</ul>
			<form method="post">
				<button type="submit" class="button-primary">Set Up Database</button>
			</form>
		<?php endif; ?>
	</div>
</body>
</html>

