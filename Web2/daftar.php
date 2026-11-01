
<?php
require_once __DIR__ . '/config/koneksi.php';

// Jika pengguna sudah login, kembali ke beranda
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Ambil data dari formulir
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Sesuaikan dengan nama field konfirmasi pada formulir
$konfirmasi = $_POST['konfirmasi_password']
    ?? $_POST['konfirmasi']
    ?? $_POST['password_confirmation']
    ?? $_POST['confirm_password']
    ?? '';

// Fungsi untuk mengembalikan pengguna ke modal daftar
function gagalDaftar($pesan, $nama = '', $email = '')
{
    $_SESSION['register_error'] = $pesan;
    $_SESSION['register_nama'] = $nama;
    $_SESSION['register_email'] = $email;

    header('Location: index.php?daftar=gagal');
    exit;
}

// Validasi input
if ($nama === '' || $email === '' || $password === '') {
    gagalDaftar('Nama, email, dan password wajib diisi.', $nama, $email);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    gagalDaftar('Format email tidak valid.', $nama, $email);
}

if (strlen($password) < 8) {
    gagalDaftar('Password minimal 8 karakter.', $nama, $email);
}

if ($konfirmasi === '') {
    gagalDaftar('Konfirmasi password wajib diisi.', $nama, $email);
}

if ($password !== $konfirmasi) {
    gagalDaftar('Konfirmasi password tidak cocok.', $nama, $email);
}

try {
    // Pastikan email belum terdaftar
    $cek = $pdo->prepare(
        'SELECT id FROM users WHERE email = :email LIMIT 1'
    );
    $cek->execute(['email' => $email]);

    if ($cek->fetch()) {
        gagalDaftar('Email sudah terdaftar. Silakan masuk.', $nama, $email);
    }

    // Hash password sebelum disimpan
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Simpan akun baru ke database
    $sql = 'INSERT INTO users (nama, email, password)
            VALUES (:nama, :email, :password)';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nama' => $nama,
        'email' => $email,
        'password' => $passwordHash
    ]);

    // Pastikan data benar-benar tersimpan
    if ($stmt->rowCount() !== 1) {
        gagalDaftar('Akun belum berhasil disimpan. Silakan coba lagi.', $nama, $email);
    }

    // Login otomatis setelah pendaftaran berhasil
    session_regenerate_id(true);

    $_SESSION['user_id'] = $pdo->lastInsertId();
    $_SESSION['user_nama'] = $nama;
    $_SESSION['user_email'] = $email;

    unset(
        $_SESSION['register_error'],
        $_SESSION['register_nama'],
        $_SESSION['register_email']
    );

    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    // Catat detail error di log server, bukan menampilkannya ke pengunjung
    error_log('Pendaftaran gagal: ' . $e->getMessage());

    if ($e->getCode() === '23000') {
        gagalDaftar('Email sudah digunakan atau data melanggar aturan database.', $nama, $email);
    }

    gagalDaftar('Pendaftaran gagal karena masalah database. Periksa struktur tabel users.', $nama, $email);
}
