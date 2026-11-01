```php
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/database.php";

function e($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

function rupiah($value): string
{
    return "Rp" . number_format((float) $value, 0, ",", ".");
}

function normalizeStatus($status): string
{
    return strtolower(trim((string) $status));
}

$orderId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$orderId || $orderId < 1) {
    http_response_code(400);
    exit("ID pesanan tidak valid.");
}

$error = "";
$success = "";

if (empty($_SESSION["csrf_detail_pesanan"])) {
    $_SESSION["csrf_detail_pesanan"] = bin2hex(random_bytes(32));
}

$allowedStatuses = [
    "menunggu pembayaran",
    "menunggu verifikasi",
    "lunas",
    "ditolak"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $newStatus = normalizeStatus($_POST["payment_status"] ?? "");

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_detail_pesanan"], $token)
    ) {
        $error = "Permintaan tidak valid. Silakan muat ulang halaman.";
    } elseif (!in_array($newStatus, $allowedStatuses, true)) {
        $error = "Status pesanan tidak valid.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "SELECT id, booking_code, payment_status, payment_proof
                 FROM orders
                 WHERE id = ?
                 LIMIT 1
                 FOR UPDATE"
            );

            $stmt->execute([$orderId]);
            $currentOrder = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentOrder) {
                throw new RuntimeException("Pesanan tidak ditemukan.");
            }

            $currentStatus = normalizeStatus(
                $currentOrder["payment_status"]
            );

            if (
                $newStatus === "lunas" &&
                empty($currentOrder["payment_proof"])
            ) {
                throw new RuntimeException(
                    "Pesanan belum memiliki bukti pembayaran. " .
                    "Pembayaran tidak dapat dikonfirmasi lunas."
                );
            }

            if ($currentStatus === $newStatus) {
                throw new RuntimeException(
                    "Pesanan sudah memiliki status tersebut."
                );
            }

            $update = $pdo->prepare(
                "UPDATE orders
                 SET payment_status = ?
                 WHERE id = ?
                   AND payment_status = ?"
            );

            $update->execute([
                $newStatus,
                $orderId,
                $currentOrder["payment_status"]
            ]);

            if ($update->rowCount() !== 1) {
                throw new RuntimeException(
                    "Status pesanan berubah atau sudah diproses. " .
                    "Silakan muat ulang halaman."
                );
            }

            $pdo->commit();
            $success = "Status pesanan berhasil diperbarui.";

        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($exception instanceof RuntimeException) {
                $error = $exception->getMessage();
            } else {
                error_log(
                    "Gagal memperbarui status pesanan: " .
                    $exception->getMessage()
                );

                $error = "Status gagal diperbarui. Silakan coba kembali.";
            }
        }
    }
}

$stmt = $pdo->prepare(
    "SELECT * FROM orders WHERE id = ? LIMIT 1"
);

$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    exit("Pesanan tidak ditemukan.");
}

$status = normalizeStatus($order["payment_status"]);
$statusClass = "pending";

if ($status === "lunas") {
    $statusClass = "paid";
} elseif ($status === "ditolak") {
    $statusClass = "rejected";
} elseif ($status === "menunggu verifikasi") {
    $statusClass = "verification";
}

$selectableStatuses = [
    "menunggu pembayaran",
    "menunggu verifikasi",
    "lunas",
    "ditolak"
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Detail Pesanan | Admin Visit Ciamis</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 24px;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    color: #263238;
}

.container {
    max-width: 850px;
    margin: 0 auto;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 22px;
}

h1 {
    margin: 0;
    font-size: 26px;
}

.card {
    background: #fff;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding: 13px 0;
    border-bottom: 1px solid #edf0f2;
}

.detail-row:last-child {
    border-bottom: none;
}

.label {
    color: #687782;
}

.value {
    text-align: right;
    font-weight: 600;
    overflow-wrap: anywhere;
}

.status {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 20px;
    font-size: 13px;
}

.pending {
    background: #fff1cc;
    color: #805600;
}

.verification {
    background: #dceeff;
    color: #145a8d;
}

.paid {
    background: #d9f5e5;
    color: #176b3a;
}

.rejected {
    background: #ffe0e0;
    color: #a32020;
}

select,
button,
.button {
    padding: 11px 14px;
    border-radius: 7px;
    font-size: 14px;
}

select {
    width: 100%;
    margin: 10px 0 14px;
    border: 1px solid #ccd3d8;
    background: white;
}

button {
    width: 100%;
    border: none;
    background: #176b4d;
    color: white;
    cursor: pointer;
}

button:hover {
    background: #10543c;
}

.button {
    display: inline-block;
    background: #e8edf0;
    color: #263238;
    text-decoration: none;
}

.message {
    padding: 12px;
    border-radius: 7px;
    margin-bottom: 16px;
    line-height: 1.5;
}

.success {
    background: #d9f5e5;
    color: #176b3a;
}

.error {
    background: #ffe0e0;
    color: #a32020;
}

.warning {
    color: #805600;
    font-size: 14px;
    line-height: 1.6;
}

.proof-link {
    color: #176b4d;
    font-weight: bold;
}

.readonly-note {
    padding: 13px;
    background: #f1f5f2;
    border-radius: 8px;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 20px;
}

@media (max-width: 560px) {
    body {
        padding: 14px;
    }

    .card {
        padding: 18px;
    }

    .detail-row {
        flex-direction: column;
        gap: 6px;
    }

    .value {
        text-align: left;
    }
}
</style>
</head>

<body>
<div class="container">

    <div class="topbar">
        <h1>Detail Pesanan</h1>
        <a class="button" href="orders.php">
            &larr; Kembali ke Pesanan
        </a>
    </div>

    <?php if ($success !== ""): ?>
        <div class="message success">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="message error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <!-- INFORMASI PEMESANAN -->
    <section class="card">
        <h2>Informasi Pemesanan</h2>

        <div class="detail-row">
            <span class="label">Kode Booking</span>
            <span class="value">
                <?= e($order["booking_code"]) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="label">Nama Pemesan</span>
            <span class="value">
                <?= e($order["full_name"]) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="label">Nomor WhatsApp</span>
            <span class="value">
                <?= e($order["whatsapp"]) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="label">Destinasi</span>
            <span class="value">
                <?= e($order["destination_name"]) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="label">Tanggal Kunjungan</span>
            <span class="value">
                <?php
                $visitTimestamp = strtotime(
                    (string) $order["visit_date"]
                );

                echo $visitTimestamp
                    ? e(date("d-m-Y", $visitTimestamp))
                    : e($order["visit_date"]);
                ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="label">Jumlah Tiket</span>
            <span class="value">
                <?= (int) $order["ticket_qty"] ?> tiket
            </span>
        </div>

        <div class="detail-row">
            <span class="label">Harga per Tiket</span>
            <span class="value">
                <?= rupiah($order["ticket_price"]) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="label">Total Pembayaran</span>
            <span class="value">
                <?= rupiah($order["total_price"]) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="label">Tanggal Pemesanan</span>
            <span class="value">
                <?= e($order["created_at"]) ?>
            </span>
        </div>

        <div class="detail-row">
            <span class="label">Status Saat Ini</span>
            <span class="value">
                <span class="status <?= e($statusClass) ?>">
                    <?= e($order["payment_status"]) ?>
                </span>
            </span>
        </div>
    </section>

    <!-- KONFIRMASI PESANAN -->
    <section class="card">
        <h2>Konfirmasi Pesanan</h2>

        <p class="warning">
            Periksa data pesanan dan bukti pembayaran sebelum
            mengubah status. Status pembayaran dikelola pada
            tingkat pesanan, bukan per tiket.
        </p>

        <?php if (!empty($order["payment_proof"])): ?>
            <?php
            $proofPath = ltrim(
                (string) $order["payment_proof"],
                "/\\"
            );

            $proofPathIsSafe =
                strpos($proofPath, "..") === false &&
                strpos($proofPath, "\0") === false;
            ?>

            <?php if ($proofPathIsSafe): ?>
                <p>
                    Bukti pembayaran:
                    <a
                        class="proof-link"
                        href="../<?= e($proofPath) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Lihat bukti pembayaran
                    </a>
                </p>
            <?php else: ?>
                <p class="warning">
                    Lokasi bukti pembayaran tidak valid.
                </p>
            <?php endif; ?>

        <?php else: ?>
            <p class="warning">
                Belum ada bukti pembayaran yang tersimpan.
                Jangan konfirmasi lunas sebelum pembayaran
                benar-benar diterima.
            </p>
        <?php endif; ?>

        <div class="readonly-note">
            <?php if ($status === "lunas"): ?>
                Pembayaran sudah dikonfirmasi lunas.
                Pelanggan dapat mengakses e-tiket melalui akun mereka.

            <?php elseif ($status === "menunggu verifikasi"): ?>
                Bukti pembayaran sedang menunggu pemeriksaan admin.
                Silakan lanjutkan konfirmasi melalui halaman verifikasi.

                <br><br>

                <a class="proof-link" href="verifikasi.php">
                    Buka Halaman Verifikasi
                </a>

            <?php elseif ($status === "ditolak"): ?>
                Bukti pembayaran ditolak. Pelanggan perlu mengunggah
                ulang bukti pembayaran yang benar.

            <?php else: ?>
                Pembayaran belum diverifikasi. Tunggu sampai pelanggan
                mengunggah bukti pembayaran.
            <?php endif; ?>
        </div>

        <form
            method="POST"
            onsubmit="return confirm('Yakin ingin memperbarui status pesanan ini?');"
        >
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION["csrf_detail_pesanan"]) ?>"
            >

            <label for="payment_status">
                Pilih status pesanan
            </label>

            <select
                name="payment_status"
                id="payment_status"
                required
            >
                <?php foreach ($selectableStatuses as $optionStatus): ?>
                    <option
                        value="<?= e($optionStatus) ?>"
                        <?= $status === $optionStatus ? "selected" : "" ?>
                    >
                        <?= e(ucwords($optionStatus)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">
                Simpan Konfirmasi
            </button>
        </form>
    </section>

</div>
</body>
</html>
```