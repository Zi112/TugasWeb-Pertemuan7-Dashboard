<?php
require __DIR__ . '/includes/functions.php';

// Kalau sudah login, tidak perlu daftar lagi — langsung ke dashboard.
if (isset($_SESSION['user_id'])) {
    redirectTo('dashboard.php');
}

$errors = [];
$success = false;

// Nilai lama untuk dikembalikan ke form kalau validasi gagal
// (biar user tidak perlu ngetik ulang dari nol). Password sengaja TIDAK
// dikembalikan, demi keamanan.
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $old['name'] = sanitize($name);
    $old['email'] = sanitize($email);

    // --- Validasi nama ---
    if ($name === '') {
        $errors[] = 'Nama tidak boleh kosong.';
    } elseif (mb_strlen($name) < 3) {
        $errors[] = 'Nama minimal 3 karakter.';
    }

    // --- Validasi email (requirement #2: filter_var) ---
    if ($email === '') {
        $errors[] = 'Email tidak boleh kosong.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } elseif (emailExists($email)) {
        // requirement #5: cek duplikasi email
        $errors[] = 'Email ini sudah terdaftar. Silakan login atau pakai email lain.';
    }

    // --- Validasi password ---
    if ($password === '') {
        $errors[] = 'Password tidak boleh kosong.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // --- Kalau semua validasi lolos, simpan user baru ---
    if (empty($errors)) {
        $users = getUsers();

        $newUser = [
            'id' => uniqid('u_', true),
            // Nama disimpan APA ADANYA (tidak di-escape saat disimpan).
            // Escaping cukup dilakukan SEKALI, nanti pas ditampilkan ke
            // HTML (lihat dashboard.php) — supaya tidak "ke-escape dobel"
            // yang bikin tampilan aneh (mis. <script> muncul jadi
            // &amp;lt;script&amp;gt; kalau di-escape dua kali).
            'name' => trim($name),
            'email' => strtolower($email),
            // requirement #3: password di-hash, TIDAK PERNAH disimpan plain text
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'remember_token_hash' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $users[] = $newUser;

        if (saveUsers($users)) {
            $success = true;
            $old = ['name' => '', 'email' => '']; // kosongkan form lagi
        } else {
            $errors[] = 'Gagal menyimpan data. Coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="card__icon">📝</div>
            <h1 class="card__title">Buat Akun Baru</h1>
            <p class="card__subtitle">Daftar untuk mengakses dashboard</p>

            <?php if ($success): ?>
                <div class="alert alert--success">
                    ✅ Pendaftaran berhasil! Silakan <a href="login.php" style="color:#bbf7d0; font-weight:700;">login</a> memakai akun barumu.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert--error">
                    <strong>⚠️ Pendaftaran gagal:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="register.php" novalidate>
                <div class="field">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="<?= $old['name'] ?>" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= $old['email'] ?>" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small>Minimal 6 karakter.</small>
                </div>

                <div class="field">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn">Daftar</button>
            </form>
            <?php endif; ?>

            <p class="card__footer">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </p>
        </div>
    </div>
</body>
</html>
