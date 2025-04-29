<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;

if (empty($user) || ($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    require __DIR__ . '/../views/errors/403.php';
    exit;
}

require_once __DIR__ . '/../config/config.php';
global $pdo;

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    echo "<p>User not found.</p>";
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
$stmt->execute([$id]);
$editUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$editUser) {
    echo "<p>User not found.</p>";
    exit;
}

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    if ($username && $email && in_array($role, ['user', 'admin'])) {
        $update = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
        $update->execute([$username, $email, $role, $id]);
        header("Location: /admin/users?success=User+updated");
        exit;
    } else {
        $error = "All fields are required and role must be valid.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | Hem & Ivy Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <style>
        .form-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 32px 24px;
            max-width: 420px;
            margin: 40px auto;
        }
        .form-card h2 {
            margin-bottom: 18px;
            font-size: 1.3rem;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 6px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 9px 10px;
            border: 1px solid #cfd8dc;
            border-radius: 5px;
            font-size: 1rem;
        }
        .form-actions {
            margin-top: 20px;
            text-align: right;
        }
        .btn-primary {
            background: #2d7ff9;
            color: #fff;
            border: none;
            padding: 9px 22px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.18s;
        }
        .btn-primary:hover {
            background: #195bb5;
        }
        .error-msg {
            color: #d32f2f;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="admin-main">
        <div class="form-card">
            <h2>Edit User</h2>
            <?php if (!empty($error)): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" value="<?= htmlspecialchars($editUser['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($editUser['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role" required>
                        <option value="user" <?= $editUser['role'] === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="/admin/users" style="margin-left:12px;">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
