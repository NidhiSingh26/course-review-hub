<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim((string)($_POST['name'] ?? ''));
	$email = trim((string)($_POST['email'] ?? ''));
	$password = (string)($_POST['password'] ?? '');
	$confirm = (string)($_POST['confirm'] ?? '');

	if ($name === '' || $email === '' || $password === '') {
		$error = 'All fields are required.';
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error = 'Invalid email address.';
	} elseif ($password !== $confirm) {
		$error = 'Passwords do not match.';
	} else {
		try {
			$pdo = get_pdo();
			$stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
			$stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
			// Auto-login
			login_user($email, $password);
			redirect('/index.php');
		} catch (PDOException $e) {
			if ($e->getCode() === '23000') {
				$error = 'Email already registered.';
			} else {
				$error = 'Registration error. Please try again.';
			}
		}
	}
}

include __DIR__ . '/../includes/header.php';
?>
<div class="card">
	<h1>Create Account</h1>
	<?php if ($error): ?><p style="color:#b91c1c;"><?= h($error) ?></p><?php endif; ?>
	<form method="post">
		<div class="form-row">
			<label>Name
				<input class="input" type="text" name="name" required />
			</label>
			<label>Email
				<input class="input" type="email" name="email" required />
			</label>
			<label>Password
				<input class="input" type="password" name="password" required />
			</label>
			<label>Confirm Password
				<input class="input" type="password" name="confirm" required />
			</label>
		</div>
		<button class="button-primary" type="submit">Sign up</button>
	</form>
	<p>Already have an account? <a href="/login.php">Login</a></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
