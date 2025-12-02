<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pdo = get_pdo();
$user = current_user();
$error = '';
$success = '';

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim((string)($_POST['name'] ?? ''));
	$email = trim((string)($_POST['email'] ?? ''));
	$currentPassword = (string)($_POST['current_password'] ?? '');
	$newPassword = (string)($_POST['new_password'] ?? '');
	$confirmPassword = (string)($_POST['confirm_password'] ?? '');
	
	if ($name === '' || $email === '') {
		$error = 'Name and email are required.';
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error = 'Invalid email address.';
	} else {
		$pdo->beginTransaction();
		try {
			// Check if email is already taken by another user
			$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
			$stmt->execute([$email, $user['id']]);
			if ($stmt->fetch()) {
				$error = 'Email already taken by another user.';
				$pdo->rollBack();
			} else {
				// Update name and email
				$stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
				$stmt->execute([$name, $email, $user['id']]);
				
				// Update password if provided
				if ($newPassword !== '') {
					if ($currentPassword === '') {
						$error = 'Current password is required to change password.';
						$pdo->rollBack();
					} elseif ($newPassword !== $confirmPassword) {
						$error = 'New passwords do not match.';
						$pdo->rollBack();
					} elseif (strlen($newPassword) < 6) {
						$error = 'Password must be at least 6 characters long.';
						$pdo->rollBack();
					} else {
						// Verify current password
						$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
						$stmt->execute([$user['id']]);
						$dbUser = $stmt->fetch();
						
						if (!$dbUser || !password_verify($currentPassword, $dbUser['password_hash'])) {
							$error = 'Current password is incorrect.';
							$pdo->rollBack();
						} else {
							// Update password
							$stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
							$stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $user['id']]);
						}
					}
				}
				
				if ($error === '') {
					$pdo->commit();
					// Update session
					$_SESSION['user']['name'] = $name;
					$_SESSION['user']['email'] = $email;
					$success = 'Profile updated successfully!';
					$user = current_user(); // Refresh user data
				}
			}
		} catch (PDOException $e) {
			$pdo->rollBack();
			$error = 'Failed to update profile. Please try again.';
		}
	}
}

// Get current user data
$stmt = $pdo->prepare('SELECT name, email, role, created_at FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

// Get user's reviews count
$stmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
$stmt->execute([$user['id']]);
$reviewsCount = (int)$stmt->fetchColumn();

// Get user's recent reviews
$stmt = $pdo->prepare('SELECT r.*, c.code, c.title FROM reviews r JOIN courses c ON c.id = r.course_id WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 5');
$stmt->execute([$user['id']]);
$recentReviews = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<nav class="breadcrumb">
	<a href="/index.php">Home</a>
	<span class="breadcrumb-separator">›</span>
	<span class="breadcrumb-current">My Profile</span>
</nav>

<div class="grid" style="grid-template-columns: 1fr 2fr;">
	<div>
		<div class="card">
			<h2>Profile Information</h2>
			<?php if ($success): ?>
				<div class="success-message"><?= h($success) ?></div>
			<?php endif; ?>
			<?php if ($error): ?>
				<div class="error-message"><?= h($error) ?></div>
			<?php endif; ?>
			
			<form method="post">
				<div class="form-row">
					<label>
						Name
						<input class="input" type="text" name="name" value="<?= h($userData['name'] ?? '') ?>" required />
					</label>
					<label>
						Email
						<input class="input" type="email" name="email" value="<?= h($userData['email'] ?? '') ?>" required />
					</label>
					<label>
						Role
						<input class="input" type="text" value="<?= h($userData['role'] === 'admin' ? 'Administrator' : 'User') ?>" disabled style="background: #f3f4f6;" />
					</label>
					<label>
						Member Since
						<input class="input" type="text" value="<?= date('F Y', strtotime($userData['created_at'] ?? 'now')) ?>" disabled style="background: #f3f4f6;" />
					</label>
				</div>
				
				<h3 style="margin-top: 24px; margin-bottom: 12px;">Change Password (Optional)</h3>
				<div class="form-row">
					<label>
						Current Password
						<input class="input" type="password" name="current_password" placeholder="Enter current password" />
					</label>
					<label>
						New Password
						<input class="input" type="password" name="new_password" placeholder="Enter new password (min 6 characters)" />
					</label>
					<label>
						Confirm New Password
						<input class="input" type="password" name="confirm_password" placeholder="Confirm new password" />
					</label>
				</div>
				
				<button class="button-primary" type="submit">Update Profile</button>
			</form>
		</div>
	</div>
	
	<div>
		<div class="card">
			<h2>My Activity</h2>
			<div style="margin-bottom: 20px;">
				<p style="font-size: 18px; margin: 0;"><strong><?= $reviewsCount ?></strong> review<?= $reviewsCount !== 1 ? 's' : '' ?> posted</p>
			</div>
			
			<?php if (empty($recentReviews)): ?>
				<p>You haven't posted any reviews yet. <a href="/index.php">Browse courses</a> and share your experience!</p>
			<?php else: ?>
				<h3 style="margin-top: 24px; margin-bottom: 12px;">Recent Reviews</h3>
				<ul class="list">
					<?php foreach ($recentReviews as $review): ?>
						<li class="card" style="margin-bottom: 12px;">
							<div>
								<strong><a href="/course.php?id=<?= (int)$review['course_id'] ?>"><?= h($review['code']) ?> - <?= h($review['title']) ?></a></strong>
								<span style="color: #6b7280; margin-left: 8px;">Rating: <?= (int)$review['rating'] ?>/5</span>
							</div>
							<p style="margin: 8px 0; color: #4b5563;"><?= h(substr($review['comment'], 0, 150)) ?><?= strlen($review['comment']) > 150 ? '...' : '' ?></p>
							<p style="color: #9ca3af; font-size: 14px; margin: 0;">
								<?= date('M j, Y', strtotime($review['created_at'])) ?>
								<?php if ($review['updated_at']): ?>
									<span style="color: #6b7280;">(updated <?= date('M j, Y', strtotime($review['updated_at'])) ?>)</span>
								<?php endif; ?>
							</p>
						</li>
					<?php endforeach; ?>
				</ul>
				<p><a href="/index.php">View all courses →</a></p>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

