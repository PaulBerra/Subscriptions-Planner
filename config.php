<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict',
]);

$config = require __DIR__ . '/.env.php';

define('DB_DSN', $config['DB_DSN']);
define('DB_USER', $config['DB_USER']);
define('DB_PASS', $config['DB_PASS']);
