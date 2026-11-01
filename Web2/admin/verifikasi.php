
<?php
session_start();

require_once __DIR__ . "/../config/database.php";

/*
 * PENGAMANAN ADMIN
 * Sesuaikan pemeriksaan ini dengan session login admin yang
 * benar-benar digunakan pada project Visit Ciamis.
 */
$isAdmin =
    (isset($_SESSION["role"]) &&
     strtolower((string) $_SESSION["role"]) === "admin")
    || (isset($_SESSION["is_admin"]) &&
        $_SESSION["is_admin"] === true);

if (!$isAdmin) {
    http_response_code(403);
    exit("Akses ditolak. Silakan login menggunakan akun admin.");
}

if (empty($_SESSION["csrf_verifikasi"])) {
    $_SESSION["csrf_verifikasi"] = bin2hex(random_bytes(32));
}

function e($value)
{
    return htmlspecialchars((string) ($value ?? ""), ENT_QUOTES, "UTF-8");
}

/*
 * Menampilkan bukti melalui endpoint yang dilindungi autentikasi.
 * Nama file dibatasi agar tidak dapat membaca file sembarangan.
 */
if (isset($_GET["bukti"])) {
    $fileName = basename((string) $_GET["bukti"]);

    if (!preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/', $fileName)) {
        http_response_code(400);
        exit("Nama file tidak valid.");
    }

    $filePath = __DIR__ . "/../uploads/bukti-pembayaran/" . $fileName;

    if (!is_file($filePath)) {
        http_response_code(404);
        exit("Bukti pembayaran tidak ditemukan.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($filePath);

    $allowed = ["image/jpeg", "image/png", "image/webp"];

    if (!in_array($mime, $allowed, true)) {
        http_response_code(415);
        exit("Format bukti tidak didukung.");
    }

    header("Content-Type: " . $mime);
    header("Content-Disposition: inline; filename=\"bukti-pembayaran\"");
    header("X-Content-Type-Options: nosniff");
    header("Cache-Control: private, no-store, max-age=0");

    readfile($filePath);
    exit;
}

$message = "";
$messageType = "success";

/*
 * PROSES KONFIRMASI ATAU PENOLAKAN
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $csrf = (string) ($_POST["csrf_token"] ?? "");
    $action = (string) ($_POST["action"] ?? "");
    $bookingCode = trim((string) ($_POST["booking_code"] ?? ""));

    if (!hash_equals($_SESSION["csrf_verifikasi"], $csrf)) {
        http_response_code(403);
        exit("Token keamanan tidak valid. Muat ulang halaman.");
    }

    if ($bookingCode === "") {
        $message = "Kode booking tidak valid.";
        $messageType = "error";
    } elseif (!in_array($action, ["confirm", "reject"], true)) {
        $message = "Aksi tidak dikenali.";
        $messageType = "error";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "SELECT booking_code, payment_status, payment_proof
                 FROM orders
                 WHERE booking_code = :booking_code
                 LIMIT 1
                 FOR UPDATE"
            );

            $stmt->execute(["booking_code" => $bookingCode]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                throw new RuntimeException("Pesanan tidak ditemukan.");
            }

            $currentStatus = strtolower(
                trim((string) $order["payment_status"])
            );

            if ($currentStatus === "lunas") {
                throw new RuntimeException(
                    "Pesanan sudah berstatus lunas."
                );
            }

            if ($currentStatus !== "menunggu verifikasi") {
                throw new RuntimeException(
                    "Pesanan tidak sedang menunggu verifikasi."
                );
            }

            if (empty($order["payment_proof"])) {
                throw new RuntimeException(
                    "Pesanan belum memiliki bukti pembayaran."
                );
            }

            $newStatus = ($action === "confirm")
                ? "lunas"
                : "ditolak";

            $update = $pdo->prepare(
                "UPDATE orders
                 SET payment_status = :status
                 WHERE booking_code = :booking_code"
            );

            $update->execute([
                "status" => $newStatus,
                "booking_code" => $bookingCode
            ]);

            $pdo->commit();

            $message = ($action === "confirm")
                ? "Pembayaran berhasil dikonfirmasi lunas."
                : "Bukti pembayaran ditolak. Pengguna dapat mengunggah ulang bukti.";

        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($exception instanceof RuntimeException) {
                $message = $exception->getMessage();
            } else {
                error_log($exception->getMessage());
                $message = "Terjadi kesalahan saat memperbarui pembayaran.";
            }

            $messageType = "error";
        }
    }
}

/*
 * DAFTAR PESANAN YANG MENUNGGU VERIFIKASI
 */
try {
    $stmt = $pdo->prepare(
        "SELECT booking_code, full_name, whatsapp,
                destination_name, visit_date, ticket_qty,
                ticket_price, total_price, payment_status,
                payment_proof, payment_proof_uploaded_at
         FROM orders
         WHERE LOWER(TRIM(payment_status)) = 'menunggu verifikasi'
         ORDER BY payment_proof_uploaded_at DESC"
    );

    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $orders = [];
    $message = "Gagal memuat pesanan. Periksa struktur tabel orders.";
    $messageType = "error";
}

function rupiah($amount)
{
    return "Rp" . number_format((float) $amount, 0, ",", ".");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Pembayaran | Visit Ciamis</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px 16px;
            background: #f2f5f3;
            color: #24352d;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1 {
            color: #246b48;
        }

        .intro {
            color: #68786e;
            line-height: 1.6;
        }

        .message {
            padding: 14px;
            margin: 18px 0;
            border-radius: 9px;
            background: #e2f6e9;
            color: #17663c;
        }

        .message.error {
            background: #fde8e7;
            color: #9a2525;
        }

        .empty {
            padding: 24px;
            background: white;
            border-radius: 12px;
            text-align: center;
        }

        .order {
            padding: 22px;
            margin: 20px 0;
            background: white;
            border: 1px solid #dce7df;
            border-radius: 14px;
            box-shadow: 0 5px 18px rgba(20, 60, 40, .05);
        }

        .code {
            font-size: 19px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin: 18px 0;
        }

        .detail {
            padding: 12px;
            background: #f7faf7;
            border-radius: 8px;
            overflow-wrap: anywhere;
        }

        .detail span {
            display: block;
            margin-bottom: 6px;
            color: #718078;
            font-size: 13px;
        }

        .proof {
            display: block;
            width: 100%;
            max-width: 420px;
            max-height: 500px;
            object-fit: contain;
            object-position: left center;
            margin: 14px 0;
            border: 1px solid #dce7df;
            border-radius: 8px;
            background: #f7faf7;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .button {
            padding: 12px 16px;
            border: 0;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .confirm {
            background: #246b48;
        }

        .reject {
            background: #b83232;
        }

        @media (max-width: 560px) {
            .details {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .button {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
<div class="container">
    <h1>Verifikasi Pembayaran</h1>

    <p class="intro">
        Periksa bukti pembayaran sebelum mengonfirmasi status pesanan.
        Pastikan nominal dan transaksi sudah sesuai.
    </p>

    <?php if ($message !== ""): ?>
        <div class="message <?= $messageType === "error" ? "error" : "" ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="empty">
            Belum ada pesanan yang menunggu verifikasi pembayaran.
        </div>
    <?php else: ?>

        <?php foreach ($orders as $order): ?>
            <article class="order">
                <div class="code">
                    Booking: <?= e($order["booking_code"]) ?>
                </div>

                <div class="details">
                    <div class="detail">
                        <span>Nama pemesan</span>
                        <strong><?= e($order["full_name"]) ?></strong>
                    </div>

                    <div class="detail">
                        <span>WhatsApp</span>
                        <strong><?= e($order["whatsapp"]) ?></strong>
                    </div>

                    <div class="detail">
                        <span>Destinasi</span>
                        <strong><?= e($order["destination_name"]) ?></strong>
                    </div>

                    <div class="detail">
                        <span>Tanggal kunjungan</span>
                        <strong><?= e($order["visit_date"]) ?></strong>
                    </div>

                    <div class="detail">
                        <span>Jumlah tiket</span>
                        <strong><?= e($order["ticket_qty"]) ?> tiket</strong>
                    </div>

                    <div class="detail">
                        <span>Total pembayaran</span>
                        <strong><?= e(rupiah($order["total_price"])) ?></strong>
                    </div>

                    <div class="detail">
                        <span>Status</span>
                        <strong><?= e($order["payment_status"]) ?></strong>
                    </div>

                    <div class="detail">
                        <span>Waktu unggah</span>
                        <strong>
                            <?= e($order["payment_proof_uploaded_at"] ?: "-") ?>
                        </strong>
                    </div>
                </div>

                <h3>Bukti pembayaran</h3>

                <?php
                    $proofName = basename(
                        (string) ($order["payment_proof"] ?? "")
                    );

                    $validProofName = preg_match(
                        '/\A[a-f0-9]{32}\.(jpg|png|webp)\z/',
                        $proofName
                    );
                ?>

                <?php if ($validProofName): ?>
                    <a
                        href="verifikasi.php?bukti=<?= rawurlencode($proofName) ?>"
                        target="_blank"
                        rel="noopener"
                    >
                        <img
                            class="proof"
                            src="verifikasi.php?bukti=<?= rawurlencode($proofName) ?>"
                            alt="Bukti pembayaran <?= e($order["booking_code"]) ?>"
                        >
                    </a>
                <?php else: ?>
                    <p>Bukti pembayaran tidak ditemukan atau nama file tidak valid.</p>
                <?php endif; ?>

                <div class="actions">
                    <form method="POST"
                          onsubmit="return confirm('Konfirmasi pembayaran ini sebagai LUNAS? Pastikan transaksi sudah diterima.');">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e($_SESSION["csrf_verifikasi"]) ?>"
                        >

                        <input
                            type="hidden"
                            name="booking_code"
                            value="<?= e($order["booking_code"]) ?>"
                        >

                        <input type="hidden" name="action" value="confirm">

                        <button type="submit" class="button confirm">
                            Konfirmasi Lunas
                        </button>
                    </form>

                    <form method="POST"
                          onsubmit="return confirm('Tolak bukti pembayaran ini?');">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e($_SESSION["csrf_verifikasi"]) ?>"
                        >

                        <input
                            type="hidden"
                            name="booking_code"
                            value="<?= e($order["booking_code"]) ?>"
                        >

                        <input type="hidden" name="action" value="reject">

                        <button type="submit" class="button reject">
                            Tolak Bukti
                        </button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>

    <?php endif; ?>
</div>
</body>
</html>
