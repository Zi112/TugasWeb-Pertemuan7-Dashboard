<?php
/**
 * functions.php
 * File ini di-require di baris pertama SETIAP halaman. Isinya: mulai
 * session, baca/tulis data user dari file JSON, validasi/sanitasi, dan
 * semua urusan "Remember Me" (bonus).
 */

session_start();

define('USERS_FILE', __DIR__ . '/../data/users.json');
define('REMEMBER_COOKIE', 'remember_token');
define('REMEMBER_DAYS', 30);

/**
 * Ambil semua user dari file JSON (requirement #4: penyimpanan pakai JSON).
 * Kalau file/folder belum ada, dibuat dulu supaya tidak error, dan
 * dianggap belum ada user sama sekali.
 */
function getUsers(): array
{
    if (!file_exists(USERS_FILE)) {
        @mkdir(dirname(USERS_FILE), 0755, true);
        file_put_contents(USERS_FILE, '[]');
        return [];
    }

    $content = file_get_contents(USERS_FILE);
    $users = json_decode($content, true);

    // Kalau file JSON kosong/rusak, jangan crash — anggap saja kosong.
    return is_array($users) ? $users : [];
}

/**
 * Simpan array user kembali ke file JSON (format rapi, gampang dibaca manual).
 */
function saveUsers(array $users): bool
{
    $json = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return file_put_contents(USERS_FILE, $json) !== false;
}

/**
 * requirement #5: cek duplikasi email saat registrasi (case-insensitive).
 */
function emailExists(string $email): bool
{
    foreach (getUsers() as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            return true;
        }
    }
    return false;
}

/**
 * Cari satu user berdasarkan email. Return null kalau tidak ketemu.
 */
function findUserByEmail(string $email): ?array
{
    foreach (getUsers() as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            return $user;
        }
    }
    return null;
}

/**
 * requirement #9: sanitasi input dengan htmlspecialchars().
 */
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect ke halaman lain lalu langsung hentikan eksekusi script.
 */
function redirectTo(string $path): void
{
    header("Location: $path");
    exit;
}

/**
 * requirement #6: sistem login dengan session — ini yang "menandai"
 * seseorang sebagai sudah login, dengan menyimpan datanya ke $_SESSION.
 */
function logUserIn(array $user): void
{
    session_regenerate_id(true); // cegah session fixation attack
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
}

/**
 * Token acak yang aman secara kriptografis, dipakai untuk cookie Remember Me.
 */
function generateSecureToken(): string
{
    return bin2hex(random_bytes(32));
}

/**
 * requirement #7: proteksi halaman privat (dashboard, edit profil).
 * Kalau session belum login, coba dulu login otomatis lewat cookie
 * Remember Me — baru kalau itu juga gagal, redirect ke halaman login.
 */
function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        attemptLoginFromRememberCookie();
    }

    if (!isset($_SESSION['user_id'])) {
        redirectTo('login.php');
    }
}

/**
 * BONUS: "Remember Me" pakai cookie.
 * Cookie berisi "idUser:tokenAcak". Token ASLI tidak pernah disimpan di
 * server — yang disimpan cuma HASH-nya (password_hash), sama seperti
 * prinsip penyimpanan password. Jadi walau file users.json bocor, cookie
 * orang lain tetap tidak bisa dipalsukan dari situ.
 */
function attemptLoginFromRememberCookie(): void
{
    if (empty($_COOKIE[REMEMBER_COOKIE])) {
        return;
    }

    $parts = explode(':', $_COOKIE[REMEMBER_COOKIE], 2);
    if (count($parts) !== 2) {
        return;
    }
    [$userId, $rawToken] = $parts;

    foreach (getUsers() as $user) {
        if (
            $user['id'] === $userId &&
            !empty($user['remember_token_hash']) &&
            password_verify($rawToken, $user['remember_token_hash'])
        ) {
            logUserIn($user);
            return;
        }
    }
}
