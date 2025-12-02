<?php

declare(strict_types=1);

function redirect(string $path): void {
	header('Location: ' . $path);
	exit;
}

function h(?string $value): string {
	return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function post_param(string $key, ?string $default = null): ?string {
	return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function get_param(string $key, ?string $default = null): ?string {
	return isset($_GET[$key]) ? trim((string)$_GET[$key]) : $default;
}
