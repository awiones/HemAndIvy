<?php
require_once __DIR__ . '/../config/config.php';
global $pdo;
// Fetch 6 most recent active auctions
$stmt = $pdo->query("SELECT * FROM auctions WHERE status = 'active' ORDER BY created_at DESC LIMIT 6");
$auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories and item counts, and a random image for each category
$catStmt = $pdo->query("
    SELECT category, COUNT(*) as count
    FROM auctions
    WHERE category IS NOT NULL AND category != ''
    GROUP BY category
    ORDER BY count DESC, category ASC
");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// For each category, get a random image from an auction in that category
$categoryImages = [];
foreach ($categories as $cat) {
    $catName = $cat['category'];
    $imgStmt = $pdo->prepare("SELECT image FROM auctions WHERE category = ? AND image IS NOT NULL AND image != '' ORDER BY RAND() LIMIT 1");
    $imgStmt->execute([$catName]);
    $imgRow = $imgStmt->fetch(PDO::FETCH_ASSOC);
    $categoryImages[$catName] = $imgRow ? $imgRow['image'] : null;
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hem & Ivy - Curated Fashion Auctions</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/home.css">
</head>
<body>

    <?php include __DIR__ . '/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-subtitle">Curated Fashion Auctions</div>
                <h1 class="hero-title">Discover Timeless Style at Your Bid</h1>
                <p class="hero-description">
                    Explore our exclusive collection of vintage-inspired and designer clothing pieces. 
                    Place your bids and elevate your wardrobe with unique finds.
                </p>
                <div class="hero-cta">
                    <a href="/search" class="btn btn-primary">Browse Auctions</a>
                    <a href="#" class="btn btn-outline">How It Works</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Current Bids Section -->   
    <section class="bids-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Current Auctions</h2>
                <p class="section-subtitle">
                    Discover our carefully curated selection of clothing pieces currently open for bidding.
                    Each item is verified for authenticity and quality.
                </p>
            </div>
            <div class="bids-grid">
                <?php if (empty($auctions)): ?>
                    <p style="grid-column: 1/-1; text-align:center;">No auctions available.</p>
                <?php else: ?>
                    <?php foreach ($auctions as $auction): ?>
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
            <div style="text-align: center; margin-top: 50px;">
                <a href="/auction" class="btn btn-outline">View All Auctions</a>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="categories-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Browse by Category</h2>
                <p class="section-subtitle">
                    Explore our curated collections organized by style, era, and occasion.
                </p>
            </div>
            <div class="categories-grid">
                <?php
                // Only show the first 4 categories
                $displayCategories = array_slice($categories, 0, 4);
                ?>
                <?php if (empty($displayCategories)): ?>
                    <p style="grid-column: 1/-1; text-align:center;">No categories available.</p>
                <?php else: ?>
                    <?php foreach ($displayCategories as $cat): ?>
                        <div class="category-card">
                            <?php
                                $catName = $cat['category'];
                                $imgPath = $categoryImages[$catName] ?? null;
                                if ($imgPath) {
                                    $imgPath = preg_replace('#/+#','/',$imgPath);
                                    $imgFile = null;
                                    if (strpos($imgPath, '/uploads/') === 0) {
                                        $imgFile = realpath(__DIR__ . '/../public' . $imgPath);
                                    } else {
                                        $imgFile = $_SERVER['DOCUMENT_ROOT'] . $imgPath;
                                    }
                                    if (!$imgFile || !file_exists($imgFile)) {
                                        $imgPath = null;
                                    }
                                }
                                // Fallback image if no product image found
                                if (!$imgPath) {
                                    $imgMap = [
                                        'Vintage' => 'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?ixlib=rb-4.0.3&auto=format&fit=crop&w=735&q=80',
                                        'Designer' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80',
                                        'Accessories' => 'https://images.unsplash.com/photo-1617019114583-affb34d1b3cd?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80',
                                        "Men's" => 'https://images.unsplash.com/photo-1516762689617-e1cffcef479d?ixlib=rb-4.0.3&auto=format&fit=crop&w=711&q=80',
                                        'Luxury' => 'https://images.unsplash.com/photo-1567401893414-76b7b1e5a7a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80',
                                        'Sustainable' => 'https://images.unsplash.com/photo-1551232864-3f0890e580d9?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80',
                                    ];
                                    $imgPath = $imgMap[$catName] ?? 'https://images.unsplash.com/photo-1469398715555-76331a6c7c9b?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80';
                                }
                            ?>
                            <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($catName) ?>">
                            <div class="category-overlay">
                                <h3 class="category-name"><?= htmlspecialchars($catName) ?></h3>
                                <div class="category-count"><?= (int)$cat['count'] ?> item<?= $cat['count'] == 1 ? '' : 's' ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="categories-cta">
                <a href="/categories" class="btn btn-outline">View All Categories</a>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <h2 class="newsletter-title">Join Our Exclusive Auctions</h2>
                <p class="newsletter-description">
                    Subscribe to receive notifications about new arrivals, upcoming auctions, 
                    and exclusive early access to premium collections.
                </p>
                <form class="newsletter-form">
                    <input type="email" class="newsletter-input" placeholder="Your email address" required>
                    <button type="submit" class="newsletter-btn">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
    <script>
        // Simple script to handle navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.backgroundColor = 'rgba(245, 243, 239, 0.98)';
                navbar.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.backgroundColor = 'rgba(245, 243, 239, 0.95)';
                navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.05)';
            }
        });

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