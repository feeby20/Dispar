
<?php
require_once __DIR__ . '/config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (isset($_SESSION['user_id'])) {
    header('Location: ' . (
        ($_SESSION['role'] ?? 'user') === 'admin'
        ? 'admin/berita.php'
        : 'index.php'
    ));
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['login_error'] = 'Email dan password wajib diisi.';
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT id, nama, email, password, role
         FROM users
         WHERE email = :email
         LIMIT 1'
    );

    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['login_error'] = 'Email atau password salah.';
        header('Location: index.php');
        exit;
    }

    $storedPassword = (string) $user['password'];
    $passwordValid = password_verify($password, $storedPassword);

    // Dukungan sementara untuk akun lama dengan password teks biasa.
    $info = password_get_info($storedPassword);

    if (
        !$passwordValid &&
        $info['algo'] === null &&
        hash_equals($storedPassword, $password)
    ) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);

        $update = $pdo->prepare(
            'UPDATE users SET password = :password WHERE id = :id'
        );

        $update->execute([
            'password' => $newHash,
            'id' => $user['id']
        ]);

        $passwordValid = true;
    }

    if (!$passwordValid) {
        $_SESSION['login_error'] = 'Email atau password salah.';
        header('Location: index.php');
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nama'] = $user['nama'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role'] = $user['role'] ?? 'user';

    unset($_SESSION['login_error']);

    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/berita.php');
    } else {
        header('Location: index.php');
    }

    exit;

} catch (Throwable $e) {
    error_log('Login Visit Ciamis: ' . $e->getMessage());

    $_SESSION['login_error'] =
        'Terjadi kesalahan saat login. Silakan coba lagi.';

    header('Location: index.php');
    exit;
}
