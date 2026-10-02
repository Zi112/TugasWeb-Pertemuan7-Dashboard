<?php
require __DIR__ . '/includes/functions.php';

// requirement #7: halaman ini diproteksi — kalau belum login, otomatis
// di-redirect ke login.php (fungsi ini juga coba login lewat cookie
// Remember Me dulu sebelum menyerah dan redirect).
requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="dashboard-header">
                <h1>👋 Dashboard</h1>
            </div>

            <div class="profile-info">
                <p>Nama: <strong><?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                <p>Email: <strong><?= htmlspecialchars($_SESSION['user_email'], ENT_QUOTES, 'UTF-8') ?></strong></p>
            </div>

            <p class="card__subtitle" style="margin-bottom:1.5rem;">
                Ini halaman privat — cuma bisa diakses kalau kamu sudah login.
                Coba buka halaman ini lewat tab penyamaran (incognito) tanpa
                login, kamu akan otomatis dilempar ke halaman login.
            </p>

            <div class="action-row">
                <a href="edit-profile.php" class="btn btn--ghost">Edit Profil</a>
                <a href="logout.php" class="btn btn--danger">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
