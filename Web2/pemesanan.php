
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/database.php";

// Halaman hanya dapat diakses setelah login.
if (empty($_SESSION["user_id"])) {
    header("Location: masuk.php");
    exit;
}

function e($value)
{
    return htmlspecialchars((string) ($value ?? ""), ENT_QUOTES, "UTF-8");
}

function rupiah($value)
{
    return "Rp" . number_format((float) $value, 0, ",", ".");
}

$userId = (int) $_SESSION["user_id"];
$namaPengguna = $_SESSION["user_nama"] ?? "Akun Saya";

$orders = [];
$errorMessage = "";

try {
    $stmt = $pdo->prepare(
        "SELECT
            booking_code,
            full_name,
            whatsapp,
            destination_name,
            visit_date,
            ticket_qty,
            ticket_price,
            total_price,
            payment_status,
            created_at
         FROM orders
         WHERE user_id = :user_id
         ORDER BY created_at DESC"
    );

    $stmt->execute(["user_id" => $userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $errorMessage = "Data pesanan belum dapat dimuat. Silakan coba beberapa saat lagi.";
}

function statusPesanan($status)
{
    $status = strtolower(trim((string) $status));

    $labels = [
        "menunggu pembayaran" => "Menunggu Pembayaran",
        "menunggu verifikasi" => "Menunggu Pembayaran",
        "menunggu verifikasi admin" => "Menunggu Pembayaran",
        "pending" => "Menunggu Pembayaran",
        "lunas" => "Lunas",
        "paid" => "Lunas",
        "ditolak" => "Belum Lunas",
        "gagal" => "Gagal",
        "dibatalkan" => "Dibatalkan"
    ];

    return $labels[$status] ?? (
        $status !== "" ? ucwords($status) : "Belum Diketahui"
    );
}

function statusClass($status)
{
    $status = strtolower(trim((string) $status));

    if (in_array($status, ["lunas", "paid"], true)) {
        return "lunas";
    }

    if (in_array($status, ["gagal", "dibatalkan"], true)) {
        return "gagal";
    }

    return "menunggu";
}

function statusSudahLunas($status)
{
    $status = strtolower(trim((string) $status));

    // Status Lunas digunakan sebagai status pembayaran yang sah.
    return $status === "lunas";
}
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pemesanan Saya | Visit Ciamis</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="css/style.css">

    <style>
        :root {
            --booking-green: #246b48;
            --booking-green-dark: #194f35;
            --booking-green-light: #edf7f0;
            --booking-text: #26372d;
            --booking-muted: #718078;
            --booking-border: #e0e8e2;
        }

        body {
            background: #f7faf8;
            color: var(--booking-text);
        }

        .page-container {
            width: min(1100px, 94%);
            margin: 120px auto 55px;
            min-height: 55vh;
        }

        .page-heading {
            margin-bottom: 28px;
        }

        .page-heading h1 {
            margin-bottom: 9px;
            color: var(--booking-green);
            font-size: clamp(27px, 4vw, 35px);
            font-weight: 800;
        }

        .page-heading p {
            color: var(--booking-muted);
            line-height: 1.7;
            margin: 0;
        }

        .order-card {
            margin-bottom: 22px;
            padding: 24px;
            background: #fff;
            border: 1px solid var(--booking-border);
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(20, 60, 40, .045);
        }

        .order-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            padding-bottom: 18px;
            border-bottom: 1px solid #edf1ee;
        }

        .order-top h2 {
            margin: 0 0 8px;
            font-size: 20px;
            font-weight: 800;
        }

        .booking-code {
            color: var(--booking-muted);
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .status {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 30px;
            background: #fff0cf;
            color: #805b00;
            font-size: 12px;
            font-weight: 700;
        }

        .status.lunas {
            background: #e2f6e9;
            color: #17663c;
        }

        .status.gagal {
            background: #fde8e7;
            color: #9a2525;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
            margin-top: 22px;
        }

        .detail-label {
            display: block;
            margin-bottom: 7px;
            color: var(--booking-muted);
            font-size: 12px;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .order-total {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px dashed #d4ded7;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-weight: 700;
        }

        .order-total strong {
            color: var(--booking-green);
            text-align: right;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 22px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 18px;
            border: 1px solid var(--booking-green);
            border-radius: 9px;
            background: var(--booking-green);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: .2s;
        }

        .button:hover {
            background: var(--booking-green-dark);
            border-color: var(--booking-green-dark);
            color: #fff;
        }

        .button.secondary {
            background: #fff;
            color: var(--booking-green);
        }

        .button.secondary:hover {
            background: var(--booking-green-light);
        }

        .error-box {
            padding: 22px;
            background: #fff0ef;
            border: 1px solid #f1cecb;
            border-radius: 14px;
            color: #9a2525;
            line-height: 1.7;
        }

        .empty-state {
            position: relative;
            overflow: hidden;
            padding: 40px 28px 30px;
            background: #fff;
            border: 1px solid var(--booking-border);
            border-radius: 18px;
            box-shadow: 0 8px 28px rgba(20, 60, 40, .045);
            text-align: center;
        }

        .empty-content {
            position: relative;
            z-index: 1;
            max-width: 560px;
            margin: 0 auto;
        }

        .empty-illustration {
            position: relative;
            width: 118px;
            height: 118px;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--booking-green-light);
            color: var(--booking-green);
            font-size: 55px;
        }

        .empty-state h2 {
            margin: 0 0 12px;
            color: var(--booking-text);
            font-size: 23px;
            font-weight: 800;
        }

        .empty-description {
            max-width: 460px;
            margin: 0 auto 23px;
            color: var(--booking-muted);
            font-size: 14px;
            line-height: 1.8;
        }

        .empty-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 11px;
            flex-wrap: wrap;
        }

        .empty-note {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 8px;
            margin-top: 27px;
            padding-top: 18px;
            border-top: 1px solid #edf1ee;
            color: var(--booking-muted);
            font-size: 12px;
            line-height: 1.7;
        }

        .empty-note i {
            flex-shrink: 0;
            color: var(--booking-green);
            font-size: 15px;
        }

        @media (max-width: 600px) {
            .page-container {
                margin: 105px auto 30px;
            }

            .order-card {
                padding: 18px;
            }

            .order-details {
                grid-template-columns: 1fr;
                gap: 17px;
            }

            .actions {
                flex-direction: column;
            }

            .button {
                width: 100%;
                text-align: center;
            }

            .empty-state {
                padding: 30px 19px 22px;
            }

            .empty-illustration {
                width: 100px;
                height: 100px;
                font-size: 46px;
            }

            .empty-state h2 {
                font-size: 21px;
            }

            .empty-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .empty-note {
                text-align: left;
            }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light fixed-top" id="navbar">
    <div class="container">

        <a class="navbar-brand logo" href="index.php">
            <img src="pictures/LogoDispar.png" alt="Logo Desa Wisata Ciamis">
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Buka navigasi"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link" href="index.php">Beranda</a>
                </li>

                <li class="nav-item dropdown">
                    <a
                        class="nav-link dropdown-toggle"
                        href="destinasi.php"
                        id="destinasiDropdown"
                        role="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        Destinasi
                    </a>

                    <ul class="dropdown-menu" aria-labelledby="destinasiDropdown">
                        <li>
                            <a class="dropdown-item" href="destinasi.php">
                                <i class="bi bi-grid-fill me-2"></i>
                                Semua Wisata
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="destinasi.php?kategori=Alam">
                                <i class="bi bi-tree-fill me-2"></i>
                                Wisata Alam
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="destinasi.php?kategori=Buatan">
                                <i class="bi bi-building me-2"></i>
                                Wisata Buatan
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="destinasi.php?kategori=Sejarah%20%26%20Budaya">
                                <i class="bi bi-bank me-2"></i>
                                Sejarah &amp; Budaya
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="berita.php">Berita</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="pemesanan.php" aria-current="page">
                        Tiket
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="pusat-bantuan.php">
                        Pusat Bantuan
                    </a>
                </li>

                <li class="nav-item dropdown ms-lg-2">
                    <a
                        class="nav-link dropdown-toggle"
                        href="#"
                        id="akunDropdown"
                        role="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <i class="bi bi-person-circle me-1"></i>
                        <?= e($namaPengguna) ?>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="akunDropdown">
                        <li>
                            <a class="dropdown-item" href="akun-saya.php">
                                <i class="bi bi-person-circle me-2"></i>
                                Akun Saya
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="keluar.php">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Keluar
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</nav>

<main class="page-container">

    <header class="page-heading">
        <h1>Pemesanan Saya</h1>
        <p>
            Lihat detail pesanan, tanggal kunjungan, dan status
            pembayaran tiket wisata Anda.
        </p>
    </header>

    <?php if ($errorMessage !== ""): ?>

        <div class="error-box">
            <i class="bi bi-exclamation-circle me-2"></i>
            <?= e($errorMessage) ?>
        </div>

    <?php elseif (empty($orders)): ?>

        <section class="empty-state">
            <div class="empty-content">

                <div class="empty-illustration">
                    <i class="bi bi-ticket-perforated"></i>
                </div>

                <h2>Belum Ada Pemesanan</h2>

                <p class="empty-description">
                    Anda belum memiliki pesanan tiket wisata.
                    Yuk, temukan keindahan desa wisata di Kabupaten Ciamis
                    dan rencanakan perjalanan seru bersama orang-orang terdekat!
                </p>

                <div class="empty-actions">
                    <a href="destinasi.php" class="button">
                        <i class="bi bi-compass"></i>
                        Jelajahi Destinasi
                    </a>
                </div>

                <div class="empty-note">
                    <i class="bi bi-shield-check"></i>
                    <span>
                        Riwayat pemesanan dan status pembayaran Anda
                        akan ditampilkan di halaman ini setelah melakukan pemesanan.
                    </span>
                </div>

            </div>
        </section>

    <?php else: ?>

        <?php foreach ($orders as $order): ?>

            <?php
                $paymentStatus = strtolower(
                    trim((string) ($order["payment_status"] ?? ""))
                );

                $statusLabel = statusPesanan($paymentStatus);
                $statusCss = statusClass($paymentStatus);
                $isPaid = statusSudahLunas($paymentStatus);

                $visitTimestamp = strtotime((string) ($order["visit_date"] ?? ""));

                $visitDate = $visitTimestamp !== false
                    ? date("d-m-Y", $visitTimestamp)
                    : "-";

                $bookingCode = (string) ($order["booking_code"] ?? "");
            ?>

            <article class="order-card">

                <div class="order-top">
                    <div>
                        <h2>
                            <?= e($order["destination_name"] ?? "Destinasi Wisata") ?>
                        </h2>

                        <div class="booking-code">
                            Kode booking: <?= e($bookingCode) ?>
                        </div>
                    </div>

                    <span class="status <?= e($statusCss) ?>">
                        <?= e($statusLabel) ?>
                    </span>
                </div>

                <div class="order-details">

                    <div>
                        <span class="detail-label">Nama Pemesan</span>
                        <div class="detail-value">
                            <?= e($order["full_name"] ?? "-") ?>
                        </div>
                    </div>

                    <div>
                        <span class="detail-label">Nomor WhatsApp</span>
                        <div class="detail-value">
                            <?= e($order["whatsapp"] ?? "-") ?>
                        </div>
                    </div>

                    <div>
                        <span class="detail-label">Tanggal Kunjungan</span>
                        <div class="detail-value">
                            <?= e($visitDate) ?>
                        </div>
                    </div>

                    <div>
                        <span class="detail-label">Jumlah Tiket</span>
                        <div class="detail-value">
                            <?= (int) ($order["ticket_qty"] ?? 0) ?> tiket
                        </div>
                    </div>

                    <div>
                        <span class="detail-label">Harga per Tiket</span>
                        <div class="detail-value">
                            <?= e(rupiah($order["ticket_price"] ?? 0)) ?>
                        </div>
                    </div>

                </div>

                <div class="order-total">
                    <span>Total Pembayaran</span>
                    <strong>
                        <?= e(rupiah($order["total_price"] ?? 0)) ?>
                    </strong>
                </div>

                <div class="actions">

                    <?php if ($isPaid): ?>

                        <a
                            class="button"
                            href="tiket.php?booking=<?= rawurlencode($bookingCode) ?>"
                        >
                            <i class="bi bi-qr-code"></i>
                            Lihat E-Tiket
                        </a>

                    <?php else: ?>

                        <a
                            class="button"
                            href="pembayaran.php?booking=<?= rawurlencode($bookingCode) ?>"
                        >
                            <i class="bi bi-credit-card"></i>
                            Lihat Pembayaran
                        </a>

                    <?php endif; ?>

                    <a href="destinasi.php" class="button secondary">
                        <i class="bi bi-compass"></i>
                        Jelajahi Destinasi
                    </a>

                </div>

            </article>

        <?php endforeach; ?>

    <?php endif; ?>

</main>

<footer class="py-4 text-center">
    <div class="container">
        <p class="mb-0 text-secondary small">
            &copy; <?= date("Y") ?> Visit Ciamis.
            Jelajahi keindahan desa wisata Kabupaten Ciamis.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>

</body>
</html>
