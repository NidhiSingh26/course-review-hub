<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$pdo = get_pdo();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { redirect('/index.php'); }

// Fetch course
$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
$stmt->execute([$id]);
$course = $stmt->fetch();
if (!$course) { redirect('/index.php'); }


// Fetch reviews with users and tags
$stmt = $pdo->prepare('SELECT r.*, u.name AS user_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.course_id = ? ORDER BY r.created_at DESC');
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

$reviewIds = array_map(fn($r) => (int)$r['id'], $reviews);
$tagsByReview = [];
if (!empty($reviewIds)) {
	$in = implode(',', array_fill(0, count($reviewIds), '?'));
	$rt = $pdo->prepare("SELECT rt.review_id, t.name FROM review_tags rt JOIN tags t ON t.id = rt.tag_id WHERE rt.review_id IN ($in)");
	$rt->execute($reviewIds);
	while ($row = $rt->fetch()) {
		$rid = (int)$row['review_id'];
		$tagsByReview[$rid][] = $row['name'];
	}
}

include __DIR__ . '/../includes/header.php';
?>
<nav class="breadcrumb">
	<a href="/index.php">Home</a>
	<span class="breadcrumb-separator">›</span>
	<a href="/index.php">Courses</a>
	<span class="breadcrumb-separator">›</span>
	<span class="breadcrumb-current"><?= h($course['code']) ?></span>
</nav>
<div class="card">
	<h1><?= h($course['code']) ?> — <?= h($course['title']) ?></h1>
	<p><?= nl2br(h($course['description'] ?? '')) ?></p>
</div>

<div class="card">
	<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
		<h2 style="margin: 0;">Reviews</h2>
		<?php if (current_user()): ?>
			<a href="/add_review.php?course_id=<?= (int)$course['id'] ?>" class="button-primary" style="text-decoration: none;">+ Add Review</a>
		<?php else: ?>
			<a href="/login.php" class="button-primary" style="text-decoration: none;">Login to Review</a>
		<?php endif; ?>
	</div>
	<?php if (empty($reviews)): ?>
		<p style="text-align: center; color: #6b7280; padding: 40px 0;">
			No reviews yet.<?php if (current_user()): ?> <a href="/add_review.php?course_id=<?= (int)$course['id'] ?>">Be the first to review!</a><?php else: ?> <a href="/login.php">Login</a> to post a review.<?php endif; ?>
		</p>
	<?php else: ?>
		<ul class="list">
			<?php foreach ($reviews as $r): ?>
				<li class="review-card">
					<div style="display:flex; justify-content:space-between; align-items:flex-start;">
						<div style="flex: 1; min-width: 0;">
							<div class="review-header">
								<div>
									<strong class="review-author"><?= h($r['user_name']) ?></strong>
									<span style="color: #6b7280; margin-left: 8px;">· Rating: <?= (int)$r['rating'] ?>/5</span>
								</div>
								<div class="review-date">
									<?php if (!empty($r['semester_taken'])): ?>
										Taken <?= h($r['semester_taken']) ?><br>
									<?php endif; ?>
									Reviewed on <?= date('M j, Y', strtotime($r['created_at'])) ?>
									<?php if ($r['updated_at']): ?>
										<span style="color: #9ca3af;">(updated <?= date('M j, Y', strtotime($r['updated_at'])) ?>)</span>
									<?php endif; ?>
								</div>
							</div>
							<?php if (!empty($tagsByReview[(int)$r['id']] ?? [])): ?>
								<div style="margin-top:12px; margin-bottom:12px;">
									<?php foreach ($tagsByReview[(int)$r['id']] as $t): ?>
										<span class="badge"><?= h($t) ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
							<?php if (!empty($r['comment'])): ?>
								<p style="margin-top:16px; color: #4b5563; line-height: 1.6;">
									<?= nl2br(h($r['comment'])) ?>
								</p>
							<?php endif; ?>
						</div>
						<div style="margin-left: 16px; flex-shrink: 0;">
							<?php if (current_user() && ((int)$r['user_id'] === (int)current_user()['id'] || is_admin())): ?>
								<div style="display: flex; flex-direction: column; gap: 8px;">
									<a href="/edit_review.php?id=<?= (int)$r['id'] ?>&course=<?= (int)$course['id'] ?>" style="color: #2563eb; font-size: 14px;">Edit</a>
									<a href="/delete_review.php?id=<?= (int)$r['id'] ?>&course=<?= (int)$course['id'] ?>" onclick="return confirm('Delete this review?')" style="color: #dc2626; font-size: 14px;">Delete</a>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
