
<?php
session_start();

require_once __DIR__ . "/config/database.php";

if (empty($_SESSION["user_id"])) {
    header("Location: masuk.php");
    exit;
}

$bookingCode = trim((string) ($_GET["booking"] ?? ""));

if ($bookingCode === "" || strlen($bookingCode) > 30) {
    http_response_code(400);
    exit("Kode booking tidak valid.");
}

try {
    $stmt = $pdo->prepare("
        SELECT
            booking_code,
            full_name,
            destination_name,
            visit_date,
            ticket_qty,
            ticket_price,
            total_price,
            payment_status,
            created_at
        FROM orders
        WHERE booking_code = :booking_code
          AND user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        ":booking_code" => $bookingCode,
        ":user_id" => $_SESSION["user_id"]
    ]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        exit("Pesanan tidak ditemukan atau bukan milik akun kamu.");
    }

} catch (Throwable $e) {
    error_log("Gagal mengambil data e-tiket: " . $e->getMessage());
    http_response_code(500);
    exit("Terjadi kesalahan saat mengambil data tiket.");
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function rupiah($amount): string
{
    return "Rp" . number_format((float) $amount, 0, ",", ".");
}

// Tentukan jenis status pesanan.
$status = strtolower(trim((string) $order["payment_status"]));
$total = (float) $order["total_price"];

$isPaid = ($status === "lunas");

$isFree = (
    $status === "gratis" &&
    $total === 0.0
);

// Tiket tersedia jika sudah lunas atau memang gratis.
$ticketAvailable = $isPaid || $isFree;

// Informasi destinasi.
$destination = trim((string) $order["destination_name"]);
$destinationKey = strtolower($destination);

$destinationInfo = [
    "astana gede kawali" => [
        "name" => "Astana Gede Kawali",
        "image" => "pictures/astana-gede-kawali.jpg"
    ],
    "kawali" => [
        "name" => "Astana Gede Kawali",
        "image" => "pictures/astana-gede-kawali.jpg"
    ],
    "windu raja" => [
        "name" => "Windu Raja",
        "image" => "pictures/windu-raja.jpg"
    ],
    "winduraja" => [
        "name" => "Windu Raja",
        "image" => "pictures/windu-raja.jpg"
    ],
    "situ wangi" => [
        "name" => "Situ Wangi",
        "image" => "pictures/situ-wangi.jpg"
    ],
    "situ wangi winduraja" => [
        "name" => "Situ Wangi Winduraja",
        "image" => "pictures/situ-wangi.jpg"
    ],
    "kampung sawah" => [
        "name" => "Kampung Sawah",
        "image" => "pictures/kampung-sawah.jpg"
    ],
    "mekarsari" => [
        "name" => "Mekarsari",
        "image" => "pictures/mekarsari.jpg"
    ]
];

$info = $destinationInfo[$destinationKey] ?? [
    "name" => ($destination !== "" ? $destination : "Desa Wisata"),
    "image" => ""
];

$imagePath = $info["image"];

$imageExists = (
    $imagePath !== "" &&
    is_file(__DIR__ . "/" . $imagePath)
);

// Format tanggal.
$visitDate = !empty($order["visit_date"])
    ? date("d-m-Y", strtotime($order["visit_date"]))
    : "-";

$qty = (int) $order["ticket_qty"];
$ticketPrice = (float) $order["ticket_price"];

if ($isFree) {
    $statusLabel = "✓ RESERVASI GRATIS BERHASIL";
    $statusMessage = "Reservasi destinasi gratis berhasil. E-tiket kamu sudah tersedia.";
} elseif ($isPaid) {
    $statusLabel = "✓ PEMBAYARAN LUNAS";
    $statusMessage = "Pembayaran berhasil dikonfirmasi. E-tiket kamu sudah tersedia.";
} elseif ($status === "menunggu verifikasi admin") {
    $statusMessage = "Bukti pembayaran sedang diperiksa. E-tiket akan tersedia setelah pembayaran dikonfirmasi.";
} elseif ($status === "menunggu pembayaran") {
    $statusMessage = "Pembayaran belum dikonfirmasi. Silakan selesaikan pembayaran terlebih dahulu.";
} else {
    $statusMessage = "E-tiket belum tersedia. Silakan hubungi pengelola untuk informasi lebih lanjut.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= $ticketAvailable ? "E-Tiket" : "Status Pesanan" ?>
        | Visit Ciamis
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 28px 16px;
            background: #f1f5f2;
            color: #26352b;
            font-family: Arial, Helvetica, sans-serif;
        }

        .container {
            max-width: 760px;
            margin: 0 auto;
        }

        .topbar {
            margin-bottom: 24px;
            text-align: center;
        }

        .topbar h1 {
            margin: 0 0 8px;
            color: #176b45;
        }

        .topbar p {
            margin: 0;
            color: #657269;
        }

        .ticket,
        .notice {
            overflow: hidden;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 8px 28px #00000012;
        }

        .ticket-header {
            padding: 26px;
            background: #176b45;
            color: #fff;
        }

        .brand {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .ticket-header h2 {
            margin: 14px 0 8px;
            font-size: 28px;
        }

        .ticket-header p {
            margin: 0;
        }

        .destination-image {
            display: block;
            width: 100%;
            max-height: 300px;
            object-fit: cover;
        }

        .ticket-body,
        .notice {
            padding: 26px;
        }

        .status {
            display: inline-block;
            margin-bottom: 22px;
            padding: 9px 14px;
            border-radius: 30px;
            background: #e3f7e9;
            color: #176b45;
            font-size: 12px;
            font-weight: bold;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 24px;
        }

        .detail-label {
            margin-bottom: 6px;
            color: #758078;
            font-size: 12px;
        }

        .detail-value {
            overflow-wrap: anywhere;
            font-size: 14px;
            font-weight: bold;
        }

        .separator {
            margin: 24px 0;
            border: 0;
            border-top: 1px dashed #cbd5cd;
        }

        .total-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .total-row strong {
            color: #176b45;
            font-size: 21px;
        }

        .notice {
            line-height: 1.6;
        }

        .notice h2 {
            margin-top: 0;
        }

        .notice-code {
            overflow-wrap: anywhere;
            font-weight: bold;
        }

        .note {
            margin-top: 20px;
            padding: 14px;
            border-radius: 10px;
            background: #f4f8f5;
            color: #536258;
            font-size: 12px;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 22px;
        }

        .button {
            display: inline-block;
            padding: 12px 18px;
            border: 0;
            border-radius: 9px;
            background: #176b45;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .button.secondary {
            background: #e3ebe5;
            color: #26352b;
        }

        @media (max-width: 480px) {
            body {
                padding: 18px 12px;
            }

            .ticket-header,
            .ticket-body,
            .notice {
                padding: 20px;
            }

            .ticket-header h2 {
                font-size: 23px;
            }

            .details {
                gap: 18px 12px;
            }

            .detail-value {
                font-size: 13px;
            }
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .container {
                max-width: none;
            }

            .ticket {
                border: 1px solid #ddd;
                box-shadow: none;
            }

            .actions,
            .topbar p {
                display: none;
            }

            .ticket-header,
            .status {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
<div class="container">

    <header class="topbar">
        <h1>Visit Ciamis</h1>
        <p>E-Tiket Desa Wisata</p>
    </header>

    <?php if (!$ticketAvailable): ?>

        <section class="notice">
            <h2>E-Tiket Belum Tersedia</h2>

            <p><?= e($statusMessage) ?></p>

            <p>
                Kode booking:
                <span class="notice-code">
                    <?= e($order["booking_code"]) ?>
                </span>
            </p>

            <p>
                Destinasi:
                <strong><?= e($info["name"]) ?></strong>
            </p>

            <p>
                Tanggal kunjungan:
                <strong><?= e($visitDate) ?></strong>
            </p>

            <p>
                Status:
                <strong><?= e($order["payment_status"]) ?></strong>
            </p>

            <div class="actions">
                <?php if ($status === "menunggu pembayaran"): ?>
                    <a
                        class="button"
                        href="pembayaran.php?booking=<?= urlencode($order["booking_code"]) ?>"
                    >
                        Lanjutkan Pembayaran
                    </a>
                <?php endif; ?>

                <a class="button secondary" href="pemesanan.php">
                    Kembali ke Pesanan Saya
                </a>
            </div>
        </section>

    <?php else: ?>

        <main class="ticket">

            <div class="ticket-header">
                <div class="brand">VISIT CIAMIS</div>
                <h2><?= e($info["name"]) ?></h2>
                <p>E-Tiket kunjungan desa wisata</p>
            </div>

            <?php if ($imageExists): ?>
                <img
                    class="destination-image"
                    src="<?= e($imagePath) ?>"
                    alt="Foto <?= e($info["name"]) ?>"
                >
            <?php endif; ?>

            <div class="ticket-body">

                <div class="status">
                    <?= e($statusLabel) ?>
                </div>

                <h3>Informasi Pemesanan</h3>

                <div class="details">

                    <div>
                        <div class="detail-label">Kode Booking</div>
                        <div class="detail-value">
                            <?= e($order["booking_code"]) ?>
                        </div>
                    </div>

                    <div>
                        <div class="detail-label">Nama Pemesan</div>
                        <div class="detail-value">
                            <?= e($order["full_name"]) ?>
                        </div>
                    </div>

                    <div>
                        <div class="detail-label">Destinasi Wisata</div>
                        <div class="detail-value">
                            <?= e($info["name"]) ?>
                        </div>
                    </div>

                    <div>
                        <div class="detail-label">Tanggal Kunjungan</div>
                        <div class="detail-value">
                            <?= e($visitDate) ?>
                        </div>
                    </div>

                    <div>
                        <div class="detail-label">Jumlah Tiket</div>
                        <div class="detail-value">
                            <?= e($qty) ?> tiket
                        </div>
                    </div>

                    <div>
                        <div class="detail-label">Harga per Tiket</div>
                        <div class="detail-value">
                            <?= e(rupiah($ticketPrice)) ?>
                        </div>
                    </div>

                </div>

                <hr class="separator">

                <div class="total-row">
                    <span>
                        <?= $isFree ? "Total Biaya" : "Total Pembayaran" ?>
                    </span>

                    <strong><?= e(rupiah($total)) ?></strong>
                </div>

                <div class="note">
                    <?= $isFree
                        ? "Reservasi ini tidak dikenakan biaya. Simpan e-tiket dan tunjukkan kode booking kepada petugas saat kunjungan."
                        : "Simpan e-tiket ini dan tunjukkan kode booking kepada petugas saat kunjungan. Tiket berlaku sesuai destinasi, tanggal kunjungan, dan jumlah tiket yang tertera."
                    ?>
                </div>

            </div>
        </main>

        <div class="actions">

            <button
                class="button"
                type="button"
                onclick="window.print()"
            >
                Cetak / Simpan PDF
            </button>

            <a class="button secondary" href="pemesanan.php">
                Kembali ke Pesanan Saya
            </a>

        </div>

    <?php endif; ?>

</div>
</body>
</html>
