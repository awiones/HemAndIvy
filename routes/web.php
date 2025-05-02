<?php
// Ensure session is started before any session usage
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load .env if not already loaded
if (!getenv('GOOGLE_CLIENT_ID')) {
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0 || strpos(trim($line), '//') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                if (!getenv($name)) {
                    putenv("$name=$value");
                }
            }
        }
    }
}

// Main routes
$router->get('/', function() {
    require __DIR__ . '/../views/home.php';
});

$router->get('/home', function() {
    require __DIR__ . '/../views/home.php';
});

$router->get('/about', function() {
    require __DIR__ . '/../views/about.php';
});

// Category routes
$router->get('/collections', function() {
    require __DIR__ . '/../views/categories.php';
});

// Optionally remove or comment out this line if you don't want /categories
// $router->get('/categories', function() {
//     require __DIR__ . '/../views/categories.php';
// });

// Authentication routes
$router->get('/login', function() {
    require __DIR__ . '/../views/auth/login.php';
});

$router->post('/login', function() {
    require __DIR__ . '/../views/auth/login.php';
});

$router->get('/logout', function() {
    require __DIR__ . '/../views/auth/logout.php';
});

$router->get('/register', function() {
    require __DIR__ . '/../views/auth/register.php';
});

$router->post('/register', function() {
    require __DIR__ . '/../views/auth/register.php';
});

// Google OAuth 2.0 login route
$router->get('/auth/google', function() {
    // Load credentials from .env or config
    $client_id = getenv('GOOGLE_CLIENT_ID');
    // Use the correct callback route
    $redirect_uri = 'http://localhost:8000/auth/google/callback';
    $scope = 'openid email profile';
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth2state'] = $state;

    if (!$client_id) {
        http_response_code(500);
        echo "Google Client ID not set. Check your .env and server configuration.";
        exit;
    }

    $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => $scope,
        'state' => $state,
        'access_type' => 'online',
        'prompt' => 'select_account'
    ]);
    header('Location: ' . $auth_url);
    exit;
});

// Google OAuth 2.0 callback route
$router->get('/auth/google/callback', function() {
    $client_id = getenv('GOOGLE_CLIENT_ID');
    $client_secret = getenv('GOOGLE_CLIENT_SECRET');
    $redirect_uri = getenv('GOOGLE_REDIRECT_URI');
    $code = $_GET['code'] ?? null;
    $state = $_GET['state'] ?? null;

    if (!$code || !$state || !isset($_SESSION['oauth2state']) || $state !== $_SESSION['oauth2state']) {
        echo "Invalid OAuth state or missing code.";
        exit;
    }
    unset($_SESSION['oauth2state']);

    // Exchange code for access token
    $token_url = 'https://oauth2.googleapis.com/token';
    $post_fields = [
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];
    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $token_data = json_decode($response, true);

    if (!isset($token_data['access_token'])) {
        echo "Failed to get access token.";
        exit;
    }

    // Fetch user info
    $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
    $ch = curl_init($user_info_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token_data['access_token']
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $user_info_response = curl_exec($ch);
    curl_close($ch);
    $user_info = json_decode($user_info_response, true);

    if (!isset($user_info['id'])) {
        echo "Failed to fetch user info.";
        exit;
    }

    // Connect to DB
    require_once __DIR__ . '/../config/config.php';
    global $pdo;

    // Check if user exists by google_id or email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = :google_id OR email = :email LIMIT 1");
    $stmt->execute([
        'google_id' => $user_info['id'],
        'email' => $user_info['email']
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Update google_id and avatar if needed
        if (!$user['google_id'] || $user['google_id'] !== $user_info['id'] || $user['avatar_url'] !== ($user_info['picture'] ?? null)) {
            $stmt = $pdo->prepare("UPDATE users SET google_id = :google_id, avatar_url = :avatar_url WHERE id = :id");
            $stmt->execute([
                'google_id' => $user_info['id'],
                'avatar_url' => $user_info['picture'] ?? null,
                'id' => $user['id']
            ]);
        }
    } else {
        // Register new user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, google_id, avatar_url) VALUES (:username, :email, :google_id, :avatar_url)");
        $username = explode('@', $user_info['email'])[0];
        $stmt->execute([
            'username' => $username,
            'email' => $user_info['email'],
            'google_id' => $user_info['id'],
            'avatar_url' => $user_info['picture'] ?? null
        ]);
        $user = [
            'id' => $pdo->lastInsertId(),
            'username' => $username,
            'email' => $user_info['email'],
            'role' => 'user'
        ];
    }

    // Log in user
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'] ?? 'user'
    ];
    $_SESSION['login_time'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    session_regenerate_id(true);

    header('Location: /home');
    exit;
});

// Auction routes (public)
$router->get('/auctions', function() {
    require __DIR__ . '/../views/auction.php';
});
$router->get('/auction', function() { // alias for /auctions
    require __DIR__ . '/../views/auction.php';
});

// Admin auction management
$router->get('/admin/auctions', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/auctions.php';
});

$router->post('/admin/auctions', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/auctions.php';
});

$router->get('/admin/auctions/new', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/auctions_new.php';
});
$router->post('/admin/auctions/new', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/auctions_new.php';
});

// Admin categories management routes
$router->get('/admin/categories', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/categories.php';
});
$router->post('/admin/categories', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/categories.php';
});

// Admin users management routes
$router->get('/admin/users', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/users.php';
});

$router->get('/admin/users/new', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/users_new.php';
});

$router->post('/admin/users/new', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/users_new.php';
});

$router->get('/admin/users/edit', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/users_edit.php';
});

$router->post('/admin/users/edit', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/users_edit.php';
});

$router->get('/admin/users/delete', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/users_delete.php';
});

// Add admin approvals route
$router->get('/admin/approvals', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/approvals.php';
});

$router->post('/admin/approvals', function() {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    require __DIR__ . '/../admin/approvals.php';
});

// Auction routes
$router->get('/auctions', function() {
    require __DIR__ . '/../views/auctions/index.php';
});

$router->get('/auctions/create', function() {
    require __DIR__ . '/../views/auctions/create.php';
});

// Admin dashboard route
$router->get('/admin/dashboard', function() {
    if (empty($_SESSION['user'])) {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    // Always fetch the latest user role from DB for security
    require_once __DIR__ . '/../config/config.php';
    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $_SESSION['user']['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    // Optionally update session role to keep it in sync
    $_SESSION['user']['role'] = $user['role'];
    require __DIR__ . '/../admin/dashboard.php';
});

// Add admin activity log route
$router->get('/admin/activity', function() {
    if (empty($_SESSION['user'])) {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    // Always fetch the latest user role from DB for security
    require_once __DIR__ . '/../config/config.php';
    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $_SESSION['user']['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
    $_SESSION['user']['role'] = $user['role'];
    require __DIR__ . '/../admin/activity.php';
});

$router->get('/favorite', function() {
    require __DIR__ . '/../views/favorite.php';
});

// User settings route (must be under /users/settings)
$router->get('/users/settings', function() {
    require __DIR__ . '/../views/users/settings.php';
});

$router->post('/users/settings', function() {
    require __DIR__ . '/../views/users/settings.php';
});

// Add profile route
$router->get('/users/profile', function() {
    require __DIR__ . '/../views/users/profile.php';
});

// Add become-seller routes
$router->get('/users/become-seller', function() {
    require __DIR__ . '/../views/users/become-seller.php';
});

$router->post('/users/become-seller', function() {
    require __DIR__ . '/../views/users/become-seller.php';
});

// Add favorite route (for AJAX POST)
$router->post('/favorite', function() {
    if (empty($_SESSION['user'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'You must be logged in to favorite.']);
        exit;
    }
    $userId = $_SESSION['user']['id'];
    $auctionId = $_POST['auction_id'] ?? null;
    if (!$auctionId || !is_numeric($auctionId)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid auction ID.']);
        exit;
    }
    require_once __DIR__ . '/../config/config.php';
    global $pdo;
    // Check if already favorited
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND auction_id = ?");
    $stmt->execute([$userId, $auctionId]);
    $favorite = $stmt->fetch();
    if ($favorite) {
        // Remove favorite (unfavorite)
        $delStmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND auction_id = ?");
        $delStmt->execute([$userId, $auctionId]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'favorited' => false]);
        exit;
    } else {
        // Insert favorite
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, auction_id) VALUES (?, ?)");
        $stmt->execute([$userId, $auctionId]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'favorited' => true]);
        exit;
    }
});

$router->get('/search', function() {
    require __DIR__ . '/../views/search.php';
});

// Bidding page routes (GET and POST)
$router->get('/biding/{slug}', function($slug) {
    $_GET['biding_slug'] = $slug;
    require __DIR__ . '/../views/biding.php';
});
$router->post('/biding/{slug}', function($slug) {
    $_GET['biding_slug'] = $slug;
    require __DIR__ . '/../views/biding.php';
});

// Place Bid POST handler (no need for place-bid.php file)
$router->post('/place-bid.php', function() {
    require_once __DIR__ . '/../config/config.php';
    if (session_status() === PHP_SESSION_NONE) session_start();

    $slug = $_GET['biding_slug'] ?? '';
    $bidAmount = $_POST['bid_amount'] ?? '';
    $bidAmount = floatval(str_replace(',', '', $bidAmount));
    $redirectUrl = '/biding/' . urlencode($slug);

    if (!$slug || !$bidAmount) {
        header("Location: $redirectUrl?error=Invalid+request");
        exit;
    }

    // Find auction by slug
    $stmt = $pdo->query("SELECT * FROM auctions WHERE status = 'active'");
    $auction = null;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $titleSlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $row['title']));
        if ($slug === $titleSlug) {
            $auction = $row;
            break;
        }
    }
    if (!$auction) {
        header("Location: $redirectUrl?error=Auction+not+found");
        exit;
    }

    // Validate bid
    if ($bidAmount < 0.5) {
        header("Location: $redirectUrl?error=Minimum+raise+is+$0.50");
        exit;
    }
    if ($bidAmount <= 0) {
        header("Location: $redirectUrl?error=Raise+amount+must+be+greater+than+zero");
        exit;
    }

    // Update price
    $newPrice = $auction['price'] + $bidAmount;
    $stmt = $pdo->prepare("UPDATE auctions SET price = ? WHERE id = ?");
    if ($stmt->execute([$newPrice, $auction['id']])) {
        header("Location: $redirectUrl?success=Your+bid+was+placed+successfully");
        exit;
    } else {
        header("Location: $redirectUrl?error=Failed+to+place+bid");
        exit;
    }
});