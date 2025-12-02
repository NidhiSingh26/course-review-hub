<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

try {
	$pdo = get_pdo();
	$q = trim((string)($_GET['q'] ?? ''));

	if ($q !== '') {
		$sql = "SELECT * FROM courses WHERE code LIKE :q1 OR title LIKE :q2 ORDER BY code ASC";
		$stmt = $pdo->prepare($sql);
		$searchTerm = "%{$q}%";
		$stmt->execute(['q1' => $searchTerm, 'q2' => $searchTerm]);
		$courses = $stmt->fetchAll();
	} else {
		$stmt = $pdo->query('SELECT * FROM courses ORDER BY code ASC');
		$courses = $stmt->fetchAll();
	}
} catch (PDOException $e) {
	error_log('Database error: ' . $e->getMessage());
	$courses = [];
	$error = 'Database error: ' . htmlspecialchars($e->getMessage());
}

include __DIR__ . '/../includes/header.php';
?>
<div class="card">
	<h1>Courses</h1>
	<?php if (isset($error)): ?>
		<div class="error-message"><?= h($error) ?></div>
	<?php endif; ?>
	<form method="get" style="margin-bottom: 20px; display: flex; gap: 12px;">
		<input class="input" type="text" name="q" placeholder="Search by code or title" value="<?= h($q ?? '') ?>" style="flex: 1;" />
		<button class="button-secondary" type="submit">Search</button>
	</form>
	<?php if (empty($courses)): ?>
		<p>No courses found.</p>
	<?php else: ?>
		<div class="grid">
			<?php foreach ($courses as $course): ?>
				<div class="card course-card">
					<div class="course-card-header">
						<div class="course-code"><?= h($course['code']) ?></div>
						<h3 style="margin: 8px 0; font-size: 18px; font-weight: 600;"><?= h($course['title']) ?></h3>
					</div>
					<?php if (!empty($course['description'])): ?>
						<p style="color: #6b7280; font-size: 14px; margin: 12px 0 0 0; flex-grow: 1;"><?= h(substr($course['description'], 0, 150)) ?><?= strlen($course['description']) > 150 ? '...' : '' ?></p>
					<?php else: ?>
						<div style="flex-grow: 1;"></div>
					<?php endif; ?>
					<a href="/course.php?id=<?= (int)$course['id'] ?>" class="button-primary" style="display: inline-block; margin-top: 16px; text-align: center; width: 100%;">View Details</a>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
