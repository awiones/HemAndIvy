<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/config.php';
global $pdo;

// Initialize message variables
$success_message = '';
$error_message = '';

// Ensure user is logged in
if (empty($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

$userId = $_SESSION['user']['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Hem & Ivy</title>
    <link rel="stylesheet" href="/assets/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --imperial-purple: #4b286d;
            --sage-green: #a3b18a;
            --lavender-mist: #d6cadd;
            --olive-leaf: #6e7f58;
            --antique-pearl: #f5f3ef;
            --charcoal-velvet: #2d2d2d;
            --aged-gold: #c2b280;
        }
        
        body {
            background-color: var(--antique-pearl);
            font-family: 'Montserrat', sans-serif;
            color: var(--charcoal-velvet);
            margin: 0;
            padding: 0;
        }
        
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 20px 40px;  /* Increased top padding from 40px to 80px */
        }
        
        .settings-header {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }
        
        .settings-header::after {
            content: "";
            display: block;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--imperial-purple), var(--lavender-mist));
            margin: 15px auto 0;
        }
        
        .settings-header h1 {
            color: var(--imperial-purple);
            font-family: 'Playfair Display', serif;
            margin-bottom: 20px;  /* Increased from 10px to 20px */
            font-size: 2.5rem;
        }
        
        .settings-header p {
            color: var(--charcoal-velvet);
            font-size: 1.1rem;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
        }
        
        .settings-nav {
            position: sticky;
            top: 20px;
            align-self: start;
        }
        
        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .nav-item {
            border-bottom: 1px solid var(--lavender-mist);
        }
        
        .nav-item:last-child {
            border-bottom: none;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: var(--charcoal-velvet);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .nav-link.active, .nav-link:hover {
            background-color: var(--imperial-purple);
            color: white;
        }
        
        .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        
        .settings-content {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            min-height: 600px;
        }
        
        .tab-content {
            display: none;
            padding: 30px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-title {
            color: var(--imperial-purple);
            font-family: 'Playfair Display', serif;
            margin-top: 0;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--lavender-mist);
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
            border-color: var(--imperial-purple);
            outline: none;
            box-shadow: 0 0 0 2px rgba(75, 40, 109, 0.1);
        }
        
        .btn {
            background: var(--imperial-purple);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #5f3587;
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--imperial-purple);
            border: 1px solid var(--imperial-purple);
        }
        
        .btn-secondary:hover {
            background: rgba(75, 40, 109, 0.1);
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #bd2130;
        }
        
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
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
        
        .user-profile {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .user-avatar-container {
            position: relative;
            margin-right: 30px;
        }
        
        .user-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--lavender-mist);
        }
        
        .avatar-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--imperial-purple), var(--aged-gold));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 48px;
        }
        
        .avatar-upload {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--imperial-purple);
            color: white;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .avatar-upload:hover {
            background: #5f3587;
        }
        
        .avatar-upload input {
            display: none;
        }
        
        .user-info h2 {
            margin: 0 0 5px;
            color: var(--imperial-purple);
        }
        
        .user-info p {
            margin: 0;
            color: var(--charcoal-velvet);
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--imperial-purple);
        }
        
        input:focus + .slider {
            box-shadow: 0 0 1px var(--imperial-purple);
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .notification-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--lavender-mist);
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-info h4 {
            margin: 0 0 5px;
            color: var(--imperial-purple);
        }
        
        .notification-info p {
            margin: 0;
            color: var(--charcoal-velvet);
            font-size: 0.9rem;
        }
        
        .two-factor-section {
            background-color: rgba(163, 177, 138, 0.1);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .two-factor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .two-factor-header h3 {
            margin: 0;
            color: var(--imperial-purple);
        }
        
        .connected-apps-item {
            display: flex;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid var(--lavender-mist);
        }
        
        .connected-apps-item:last-child {
            border-bottom: none;
        }
        
        .app-icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .app-icon.google {
            background-color: #4285F4;
        }
        
        .app-icon.facebook {
            background-color: #3b5998;
        }
        
        .app-icon.twitter {
            background-color: #1DA1F2;
        }
        
        .app-info {
            flex-grow: 1;
        }
        
        .app-info h4 {
            margin: 0 0 5px;
            color: var(--imperial-purple);
        }
        
        .app-info p {
            margin: 0;
            color: var(--charcoal-velvet);
            font-size: 0.9rem;
        }
        
        .app-actions {
            display: flex;
            gap: 10px;
        }
        
        .color-theme-options {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        
        .color-option {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid transparent;
        }
        
        .color-option.active {
            border-color: var(--imperial-purple);
        }
        
        .color-option.purple {
            background-color: var(--imperial-purple);
        }
        
        .color-option.green {
            background-color: var(--sage-green);
        }
        
        .color-option.gold {
            background-color: var(--aged-gold);
        }
        
        .address-item {
            padding: 15px;
            border: 1px solid var(--lavender-mist);
            border-radius: 8px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .address-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
        }
        
        .address-actions a {
            color: var(--imperial-purple);
            text-decoration: none;
        }
        
        .address-actions a:hover {
            text-decoration: underline;
        }
        
        .address-default {
            margin-top: 10px;
            font-weight: bold;
            color: var(--olive-leaf);
        }
        
        .payment-card {
            padding: 15px;
            border: 1px solid var(--lavender-mist);
            border-radius: 8px;
            margin-bottom: 15px;
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .card-icon {
            margin-right: 15px;
            font-size: 24px;
            color: var(--imperial-purple);
        }
        
        .card-info h4 {
            margin: 0 0 5px;
            color: var(--imperial-purple);
        }
        
        .card-info p {
            margin: 0;
            color: var(--charcoal-velvet);
        }
        
        .card-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
        }
        
        .password-meter {
            height: 5px;
            background-color: #e9ecef;
            border-radius: 3px;
            margin-top: 10px;
        }
        
        .password-meter-bar {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        
        .password-meter-bar.weak {
            width: 25%;
            background-color: #dc3545;
        }
        
        .password-meter-bar.medium {
            width: 50%;
            background-color: #ffc107;
        }
        
        .password-meter-bar.strong {
            width: 75%;
            background-color: #17a2b8;
        }
        
        .password-meter-bar.very-strong {
            width: 100%;
            background-color: #28a745;
        }
        
        .password-requirements {
            margin-top: 15px;
            font-size: 0.9rem;
        }
        
        .password-requirements ul {
            padding-left: 20px;
            margin: 10px 0 0;
        }
        
        .requirement {
            margin-bottom: 5px;
            color: #6c757d;
        }
        
        .requirement.met {
            color: #28a745;
        }
        
        .requirement i {
            margin-right: 5px;
        }
        
        .activity-log {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .activity-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid var(--lavender-mist);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(75, 40, 109, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--imperial-purple);
        }
        
        .activity-details {
            flex-grow: 1;
        }
        
        .activity-details h4 {
            margin: 0 0 5px;
            color: var(--imperial-purple);
        }
        
        .activity-details p {
            margin: 0;
            color: var(--charcoal-velvet);
            font-size: 0.9rem;
        }
        
        .activity-time {
            color: #6c757d;
            font-size: 0.8rem;
            white-space: nowrap;
            margin-left: 15px;
        }
        
        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .settings-nav {
                position: static;
                margin-bottom: 20px;
            }
            
            .nav-list {
                display: flex;
                flex-wrap: wrap;
            }
            
            .nav-item {
                flex: 1 1 calc(33.333% - 10px);
                min-width: 100px;
                border-bottom: none;
                border-right: 1px solid var(--lavender-mist);
            }
            
            .nav-link {
                flex-direction: column;
                text-align: center;
                padding: 10px;
            }
            
            .nav-link i {
                margin-right: 0;
                margin-bottom: 5px;
                font-size: 18px;
            }
            
            .user-profile {
                flex-direction: column;
                text-align: center;
            }
            
            .user-avatar-container {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="settings-container">
        <div class="settings-header">
            <h1>Account Settings</h1>
            <p>Customize your account preferences and manage your personal information</p>
        </div>

        <?php if ($success_message): ?>
            <div class="message success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="settings-grid">
            <div class="settings-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="#profile" class="nav-link active" onclick="showTab('profile'); return false;">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#security" class="nav-link" onclick="showTab('security'); return false;">
                            <i class="fas fa-lock"></i> Security
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#notifications" class="nav-link" onclick="showTab('notifications'); return false;">
                            <i class="fas fa-bell"></i> Notifications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#appearance" class="nav-link" onclick="showTab('appearance'); return false;">
                            <i class="fas fa-paint-brush"></i> Appearance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#addresses" class="nav-link" onclick="showTab('addresses'); return false;">
                            <i class="fas fa-map-marker-alt"></i> Addresses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#payment" class="nav-link" onclick="showTab('payment'); return false;">
                            <i class="fas fa-credit-card"></i> Payment
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#connected" class="nav-link" onclick="showTab('connected'); return false;">
                            <i class="fas fa-link"></i> Connected Apps
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#activity" class="nav-link" onclick="showTab('activity'); return false;">
                            <i class="fas fa-history"></i> Activity Log
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="settings-content">
                <!-- Profile Tab -->
                <div id="profile" class="tab-content active">
                    <h2 class="tab-title">Profile Information</h2>
                    
                    <div class="user-profile">
                        <div class="user-avatar-container">
                            <?php if (!empty($user['avatar_url'])): ?>
                                <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Profile Picture" class="user-avatar">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <?= htmlspecialchars(strtoupper(substr($user['username'], 0, 1))) ?>
                                </div>
                            <?php endif; ?>
                            
                            <label class="avatar-upload">
                                <i class="fas fa-camera"></i>
                                <input type="file" name="avatar" accept="image/*">
                            </label>
                        </div>
                        
                        <div class="user-info">
                            <h2><?= htmlspecialchars($user['username']) ?></h2>
                            <p><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                    
                    <form method="POST" action="/users/settings">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="">
                        </div>
                        
                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio" class="form-control" rows="4"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" class="form-control" value="">
                        </div>
                        
                        <button type="submit" class="btn">Save Changes</button>
                    </form>
                </div>
                
                <!-- Security Tab -->
                <div id="security" class="tab-content">
                    <h2 class="tab-title">Security Settings</h2>
                    
                    <div class="two-factor-section">
                        <div class="two-factor-header">
                            <h3>Two-Factor Authentication</h3>
                            <label class="toggle-switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <p>Add an extra layer of security to your account by enabling two-factor authentication.</p>
                        <button class="btn btn-secondary">Set Up Two-Factor Authentication</button>
                    </div>
                    
                    <h3>Change Password</h3>
                    <form method="POST" action="/users/settings">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control">
                            <div class="password-meter">
                                <div class="password-meter-bar medium"></div>
                            </div>
                            <div class="password-requirements">
                                <p>Password should contain:</p>
                                <ul>
                                    <li class="requirement met"><i class="fas fa-check"></i> At least 8 characters</li>
                                    <li class="requirement"><i class="fas fa-times"></i> Uppercase & lowercase letters</li>
                                    <li class="requirement"><i class="fas fa-times"></i> At least one number</li>
                                    <li class="requirement met"><i class="fas fa-check"></i> At least one special character</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                        </div>
                        
                        <button type="submit" class="btn">Update Password</button>
                    </form>
                    
                    <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--lavender-mist);">
                        <h3 style="color: #dc3545;">Danger Zone</h3>
                        <p>Once you delete your account, there is no going back. Please be certain.</p>
                        <button class="btn btn-danger">Delete Account</button>
                    </div>
                </div>
                
                <!-- Notifications Tab -->
                <div id="notifications" class="tab-content">
                    <h2 class="tab-title">Notification Preferences</h2>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <h4>Order Updates</h4>
                            <p>Receive notifications about your order status.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <h4>New Arrivals</h4>
                            <p>Get notified when new products are added to our collection.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <h4>Promotions & Sales</h4>
                            <p>Receive notifications about special offers and discounts.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <h4>Account Activity</h4>
                            <p>Get alerts about login attempts and account changes.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-info">
                            <h4>Wishlist Updates</h4>
                            <p>Get notified when items in your wishlist go on sale.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <h3 style="margin-top: 30px;">Notification Methods</h3>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" checked> Email Notifications
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox"> SMS Notifications
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" checked> Browser Notifications
                        </label>
                    </div>
                    
                    <button class="btn">Save Preferences</button>
                </div>
                
                <!-- Appearance Tab -->
                <div id="appearance" class="tab-content">
                    <h2 class="tab-title">Appearance Settings</h2>
                    
                    <div class="form-group">
                        <label>Theme Mode</label>
                        <div style="display: flex; gap: 20px; margin-top: 10px;">
                            <label style="display: flex; align-items: center;">
                                <input type="radio" name="theme" value="dark" style="margin-right: 8px;">
                                Dark Mode
                            </label>
                            <label style="display: flex; align-items: center;">
                                <input type="radio" name="theme" value="system" style="margin-right: 8px;">
                                System Default
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Primary Color</label>
                        <div class="color-theme-options">
                            <div class="color-option purple active" data-color="purple"></div>
                            <div class="color-option green" data-color="green"></div>
                            <div class="color-option gold" data-color="gold"></div>
                            <div class="color-option" style="background-color: #6a4c93;" data-color="lavender"></div>
                            <div class="color-option" style="background-color: #1b4965;" data-color="navy"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Font Size</label>
                        <select class="form-control">
                            <option value="small">Small</option>
                            <option value="medium" selected>Medium</option>
                            <option value="large">Large</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Layout Density</label>
                        <select class="form-control">
                            <option value="compact">Compact</option>
                            <option value="comfortable" selected>Comfortable</option>
                            <option value="spacious">Spacious</option>
                        </select>
                    </div>
                    
                    <button class="btn">Apply Settings</button>
                </div>
                
                <!-- Addresses Tab -->
                <div id="addresses" class="tab-content">
                    <h2 class="tab-title">Your Addresses</h2>
                    
                    <div class="address-item">
                        <div class="address-actions">
                            <a href="#" onclick="return false;"><i class="fas fa-edit"></i> Edit</a>
                            <a href="#" onclick="return false;"><i class="fas fa-trash"></i> Delete</a>
                        </div>
                        <h4>Home Address</h4>
                        <p>123 Main Street, Apt 4B</p>
                        <p>New York, NY 10001</p>
                        <p>United States</p>
                        <p>Phone: (555) 123-4567</p>
                        <div class="address-default">Default Shipping Address</div>
                    </div>
                    
                    <div class="address-item">
                        <div class="address-actions">
                            <a href="#" onclick="return false;"><i class="fas fa-edit"></i> Edit</a>
                            <a href="#" onclick="return false;"><i class="fas fa-trash"></i> Delete</a>
                        </div>
                        <h4>Work Address</h4>
                        <p>456 Business Ave, Suite 200</p>
                        <p>New York, NY 10004</p>
                        <p>United States</p>
                        <p>Phone: (555) 987-6543</p>
                    </div>
                    
                    <button class="btn" style="margin-top: 20px;"><i class="fas fa-plus"></i> Add New Address</button>
                </div>
                
                <!-- Payment Tab -->
                <div id="payment" class="tab-content">
                    <h2 class="tab-title">Payment Methods</h2>
                    
                    <div class="payment-card">
                        <div class="card-icon">
                            <i class="fab fa-cc-visa"></i>
                        </div>
                        <div class="card-info">
                            <h4>Visa ending in 4242</h4>
                            <p>Expires 12/2025</p>
                        </div>
                        <div class="card-actions">
                            <a href="#" onclick="return false;"><i class="fas fa-edit"></i> Edit</a>
                            <a href="#" onclick="return false;"><i class="fas fa-trash"></i> Delete</a>
                        </div>
                    </div>
                    
                    <div class="payment-card">
                        <div class="card-icon">
                            <i class="fab fa-cc-mastercard"></i>
                        </div>
                        <div class="card-info">
                            <h4>Mastercard ending in 8888</h4>
                            <p>Expires 09/2024</p>
                            <p class="address-default">Default Payment Method</p>
                        </div>
                        <div class="card-actions">
                            <a href="#" onclick="return false;"><i class="fas fa-edit"></i> Edit</a>
                            <a href="#" onclick="return false;"><i class="fas fa-trash"></i> Delete</a>
                        </div>
                    </div>
                    
                    <button class="btn" style="margin-top: 20px;"><i class="fas fa-plus"></i> Add Payment Method</button>
                </div>
                
                <!-- Connected Apps Tab -->
                <div id="connected" class="tab-content">
                    <h2 class="tab-title">Connected Applications</h2>
                    
                    <div class="connected-apps-item">
                        <div class="app-icon google">
                            <i class="fab fa-google"></i>
                        </div>
                        <div class="app-info">
                            <h4>Google</h4>
                            <p>Connected on October 12, 2023. This app can access your basic profile information.</p>
                        </div>
                        <div class="app-actions">
                            <button class="btn btn-secondary">Disconnect</button>
                        </div>
                    </div>
                    
                    <div class="connected-apps-item">
                        <div class="app-icon facebook">
                            <i class="fab fa-facebook-f"></i>
                        </div>
                        <div class="app-info">
                            <h4>Facebook</h4>
                            <p>Connected on January 5, 2024. This app can access your profile information and friend list.</p>
                        </div>
                        <div class="app-actions">
                            <button class="btn btn-secondary">Disconnect</button>
                        </div>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <h3>Connect More Apps</h3>
                        <p>Enhance your experience by connecting with these services.</p>
                        
                        <div style="display: flex; gap: 15px; margin-top: 20px;">
                            <button class="btn btn-secondary"><i class="fab fa-twitter"></i> Twitter</button>
                            <button class="btn btn-secondary"><i class="fab fa-apple"></i> Apple</button>
                            <button class="btn btn-secondary"><i class="fab fa-amazon"></i> Amazon</button>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Log Tab -->
                <div id="activity" class="tab-content">
                    <h2 class="tab-title">Recent Account Activity</h2>
                    
                    <div class="activity-log">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div class="activity-details">
                                <h4>Account Login</h4>
                                <p>New York, United States - Chrome on Windows</p>
                            </div>
                            <div class="activity-time">Today, 9:45 AM</div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="activity-details">
                                <h4>New Order Placed</h4>
                                <p>Order #HI78254 - 2 items ($145.00)</p>
                            </div>
                            <div class="activity-time">Yesterday, 3:12 PM</div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-address-card"></i>
                            </div>
                            <div class="activity-details">
                                <h4>Address Added</h4>
                                <p>Added new work address</p>
                            </div>
                            <div class="activity-time">Apr 28, 2025</div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="activity-details">
                                <h4>Payment Method Added</h4>
                                <p>Added Mastercard ending in 8888</p>
                            </div>
                            <div class="activity-time">Apr 26, 2025</div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="activity-details">
                                <h4>Profile Updated</h4>
                                <p>Changed email address</p>
                            </div>
                            <div class="activity-time">Apr 20, 2025</div>
                        </div>
                    </div>
                    
                    <button class="btn btn-secondary" style="margin-top: 20px;">Download Activity Log</button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script>
        function showTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to selected nav link
            document.querySelector(`.nav-link[href="#${tabId}"]`).classList.add('active');
        }
        
        // Password strength meter
        const passwordInput = document.getElementById('new_password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const value = this.value;
                const meterBar = document.querySelector('.password-meter-bar');
                
                if (value.length === 0) {
                    meterBar.className = 'password-meter-bar';
                    meterBar.style.width = '0';
                } else if (value.length < 6) {
                    meterBar.className = 'password-meter-bar weak';
                } else if (value.length < 10) {
                    meterBar.className = 'password-meter-bar medium';
                } else if (value.length < 12) {
                    meterBar.className = 'password-meter-bar strong';
                } else {
                    meterBar.className = 'password-meter-bar very-strong';
                }
                
                // Update requirements
                const requirements = document.querySelectorAll('.requirement');
                
                // At least 8 characters
                if (value.length >= 8) {
                    requirements[0].classList.add('met');
                    requirements[0].querySelector('i').className = 'fas fa-check';
                } else {
                    requirements[0].classList.remove('met');
                    requirements[0].querySelector('i').className = 'fas fa-times';
                }
                
                // Uppercase & lowercase
                if (/[a-z]/.test(value) && /[A-Z]/.test(value)) {
                    requirements[1].classList.add('met');
                    requirements[1].querySelector('i').className = 'fas fa-check';
                } else {
                    requirements[1].classList.remove('met');
                    requirements[1].querySelector('i').className = 'fas fa-times';
                }
                
                // At least one number
                if (/\d/.test(value)) {
                    requirements[2].classList.add('met');
                    requirements[2].querySelector('i').className = 'fas fa-check';
                } else {
                    requirements[2].classList.remove('met');
                    requirements[2].querySelector('i').className = 'fas fa-times';
                }
                
                // At least one special character
                if (/[^a-zA-Z0-9]/.test(value)) {
                    requirements[3].classList.add('met');
                    requirements[3].querySelector('i').className = 'fas fa-check';
                } else {
                    requirements[3].classList.remove('met');
                    requirements[3].querySelector('i').className = 'fas fa-times';
                }
            });
        }
        
        // Color theme options
        document.querySelectorAll('.color-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                document.querySelectorAll('.color-option').forEach(opt => {
                    opt.classList.remove('active');
                });
                
                // Add active class to selected option
                this.classList.add('active');
                
                // Get selected color
                const color = this.getAttribute('data-color');
                console.log(`Selected color theme: ${color}`);
                // In a real app, would apply the theme here
            });
        });
    </script>
</body>
</html>
