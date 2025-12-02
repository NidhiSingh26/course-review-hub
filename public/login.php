<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim((string)($_POST['email'] ?? ''));
	$password = (string)($_POST['password'] ?? '');
	if ($email === '' || $password === '') {
		$error = 'Email and password are required.';
	} else {
		if (login_user($email, $password)) {
			redirect('/index.php');
		} else {
			$error = 'Invalid credentials.';
		}
	}
}

include __DIR__ . '/../includes/header.php';
?>
<div class="card">
	<h1>Login</h1>
	<?php if ($error): ?><p style="color:#b91c1c;"><?= h($error) ?></p><?php endif; ?>
	<form method="post">
		<div class="form-row">
			<label>Email
				<input class="input" type="email" name="email" required />
			</label>
			<label>Password
				<input class="input" type="password" name="password" required />
			</label>
		</div>
		<button class="button-primary" type="submit">Login</button>
	</form>
	<p>New here? <a href="/register.php">Create an account</a></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
