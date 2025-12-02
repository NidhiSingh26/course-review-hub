<?php

declare(strict_types=1);
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$pdo = get_pdo();

// List courses
$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
	$sql = "SELECT * FROM courses WHERE code LIKE :q1 OR title LIKE :q2 ORDER BY code";
	$stmt = $pdo->prepare($sql);
	$searchTerm = "%{$q}%";
	$stmt->execute(['q1' => $searchTerm, 'q2' => $searchTerm]);
	$courses = $stmt->fetchAll();
} else {
	$courses = $pdo->query('SELECT * FROM courses ORDER BY code')->fetchAll();
}

include __DIR__ . '/../../includes/header.php';
?>
<nav class="breadcrumb">
	<a href="/index.php">Home</a>
	<span class="breadcrumb-separator">›</span>
	<span class="breadcrumb-current">Admin Panel</span>
</nav>
<div class="card">
	<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
		<h1 style="margin: 0;">Admin · Courses</h1>
		<div style="display: flex; gap: 12px;">
			<a class="button-secondary" href="/admin/users.php">Manage Users</a>
			<a class="button-primary" href="/admin/course_form.php">Add Course</a>
		</div>
	</div>
	<form method="get" style="margin-bottom:12px; display:flex; gap:8px;">
		<input class="input" type="text" name="q" placeholder="Search" value="<?= h($q) ?>" />
		<button class="button-secondary" type="submit">Search</button>
	</form>
	<?php if (empty($courses)): ?>
		<p>No courses yet.</p>
	<?php else: ?>
		<ul class="list">
			<?php foreach ($courses as $c): ?>
				<li class="card" style="display:flex; justify-content:space-between; align-items:center;">
					<div><strong><?= h($c['code']) ?></strong> — <?= h($c['title']) ?></div>
					<div>
						<a href="/admin/course_form.php?id=<?= (int)$c['id'] ?>">Edit</a>
						&nbsp;|&nbsp;
						<a href="/admin/delete_course.php?id=<?= (int)$c['id'] ?>" onclick="return confirm('Delete this course? This removes all reviews.')">Delete</a>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
