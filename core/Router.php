<?php
class Router {
    protected $routes = [];
    protected $staticPaths = ['/assets', '/uploads'];

    public function get($route, $handler) {
        $this->routes['GET'][$route] = $handler;
    }

    public function post($route, $handler) {
        $this->routes['POST'][$route] = $handler;
    }

    public function isStaticPath($url) {
        foreach ($this->staticPaths as $path) {
            if (strpos($url, $path) === 0) {
                return true;
            }
        }
        return false;
    }

    public function serveStaticFile($url) {
        // Always resolve to public directory for static files
        $filePath = realpath(__DIR__ . '/../public' . $url);
        $publicDir = realpath(__DIR__ . '/../public');
        // Security: Only serve files inside /public
        if ($filePath && strpos($filePath, $publicDir) === 0 && file_exists($filePath)) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml'
            ];

            if (isset($mimeTypes[$extension])) {
                header('Content-Type: ' . $mimeTypes[$extension]);
            } else {
                header('Content-Type: application/octet-stream');
            }
            readfile($filePath);
            return true;
        }
        return false;
    }

    public function dispatch($url) {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = '/' . ltrim($url, '/');

        // Handle static files
        if ($this->isStaticPath($url)) {
            if ($this->serveStaticFile($url)) {
                return;
            }
        }

        // Handle exact routes
        if (isset($this->routes[$method][$url])) {
            call_user_func($this->routes[$method][$url]);
            return;
        }

        // Handle dynamic /biding/{slug} route for GET
        if ($method === 'GET' && preg_match('#^/biding/([^/]+)$#', $url, $matches)) {
            $_GET['biding_slug'] = $matches[1];
            require __DIR__ . '/../views/biding.php';
            return;
        }

        // Handle dynamic /biding/{slug} route for POST
        if ($method === 'POST' && preg_match('#^/biding/([^/]+)$#', $url, $matches)) {
            $_GET['biding_slug'] = $matches[1];
            require __DIR__ . '/../views/biding.php';
            return;
        }

        // Not found
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }
}
