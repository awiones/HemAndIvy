<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;

require_once __DIR__ . '/../config/config.php';

// Fetch all users
try {
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Users | Hem & Ivy</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="admin-main">
        <header class="admin-header">
            <div class="header-search">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Search users..." class="search-input">
            </div>
            <div class="header-actions">
                <!-- ...copy from dashboard.php if needed... -->
            </div>
        </header>
        <div class="dashboard-content">
            <div class="dashboard-welcome">
                <h1>User Management</h1>
                <p>View and manage all registered users.</p>
            </div>
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>All Users</h2>
                </div>
                <div class="card-body">
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                        <tr>
                            <th style="text-align:left;padding:8px;">ID</th>
                            <th style="text-align:left;padding:8px;">Username</th>
                            <th style="text-align:left;padding:8px;">Email</th>
                            <th style="text-align:left;padding:8px;">Role</th>
                            <th style="text-align:left;padding:8px;">Joined</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td style="padding:8px;"><?= htmlspecialchars($u['id']) ?></td>
                                <td style="padding:8px;"><?= htmlspecialchars($u['username']) ?></td>
                                <td style="padding:8px;"><?= htmlspecialchars($u['email']) ?></td>
                                <td style="padding:8px;"><?= htmlspecialchars($u['role']) ?></td>
                                <td style="padding:8px;"><?= htmlspecialchars($u['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" style="padding:8px;">No users found.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
