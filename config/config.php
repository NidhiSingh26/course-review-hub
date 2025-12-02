<?php

declare(strict_types=1);

$envHost = getenv('DB_HOST') ?: '127.0.0.1';
$envName = getenv('DB_NAME') ?: 'course_review_hub';
$envUser = getenv('DB_USER') ?: 'root';
// Set DB_PASS environment variable OR set password directly below (remove '' and set your password)
$envPass = getenv('DB_PASS') ?: 'Fine2955'; // Replace '' with your MySQL password if not using environment variable

function get_pdo(): PDO {
	static $pdo = null;
	if ($pdo instanceof PDO) {
		return $pdo;
	}
	global $envHost, $envName, $envUser, $envPass;
	$dsn = "mysql:host={$envHost};dbname={$envName};charset=utf8mb4";
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];
	$pdo = new PDO($dsn, $envUser, $envPass, $options);
	return $pdo;
}
