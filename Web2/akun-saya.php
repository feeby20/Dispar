```php
<?php
require_once __DIR__ . '/config/koneksi.php';

// Pastikan pengguna sudah login.
$userName = $_SESSION['user_nama']
    ?? $_SESSION['user_name']
    ?? $_SESSION['nama']
    ?? $_SESSION['name']
    ?? $_SESSION['full_name']
    ?? '';

$userEmail = $_SESSION['user_email']
    ?? $_SESSION['email']
    ?? '';

$userId = $_SESSION['user_id']
    ?? $_SESSION['id_user']
    ?? $_SESSION['id']
    ?? null;

if (empty($userId)) {
    header('Location: index.php');
    exit;
}

// Ambil riwayat pemesanan pengguna.
$orders = [];

try {
    $stmt = $pdo->prepare(
        'SELECT * FROM orders WHERE full_name = :name ORDER BY id DESC'
    );
    $stmt->execute(['name' => $userName]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Riwayat pesanan: ' . $e->getMessage());
}

// Ambil riwayat pesan pusat bantuan.
$supportMessages = [];

if ($userEmail !== '') {
    try {
        $stmt = $pdo->prepare(
            'SELECT * FROM support_messages WHERE email = :email ORDER BY id DESC'
        );
        $stmt->execute(['email' => $userEmail]);
        $supportMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Riwayat bantuan: ' . $e->getMessage());
    }
}

function formatRupiah($value)
{
    if (!is_numeric($value)) {
        return htmlspecialchars((string) ($value ?? '-'), ENT_QUOTES, 'UTF-8');
    }

    return 'Rp ' . number_format((float) $value, 0, ',', '.');
}

function e($value)
{
    return htmlspecialchars((string) ($value ?? '-'), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - Visit Ciamis</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --green: #176b45;
            --green-dark: #105237;
            --green-light: #eaf5ee;
            --text: #24352c;
            --muted: #718078;
            --border: #e5ece7;
            --background: #f6f9f7;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        a { text-decoration: none; }

        .navbar {
            background: #fff;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
            box-shadow: 0 3px 15px rgba(20,60,40,.04);
            position: relative;
            z-index: 1000;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--green-dark);
            font-size: 19px;
            font-weight: 800;
        }

        .navbar-brand img {
            width: 45px;
            height: 45px;
            object-fit: contain;
        }

        .navbar-nav {
            align-items: center;
            gap: 5px;
        }

        .navbar .nav-link {
            color: #405248;
            padding: 10px 12px !important;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: .2s;
        }

        .navbar .nav-link:hover,
        .navbar .nav-link.active {
            color: var(--green);
            background: var(--green-light);
        }

        .navbar .logout-link { color: #c0392b; }

        .navbar .logout-link:hover {
            background: #fff0ee;
            color: #a5281d;
        }

        .page-header {
            background: linear-gradient(120deg, #176b45, #27865b);
            color: #fff;
            padding: 42px 0 65px;
        }

        .page-header h1 {
            font-size: clamp(27px, 4vw, 36px);
            font-weight: 800;
            margin-bottom: 10px;
        }

        .page-header p {
            margin: 0;
            color: #e5f4e9;
        }

        .account-wrapper {
            margin-top: -32px;
            padding-bottom: 55px;
            position: relative;
            z-index: 1;
        }

        .profile-card,
        .content-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 6px 22px rgba(20,60,40,.045);
        }

        .profile-card {
            padding: 25px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .profile-icon {
            width: 72px;
            height: 72px;
            min-width: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--green-light);
            color: var(--green);
            font-size: 34px;
        }

        .profile-info h2 {
            margin: 0 0 7px;
            font-size: 22px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .profile-info p {
            margin: 0;
            color: var(--muted);
            overflow-wrap: anywhere;
        }

        .account-badge {
            display: inline-block;
            margin-top: 9px;
            background: #e6f5eb;
            color: var(--green);
            border-radius: 20px;
            padding: 5px 11px;
            font-size: 12px;
            font-weight: 700;
        }

        .content-card {
            padding: 24px;
            margin-bottom: 22px;
            min-width: 0;
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 20px;
        }

        .heading-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--green-light);
            color: var(--green);
            font-size: 21px;
            flex-shrink: 0;
        }

        .section-heading h3 {
            font-size: 19px;
            font-weight: 800;
            margin: 0;
        }

        .section-heading p {
            color: var(--muted);
            font-size: 13px;
            margin: 4px 0 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #edf1ee;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            color: var(--muted);
            font-size: 14px;
        }

        .info-value {
            text-align: right;
            font-weight: 600;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .table-responsive { border-radius: 10px; }

        .table {
            margin-bottom: 0;
            vertical-align: middle;
            font-size: 13px;
        }

        .table thead th {
            background: #f1f7f3;
            color: #405b4a;
            font-weight: 700;
            white-space: nowrap;
            padding: 13px;
            border-bottom: 1px solid var(--border);
        }

        .table tbody td {
            padding: 13px;
            border-color: #edf1ee;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            background: #edf1ee;
            color: #59665e;
        }

        .status-success { background: #e2f6e8; color: #18733e; }
        .status-pending { background: #fff3d8; color: #946200; }
        .status-failed { background: #ffebea; color: #b42318; }

        .empty-state {
            text-align: center;
            padding: 28px 15px;
            color: var(--muted);
        }

        .empty-state i {
            display: block;
            font-size: 36px;
            color: #a8b9ad;
            margin-bottom: 10px;
        }

        .empty-state p {
            margin: 0 0 15px;
            font-size: 14px;
        }

        .btn-green,
        .btn-outline-green,
        .btn-logout {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 700;
            transition: .2s;
        }

        .btn-green {
            background: var(--green);
            color: #fff;
            border: 1px solid var(--green);
        }

        .btn-green:hover {
            background: var(--green-dark);
            border-color: var(--green-dark);
            color: #fff;
        }

        .btn-outline-green {
            background: #fff;
            color: var(--green);
            border: 1px solid #b9d8c5;
        }

        .btn-outline-green:hover {
            background: var(--green-light);
            color: var(--green-dark);
        }

        .security-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            padding: 17px;
            border-radius: 12px;
            border: 1px solid #f1d5d2;
            background: #fffafa;
        }

        .security-box h4 {
            margin: 0 0 6px;
            font-size: 15px;
            font-weight: 800;
        }

        .security-box p {
            margin: 0;
            font-size: 13px;
            color: var(--muted);
        }

        .btn-logout {
            background: #fff;
            color: #c0392b;
            border: 1px solid #e9b8b3;
        }

        .btn-logout:hover {
            background: #fff0ee;
            color: #a5281d;
        }

        footer {
            background: #123e2c;
            color: #e8f2eb;
            padding: 25px 0;
            text-align: center;
            font-size: 13px;
        }

        footer p { margin: 0; }

        @media (max-width: 991px) {
            .navbar-nav {
                align-items: stretch;
                padding-top: 12px;
            }

            .navbar .nav-link { padding: 10px 12px !important; }
        }

        @media (max-width: 576px) {
            .page-header { padding: 32px 0 52px; }
            .profile-card { padding: 18px; gap: 13px; }

            .profile-icon {
                width: 58px;
                height: 58px;
                min-width: 58px;
                font-size: 27px;
            }

            .profile-info h2 { font-size: 18px; }
            .content-card { padding: 17px; }
            .section-heading h3 { font-size: 17px; }
            .info-row { gap: 12px; }
            .security-box { align-items: stretch; }
            .security-box .btn-logout { width: 100%; }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="pictures/LogoDispar.png" alt="Logo Visit Ciamis">
            <span>Visit Ciamis</span>
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMenu"
                aria-controls="navbarMenu" aria-expanded="false"
                aria-label="Buka navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="destinasi.php">Destinasi</a></li>
                <li class="nav-item"><a class="nav-link" href="berita.php">Berita</a></li>
                <li class="nav-item"><a class="nav-link" href="pemesanan.php">Pemesanan</a></li>
                <li class="nav-item"><a class="nav-link" href="pusat-bantuan.php">Pusat Bantuan</a></li>
                <li class="nav-item"><a class="nav-link active" href="akun-saya.php">Akun Saya</a></li>

                <!-- Link keluar mengarah ke keluar.php -->
                <li class="nav-item">
                    <a class="nav-link logout-link" href="keluar.php">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<header class="page-header">
    <div class="container">
        <h1>Akun Saya</h1>
        <p>Kelola informasi akun dan lihat aktivitas pemesanan wisata Anda.</p>
    </div>
</header>

<main class="container account-wrapper">

    <section class="profile-card">
        <div class="profile-icon">
            <i class="bi bi-person-fill"></i>
        </div>

        <div class="profile-info">
            <h2><?= e($userName) ?></h2>
            <p><?= e($userEmail ?: 'Email belum tersedia') ?></p>
            <span class="account-badge">
                <i class="bi bi-patch-check-fill"></i> Pengguna Visit Ciamis
            </span>
        </div>
    </section>

    <div class="row">
        <div class="col-lg-6">

            <section class="content-card">
                <div class="section-heading">
                    <div class="heading-icon"><i class="bi bi-person-vcard"></i></div>
                    <div>
                        <h3>Informasi Akun</h3>
                        <p>Informasi akun yang sedang digunakan.</p>
                    </div>
                </div>

                <div class="info-row">
                    <span class="info-label">Nama lengkap</span>
                    <span class="info-value"><?= e($userName) ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?= e($userEmail ?: 'Belum tersedia') ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Status akun</span>
                    <span class="info-value"><span class="status status-success">Aktif</span></span>
                </div>
            </section>

            <section class="content-card">
                <div class="section-heading">
                    <div class="heading-icon"><i class="bi bi-headset"></i></div>
                    <div>
                        <h3>Riwayat Bantuan</h3>
                        <p>Pesan yang pernah dikirim melalui pusat bantuan.</p>
                    </div>
                </div>

                <?php if (!empty($supportMessages)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr><th>Pesan</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($supportMessages as $message): ?>
                                <tr>
                                    <td><?= e($message['subject'] ?? $message['message'] ?? $message['pesan'] ?? 'Pesan bantuan') ?></td>
                                    <td><span class="status"><?= e($message['status'] ?? 'Terkirim') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-chat-square-text"></i>
                        <p>Belum ada riwayat pesan bantuan.</p>
                    </div>
                <?php endif; ?>

                <a href="pusat-bantuan.php" class="btn-outline-green">
                    <i class="bi bi-chat-dots"></i> Hubungi Bantuan
                </a>
            </section>
        </div>

        <div class="col-lg-6">
            <section class="content-card">
                <div class="section-heading">
                    <div class="heading-icon"><i class="bi bi-ticket-perforated"></i></div>
                    <div>
                        <h3>Riwayat Pemesanan</h3>
                        <p>Daftar pesanan tiket wisata Anda.</p>
                    </div>
                </div>

                <?php if (!empty($orders)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr><th>Destinasi</th><th>Tanggal</th><th>Total</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($orders as $order): ?>
                                <?php
                                $destination = $order['destination_name']
                                    ?? $order['destination']
                                    ?? $order['nama_destinasi']
                                    ?? $order['destinasi']
                                    ?? 'Destinasi wisata';

                                $date = $order['visit_date']
                                    ?? $order['tanggal_kunjungan']
                                    ?? $order['created_at']
                                    ?? '-';

                                $total = $order['total_price']
                                    ?? $order['total_harga']
                                    ?? $order['total']
                                    ?? null;

                                $statusText = $order['status']
                                    ?? $order['order_status']
                                    ?? 'Menunggu';

                                $statusLower = strtolower((string) $statusText);
                                $statusClass = 'status-pending';

                                if (
                                    strpos($statusLower, 'berhasil') !== false ||
                                    strpos($statusLower, 'selesai') !== false ||
                                    strpos($statusLower, 'lunas') !== false ||
                                    strpos($statusLower, 'paid') !== false ||
                                    strpos($statusLower, 'confirm') !== false
                                ) {
                                    $statusClass = 'status-success';
                                } elseif (
                                    strpos($statusLower, 'batal') !== false ||
                                    strpos($statusLower, 'gagal') !== false ||
                                    strpos($statusLower, 'cancel') !== false ||
                                    strpos($statusLower, 'failed') !== false
                                ) {
                                    $statusClass = 'status-failed';
                                }

                                $timestamp = $date !== '-' ? strtotime((string) $date) : false;
                                ?>
                                <tr>
                                    <td><?= e($destination) ?></td>
                                    <td><?= $timestamp !== false ? date('d-m-Y', $timestamp) : e($date) ?></td>
                                    <td><?= $total !== null ? formatRupiah($total) : '-' ?></td>
                                    <td><span class="status <?= e($statusClass) ?>"><?= e($statusText) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-ticket"></i>
                        <p>Anda belum memiliki riwayat pemesanan.</p>
                        <a href="pemesanan.php" class="btn-green">
                            <i class="bi bi-plus-circle"></i> Pesan Tiket
                        </a>
                    </div>
                <?php endif; ?>

                <div class="mt-3">
                    <a href="pemesanan.php" class="btn-outline-green">
                        <i class="bi bi-calendar-check"></i> Lihat Pemesanan
                    </a>
                </div>
            </section>

            <section class="content-card">
                <div class="section-heading">
                    <div class="heading-icon"><i class="bi bi-shield-lock"></i></div>
                    <div>
                        <h3>Keamanan Akun</h3>
                        <p>Pastikan akun Anda digunakan dengan aman.</p>
                    </div>
                </div>

                <div class="security-box">
                    <div>
                        <h4>Keluar dari akun</h4>
                        <p>Gunakan tombol ini untuk mengakhiri sesi login.</p>
                    </div>

                    <!-- Link keluar mengarah ke keluar.php -->
                    <a href="keluar.php" class="btn-logout">
                        <i class="bi bi-box-arrow-right"></i> Keluar dari Akun
                    </a>
                </div>
            </section>
        </div>
    </div>
</main>

<footer>
    <div class="container">
        <p>&copy; <?= date('Y') ?> Visit Ciamis. Jelajahi keindahan desa wisata Kabupaten Ciamis.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```
