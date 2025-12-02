<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pdo = get_pdo();
$courseId = (int)($_GET['course_id'] ?? $_POST['course_id'] ?? 0);

if ($courseId <= 0) {
	redirect('/index.php');
}

// Fetch course
$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
	redirect('/index.php');
}

// Handle review submission
$createError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$rating = (int)($_POST['rating'] ?? 0);
	$comment = trim((string)($_POST['comment'] ?? ''));
	$semesterTaken = trim((string)($_POST['semester_taken'] ?? ''));
	$tags = isset($_POST['tags']) && is_array($_POST['tags']) ? array_map('intval', $_POST['tags']) : [];
	
	if ($rating < 1 || $rating > 5) {
		$createError = 'Rating must be between 1 and 5.';
	} else {
		$pdo->beginTransaction();
		try {
			// Check if semester_taken column exists
			$stmt = $pdo->query("SHOW COLUMNS FROM reviews LIKE 'semester_taken'");
			$hasSemesterColumn = (bool)$stmt->fetch();
			
			if ($hasSemesterColumn) {
				$stmt = $pdo->prepare('INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken) VALUES (?, ?, ?, ?, ?)');
				$stmt->execute([$courseId, current_user()['id'], $rating, $comment, $semesterTaken ?: null]);
			} else {
				$stmt = $pdo->prepare('INSERT INTO reviews (course_id, user_id, rating, comment) VALUES (?, ?, ?, ?)');
				$stmt->execute([$courseId, current_user()['id'], $rating, $comment]);
			}
			$reviewId = (int)$pdo->lastInsertId();
			
			if (!empty($tags)) {
				$rt = $pdo->prepare('INSERT INTO review_tags (review_id, tag_id) VALUES (?, ?)');
				foreach ($tags as $tagId) {
					$rt->execute([$reviewId, $tagId]);
				}
			}
			$pdo->commit();
			redirect('/course.php?id=' . $courseId);
		} catch (Throwable $e) {
			$pdo->rollBack();
			error_log('Review creation error: ' . $e->getMessage());
			$createError = 'Failed to post review. Please try again.';
		}
	}
}

// Fetch tags
$tags = $pdo->query('SELECT id, name FROM tags ORDER BY name ASC')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<nav class="breadcrumb">
	<a href="/index.php">Home</a>
	<span class="breadcrumb-separator">›</span>
	<a href="/index.php">Courses</a>
	<span class="breadcrumb-separator">›</span>
	<a href="/course.php?id=<?= (int)$courseId ?>"><?= h($course['code']) ?></a>
	<span class="breadcrumb-separator">›</span>
	<span class="breadcrumb-current">Add Review</span>
</nav>

<div class="card">
	<h1>Add a Review</h1>
	<div style="background: #f3f4f6; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
		<strong><?= h($course['code']) ?> — <?= h($course['title']) ?></strong>
	</div>
	
	<?php if ($createError): ?>
		<div class="error-message"><?= h($createError) ?></div>
	<?php endif; ?>
	
	<form method="post">
		<input type="hidden" name="course_id" value="<?= (int)$courseId ?>" />
		<div class="form-row">
			<label>Rating
				<select name="rating" required>
					<?php for ($i=1; $i<=5; $i++): ?>
						<option value="<?= $i ?>" <?= $i === 5 ? 'selected' : '' ?>><?= $i ?> star<?= $i !== 1 ? 's' : '' ?></option>
					<?php endfor; ?>
				</select>
			</label>
			<label>Semester Taken (Optional)
				<select name="semester_taken">
					<option value="">Select semester...</option>
					<?php 
					$currentYear = (int)date('Y');
					$years = range($currentYear - 5, $currentYear + 1);
					$semesters = ['Fall', 'Winter', 'Spring', 'Summer'];
					foreach ($years as $year) {
						foreach ($semesters as $semester) {
							$value = "{$semester} {$year}";
							echo "<option value=\"" . htmlspecialchars($value) . "\">{$value}</option>\n";
						}
					}
					?>
				</select>
			</label>
			<label>Comment
				<textarea name="comment" rows="6" placeholder="Share your experience with this course..."></textarea>
			</label>
			<label>Tags (Optional - Hold Ctrl/Cmd to select multiple)
				<select name="tags[]" multiple size="7">
					<?php foreach ($tags as $t): ?>
						<option value="<?= (int)$t['id'] ?>"><?= h($t['name']) ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>
		<div style="display: flex; gap: 12px; margin-top: 16px;">
			<button class="button-primary" type="submit">Post Review</button>
			<a class="button-secondary" href="/course.php?id=<?= (int)$courseId ?>" style="text-decoration: none; display: inline-block; text-align: center;">Cancel</a>
		</div>
	</form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

