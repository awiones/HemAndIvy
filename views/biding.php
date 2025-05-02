<?php
require_once __DIR__ . '/../config/config.php';
global $pdo;

// Get slug from router
$slug = $_GET['biding_slug'] ?? '';

// Find auction by slugified title that hasn't ended more than 1 day ago
$auction = null;
if ($slug) {
    $stmt = $pdo->query("SELECT * FROM auctions 
        WHERE status = 'active' 
        AND (
            end_time IS NULL 
            OR end_time > DATE_SUB(NOW(), INTERVAL 1 DAY)
        )");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $titleSlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $row['title']));
        if ($slug === $titleSlug) {
            $auction = $row;
            break;
        }
    }
}

if (!$auction) {
    http_response_code(404);
    echo "<h2 style='text-align:center;margin-top:100px;'>Auction not found.</h2>";
    exit;
}

// --- Handle Place Bid POST ---
$bidSuccess = false;
$bidError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bid_amount'])) {
    // Check if user is logged in
    if (!isset($_SESSION['user']['id'])) {
        $bidError = "You must be logged in to place a bid.";
    } else {
        // Always fetch the latest auction price before processing the bid
        $stmt = $pdo->prepare("SELECT * FROM auctions WHERE id = ?");
        $stmt->execute([$auction['id']]);
        $latestAuction = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($latestAuction) {
            $raise = floatval(str_replace(',', '', $_POST['bid_amount']));
            if ($raise < 0.5) {
                $bidError = "Minimum raise is $0.50.";
            } elseif ($raise <= 0) {
                $bidError = "Raise amount must be greater than zero.";
            } elseif ($raise > 2500) {
                $bidError = "Maximum raise per bid is $2,500.00.";
            } else {
                $newPrice = $latestAuction['price'] + $raise;
                $stmt = $pdo->prepare("UPDATE auctions SET price = ? WHERE id = ?");
                if ($stmt->execute([$newPrice, $auction['id']])) {
                    // Insert bid record
                    $userId = $_SESSION['user']['id'];
                    $insertBid = $pdo->prepare("INSERT INTO bids (auction_id, user_id, raise_amount, previous_price, new_price) VALUES (?, ?, ?, ?, ?)");
                    $insertBid->execute([
                        $auction['id'],
                        $userId,
                        $raise,
                        $latestAuction['price'],
                        $newPrice
                    ]);
                    $bidSuccess = true;
                    // Refresh auction data
                    $stmt = $pdo->prepare("SELECT * FROM auctions WHERE id = ?");
                    $stmt->execute([$auction['id']]);
                    $auction = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $bidError = "Failed to place bid. Please try again.";
                }
            }
        } else {
            $bidError = "Auction not found or has ended.";
        }
    }
}

// Get additional images if available (simulating multiple images)
$additionalImages = [];
// This would normally come from your database - this is just for example
if (!empty($auction['image'])) {
    $additionalImages[] = $auction['image']; // Main image
    // You would add code here to fetch additional images if they exist
}

// Fetch last 10 bids for this auction
$lastBids = [];
$stmt = $pdo->prepare("
    SELECT b.*, u.username 
    FROM bids b 
    LEFT JOIN users u ON b.user_id = u.id 
    WHERE b.auction_id = ? 
    ORDER BY b.created_at DESC 
    LIMIT 10
");
$stmt->execute([$auction['id']]);
$lastBids = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Only keep the 3 most recent bids
$lastBids = array_slice($lastBids, 0, 3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bid on <?= htmlspecialchars($auction['title']) ?> - Hem & Ivy</title>
    <link rel="stylesheet" href="/assets/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
          --imperial-purple: #4b286d;
          --sage-green: #a3b18a;
          --lavender-mist: #d6cadd;
          --olive-leaf: #6e7f58;
          --antique-pearl: #f5f3ef;
          --charcoal-velvet: #2d2d2d;
          --aged-gold: #c2b280;
        }
        
        body {
            background-color: var(--antique-pearl);
            color: var(--charcoal-velvet);
        }

        /* Remove: .header gap, not needed */
        
        /* Product page specific styles */
        .product-container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            padding: 40px 0;
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 90px; /* Add space for fixed header */
        }
        
        .product-gallery {
            flex: 1;
            min-width: 300px;
            max-width: 600px;
        }
        
        .main-image {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            object-fit: cover;
            aspect-ratio: 1/1;
            border: 2px solid var(--lavender-mist);
        }
        
        .thumbnail-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: 6px;
            cursor: pointer;
            object-fit: cover;
            border: 2px solid var(--lavender-mist);
            transition: all 0.2s;
        }
        
        .thumbnail.active {
            border-color: var(--imperial-purple);
        }
        
        .thumbnail:hover {
            transform: translateY(-2px);
        }
        
        .product-details {
            flex: 1;
            min-width: 300px;
        }
        
        .product-title {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            margin-bottom: 5px;
            color: var(--imperial-purple);
        }
        
        .product-category {
            color: var(--charcoal-velvet);
            font-size: 14px;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .product-price {
            font-size: 24px;
            font-weight: 600;
            color: var(--olive-leaf);
            margin-bottom: 15px;
        }
        
        .product-time {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px 0;
            border-top: 1px solid var(--lavender-mist);
            border-bottom: 1px solid var(--lavender-mist);
        }
        
        .time-remaining {
            font-weight: 500;
            color: var(--imperial-purple);
            margin-left: 10px;
        }
        
        .ended {
            color: #e74c3c;
            font-weight: 600;
        }
        
        .bid-form {
            background-color: var(--lavender-mist);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid var(--imperial-purple);
        }
        
        .bid-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--imperial-purple);
        }
        
        .bid-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--sage-green);
            border-radius: 4px;
            font-size: 16px;
            margin-bottom: 15px;
            background-color: white;
        }
        
        .bid-form input:focus {
            border-color: var(--imperial-purple);
            outline: none;
            box-shadow: 0 0 0 2px rgba(75, 40, 109, 0.2);
        }
        
        .bid-button {
            background-color: var(--imperial-purple);
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        
        .bid-button:hover {
            background-color: #5f3587; /* Slightly lighter imperial purple */
        }
        
        .product-description {
            margin-top: 30px;
        }
        
        .description-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--lavender-mist);
            color: var(--imperial-purple);
        }
        
        .description-content {
            line-height: 1.6;
            color: var(--charcoal-velvet);
        }
        
        .special-offer {
            background-color: rgba(163, 177, 138, 0.2); /* Light sage-green */
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid var(--aged-gold);
        }
        
        .special-offer i {
            color: var(--aged-gold);
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .product-container {
                flex-direction: column;
            }
            
            .product-gallery, .product-details {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="product-container">
        <!-- Left Side: Gallery & Description -->
        <div class="product-gallery">
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
                <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($auction['title']) ?>" class="main-image" id="main-product-image">
            <?php else: ?>
                <div class="main-image" style="display:flex;align-items:center;justify-content:center;background:#f5f5f5;color:#aaa;font-size:18px;">
                    No Image Available
                </div>
            <?php endif; ?>
            
            <?php if (!empty($additionalImages)): ?>
            <div class="thumbnail-container">
                <?php foreach($additionalImages as $index => $img): ?>
                    <img 
                        src="<?= htmlspecialchars($img) ?>" 
                        alt="Thumbnail <?= $index + 1 ?>" 
                        class="thumbnail <?= $index === 0 ? 'active' : '' ?>"
                        onclick="document.getElementById('main-product-image').src='<?= htmlspecialchars($img) ?>';
                                document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                                this.classList.add('active');">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="product-description">
                <h3 class="description-title">Description</h3>
                <div class="description-content">
                    <?= nl2br(htmlspecialchars($auction['description'])) ?>
                </div>
            </div>
        </div>
        
        <!-- Right Side: Price & Bidding -->
        <div class="product-details">
            <h1 class="product-title"><?= htmlspecialchars($auction['title']) ?></h1>
            <p class="product-category"><?= htmlspecialchars($auction['category'] ?? '') ?> <?= htmlspecialchars($auction['rarity'] ?? '') ?></p>
            
            <div class="product-price">
                Current Bid: $<?= number_format($auction['price'], 2) ?>
            </div>
            <?php if ($bidSuccess): ?>
                <div style="color:green;font-weight:600;margin-bottom:10px;"><?= htmlspecialchars($bidSuccess) ?></div>
            <?php elseif ($bidError): ?>
                <div style="color:#e74c3c;font-weight:600;margin-bottom:10px;"><?= htmlspecialchars($bidError) ?></div>
            <?php endif; ?>

            <?php
            $auctionEnded = false;
            $endTimeStr = '';
            if (!empty($auction['end_time'])) {
                $endTime = new DateTime($auction['end_time']);
                $now = new DateTime();
                if ($endTime <= $now) {
                    $auctionEnded = true;
                    $endTimeStr = $endTime->format('M j, Y H:i');
                }
            }
            ?>

            <?php if ($auctionEnded): ?>
                <div style="color:#e74c3c;font-weight:700;font-size:18px;margin:18px 0 10px 0;">
                    The Auction has ended
                </div>
                <div style="color:#888;font-size:15px;margin-bottom:18px;">
                    Ended at: <?= htmlspecialchars($endTimeStr) ?>
                </div>
            <?php endif; ?>

            <?php if (!$auctionEnded): ?>
            <div class="bid-form">
                <form method="post" action="/biding/<?= urlencode($slug) ?>">
                    <label for="bid-amount">Raise Amount</label>
                    <div style="position:relative;display:flex;align-items:center;gap:12px;">
                        <input 
                            type="text"
                            inputmode="decimal"
                            pattern="^\d{1,3}(,\d{3})*(\.\d{0,2})?$"
                            min="0.50"
                            step="0.01"
                            name="bid_amount"
                            id="bid-amount"
                            placeholder="Enter raise amount"
                            required
                            autocomplete="off"
                            style="
                                flex:1;
                                padding: 12px 16px;
                                border: 2px solid var(--imperial-purple);
                                border-radius: 6px;
                                font-size: 18px;
                                background: #fff;
                                margin-left: 0;
                                box-sizing: border-box;
                                transition: border-color 0.2s;
                            ">
                    </div>
                    <div id="bid-summary" style="margin-top:10px;color:var(--imperial-purple);font-weight:500;padding-left:16px;">
                        <!-- JS will fill this -->
                    </div>
                    <button type="submit" class="bid-button" style="margin-top:10px;">
                        <i class="fas fa-gavel"></i> Place Bid
                    </button>
                </form>
            </div>
            <?php endif; ?>

    <div class="special-offer">
        <i class="fas fa-tag"></i> Free shipping on all items above $50!
    </div>
    <div class="special-offer">
        <i class="fas fa-shield-alt"></i> Buyer protection guarantee
    </div>

    <!-- Last Bids List -->
    <?php if (!empty($lastBids)): ?>
    <div style="margin: 24px 0 0 0; padding: 0; background: none; border-radius: 12px; border: none;">
        <h3 style="color: var(--imperial-purple); margin-bottom: 14px; font-size: 18px; letter-spacing: 0.5px;">Recent Bids</h3>
        <div style="display: flex; flex-direction: column; gap: 18px;">
            <?php foreach ($lastBids as $bid): ?>
                <?php
                    $username = $bid['username'] ?? 'Unknown user';
                    $avatarBg = ['#a3b18a', '#d6cadd', '#4b286d', '#c2b280', '#6e7f58', '#f5f3ef'];
                    $bgColor = $avatarBg[crc32($username) % count($avatarBg)];
                    $initials = strtoupper(mb_substr($username, 0, 1));
                ?>
                <div style="
                    display: flex;
                    align-items: center;
                    background: #fff;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(75,40,109,0.07);
                    padding: 14px 20px;
                    border: 1.5px solid var(--lavender-mist);
                    gap: 18px;
                    position: relative;
                ">
                    <div style="
                        width: 48px;
                        height: 48px;
                        border-radius: 50%;
                        background: <?= $bgColor ?>;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 22px;
                        font-weight: 700;
                        color: #fff;
                        flex-shrink: 0;
                        border: 2.5px solid #fff;
                        box-shadow: 0 1px 4px rgba(75,40,109,0.10);
                        margin-right: 10px;
                    "><?= $initials ?></div>
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <span style="font-weight: 600; color: var(--imperial-purple); font-size: 16px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <?= htmlspecialchars($username) ?>
                            </span>
                            <span style="font-weight:400; color: #888; font-size: 13px; margin-left: 12px;">
                                <?= date('M j, H:i', strtotime($bid['created_at'])) ?>
                            </span>
                        </div>
                        <div style="margin-top: 5px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span style="font-size: 14px; color: var(--charcoal-velvet);">
                                raised 
                                <span style="color: var(--olive-leaf); font-weight:600;">
                                    $<?= number_format($bid['raise_amount'], 2) ?>
                                </span>
                            </span>
                            <span style="font-size: 13px; color: #888;">
                                from <span style="color:#888;">$<?= number_format($bid['previous_price'], 2) ?></span>
                            </span>
                            <span style="font-size: 13px; color: var(--imperial-purple); font-weight:600;">
                                to $<?= number_format($bid['new_price'], 2) ?>
                            </span>
                        </div>
                    </div>
                    <i class="fas fa-gavel" style="color: var(--imperial-purple); font-size: 18px; margin-left: 10px;"></i>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Additional bidding information -->
    <div style="margin-top: 30px; padding: 15px; background-color: white; border-radius: 8px; border: 1px solid var(--lavender-mist);">
        <h3 style="color: var(--imperial-purple); margin-bottom: 10px; font-size: 16px;">Bidding Information</h3>
        <ul style="padding-left: 20px; color: var(--charcoal-velvet);">
            <li>All bids are binding</li>
            <li>Minimum bid increase: $0.50</li>
            <li>Buyer's premium: 10%</li>
            <li>Returns accepted within 14 days</li>
        </ul>
    </div>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format bid input with thousands separator directly in the input
    const bidInput = document.getElementById('bid-amount');
    const bidSummary = document.getElementById('bid-summary');
    const currentBid = <?= json_encode((float)$auction['price']) ?>;
    function formatMoney(val) {
        return Number(val).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    function updateSummary() {
        let value = bidInput.value.replace(/[^0-9.]/g, '');
        let raise = parseFloat(value) || 0;
        let total = currentBid + raise;
        if (raise > 0) {
            bidSummary.innerHTML = 
                'You are raising by: <span style="display:inline-block;min-width:48px;text-align:left;">' + formatMoney(raise) + '</span><br>' +
                'Your total bid will be: <span style="display:inline-block;min-width:48px;text-align:left;">' + formatMoney(total) + '</span>';
        } else {
            bidSummary.innerHTML = '';
        }
    }
    if (bidInput) {
        bidInput.addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9.]/g, '');
            // Split integer and decimal parts
            let parts = value.split('.');
            let intPart = parts[0] || '';
            let decPart = parts[1] || '';
            // Format integer part with commas
            intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            // Limit decimal to 2 digits
            if (decPart.length > 2) decPart = decPart.slice(0,2);
            this.value = decPart.length > 0 ? intPart + '.' + decPart : intPart;
            updateSummary();
        });
        bidInput.addEventListener('blur', updateSummary);
        updateSummary();
    }
});
</script>
</body>
</html>