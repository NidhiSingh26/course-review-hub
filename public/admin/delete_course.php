<?php

declare(strict_types=1);
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
	$pdo = get_pdo();
	$pdo->prepare('DELETE FROM courses WHERE id = ?')->execute([$id]);
}
redirect('/admin/courses.php');
