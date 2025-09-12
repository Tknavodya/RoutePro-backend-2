<?php
// Router script for PHP built-in development server

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Check if it's a file request for static assets
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $path)) {
    // For static files, check if they exist and serve them
    $file = __DIR__ . $path;
    if (file_exists($file)) {
        return false; // Let the built-in server handle it
    } else {
        http_response_code(404);
        echo "File not found: " . $path;
        return true;
    }
}

// For all other requests, route to index.php
require_once __DIR__ . '/index.php';
return true;
?>
