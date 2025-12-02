<?php

declare(strict_types=1);

// Dummy data seeding script
// Access this at: http://localhost:8000/seed_data.php

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';

$dbUrl = getenv('JAWSDB_URL') ?: getenv('CLEARDB_DATABASE_URL');

if ($dbUrl) {
    $url = parse_url($dbUrl);
    $envHost = $url["host"];
    $envName = ltrim($url["path"], '/');
    $envUser = $url["user"];
    $envPass = $url["pass"];
} else {
    $envHost = getenv('DB_HOST') ?: '127.0.0.1';
    $envName = getenv('DB_NAME') ?: 'course_review_hub';
    $envUser = getenv('DB_USER') ?: 'root';
    $envPass = getenv('DB_PASS') ?: 'Fine2955';
}

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

		// Check if courses already exist
		$stmt = $pdo->query("SELECT COUNT(*) FROM courses");
		$existingCourses = (int)$stmt->fetchColumn();

		if ($existingCourses > 0) {
			$messages[] = "⚠ Found {$existingCourses} existing courses. Adding new ones...";
		}

		// Define courses with descriptions
		$courses = [
			[
				'code' => 'EECS 183',
				'title' => 'Elementary Programming Concepts',
				'description' => 'Introduction to computer programming. Covers basic programming concepts including variables, functions, control structures, and simple data structures. Perfect for beginners with no prior programming experience.'
			],
			[
				'code' => 'EECS 280',
				'title' => 'Programming and Introductory Data Structures',
				'description' => 'Intermediate programming concepts with focus on C++. Covers pointers, memory management, data structures (arrays, linked lists, trees), and algorithms. Essential course for all CS majors.'
			],
			[
				'code' => 'EECS 281',
				'title' => 'Data Structures and Algorithms',
				'description' => 'Advanced data structures and algorithm analysis. Topics include hash tables, graphs, sorting algorithms, dynamic programming, and algorithmic complexity. Challenging but rewarding course.'
			],
			[
				'code' => 'EECS 370',
				'title' => 'Introduction to Computer Organization',
				'description' => 'Computer architecture and organization. Covers assembly language, processor design, memory systems, and computer organization. Requires EECS 280.'
			],
			[
				'code' => 'EECS 445',
				'title' => 'Machine Learning',
				'description' => 'Introduction to machine learning algorithms and applications. Covers supervised learning, unsupervised learning, neural networks, and deep learning fundamentals.'
			],
			[
				'code' => 'EECS 484',
				'title' => 'Database Management Systems',
				'description' => 'Principles of database design and management. Covers SQL, database design, transactions, indexing, and query optimization. Great practical skills for industry.'
			],
			[
				'code' => 'EECS 485',
				'title' => 'Web Systems',
				'description' => 'Full-stack web development. Covers frontend (HTML, CSS, JavaScript), backend (server-side programming), databases, and web application architecture.'
			]
		];

		// Insert courses
		$stmt = $pdo->prepare("INSERT IGNORE INTO courses (code, title, description) VALUES (?, ?, ?)");
		$courseIds = [];

		foreach ($courses as $course) {
			$stmt->execute([$course['code'], $course['title'], $course['description']]);
			$courseId = (int)$pdo->lastInsertId();
			if ($courseId > 0) {
				$courseIds[] = $courseId;
				$messages[] = "✓ Added course: {$course['code']} - {$course['title']}";
			} else {
				// Course already exists, get its ID
				$stmt2 = $pdo->prepare("SELECT id FROM courses WHERE code = ?");
				$stmt2->execute([$course['code']]);
				$existingId = (int)$stmt2->fetchColumn();
				if ($existingId > 0) {
					$courseIds[] = $existingId;
				}
			}
		}

		// Get existing tags
		$stmt = $pdo->query("SELECT id, name FROM tags");
		$tags = [];
		while ($row = $stmt->fetch()) {
			$tags[$row['name']] = (int)$row['id'];
		}

		// Get or create a test user for reviews (if not logged in)
		$userId = null;
		if (current_user()) {
			$userId = (int)current_user()['id'];
		} else {
			// Create a test user for reviews
			$stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'test@example.com' LIMIT 1");
			$stmt->execute();
			$user = $stmt->fetch();
			
			if ($user) {
				$userId = (int)$user['id'];
			} else {
				$stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
				$stmt->execute(['Test User', 'test@example.com', password_hash('password', PASSWORD_DEFAULT)]);
				$userId = (int)$pdo->lastInsertId();
				$messages[] = "✓ Created test user for reviews";
			}
		}

		// Sample reviews for courses
		$reviews = [
			[
				'course' => 'EECS 183',
				'reviews' => [
					['rating' => 5, 'comment' => 'Great introduction to programming! The professors are very helpful and the material is well-structured.', 'tags' => ['Light Workload', 'Project Based']],
					['rating' => 4, 'comment' => 'Solid foundation course. Some assignments can be challenging but very manageable.', 'tags' => ['Project Based']]
				]
			],
			[
				'course' => 'EECS 280',
				'reviews' => [
					['rating' => 5, 'comment' => 'Excellent course! Really solidifies your programming skills. Projects are challenging but fair.', 'tags' => ['Project Based', 'Heavy Reading']],
					['rating' => 4, 'comment' => 'Good course but workload is heavy. Make sure to start projects early!', 'tags' => ['Project Based', 'Heavy Reading']],
					['rating' => 5, 'comment' => 'Loved this course! The staff is amazing and the projects are well-designed.', 'tags' => ['Project Based']]
				]
			],
			[
				'course' => 'EECS 281',
				'reviews' => [
					['rating' => 5, 'comment' => 'Tough but rewarding! You learn a lot about algorithms and data structures.', 'tags' => ['Exam Heavy', 'Heavy Reading']],
					['rating' => 4, 'comment' => 'Challenging course but essential for interviews. Exams are tough but fair.', 'tags' => ['Exam Heavy', 'Project Based']]
				]
			],
			[
				'course' => 'EECS 370',
				'reviews' => [
					['rating' => 4, 'comment' => 'Interesting material on computer architecture. Assembly language can be tricky.', 'tags' => ['Heavy Reading', 'Project Based']],
					['rating' => 3, 'comment' => 'Good content but very theoretical. Lots of reading required.', 'tags' => ['Heavy Reading']]
				]
			],
			[
				'course' => 'EECS 445',
				'reviews' => [
					['rating' => 5, 'comment' => 'Amazing introduction to ML! Projects are engaging and you learn a lot.', 'tags' => ['Project Based', 'Group Projects']],
					['rating' => 4, 'comment' => 'Great course but requires strong math background. Group projects are fun!', 'tags' => ['Group Projects', 'Heavy Reading']]
				]
			],
			[
				'course' => 'EECS 484',
				'reviews' => [
					['rating' => 5, 'comment' => 'Very practical course! SQL skills are directly applicable in industry.', 'tags' => ['Project Based', 'Light Workload']],
					['rating' => 4, 'comment' => 'Good balance of theory and practice. Projects help you understand databases well.', 'tags' => ['Project Based']]
				]
			],
			[
				'course' => 'EECS 485',
				'reviews' => [
					['rating' => 5, 'comment' => 'Best course I\'ve taken! You build a real web application and learn modern frameworks.', 'tags' => ['Project Based', 'Group Projects']],
					['rating' => 5, 'comment' => 'Fantastic full-stack course. Projects are challenging but you learn so much!', 'tags' => ['Project Based', 'Group Projects']],
					['rating' => 4, 'comment' => 'Great course! Workload is heavy but very rewarding. Group work is well-organized.', 'tags' => ['Group Projects', 'Heavy Reading']]
				]
			]
		];

		// Insert reviews
		$reviewStmt = $pdo->prepare("INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken) VALUES (?, ?, ?, ?, ?)");
		$tagStmt = $pdo->prepare("INSERT IGNORE INTO review_tags (review_id, tag_id) VALUES (?, ?)");
		$reviewCount = 0;

		// Generate random semesters for dummy data
		$semesters = ['Fall 2024', 'Winter 2024', 'Spring 2024', 'Summer 2024', 'Fall 2023', 'Winter 2023', 'Spring 2025', 'Fall 2025'];

		foreach ($reviews as $reviewData) {
			// Find course ID by code
			$stmt = $pdo->prepare("SELECT id FROM courses WHERE code = ? LIMIT 1");
			$stmt->execute([$reviewData['course']]);
			$courseRow = $stmt->fetch();
			
			if (!$courseRow) continue;
			
			$courseId = (int)$courseRow['id'];

			foreach ($reviewData['reviews'] as $review) {
				$semester = $semesters[array_rand($semesters)] ?? null;
				$reviewStmt->execute([$courseId, $userId, $review['rating'], $review['comment'], $semester]);
				$reviewId = (int)$pdo->lastInsertId();

				// Add tags to review
				foreach ($review['tags'] as $tagName) {
					if (isset($tags[$tagName])) {
						$tagStmt->execute([$reviewId, $tags[$tagName]]);
					}
				}

				$reviewCount++;
			}
		}

		$messages[] = "✓ Added {$reviewCount} reviews";
		$messages[] = "==========================================";
		$messages[] = "✓ Data seeding completed successfully!";
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
	<title>Seed Dummy Data</title>
	<link rel="stylesheet" href="/assets/styles.css" />
</head>
<body>
	<header class="site-header">
		<div class="container">
			<a class="logo" href="/index.php">Course Review Hub</a>
			<nav class="nav">
				<a href="/index.php">Courses</a>
				<?php if (current_user()): ?>
					<span class="welcome">Hi, <?= h(current_user()['name']) ?></span>
					<a href="/logout.php">Logout</a>
				<?php else: ?>
					<a href="/login.php">Login</a>
					<a href="/register.php" class="button">Sign up</a>
				<?php endif; ?>
			</nav>
		</div>
	</header>
	<main class="container">
		<div class="card">
			<h1>Seed Dummy Data</h1>

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
				<p><a href="/index.php">← Go to Homepage</a> to see the courses!</p>
			<?php else: ?>
				<p>This script will add dummy data to your database:</p>
				<ul>
					<li><strong>7 courses</strong> (EECS 183, 280, 281, 370, 445, 484, 485)</li>
					<li><strong>Reviews</strong> for each course with ratings and comments</li>
					<li><strong>Tags</strong> associated with reviews</li>
				</ul>
				<form method="post">
					<button type="submit" class="button-primary">Seed Data</button>
				</form>
			<?php endif; ?>
		</div>
	</main>
</body>
</html>

