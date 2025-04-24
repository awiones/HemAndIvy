<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Router.php';

$router = new Router();
require __DIR__ . '/../routes/web.php';

// Handle static assets first
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($requestUri, '/assets/') === 0) {
    $filePath = __DIR__ . $requestUri;  // Changed to look in public directory
    if (file_exists($filePath)) {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $contentTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif'
        ];
        
        if (isset($contentTypes[$extension])) {
            header('Content-Type: ' . $contentTypes[$extension]);
            readfile($filePath);
            exit;
        }
    }
}

// Handle regular routes
if (php_sapi_name() === 'cli-server') {
    if ($requestUri === '/' || $requestUri === '') {
        $url = '/';
    } else {
        $url = ltrim($requestUri, '/');
    }
} else {
    $url = $_GET['url'] ?? '/';
}
$router->dispatch($url);
