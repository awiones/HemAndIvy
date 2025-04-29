<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;
require_once __DIR__ . '/../config/config.php';

// Fetch recent activities (last 10 for each type)
$activities = [];

// New user registrations
$stmt = $pdo->prepare("SELECT id, username, created_at FROM users ORDER BY created_at DESC LIMIT 10");
$stmt->execute();
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $activities[] = [
        'type' => 'user',
        'title' => $row['username'] . ' registered',
        'date' => $row['created_at'],
    ];
}

// New bids
$stmt = $pdo->prepare("
    SELECT b.id, u.username, b.created_at, a.title AS auction_title
    FROM bids b
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN auctions a ON b.auction_id = a.id
    ORDER BY b.created_at DESC LIMIT 10
");
$stmt->execute();
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $activities[] = [
        'type' => 'bid',
        'title' => 'New bid by ' . ($row['username'] ?: 'Unknown') . ' on ' . ($row['auction_title'] ?: 'Auction'),
        'date' => $row['created_at'],
    ];
}

// New auctions opened
$stmt = $pdo->prepare("SELECT id, title, created_at FROM auctions ORDER BY created_at DESC LIMIT 10");
$stmt->execute();
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $activities[] = [
        'type' => 'auction',
        'title' => 'Auction opened: ' . $row['title'],
        'date' => $row['created_at'],
    ];
}

// Sort all activities by date descending
usort($activities, function($a, $b) {
    return strtotime($b['date']) <=> strtotime($a['date']);
});

// Limit to 20 most recent
$activities = array_slice($activities, 0, 20);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Log - Hem & Ivy Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Improved activity timeline styles */
        .activity-timeline {
            padding: 0 12px 24px 18px;
            border-left: 3px solid #e7e1ef;
            margin-top: 12px;
        }
        .activity-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 28px;
            position: relative;
            padding-left: 8px;
        }
        .activity-item:last-child {
            margin-bottom: 0;
        }
        .activity-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            margin-top: 2px;
            font-size: 18px;
            background: #f6f3fa;
            border: 2px solid #e7e1ef;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(75,40,109,0.04);
        }
        .activity-icon.user {
            background: #e6f3ff;
            border-color: #b3d8fd;
            color: #2b6cb0;
        }
        .activity-icon.bid {
            background: #fffbe6;
            border-color: #ffe58f;
            color: #b8860b;
        }
        .activity-icon.auction {
            background: #f3e6ff;
            border-color: #d1b3fd;
            color: #7c3aed;
        }
        .activity-content {
            flex: 1;
        }
        .activity-title {
            font-size: 1.08rem;
            font-weight: 500;
            margin: 0 0 2px 0;
            color: #4b286d;
            letter-spacing: 0.01em;
        }
        .activity-date {
            color: #888;
            font-size: 13px;
            margin-top: 2px;
            display: inline-block;
        }
        .activity-type-label {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            border-radius: 12px;
            padding: 2px 10px;
            margin-right: 8px;
            margin-bottom: 2px;
            vertical-align: middle;
        }
        .activity-type-label.user {
            background: #e6f3ff;
            color: #2b6cb0;
        }
        .activity-type-label.bid {
            background: #fffbe6;
            color: #b8860b;
        }
        .activity-type-label.auction {
            background: #f3e6ff;
            color: #7c3aed;
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="admin-main">
        <header class="admin-header">
            <h1 style="font-size: 2rem; color: #4b286d; margin: 0;">Activity Log</h1>
        </header>
        <div class="dashboard-card" style="margin-top: 32px;">
            <div class="card-header">
                <h2>Recent Activity</h2>
            </div>
            <div class="card-body">
                <div class="activity-timeline">
                    <?php if (empty($activities)): ?>
                        <div style="color:#888; padding: 24px;">No recent activity found.</div>
                    <?php else: ?>
                        <?php foreach ($activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?= $activity['type'] ?>">
                                <?php if ($activity['type'] === 'auction'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                </svg>
                                <?php elseif ($activity['type'] === 'user'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <?php elseif ($activity['type'] === 'bid'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                                <?php endif; ?>
                            </div>
                            <div class="activity-content">
                                <span class="activity-type-label <?= $activity['type'] ?>">
                                    <?= ucfirst($activity['type']) ?>
                                </span>
                                <p class="activity-title"><?= htmlspecialchars($activity['title']) ?></p>
                                <span class="activity-date">
                                    <?= date('M j, Y H:i', strtotime($activity['date'])) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
