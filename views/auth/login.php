<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$error = '';

// Include database connection
require_once __DIR__ . '/../../config/config.php';
global $pdo;

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Security verification failed. Please try again.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Rate limiting (simple implementation)
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt'] = time();
        }

        // If 5+ failed attempts within 15 minutes
        if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_attempt']) < 900) {
            $error = 'Too many failed attempts. Please try again later.';
        } else {
            // Fetch user from database by username or email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Reset login attempts on success
                $_SESSION['login_attempts'] = 0;

                // Set session with additional security
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
                $_SESSION['login_time'] = time();
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                header('Location: /home');
                exit;
            } else {
                // Track failed login attempts
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt'] = time();
                $error = 'Invalid username or password.';
            }
        }
    }
}

// Regenerate CSRF token for GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Hem & Ivy</title>
    <link rel="stylesheet" href="/assets/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <!-- Add favicon -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h1 class="login-logo">Hem <span>&</span> Ivy</h1>
                    <p class="login-subtitle">Welcome back to refined luxury</p>
                </div>
                
                <div class="login-form-container">
                    <h2>Sign In</h2>
                    <?php if ($error): ?>
                        <div class="login-error" role="alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" class="login-form">
                        <!-- CSRF Protection -->
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <input type="text" id="username" name="username" placeholder="Enter your username" autocomplete="username" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                                <button type="button" id="togglePassword" class="password-toggle" aria-label="Toggle password visibility">
                                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="eye-off-icon hidden" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c-7 0-11 8-11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-checkbox">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <div class="oauth-divider">
                            <div class="divider-line"><span>or</span></div>
                            <a href="/auth/google" class="google-btn">
                                <svg class="google-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48">
                                    <g>
                                        <path fill="#4285F4" d="M44.5 20H24v8.5h11.7C34.7 33.1 30.1 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.1 8.1 2.9l6.1-6.1C34.5 6.5 29.6 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20c11 0 19.7-8 19.7-20 0-1.3-.1-2.7-.2-4z"/>
                                        <path fill="#34A853" d="M6.3 14.7l7 5.1C15.2 16.1 19.2 13 24 13c3.1 0 5.9 1.1 8.1 2.9l6.1-6.1C34.5 6.5 29.6 4 24 4c-7.2 0-13.4 4.1-16.7 10.7z"/>
                                        <path fill="#FBBC05" d="M24 44c5.6 0 10.5-1.8 14.3-4.9l-6.6-5.4C29.7 35.5 27 36.5 24 36.5c-6.1 0-10.7-2.9-13.7-7.1l-7 5.4C6.6 39.9 14.2 44 24 44z"/>
                                        <path fill="#EA4335" d="M44.5 20H24v8.5h11.7c-1.3 3.5-4.7 6-8.7 6-6.1 0-10.7-4.6-10.7-10.5s4.6-10.5 10.7-10.5c2.7 0 5.1.9 7 2.5l6.1-6.1C34.5 6.5 29.6 4 24 4c-7.2 0-13.4 4.1-16.7 10.7l7 5.1C15.2 16.1 19.2 13 24 13c3.1 0 5.9 1.1 8.1 2.9l6.1-6.1C34.5 6.5 29.6 4 24 4c-7.2 0-13.4 4.1-16.7 10.7z"/>
                                    </g>
                                </svg>
                                Sign in with Google
                            </a>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Sign In</button>
                        </div>
                        <div class="form-footer">
                            <a href="/forgot-password" class="forgot-password">Forgot password?</a>
                            <span class="divider">•</span>
                            <a href="/register" class="create-account">Create an account</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --imperial-purple: #4B286D;
            --aged-gold: #C9A050;
            --charcoal-velvet: #3A3A3A;
            --error-red: #E53935;
            --success-green: #43A047;
            --light-gray: #F5F5F5;
            --form-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--light-gray);
            color: var(--charcoal-velvet);
            line-height: 1.6;
            background: linear-gradient(135deg, rgba(75, 40, 109, 0.05) 0%, rgba(201, 160, 80, 0.05) 100%);
            min-height: 100vh;
        }

        /* Login Page Specific Styles */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .login-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--form-shadow);
            overflow: hidden;
            width: 100%;
            max-width: 480px;
            position: relative;
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--imperial-purple), #3a1c58);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--aged-gold), #dbb978);
        }
        
        .login-logo {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        .login-logo span {
            color: var(--aged-gold);
            font-style: italic;
        }
        
        .login-subtitle {
            font-size: 14px;
            opacity: 0.9;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 1px;
        }
        
        .login-form-container {
            padding: 40px 30px;
        }
        
        .login-form-container h2 {
            color: var(--imperial-purple);
            font-size: 24px;
            margin-bottom: 25px;
            text-align: center;
            font-family: 'Playfair Display', serif;
        }
        
        .login-error {
            display: flex;
            align-items: center;
            background-color: rgba(229, 57, 53, 0.1);
            border-left: 3px solid var(--error-red);
            color: var(--error-red);
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 14px;
            border-radius: 4px;
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }
        
        .login-error svg {
            margin-right: 10px;
            min-width: 18px;
        }
        
        @keyframes shake {
            10%, 90% { transform: translateX(-1px); }
            20%, 80% { transform: translateX(2px); }
            30%, 50%, 70% { transform: translateX(-3px); }
            40%, 60% { transform: translateX(3px); }
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--charcoal-velvet);
            font-weight: 500;
        }
        
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            color: #999;
            pointer-events: none;
            transition: color 0.3s ease;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            border-color: var(--imperial-purple);
            box-shadow: 0 0 0 3px rgba(75, 40, 109, 0.1);
            outline: none;
        }
        
        .form-group input:focus + .input-icon,
        .form-group input:focus ~ .input-icon {
            color: var(--imperial-purple);
        }
        
        .password-toggle {
            position: absolute;
            right: 16px;
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            transition: color 0.3s ease;
            padding: 0;
        }
        
        .password-toggle:hover {
            color: var(--imperial-purple);
        }
        
        .hidden {
            display: none;
        }
        
        .form-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .form-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            accent-color: var(--imperial-purple);
        }
        
        .form-checkbox label {
            font-size: 14px;
            cursor: pointer;
        }
        
        .form-actions {
            margin-top: 30px;
        }
        
        .form-actions .btn {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            background: linear-gradient(to right, var(--imperial-purple), #5a3484);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .form-actions .btn:hover {
            background: linear-gradient(to right, #5a3484, var(--imperial-purple));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(75, 40, 109, 0.2);
        }
        
        .form-actions .btn:active {
            transform: translateY(0);
        }
        
        .form-footer {
            text-align: center;
            margin-top: 25px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 14px;
        }
        
        .forgot-password, .create-account {
            color: var(--imperial-purple);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .forgot-password:hover, .create-account:hover {
            color: var(--aged-gold);
            text-decoration: underline;
        }
        
        .divider {
            margin: 0 10px;
            color: #ccc;
        }
        
        .oauth-divider {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 24px;
        }
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 12px 18px;
            font-size: 15px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            color: #444;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(66,133,244,0.08);
            transition: background 0.2s, box-shadow 0.2s;
            margin-bottom: 16px;
            width: 100%;
            max-width: 320px;
        }
        .google-btn:hover {
            background: #f7f7f7;
            box-shadow: 0 4px 16px rgba(66,133,244,0.12);
        }
        .google-icon {
            margin-right: 10px;
            vertical-align: middle;
        }
        .divider-line {
            width: 100%;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
            line-height: 0.1em;
            margin: 12px 0 18px 0;
        }
        .divider-line span {
            background: #fff;
            padding: 0 12px;
            color: #aaa;
            font-size: 13px;
        }
        
        @media (max-width: 576px) {
            .login-card {
                box-shadow: none;
                border-radius: 0;
            }
            
            .login-container {
                padding: 0;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-form-container {
                padding: 30px 20px;
            }
        }
    </style>

    <script>
        // Toggle password visibility
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.querySelector('.eye-icon');
            const eyeOffIcon = document.querySelector('.eye-off-icon');
            
            togglePassword.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.classList.add('hidden');
                    eyeOffIcon.classList.remove('hidden');
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.classList.remove('hidden');
                    eyeOffIcon.classList.add('hidden');
                }
            });
            
            // Improve focus states for inputs
            const inputs = document.querySelectorAll('.form-group input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('input-focus');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('input-focus');
                });
            });
        });
    </script>
</body>
</html>