<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pdo = get_pdo();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$courseId = (int)($_GET['course'] ?? $_POST['course_id'] ?? 0);
if ($id <= 0 || $courseId <= 0) { redirect('/index.php'); }

$stmt = $pdo->prepare('SELECT * FROM reviews WHERE id = ?');
$stmt->execute([$id]);
$review = $stmt->fetch();
if (!$review) { redirect('/course.php?id=' . $courseId); }

if (!is_admin() && (int)$review['user_id'] !== (int)current_user()['id']) {
	redirect('/course.php?id=' . $courseId);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$rating = (int)($_POST['rating'] ?? 0);
	$comment = trim((string)($_POST['comment'] ?? ''));
	$semesterTaken = trim((string)($_POST['semester_taken'] ?? ''));
	$tags = isset($_POST['tags']) && is_array($_POST['tags']) ? array_map('intval', $_POST['tags']) : [];
	if ($rating < 1 || $rating > 5) {
		$error = 'Rating must be between 1 and 5.';
	} else {
		$pdo->beginTransaction();
		try {
			// Check if semester_taken column exists
			$stmt = $pdo->query("SHOW COLUMNS FROM reviews LIKE 'semester_taken'");
			$hasSemesterColumn = (bool)$stmt->fetch();
			
			if ($hasSemesterColumn) {
				$pdo->prepare('UPDATE reviews SET rating = ?, comment = ?, semester_taken = ?, updated_at = NOW() WHERE id = ?')
					->execute([$rating, $comment, $semesterTaken ?: null, $id]);
			} else {
				$pdo->prepare('UPDATE reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE id = ?')
					->execute([$rating, $comment, $id]);
			}
			$pdo->prepare('DELETE FROM review_tags WHERE review_id = ?')->execute([$id]);
			if (!empty($tags)) {
				$ins = $pdo->prepare('INSERT INTO review_tags (review_id, tag_id) VALUES (?, ?)');
				foreach ($tags as $tagId) { $ins->execute([$id, $tagId]); }
			}
			$pdo->commit();
			redirect('/course.php?id=' . $courseId);
		} catch (Throwable $e) {
			$pdo->rollBack();
			$error = 'Failed to update review.';
		}
	}
}

$tags = $pdo->query('SELECT id, name FROM tags ORDER BY name ASC')->fetchAll();
$existingTags = $pdo->prepare('SELECT tag_id FROM review_tags WHERE review_id = ?');
$existingTags->execute([$id]);
$existing = array_map(fn($r) => (int)$r['tag_id'], $existingTags->fetchAll());

// Get course info for breadcrumb
$stmt = $pdo->prepare('SELECT code, title FROM courses WHERE id = ?');
$stmt->execute([$courseId]);
$course = $stmt->fetch();

include __DIR__ . '/../includes/header.php';
?>
<nav class="breadcrumb">
	<a href="/index.php">Home</a>
	<span class="breadcrumb-separator">›</span>
	<a href="/index.php">Courses</a>
	<span class="breadcrumb-separator">›</span>
	<a href="/course.php?id=<?= (int)$courseId ?>"><?= h($course['code'] ?? 'Course') ?></a>
	<span class="breadcrumb-separator">›</span>
	<span class="breadcrumb-current">Edit Review</span>
</nav>
<div class="card">
	<h1>Edit Review</h1>
	<?php if ($error): ?><p style="color:#b91c1c;"> <?= h($error) ?> </p><?php endif; ?>
	<form method="post">
		<input type="hidden" name="id" value="<?= (int)$id ?>" />
		<input type="hidden" name="course_id" value="<?= (int)$courseId ?>" />
		<div class="form-row">
			<label>Rating
				<select name="rating">
					<?php for ($i=1; $i<=5; $i++): ?>
						<option value="<?= $i ?>" <?= $i===(int)$review['rating'] ? 'selected' : '' ?>><?= $i ?></option>
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
					$selectedSemester = $review['semester_taken'] ?? '';
					foreach ($years as $year) {
						foreach ($semesters as $semester) {
							$value = "{$semester} {$year}";
							$selected = ($selectedSemester === $value) ? 'selected' : '';
							echo "<option value=\"" . htmlspecialchars($value) . "\" {$selected}>{$value}</option>\n";
						}
					}
					?>
				</select>
			</label>
			<label>Comment
				<textarea name="comment" rows="4"><?= h($review['comment']) ?></textarea>
			</label>
			<label>Tags
				<select name="tags[]" multiple size="5">
					<?php foreach ($tags as $t): $tid=(int)$t['id']; ?>
						<option value="<?= $tid ?>" <?= in_array($tid, $existing, true) ? 'selected' : '' ?>><?= h($t['name']) ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>
		<button class="button-primary" type="submit">Save</button>
		<a class="button-secondary" href="/course.php?id=<?= (int)$courseId ?>">Cancel</a>
	</form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
