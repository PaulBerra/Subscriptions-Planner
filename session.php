<?php
function secure_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_secure' => isset($_SERVER['HTTPS']),
            'cookie_samesite' => 'Strict',
        ]);
    }
}

function is_logged_in() {
    return isset($_SESSION['user']);
}
