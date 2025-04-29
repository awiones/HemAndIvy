<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;

// Only allow admin access
if (empty($user) || ($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    require __DIR__ . '/../views/errors/403.php';
    exit;
}

require_once __DIR__ . '/../config/config.php';
global $pdo;

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
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .admin-main {
            padding: 32px 24px;
        }
        .dashboard-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 32px 24px;
            margin-bottom: 32px;
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .card-header h2 {
            font-size: 1.4rem;
            font-weight: 600;
            margin: 0;
        }
        .quick-action-btn {
            background: #2d7ff9;
            color: #fff;
            padding: 8px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
            border: none;
            box-shadow: 0 2px 8px rgba(45,127,249,0.08);
        }
        .quick-action-btn:hover {
            background: #195bb5;
        }
        .admin-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .admin-table th, .admin-table td {
            padding: 12px 14px;
            text-align: left;
        }
        .admin-table th {
            background: #f0f4fa;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e3e8f0;
        }
        .admin-table tr:nth-child(even) td {
            background: #f9fbfd;
        }
        .admin-table tr:hover td {
            background: #eaf2ff;
        }
        .btn-edit, .btn-delete {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 5px;
            font-size: 0.97rem;
            font-weight: 500;
            text-decoration: none;
            margin-right: 6px;
            transition: background 0.18s, color 0.18s;
        }
        .btn-edit {
            background: #eaf2ff;
            color: #195bb5;
            border: 1px solid #b3d1fa;
        }
        .btn-edit:hover {
            background: #195bb5;
            color: #fff;
        }
        .btn-delete {
            background: #fff0f0;
            color: #d32f2f;
            border: 1px solid #f7bcbc;
        }
        .btn-delete:hover {
            background: #d32f2f;
            color: #fff;
        }
        @media (max-width: 800px) {
            .dashboard-card, .admin-main {
                padding: 12px 4px;
            }
            .admin-table th, .admin-table td {
                padding: 8px 6px;
            }
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="admin-main">
        <header class="admin-header">
            <h1 style="font-size:2rem;font-weight:700;margin-bottom:18px;">Users Management</h1>
        </header>
        <div class="dashboard-content">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>All Users</h2>
                    <a href="/admin/users/new" class="quick-action-btn">Add User</a>
                </div>
                <div class="card-body">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['id']) ?></td>
                                <td><?= htmlspecialchars($u['username']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($u['role'])) ?></td>
                                <td><?= htmlspecialchars($u['created_at']) ?></td>
                                <td>
                                    <a href="/admin/users/edit?id=<?= urlencode($u['id']) ?>" class="btn-edit">Edit</a>
                                    <a href="/admin/users/delete?id=<?= urlencode($u['id']) ?>" class="btn-delete" onclick="return confirm('Delete this user?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="6">No users found.</td></tr>
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
