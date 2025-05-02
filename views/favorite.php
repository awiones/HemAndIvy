<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/config.php';
global $pdo;

$user = $_SESSION['user'] ?? null;
$favorites = [];

if ($user) {
    $stmt = $pdo->prepare("
        SELECT a.*
        FROM favorites f
        JOIN auctions a ON f.auction_id = a.id
        WHERE f.user_id = ?
        AND (
            a.end_time IS NULL 
            OR a.end_time > DATE_SUB(NOW(), INTERVAL 1 DAY)
        )
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Favorites - Hem & Ivy</title>
    <link rel="stylesheet" href="/assets/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<section class="bids-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">My Favorites</h2>
            <p class="section-subtitle">Your favorited auctions are listed below.</p>
        </div>
        <div class="bids-grid">
            <?php if (!$user): ?>
                <p style="grid-column: 1/-1; text-align:center;">You must be logged in to view your favorites.</p>
            <?php elseif (empty($favorites)): ?>
                <p style="grid-column: 1/-1; text-align:center;">No favorites yet.</p>
            <?php else: ?>
                <?php foreach ($favorites as $auction): ?>
                <div class="bid-card">
                    <?php
                        $imgPath = $auction['image'];
                        $imgPath = preg_replace('#/+#','/',$imgPath);
                        $imgFile = null;
                        if (strpos($imgPath, '/uploads/') === 0) {
                            $imgFile = realpath(__DIR__ . '/../public' . $imgPath);
                        } else {
                            $imgFile = $_SERVER['DOCUMENT_ROOT'] . $imgPath;
                        }
                        if (!empty($imgPath) && $imgFile && file_exists($imgFile)):
                    ?>
                        <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($auction['title']) ?>" class="bid-image">
                    <?php else: ?>
                        <div style="height:280px;display:flex;align-items:center;justify-content:center;background:#f5f5f5;color:#aaa;font-size:18px;">
                            No Image
                        </div>
                    <?php endif; ?>
                    <div class="bid-content">
                        <?php if (!empty($auction['category'])): ?>
                            <div class="bid-category"><?= htmlspecialchars($auction['category']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($auction['rarity'])): ?>
                            <div class="bid-category" style="color:var(--sage-green);font-size:12px;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">
                                <?= htmlspecialchars($auction['rarity']) ?>
                            </div>
                        <?php endif; ?>
                        <h3 class="bid-title"><?= htmlspecialchars($auction['title']) ?></h3>
                        <div class="bid-price">
                            <span class="current-bid">Current Bid:</span>
                            <span class="bid-amount">$<?= number_format($auction['price'], 2) ?></span>
                        </div>
                        <?php
                        if (!empty($auction['end_time'])) {
                            $now = new DateTime();
                            $end = new DateTime($auction['end_time']);
                            if ($end > $now) {
                                $diff = $now->diff($end);
                                $parts = [];
                                if ($diff->d > 0) $parts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
                                if ($diff->h > 0) $parts[] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
                                if ($diff->i > 0 && $diff->d == 0) $parts[] = $diff->i . ' min' . ($diff->i > 1 ? 's' : '');
                                $endsIn = implode(', ', $parts);
                                echo '<div class="bid-time">Ends in ' . ($endsIn ?: 'soon') . '</div>';
                            } else {
                                echo '<div class="bid-time" style="color:#dc3545;">Auction ended</div>';
                            }
                        } else {
                            echo '<div class="bid-time"></div>';
                        }
                        ?>
                        <div class="bid-actions">
                            <button class="btn btn-secondary bid-btn"
                                onclick="window.location.href='/biding/<?= urlencode(strtolower(preg_replace('/[^a-z0-9]+/', '-', $auction['title']))) ?>'">
                                Place Bid
                            </button>
                            <button class="bid-favorite" data-auction-id="<?= (int)$auction['id'] ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
<script>
    // Favorite button AJAX for unfavorite (remove from favorites)
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.bid-favorite').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const auctionId = btn.getAttribute('data-auction-id');
                fetch('/favorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'auction_id=' + encodeURIComponent(auctionId)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Remove the card from the grid if unfavorited
                        if (data.favorited === false) {
                            const card = btn.closest('.bid-card');
                            if (card) card.remove();
                        }
                    } else if (data.error) {
                        alert(data.error);
                    }
                });
            });
        });
    });
</script>
</body>
</html>
