<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;

// Add DB connection for status check
require_once __DIR__ . '/../config/config.php';

// MySQL status check
$db_status = 'Operational';
try {
    $pdo->query('SELECT 1');
} catch (Exception $e) {
    $db_status = 'Down';
}

// Get total users from database
$totalUsers = 0;
$usersLastMonth = 0;
try {
    // Total users
    $stmt = $pdo->query('SELECT COUNT(*) FROM users');
    $totalUsers = (int)$stmt->fetchColumn();

    // Users as of 1 month ago
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)');
    $stmt->execute();
    $usersLastMonth = (int)$stmt->fetchColumn();
} catch (Exception $e) {
    $totalUsers = 0;
    $usersLastMonth = 0;
}

// Calculate users percentage change
$usersPercentChange = 0;
if ($usersLastMonth > 0) {
    $usersPercentChange = (($totalUsers - $usersLastMonth) / $usersLastMonth) * 100;
} elseif ($totalUsers > 0) {
    $usersPercentChange = 100;
}

// Get active auctions and last month active auctions
$activeAuctions = 0;
$auctionsLastMonth = 0;
try {
    // Current active auctions (assuming status column)
    $stmt = $pdo->query("SELECT COUNT(*) FROM auctions WHERE status = 'active'");
    $activeAuctions = (int)$stmt->fetchColumn();

    // Active auctions as of 1 month ago
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM auctions WHERE status = 'active' AND created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $stmt->execute();
    $auctionsLastMonth = (int)$stmt->fetchColumn();
} catch (Exception $e) {
    $activeAuctions = 0;
    $auctionsLastMonth = 0;
}

// Calculate auctions percentage change
$auctionsPercentChange = 0;
if ($auctionsLastMonth > 0) {
    $auctionsPercentChange = (($activeAuctions - $auctionsLastMonth) / $auctionsLastMonth) * 100;
} elseif ($activeAuctions > 0) {
    $auctionsPercentChange = 100;
}

// Get pending requests for dashboard
$stmt = $pdo->query("
    SELECT 
        CASE 
            WHEN sr.id IS NOT NULL THEN 'seller'
            WHEN ar.id IS NOT NULL THEN 'auction'
            ELSE 'other'
        END as request_type,
        COALESCE(sr.id, ar.id) as id,
        COALESCE(sr.user_id, ar.user_id) as user_id,
        COALESCE(sr.status, ar.status) as status,
        COALESCE(sr.created_at, ar.created_at) as created_at,
        u.username,
        sr.business_name,
        ar.title as auction_title
    FROM users u
    LEFT JOIN seller_requests sr ON u.id = sr.user_id
    LEFT JOIN auction_requests ar ON u.id = ar.user_id
    WHERE COALESCE(sr.status, ar.status) = 'pending'
    ORDER BY created_at DESC
    LIMIT 5
");
$pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle approval/rejection via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $request_id = $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null;
    $user_id = $_POST['user_id'] ?? null;
    $request_type = $_POST['request_type'] ?? null;

    if ($request_id && $action && $user_id) {
        try {
            $pdo->beginTransaction();
            
            if ($request_type === 'seller') {
                // Update seller request status
                $stmt = $pdo->prepare("UPDATE seller_requests SET status = ? WHERE id = ?");
                $stmt->execute([$action, $request_id]);

                if ($action === 'approved') {
                    $stmt = $pdo->prepare("UPDATE users SET role = 'seller', seller_status = 'approved' WHERE id = ?");
                    $stmt->execute([$user_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET seller_status = 'rejected' WHERE id = ?");
                    $stmt->execute([$user_id]);
                }
            } else if ($request_type === 'auction') {
                // Update auction request status
                $stmt = $pdo->prepare("UPDATE auction_requests SET status = ? WHERE id = ?");
                $stmt->execute([$action, $request_id]);
            }

            $pdo->commit();
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'An error occurred']);
            exit;
        }
    }
}

$stats = [
    'users' => $totalUsers,
    'usersPercentChange' => $usersPercentChange,
    'auctions' => $activeAuctions,
    'auctionsPercentChange' => $auctionsPercentChange,
    'pending' => 12,
    'revenue' => 156780
];

// Recent activity from MySQL (users registered, new bids, new auctions)
$recentActivity = [];
try {
    // New user registrations
    $stmt = $pdo->prepare("SELECT username, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $recentActivity[] = [
            'type' => 'user',
            'title' => $row['username'] . ' registered',
            'status' => 'new',
            'date' => $row['created_at'],
        ];
    }

    // New bids
    $stmt = $pdo->prepare("
        SELECT b.created_at, u.username, a.title AS auction_title
        FROM bids b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN auctions a ON b.auction_id = a.id
        ORDER BY b.created_at DESC LIMIT 5
    ");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $recentActivity[] = [
            'type' => 'bid',
            'title' => 'New bid by ' . ($row['username'] ?: 'Unknown') . ' on ' . ($row['auction_title'] ?: 'Auction'),
            'status' => 'pending',
            'date' => $row['created_at'],
        ];
    }

    // New auctions opened
    $stmt = $pdo->prepare("SELECT title, created_at FROM auctions ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $recentActivity[] = [
            'type' => 'auction',
            'title' => 'Auction opened: ' . $row['title'],
            'status' => 'active',
            'date' => $row['created_at'],
        ];
    }

    // Sort all activities by date descending, limit to 5
    usort($recentActivity, function($a, $b) {
        return strtotime($b['date']) <=> strtotime($a['date']);
    });
    $recentActivity = array_slice($recentActivity, 0, 5);
} catch (Exception $e) {
    $recentActivity = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hem & Ivy</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    <style>
        .approval-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--imperial-purple);
            color: white;
            flex-shrink: 0;
        }

        .approval-icon svg {
            width: 18px;
            height: 18px;
        }

        .btn-approve, .btn-reject {
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-approve {
            background-color: var(--success-green);
            color: white;
        }

        .btn-reject {
            background-color: #f8f9fa;
            color: var(--danger-red);
            border: 1px solid var(--danger-red);
        }

        .btn-approve:hover {
            background-color: #218838;
        }

        .btn-reject:hover {
            background-color: var(--danger-red);
            color: white;
        }

        .btn-view {
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            background-color: var(--imperial-purple);
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-view:hover {
            background-color: var(--imperial-purple-light);
        }
    </style>
</head>
<body>
    <div class="admin-layout">

    <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Top Header Bar -->
            <header class="admin-header">
                <div class="header-search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" placeholder="Search..." class="search-input">
                </div>
                <div class="header-actions">
                    <button class="notification-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="notification-badge">3</span>
                    </button>
                    <div class="admin-profile">
                        <div class="admin-avatar">
                            <?php if (!empty($user['avatar_url'])): ?>
                                <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                            <?php else: ?>
                                <span><?= strtoupper(substr($user['username'] ?? 'A', 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="admin-info">
                            <p class="admin-name"><?= htmlspecialchars($user['username'] ?? 'Admin') ?></p>
                            <p class="admin-role">Administrator</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <div class="dashboard-welcome">
                    <h1>Welcome back, <span><?= htmlspecialchars($user['username'] ?? 'Admin') ?></span></h1>
                    <p>Here's what's happening with your luxury auction platform today.</p>
                </div>
                
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="stat-details">
                            <p class="stat-title">Total Users</p>
                            <h3 class="stat-value"><?= number_format($stats['users']) ?></h3>
                            <p class="stat-change <?= $stats['usersPercentChange'] >= 0 ? 'positive' : 'negative' ?>">
                                <?= ($stats['usersPercentChange'] >= 0 ? '+' : '') . number_format($stats['usersPercentChange'], 1) ?>% from last month
                            </p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon auctions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                <polyline points="2 17 12 22 22 17"></polyline>
                                <polyline points="2 12 12 17 22 12"></polyline>
                            </svg>
                        </div>
                        <div class="stat-details">
                            <p class="stat-title">Active Auctions</p>
                            <h3 class="stat-value"><?= number_format($stats['auctions']) ?></h3>
                            <p class="stat-change <?= $stats['auctionsPercentChange'] >= 0 ? 'positive' : 'negative' ?>">
                                <?= ($stats['auctionsPercentChange'] >= 0 ? '+' : '') . number_format($stats['auctionsPercentChange'], 1) ?>% from last month
                            </p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon pending">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="stat-details">
                            <p class="stat-title">Pending Approvals</p>
                            <h3 class="stat-value"><?= number_format($stats['pending']) ?></h3>
                            <p class="stat-change negative">+3 from yesterday</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </div>
                        <div class="stat-details">
                            <p class="stat-title">Total Revenue</p>
                            <h3 class="stat-value">$<?= number_format($stats['revenue']) ?></h3>
                            <p class="stat-change positive">+15% from last month</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Access & Recent Activity -->
                <div class="dashboard-rows">
                    <div class="dashboard-col">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h2>Quick Actions</h2>
                            </div>
                            <div class="card-body">
                                <div class="quick-actions">
                                    <a href="/admin/auctions" class="quick-action-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        Create Auction
                                    </a>
                                    <a href="/admin/users/new" class="quick-action-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="8.5" cy="7" r="4"></circle>
                                            <line x1="20" y1="8" x2="20" y2="14"></line>
                                            <line x1="23" y1="11" x2="17" y2="11"></line>
                                        </svg>
                                        Add User
                                    </a>
                                    <a href="/admin/categories/new" class="quick-action-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="8" y1="6" x2="21" y2="6"></line>
                                            <line x1="8" y1="12" x2="21" y2="12"></line>
                                            <line x1="8" y1="18" x2="21" y2="18"></line>
                                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                        </svg>
                                        Add Category
                                    </a>
                                    <a href="/admin/reports/generate" class="quick-action-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="20" x2="18" y2="10"></line>
                                            <line x1="12" y1="20" x2="12" y2="4"></line>
                                            <line x1="6" y1="20" x2="6" y2="14"></line>
                                        </svg>
                                        Generate Report
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h2>Pending Approvals</h2>
                                <a href="/admin/approvals" class="view-all">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="approval-items">
                                    <?php if (!empty($pending_requests)): ?>
                                        <?php foreach ($pending_requests as $request): ?>
                                            <div class="approval-item" id="request-<?= $request['id'] ?>">
                                                <div class="approval-icon">
                                                    <?php if ($request['request_type'] === 'seller'): ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="12" cy="7" r="4"></circle>
                                                        </svg>
                                                    <?php else: ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                                            <polyline points="2 17 12 22 22 17"></polyline>
                                                            <polyline points="2 12 12 17 22 12"></polyline>
                                                        </svg>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="approval-details">
                                                    <p class="approval-title">
                                                        <?php if ($request['request_type'] === 'seller'): ?>
                                                            Seller Verification
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($request['auction_title']) ?>
                                                        <?php endif; ?>
                                                    </p>
                                                    <p class="approval-info">
                                                        Requested by <strong><?= htmlspecialchars($request['username']) ?></strong>
                                                    </p>
                                                </div>
                                                <div class="approval-actions">
                                                    <a href="/admin/approvals" class="btn-view">
                                                        View
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="approval-item" style="justify-content: center;">
                                            <p>No pending approvals</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-col">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h2>Recent Activity</h2>
                                <a href="/admin/activity" class="view-all">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="activity-timeline">
                                    <?php foreach ($recentActivity as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon <?= $activity['type'] ?>">
                                            <?php if ($activity['type'] === 'auction'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                            </svg>
                                            <?php elseif ($activity['type'] === 'user'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                            <?php elseif ($activity['type'] === 'bid'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                            </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="activity-content">
                                            <p class="activity-title"><?= htmlspecialchars($activity['title']) ?></p>
                                            <div class="activity-meta">
                                                <span class="activity-status <?= $activity['status'] ?>">
                                                    <?= ucfirst($activity['status']) ?>
                                                </span>
                                                <span class="activity-date"><?= $activity['date'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h2>System Status</h2>
                            </div>
                            <div class="card-body">
                                <div class="system-status">
                                    <div class="status-item">
                                        <div class="status-label">Server Status</div>
                                        <div class="status-value operational">Operational</div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-label">Database</div>
                                        <?php if ($db_status === 'Operational'): ?>
                                            <div class="status-value operational">Operational</div>
                                        <?php else: ?>
                                            <div class="status-value" style="color: var(--danger-red);">Down</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-label">Payment Gateway</div>
                                        <div class="status-value operational">Operational</div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-label">Email Service</div>
                                        <div class="status-value operational">Operational</div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-label">Last Backup</div>
                                        <div class="status-value">2025-04-24 03:15 AM</div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-label">System Version</div>
                                        <div class="status-value">v2.5.3</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="/assets/js/dashboard.js"></script>
</body>
</html>

