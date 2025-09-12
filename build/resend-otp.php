<?php
// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include the main index.php logic but set the path manually
$_SERVER['REQUEST_URI'] = '/auth/resend-otp';
require_once __DIR__ . '/index.php';
?>
