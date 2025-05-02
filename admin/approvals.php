<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Verify admin access
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../config/config.php';
global $pdo;

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null;
    $user_id = $_POST['user_id'] ?? null;

    if ($request_id && $action && $user_id) {
        try {
            $pdo->beginTransaction();

            // Update request status
            $stmt = $pdo->prepare("UPDATE seller_requests SET status = ? WHERE id = ?");
            $stmt->execute([$action, $request_id]);

            // Update user role and status if approved
            if ($action === 'approved') {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET role = 'seller', 
                        seller_status = 'approved' 
                    WHERE id = ?
                ");
                $stmt->execute([$user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET seller_status = 'rejected' WHERE id = ?");
                $stmt->execute([$user_id]);
            }

            $pdo->commit();
            $success_message = "Request successfully " . ($action === 'approved' ? 'approved' : 'rejected');
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = "An error occurred. Please try again.";
        }
    }
}

// Get pending requests - modified query to include type
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
        u.email,
        sr.business_name,
        ar.title as auction_title,
        ar.description as auction_description
    FROM users u
    LEFT JOIN seller_requests sr ON u.id = sr.user_id
    LEFT JOIN auction_requests ar ON u.id = ar.user_id
    WHERE COALESCE(sr.status, ar.status) = 'pending'
    ORDER BY created_at DESC
");
$pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Approvals - Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <style>
        .approval-items {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .approval-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background-color: var(--light-gray);
            border-radius: var(--radius-md);
            border: none;
            gap: 15px;
        }

        .approval-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--imperial-purple);
            color: white;
        }

        .approval-details {
            flex: 1;
            line-height: 1.3;
        }

        .approval-title {
            font-weight: 600;
            margin-bottom: 2px;
            color: var(--text-primary);
        }

        .approval-info {
            color: var(--text-secondary);
            font-size: 12px;
            margin: 0;
        }

        .approval-meta {
            color: var(--text-secondary);
            font-size: 12px;
            margin-top: 2px;
        }

        .approval-actions {
            display: flex;
            gap: 8px;
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

        .btn-approve:hover {
            background-color: #218838;
        }

        .btn-reject {
            background-color: #f8f9fa;
            color: var(--danger-red);
            border: 1px solid var(--danger-red);
        }

        .btn-reject:hover {
            background-color: var(--danger-red);
            color: white;
        }

        .admin-content {
            padding: 25px;
        }

        .tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .tab {
            padding: 8px 16px;
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            font-weight: 500;
        }

        .tab.active {
            color: var(--imperial-purple);
            border-bottom-color: var(--imperial-purple);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .history-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background-color: var(--light-gray);
            border-radius: var(--radius-md);
            margin-bottom: 10px;
            gap: 15px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-approved {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-green);
        }

        .status-rejected {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-red);
        }
    </style>
</head>
<body class="admin-body">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-main">
        <div class="admin-content">
            <div class="content-header">
                <h1>Seller Registration Requests</h1>
                <p>Review and manage seller applications</p>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <div class="dashboard-card">
                <div class="card-header">
                    <div class="tabs">
                        <button class="tab active" data-tab="pending">Pending Approvals</button>
                        <button class="tab" data-tab="history">History</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content active" id="pending">
                        <div class="approval-items">
                            <?php if (!empty($pending_requests)): ?>
                                <?php foreach ($pending_requests as $request): ?>
                                    <div class="approval-item">
                                        <div class="approval-icon">
                                            <?php if ($request['request_type'] === 'seller'): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="12" cy="7" r="4"></circle>
                                                </svg>
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="approval-details">
                                            <p class="approval-title">
                                                <?php if ($request['request_type'] === 'seller'): ?>
                                                    Seller Verification
                                                <?php elseif ($request['request_type'] === 'auction'): ?>
                                                    New Auction Listing
                                                <?php else: ?>
                                                    Approval Request
                                                <?php endif; ?>
                                            </p>
                                            <p class="approval-info">
                                                Requested by <strong><?= htmlspecialchars($request['username']) ?></strong>
                                                <?php if ($request['request_type'] === 'seller'): ?>
                                                    (<?= htmlspecialchars($request['business_name']) ?>)
                                                <?php elseif ($request['request_type'] === 'auction'): ?>
                                                    • <?= htmlspecialchars($request['auction_title']) ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="approval-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                <input type="hidden" name="user_id" value="<?= $request['user_id'] ?>">
                                                <input type="hidden" name="request_type" value="<?= $request['request_type'] ?>">
                                                <input type="hidden" name="action" value="approved">
                                                <button type="submit" class="btn-approve">Approve</button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                <input type="hidden" name="user_id" value="<?= $request['user_id'] ?>">
                                                <input type="hidden" name="request_type" value="<?= $request['request_type'] ?>">
                                                <input type="hidden" name="action" value="rejected">
                                                <button type="submit" class="btn-reject">Reject</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="approval-item" style="justify-content: center;">
                                    <p>No pending requests.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="tab-content" id="history">
                        <div class="approval-items">
                            <?php
                            // Get approval history
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
                                    COALESCE(sr.updated_at, ar.updated_at) as updated_at,
                                    u.username,
                                    sr.business_name,
                                    ar.title as auction_title
                                FROM users u
                                LEFT JOIN seller_requests sr ON u.id = sr.user_id
                                LEFT JOIN auction_requests ar ON u.id = ar.user_id
                                WHERE COALESCE(sr.status, ar.status) IN ('approved', 'rejected')
                                ORDER BY COALESCE(sr.updated_at, ar.updated_at) DESC
                                LIMIT 10
                            ");
                            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (!empty($history)): 
                                foreach ($history as $item): ?>
                                    <div class="history-item">
                                        <div class="approval-icon">
                                            <?php if ($item['request_type'] === 'seller'): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="12" cy="7" r="4"></circle>
                                                </svg>
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="approval-details">
                                            <p class="approval-title">
                                                <?= $item['request_type'] === 'seller' ? 'Seller Verification' : 'New Auction Listing' ?>
                                            </p>
                                            <p class="approval-info">
                                                <?= htmlspecialchars($item['username']) ?> • 
                                                <?= date('M d, Y', strtotime($item['updated_at'])) ?>
                                            </p>
                                        </div>
                                        <span class="status-badge status-<?= $item['status'] ?>">
                                            <?= ucfirst($item['status']) ?>
                                        </span>
                                    </div>
                                <?php endforeach;
                            else: ?>
                                <div class="history-item" style="justify-content: center;">
                                    <p>No approval history found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs and contents
                document.querySelectorAll('.tab, .tab-content').forEach(el => el.classList.remove('active'));
                // Add active class to clicked tab and corresponding content
                tab.classList.add('active');
                document.getElementById(tab.dataset.tab).classList.add('active');
            });
        });
    </script>
</body>
</html>
``` 
