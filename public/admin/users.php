<?php

declare(strict_types=1);
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$pdo = get_pdo();

// Handle role changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
	$userId = (int)$_POST['user_id'];
	$newRole = $_POST['role'] === 'admin' ? 'admin' : 'user';
	
	// Don't allow changing own role
	if ($userId === (int)current_user()['id']) {
		$error = 'You cannot change your own role.';
	} else {
		$stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
		$stmt->execute([$newRole, $userId]);
		$success = 'User role updated successfully.';
	}
}

// Search functionality
$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
	$sql = "SELECT u.*, 
			(SELECT COUNT(*) FROM reviews WHERE user_id = u.id) as review_count 
			FROM users u 
			WHERE u.name LIKE :q1 OR u.email LIKE :q2 
			ORDER BY u.created_at DESC";
	$stmt = $pdo->prepare($sql);
	$searchTerm = "%{$q}%";
	$stmt->execute(['q1' => $searchTerm, 'q2' => $searchTerm]);
	$users = $stmt->fetchAll();
} else {
	$stmt = $pdo->query("SELECT u.*, 
		(SELECT COUNT(*) FROM reviews WHERE user_id = u.id) as review_count 
		FROM users u 
		ORDER BY u.created_at DESC");
	$users = $stmt->fetchAll();
}

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
$totalAdmins = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$totalRegularUsers = (int)$stmt->fetchColumn();

include __DIR__ . '/../../includes/header.php';
?>
<nav class="breadcrumb">
	<a href="/index.php">Home</a>
	<span class="breadcrumb-separator">›</span>
	<a href="/admin/courses.php">Admin Panel</a>
	<span class="breadcrumb-separator">›</span>
	<span class="breadcrumb-current">Users</span>
</nav>

<div class="card">
	<h1>Admin · User Management</h1>
	
	<?php if (isset($success)): ?>
		<div class="success-message"><?= h($success) ?></div>
	<?php endif; ?>
	<?php if (isset($error)): ?>
		<div class="error-message"><?= h($error) ?></div>
	<?php endif; ?>
	
	<!-- Statistics -->
	<div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">
		<div class="card" style="text-align: center; background: #eff6ff;">
			<div style="font-size: 32px; font-weight: 700; color: #2563eb;"><?= $totalUsers ?></div>
			<div style="color: #6b7280;">Total Users</div>
		</div>
		<div class="card" style="text-align: center; background: #f0fdf4;">
			<div style="font-size: 32px; font-weight: 700; color: #059669;"><?= $totalRegularUsers ?></div>
			<div style="color: #6b7280;">Regular Users</div>
		</div>
		<div class="card" style="text-align: center; background: #fef3c7;">
			<div style="font-size: 32px; font-weight: 700; color: #d97706;"><?= $totalAdmins ?></div>
			<div style="color: #6b7280;">Administrators</div>
		</div>
	</div>
	
	<!-- Search Form -->
	<form method="get" style="margin-bottom: 20px; display: flex; gap: 12px;">
		<input class="input" type="text" name="q" placeholder="Search by name or email" value="<?= h($q) ?>" style="flex: 1;" />
		<button class="button-secondary" type="submit">Search</button>
		<?php if ($q !== ''): ?>
			<a href="/admin/users.php" class="button-secondary">Clear</a>
		<?php endif; ?>
	</form>
	
	<!-- Users List -->
	<?php if (empty($users)): ?>
		<p>No users found.</p>
	<?php else: ?>
		<div class="card">
			<table class="admin-table">
				<thead>
					<tr>
						<th>Name</th>
						<th>Email</th>
						<th style="text-align: center;">Role</th>
						<th style="text-align: center;">Reviews</th>
						<th>Joined</th>
						<th style="text-align: center;">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($users as $u): ?>
						<tr>
							<td>
								<strong><?= h($u['name']) ?></strong>
								<?php if ((int)$u['id'] === (int)current_user()['id']): ?>
									<span class="badge" style="background: #2563eb; color: white; margin-left: 8px;">YOU</span>
								<?php endif; ?>
							</td>
							<td style="color: #6b7280;"><?= h($u['email']) ?></td>
							<td style="text-align: center;">
								<?php if ($u['role'] === 'admin'): ?>
									<span class="badge" style="background: #dc2626; color: white;">Admin</span>
								<?php else: ?>
									<span class="badge">User</span>
								<?php endif; ?>
							</td>
							<td style="text-align: center;"><?= (int)$u['review_count'] ?></td>
							<td style="color: #6b7280; font-size: 14px;">
								<?= date('M j, Y', strtotime($u['created_at'])) ?>
							</td>
							<td style="text-align: center;">
								<form method="post" style="display: inline-block; margin: 0;">
									<input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>" />
									<select name="role" onchange="this.form.submit()" style="padding: 6px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;" <?= (int)$u['id'] === (int)current_user()['id'] ? 'disabled' : '' ?>>
										<option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
										<option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
									</select>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

