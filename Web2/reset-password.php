
<?php
require_once __DIR__ . '/config/koneksi.php';

$pesan = '';
$berhasil = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $pesan = 'Password minimal 8 karakter.';
    } elseif ($password !== $konfirmasi) {
        $pesan = 'Konfirmasi password tidak sama.';
    } else {
        try {
            $cek = $pdo->prepare(
                'SELECT id FROM users WHERE email = :email LIMIT 1'
            );
            $cek->execute(['email' => $email]);
            $user = $cek->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $pesan = 'Email tidak ditemukan.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $update = $pdo->prepare(
                    'UPDATE users SET password = :password WHERE id = :id'
                );
                $update->execute([
                    'password' => $hash,
                    'id' => $user['id']
                ]);

                $pesan = 'Password berhasil diubah. Silakan login.';
                $berhasil = true;
            }
        } catch (PDOException $e) {
            $pesan = 'Terjadi kesalahan saat mengubah password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atur Ulang Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card mx-auto shadow-sm" style="max-width: 450px;">
        <div class="card-body p-4">
            <h4 class="mb-3">Atur Ulang Password</h4>

            <?php if ($pesan !== ''): ?>
                <div class="alert <?= $berhasil ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($berhasil): ?>
                <a href="index.php" class="btn btn-primary w-100">
                    Kembali ke Login
                </a>
            <?php else: ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email akun</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password baru</label>
                        <input type="password" name="password"
                               class="form-control" minlength="8" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi password baru</label>
                        <input type="password" name="konfirmasi"
                               class="form-control" minlength="8" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Simpan Password Baru
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
