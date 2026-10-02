# Sistem Login/Register — Tugas Rutin 7

Dibangun pakai PHP Native (tanpa framework), penyimpanan data di file JSON.
Sudah diuji otomatis lewat browser sungguhan (16 skenario tes, termasuk
percobaan XSS) — bukan cuma ditulis lalu diserahkan begitu saja.

## Cara Menjalankan

Butuh PHP terpasang di komputer kamu (cek dengan `php --version` di
terminal/cmd). Kalau belum ada, install dulu (XAMPP/Laragon paling gampang
buat Windows, sudah termasuk PHP + Apache).

**Cara 1 — pakai PHP built-in server (paling gampang):**
```bash
cd folder-ini
php -S localhost:8000
```
Lalu buka `http://localhost:8000` di browser.

**Cara 2 — pakai XAMPP/Laragon/Apache:**
Taruh folder ini di dalam `htdocs` (XAMPP) atau `www` (Laragon), lalu akses
lewat `http://localhost/nama-folder/`.

⚠️ **Penting:** folder `data/` dan file `data/users.json` di dalamnya harus
bisa DITULISI oleh PHP (writable). Kalau muncul error "Gagal menyimpan
data", cek permission folder `data/`.

## Struktur File

```
├── index.php           → pintu masuk, redirect ke login/dashboard
├── register.php         → form + proses pendaftaran akun
├── login.php            → form + proses login (+ fitur Remember Me)
├── dashboard.php        → halaman privat, wajib login
├── edit-profile.php     → ubah nama & password (bonus)
├── logout.php           → proses logout
├── style.css             → styling semua halaman
├── includes/
│   └── functions.php    → semua fungsi inti (baca/tulis JSON, validasi, dll)
└── data/
    └── users.json        → "database" tempat data user disimpan
```

## Checklist Requirement

| # | Requirement | Lokasi di kode |
|---|---|---|
| 1 | Form registrasi + validasi (nama, email, password) | `register.php` |
| 2 | Validasi email dengan `filter_var()` | `register.php`, fungsi `FILTER_VALIDATE_EMAIL` |
| 3 | Password di-hash dengan `password_hash()` | `register.php`, `edit-profile.php` |
| 4 | Data disimpan di file JSON | `includes/functions.php` → `getUsers()`/`saveUsers()` |
| 5 | Cek duplikasi email saat registrasi | `includes/functions.php` → `emailExists()` |
| 6 | Sistem login dengan session | `login.php` → `logUserIn()` |
| 7 | Dashboard diproteksi (redirect kalau belum login) | `includes/functions.php` → `requireLogin()` |
| 8 | Logout dengan `session_destroy()` | `logout.php` |
| 9 | Sanitasi input dengan `htmlspecialchars()` | `includes/functions.php` → `sanitize()`, dan tiap `<?= ... ?>` di HTML |
| 10 | Pesan error & sukses yang jelas | Semua halaman, class `.alert--error` / `.alert--success` |

## Bonus yang Dikerjakan

- ⭐ **Remember Me dengan cookies** — centang "Ingat saya" saat login, nanti
  meski browser ditutup dan session habis, kamu tetap otomatis login
  (token disimpan ter-hash, bukan plain text, demi keamanan).
- ⭐ **Halaman edit profile** — ganti nama dan/atau password. Ganti
  password wajib konfirmasi password lama dulu.
- ⭐ **Tampilan CSS rapi** — dark theme konsisten di semua halaman.

## Catatan Keamanan yang Diterapkan

- Password **tidak pernah** disimpan dalam bentuk asli (plain text) —
  selalu di-hash pakai `password_hash()` (algoritma bcrypt).
- Pesan error login sengaja dibuat generik ("Email atau password salah")
  — tidak membedakan apakah emailnya yang tidak ada atau passwordnya yang
  salah, supaya orang lain tidak bisa menebak-nebak email mana saja yang
  terdaftar.
- `session_regenerate_id()` dipanggil tiap kali user login, mencegah
  serangan *session fixation*.
- Semua data yang berasal dari input user (nama, dll) di-escape dengan
  `htmlspecialchars()` **satu kali** di titik tampilnya — sudah dites
  tahan terhadap percobaan XSS (`<script>` tidak tereksekusi).
- Token "Remember Me" disimpan ter-hash di JSON (bukan token asli), dan
  otomatis dihapus saat logout.
