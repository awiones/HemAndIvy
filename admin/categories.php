<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;
require_once __DIR__ . '/../config/config.php';
global $pdo;

// Handle add category
$addError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $name = trim($_POST['category_name']);
    if ($name === '') {
        $addError = "Category name cannot be empty.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $addError = "Category already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            header("Location: /admin/categories");
            exit;
        }
    }
}

// Handle delete category
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $catId = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$catId]);
    header("Location: /admin/categories");
    exit;
}

// Fetch all categories with auction count (show all, even if not used in auctions)
$stmt = $pdo->query("
    SELECT c.*, 
        (SELECT COUNT(*) FROM auctions a WHERE a.category_id = c.id) AS auction_count
    FROM categories c
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories - Hem & Ivy Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        .categories-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        .categories-table th, .categories-table td {
            padding: 14px 18px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        .categories-table th {
            background: #f6f3fa;
            color: #4b286d;
            font-weight: 600;
        }
        .categories-table tr:last-child td {
            border-bottom: none;
        }
        .delete-btn {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 7px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .delete-btn:hover {
            background: #c0392b;
        }
        .add-category-form {
            margin-top: 24px;
            background: #f6f3fa;
            padding: 18px 24px;
            border-radius: 10px;
            max-width: 400px;
        }
        .add-category-form input[type="text"] {
            width: 70%;
            padding: 8px 12px;
            border: 1.5px solid #b3d8fd;
            border-radius: 5px;
            font-size: 15px;
            margin-right: 10px;
        }
        .add-category-form button {
            background: #4b286d;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 5px;
            font-size: 15px;
            cursor: pointer;
        }
        .add-category-form button:hover {
            background: #6d3fa0;
        }
        .error-msg {
            color: #e74c3c;
            margin-top: 8px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="admin-main">
        <header class="admin-header">
            <h1 style="font-size: 2rem; color: #4b286d; margin: 0;">Manage Categories</h1>
        </header>
        <div class="dashboard-card" style="margin-top: 32px;">
            <div class="card-header">
                <h2>Categories</h2>
            </div>
            <div class="card-body">
                <form class="add-category-form" method="post" action="/admin/categories">
                    <label for="category_name" style="font-weight:600;">Add New Category:</label><br>
                    <input type="text" name="category_name" id="category_name" placeholder="Category name" required>
                    <button type="submit">Add</button>
                    <?php if ($addError): ?>
                        <div class="error-msg"><?= htmlspecialchars($addError) ?></div>
                    <?php endif; ?>
                </form>
                <table class="categories-table">
                    <thead>
                        <tr>
                            <th style="width:60%;">Category Name</th>
                            <th style="width:20%;">ID</th>
                            <th style="width:20%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= htmlspecialchars($cat['name']) ?></td>
                            <td><?= $cat['id'] ?></td>
                            <td>
                                <form method="get" action="/admin/categories" style="display:inline;">
                                    <input type="hidden" name="delete" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Delete this category?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="3" style="color:#888;">No categories found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
