<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

function current_user(): ?array {
	return $_SESSION['user'] ?? null;
}

function is_admin(): bool {
	return isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? 'user') === 'admin';
}

function require_login(): void {
	if (!current_user()) {
		redirect('/login.php');
	}
}

function require_admin(): void {
	if (!is_admin()) {
		redirect('/index.php');
	}
}

function login_user(string $email, string $password): bool {
	$pdo = get_pdo();
	$stmt = $pdo->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ?');
	$stmt->execute([$email]);
	$user = $stmt->fetch();
	if ($user && password_verify($password, $user['password_hash'])) {
		$_SESSION['user'] = [
			'id' => (int)$user['id'],
			'name' => $user['name'],
			'email' => $user['email'],
			'role' => $user['role'] ?? 'user',
		];
		return true;
	}
	return false;
}

function logout_user(): void {
	$_SESSION = [];
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
	}
	session_destroy();
}
