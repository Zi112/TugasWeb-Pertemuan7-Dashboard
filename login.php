<?php
require __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirectTo('dashboard.php');
}

$errors = [];
$old = ['email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $old['email'] = sanitize($email);

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } else {
        $user = findUserByEmail($email);

        // Pesan error SENGAJA dibuat generik (tidak bilang "email tidak
        // ditemukan" atau "password salah" secara terpisah) — praktik
        // keamanan umum, supaya orang lain tidak bisa menebak-nebak email
        // mana saja yang terdaftar di sistem.
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Email atau password salah.';
        } else {
            logUserIn($user);

            // --- Bonus: Remember Me dengan cookies ---
            if ($remember) {
                $rawToken = generateSecureToken();
                $users = getUsers();
                foreach ($users as &$u) {
                    if ($u['id'] === $user['id']) {
                        $u['remember_token_hash'] = password_hash($rawToken, PASSWORD_DEFAULT);
                    }
                }
                unset($u);
                saveUsers($users);

                setcookie(
                    REMEMBER_COOKIE,
                    $user['id'] . ':' . $rawToken,
                    [
                        'expires' => time() + (REMEMBER_DAYS * 24 * 60 * 60),
                        'path' => '/',
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]
                );
            }

            redirectTo('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="card__icon">🔐</div>
            <h1 class="card__title">Masuk ke Akun</h1>
            <p class="card__subtitle">Login untuk mengakses dashboard kamu</p>

            <?php if (!empty($_GET['registered'])): ?>
                <div class="alert alert--success">✅ Pendaftaran berhasil! Silakan login.</div>
            <?php endif; ?>

            <?php if (!empty($_GET['logged_out'])): ?>
                <div class="alert alert--success">👋 Kamu berhasil logout.</div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert--error">
                    <?php foreach ($errors as $error): ?>
                        <div>⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= $old['email'] ?>" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="checkbox-field">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember" style="margin:0; font-weight:400;">Ingat saya selama 30 hari</label>
                </div>

                <button type="submit" class="btn">Login</button>
            </form>

            <p class="card__footer">
                Belum punya akun? <a href="register.php">Daftar di sini</a>
            </p>
        </div>
    </div>
</body>
</html>
