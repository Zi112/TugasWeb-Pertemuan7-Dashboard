<?php
require __DIR__ . '/includes/functions.php';

// Kalau ada cookie Remember Me, hapus juga token-nya dari data user
// (supaya cookie lama tidak bisa dipakai untuk login lagi kalau bocor).
if (!empty($_COOKIE[REMEMBER_COOKIE])) {
    [$userId] = explode(':', $_COOKIE[REMEMBER_COOKIE], 2);
    $users = getUsers();
    foreach ($users as &$u) {
        if ($u['id'] === $userId) {
            $u['remember_token_hash'] = null;
        }
    }
    unset($u);
    saveUsers($users);

    setcookie(REMEMBER_COOKIE, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// requirement #8: logout pakai session_destroy()
$_SESSION = [];
session_destroy();

redirectTo('login.php?logged_out=1');
