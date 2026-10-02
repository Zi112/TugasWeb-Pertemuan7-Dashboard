<?php
require __DIR__ . '/includes/functions.php';

// Coba login otomatis lewat cookie Remember Me kalau sesi sudah habis
// tapi cookie-nya masih ada.
if (!isset($_SESSION['user_id'])) {
    attemptLoginFromRememberCookie();
}

redirectTo(isset($_SESSION['user_id']) ? 'dashboard.php' : 'login.php');
