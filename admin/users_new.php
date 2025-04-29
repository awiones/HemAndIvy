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

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    $password = $_POST['password'] ?? '';

    if ($username && $email && $password && in_array($role, ['user', 'admin'])) {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$username, $email, $hashed, $role]);
            header("Location: /admin/users?success=User+created");
            exit;
        }
    } else {
        $error = "All fields are required and role must be valid.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User | Hem & Ivy Admin</title>
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
            <h2>Add New User</h2>
            <?php if (!empty($error)): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create User</button>
                    <a href="/admin/users" style="margin-left:12px;">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
