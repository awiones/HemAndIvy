<?php
require_once __DIR__ . '/../config/config.php';
global $pdo;
$stmt = $pdo->query("SELECT * FROM auctions 
    WHERE status = 'active' 
    AND (
        end_time IS NULL 
        OR end_time > DATE_SUB(NOW(), INTERVAL 1 DAY)
    )
    ORDER BY created_at DESC");
$auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's favorites if logged in
$favoritedIds = [];
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user'])) {
    $userId = $_SESSION['user']['id'];
    $favStmt = $pdo->prepare("SELECT auction_id FROM favorites WHERE user_id = ?");
    $favStmt->execute([$userId]);
    $favoritedIds = $favStmt->fetchAll(PDO::FETCH_COLUMN, 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auctions - Hem & Ivy</title>
    <link rel="stylesheet" href="/assets/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<section class="bids-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Live Auctions</h2>
            <p class="section-subtitle">Bid on unique, rare, and one-of-a-kind fashion pieces.</p>
        </div>
        <div class="bids-grid">
            <?php if (empty($auctions)): ?>
                <p style="grid-column: 1/-1; text-align:center;">No auctions available.</p>
            <?php else: ?>
                <?php foreach ($auctions as $auction): ?>
                <div class="bid-card">
                    <?php
                        // Ensure image path is correct and file exists
                        $imgPath = $auction['image'];
                        // Remove possible duplicate slashes
                        $imgPath = preg_replace('#/+#','/',$imgPath);
                        // Fix: Check in /public if path starts with /uploads/
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
                        <?php if (!empty($auction['category']) || !empty($auction['rarity'])): ?>
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                                <?php if (!empty($auction['category'])): ?>
                                    <div class="bid-category"><?= htmlspecialchars($auction['category']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($auction['rarity'])): ?>
                                    <div class="bid-category" style="color:var(--sage-green);font-size:12px;text-transform:uppercase;letter-spacing:1px;">
                                        <?= htmlspecialchars($auction['rarity']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <h3 class="bid-title"><?= htmlspecialchars($auction['title']) ?></h3>
                        <div class="bid-price">
                            <span class="current-bid">Current Bid:</span>
                            <span class="bid-amount">$<?= number_format($auction['price'], 2) ?></span>
                        </div>
                        <?php
                        // Show time left if end_time is set and in the future
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
                                <i class="<?= in_array($auction['id'], $favoritedIds) ? 'fas' : 'far' ?> fa-heart"></i>
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
    // Favorite button AJAX
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
                        const icon = btn.querySelector('i');
                        if (data.favorited === false) {
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                            btn.title = 'Add to favorites';
                        } else {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            btn.title = 'Favorited!';
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
