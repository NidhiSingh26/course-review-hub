<?php

declare(strict_types=1);
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$pdo = get_pdo();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$editing = $id > 0;
$course = ['code' => '', 'title' => '', 'description' => ''];

if ($editing) {
	$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
	$stmt->execute([$id]);
	$course = $stmt->fetch() ?: $course;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$code = trim((string)($_POST['code'] ?? ''));
	$title = trim((string)($_POST['title'] ?? ''));
	$description = trim((string)($_POST['description'] ?? ''));
	if ($code === '' || $title === '') {
		$error = 'Code and title are required.';
	} else {
		try {
			if ($editing) {
				$pdo->prepare('UPDATE courses SET code = ?, title = ?, description = ? WHERE id = ?')
					->execute([$code, $title, $description, $id]);
			} else {
				$pdo->prepare('INSERT INTO courses (code, title, description) VALUES (?, ?, ?)')
					->execute([$code, $title, $description]);
			}
			redirect('/admin/courses.php');
		} catch (PDOException $e) {
			if ($e->getCode() === '23000') { $error = 'Course code must be unique.'; }
			else { $error = 'Failed to save course.'; }
		}
	}
}

include __DIR__ . '/../../includes/header.php';
?>
<nav class="breadcrumb">
	<a href="/index.php">Home</a>
	<span class="breadcrumb-separator">›</span>
	<a href="/admin/courses.php">Admin Panel</a>
	<span class="breadcrumb-separator">›</span>
	<span class="breadcrumb-current"><?= $editing ? 'Edit Course' : 'Add Course' ?></span>
</nav>
<div class="card">
	<h1><?= $editing ? 'Edit' : 'Add' ?> Course</h1>
	<?php if ($error): ?><p style="color:#b91c1c;"> <?= h($error) ?> </p><?php endif; ?>
	<form method="post">
		<?php if ($editing): ?><input type="hidden" name="id" value="<?= (int)$id ?>" /><?php endif; ?>
		<div class="form-row">
			<label>Code
				<input class="input" type="text" name="code" value="<?= h($course['code']) ?>" required />
			</label>
			<label>Title
				<input class="input" type="text" name="title" value="<?= h($course['title']) ?>" required />
			</label>
			<label>Description
				<textarea class="input" name="description" rows="5"><?= h($course['description']) ?></textarea>
			</label>
		</div>
		<button class="button-primary" type="submit">Save</button>
		<a class="button-secondary" href="/admin/courses.php">Cancel</a>
	</form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
