<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/config.php';
global $pdo;

// Ensure user is logged in
if (empty($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

// Get user information
$userId = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user's auction activity
$stmt = $pdo->prepare("
    SELECT a.*, c.name as category_name 
    FROM auctions a 
    LEFT JOIN categories c ON a.category_id = c.id 
    WHERE a.seller_id = ? 
    ORDER BY a.created_at DESC 
    LIMIT 5
");
$stmt->execute([$userId]);
$userAuctions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Hem & Ivy</title>
    <link rel="stylesheet" href="/assets/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 80px auto 40px;
            padding: 0 20px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--lavender-mist);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            color: white;
            background: var(--imperial-purple);
        }

        .profile-info h1 {
            color: var(--imperial-purple);
            font-family: 'Playfair Display', serif;
            margin: 0 0 10px;
            font-size: 2rem;
        }

        .profile-stats {
            display: flex;
            gap: 30px;
            margin-top: 15px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--imperial-purple);
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--charcoal-velvet);
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .profile-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            color: var(--imperial-purple);
            font-family: 'Playfair Display', serif;
            margin: 0 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--lavender-mist);
        }

        .auction-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--lavender-mist);
        }

        .auction-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
        }

        .item-details h4 {
            margin: 0 0 5px;
            color: var(--imperial-purple);
        }

        .item-details p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--charcoal-velvet);
        }

        .profile-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--imperial-purple);
            color: white;
        }

        .btn-secondary {
            background: transparent;
            color: var(--imperial-purple);
            border: 1px solid var(--imperial-purple);
        }

        .btn-seller {
            background: var(--aged-gold);
            color: white;
            border: none;
        }

        .btn-seller:hover {
            background: #d4c28e;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .profile-stats {
                justify-content: center;
            }

            .profile-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <?php
            $initials = strtoupper(substr($user['username'] ?? 'U', 0, 1));
            if (!empty($user['avatar_url'])): ?>
                <img src="<?= htmlspecialchars($user['avatar_url']) ?>" 
                     alt="Profile Picture" 
                     class="profile-avatar">
            <?php else: ?>
                <div class="profile-avatar avatar-placeholder">
                    <?= $initials ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-info">
                <h1><?= htmlspecialchars($user['username']) ?></h1>
                <p><?= htmlspecialchars($user['email']) ?></p>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?= count($userAuctions) ?></div>
                        <div class="stat-label">Auctions</div>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="/users/settings" class="btn btn-primary">Edit Profile</a>
                    <?php if ($user['role'] !== 'seller'): ?>
                        <a href="/users/become-seller" class="btn btn-seller">Register as Seller</a>
                    <?php endif; ?>
                    <a href="/favorite" class="btn btn-secondary">View All Favorites</a>
                </div>
            </div>
        </div>

        <div class="profile-grid">
            <div class="profile-section">
                <h2 class="section-title">Recent Auctions</h2>
                <?php if (empty($userAuctions)): ?>
                    <p>No auctions yet.</p>
                <?php else: ?>
                    <?php foreach ($userAuctions as $auction): ?>
                        <div class="auction-item">
                            <img src="<?= htmlspecialchars($auction['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($auction['title']) ?>" 
                                 class="item-image">
                            <div class="item-details">
                                <h4><?= htmlspecialchars($auction['title']) ?></h4>
                                <p>Category: <?= htmlspecialchars($auction['category_name']) ?></p>
                                <p>Current Bid: $<?= number_format($auction['price'], 2) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
