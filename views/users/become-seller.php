<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/config.php';
global $pdo;

// Ensure user is logged in
if (empty($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

$userId = $_SESSION['user']['id'];
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $business_name = trim($_POST['business_name'] ?? '');
    $business_description = trim($_POST['business_description'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($business_name) || empty($business_description) || empty($phone)) {
        $error_message = 'All fields are required.';
    } else {
        try {
            // First update user's seller status
            $stmt = $pdo->prepare("UPDATE users SET seller_status = 'pending' WHERE id = ?");
            $stmt->execute([$userId]);
            
            // Then create seller request
            $stmt = $pdo->prepare("
                INSERT INTO seller_requests 
                (user_id, business_name, business_description, phone) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $business_name, $business_description, $phone]);
            
            $success_message = 'Your seller registration request has been submitted and is pending approval.';
            header('refresh:2;url=/users/profile');
        } catch (PDOException $e) {
            $error_message = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Seller - Hem & Ivy</title>
    <link rel="stylesheet" href="/assets/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .seller-container {
            max-width: 600px;
            margin: 80px auto 40px;
            padding: 0 20px;
        }

        .seller-form {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h1 {
            color: var(--imperial-purple);
            font-family: 'Playfair Display', serif;
            margin-bottom: 10px;
        }

        .form-header p {
            color: var(--charcoal-velvet);
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--charcoal-velvet);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--lavender-mist);
            border-radius: 4px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--aged-gold);
            outline: none;
            box-shadow: 0 0 0 2px rgba(194, 178, 128, 0.1);
        }

        .btn-submit {
            background: var(--aged-gold);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: #d4c28e;
            transform: translateY(-1px);
        }

        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="seller-container">
        <div class="seller-form">
            <div class="form-header">
                <h1>Become a Seller</h1>
                <p>Join our marketplace and start selling your items</p>
            </div>

            <?php if ($error_message): ?>
                <div class="message error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="message success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <form method="POST" action="/users/become-seller">
                <div class="form-group">
                    <label for="business_name">Business Name</label>
                    <input type="text" id="business_name" name="business_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="business_description">Business Description</label>
                    <textarea id="business_description" name="business_description" class="form-control" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required>
                </div>

                <button type="submit" class="btn-submit">Register as Seller</button>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
