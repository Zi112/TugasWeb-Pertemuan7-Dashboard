<?php
require __DIR__ . '/includes/functions.php';
requireLogin();

$errors = [];
$success = false;
$name = $_SESSION['user_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['name'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';

    $name = sanitize($newName);

    if ($newName === '') {
        $errors[] = 'Nama tidak boleh kosong.';
    } elseif (mb_strlen($newName) < 3) {
        $errors[] = 'Nama minimal 3 karakter.';
    }

    // Password baru bersifat opsional — kalau kosong, ya cuma nama yang diupdate.
    $wantsPasswordChange = $newPassword !== '';
    if ($wantsPasswordChange && strlen($newPassword) < 6) {
        $errors[] = 'Password baru minimal 6 karakter.';
    }

    if (empty($errors)) {
        $users = getUsers();
        $updated = false;

        foreach ($users as &$u) {
            if ($u['id'] === $_SESSION['user_id']) {
                // Ganti password wajib konfirmasi password LAMA dulu,
                // demi keamanan (mencegah orang lain iseng ganti password
                // kalau sesi login kamu ketinggalan di komputer umum).
                if ($wantsPasswordChange) {
                    if (!password_verify($currentPassword, $u['password_hash'])) {
                        $errors[] = 'Password saat ini salah, perubahan password dibatalkan.';
                        break;
                    }
                    $u['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }

                $u['name'] = trim($newName); // simpan apa adanya, escape cuma sekali saat ditampilkan
                $updated = true;
                break;
            }
        }
        unset($u);

        if ($updated && empty($errors)) {
            saveUsers($users);
            $_SESSION['user_name'] = trim($newName);
            $success = true;
            $name = $_SESSION['user_name'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="card__icon">✏️</div>
            <h1 class="card__title">Edit Profil</h1>
            <p class="card__subtitle"><?= htmlspecialchars($_SESSION['user_email'], ENT_QUOTES, 'UTF-8') ?></p>

            <?php if ($success): ?>
                <div class="alert alert--success">✅ Profil berhasil diperbarui.</div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert--error">
                    <?php foreach ($errors as $error): ?>
                        <div>⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit-profile.php" novalidate>
                <div class="field">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="field">
                    <label for="new_password">Password Baru (opsional)</label>
                    <input type="password" id="new_password" name="new_password">
                    <small>Kosongkan kalau tidak mau ganti password.</small>
                </div>

                <div class="field">
                    <label for="current_password">Password Saat Ini</label>
                    <input type="password" id="current_password" name="current_password">
                    <small>Wajib diisi HANYA kalau kamu mau ganti password.</small>
                </div>

                <button type="submit" class="btn">Simpan Perubahan</button>
            </form>

            <p class="card__footer">
                <a href="dashboard.php">← Kembali ke Dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>
