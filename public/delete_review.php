<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pdo = get_pdo();
$id = (int)($_GET['id'] ?? 0);
$courseId = (int)($_GET['course'] ?? 0);
if ($id <= 0 || $courseId <= 0) { redirect('/index.php'); }

$stmt = $pdo->prepare('SELECT * FROM reviews WHERE id = ?');
$stmt->execute([$id]);
$review = $stmt->fetch();
if ($review && (is_admin() || (int)$review['user_id'] === (int)current_user()['id'])) {
	$pdo->prepare('DELETE FROM reviews WHERE id = ?')->execute([$id]);
}
redirect('/course.php?id=' . $courseId);
