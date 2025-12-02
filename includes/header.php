<?php

declare(strict_types=1);
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Course Review Hub</title>
	<link rel="stylesheet" href="/assets/styles.css" />
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
			<?php if (current_user()): ?>
				<a href="/profile.php" class="welcome" style="text-decoration: none;">Hi, <?= h(current_user()['name']) ?></a>
				<?php if (is_admin()): ?>
					<a href="/admin/courses.php">Admin</a>
				<?php endif; ?>
				<a href="/logout.php">Logout</a>
			<?php else: ?>
					<a href="/login.php">Login</a>
					<a href="/register.php" class="button">Sign up</a>
				<?php endif; ?>
			</nav>
		</div>
	</header>
	<main class="container">
