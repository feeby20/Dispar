
<?php
require_once __DIR__ . "/config/database.php";

$bookingCode = trim($_GET["booking"] ?? "");
$order = null;
$error = "";

if ($bookingCode !== "") {
    $stmt = $pdo->prepare(
        "SELECT * FROM orders WHERE booking_code = :code LIMIT 1"
    );
    $stmt->execute(["code" => $bookingCode]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $error = "Kode booking tidak ditemukan.";
    } elseif (strtolower(trim($order["payment_status"])) !== "lunas") {
        $error = "Pembayaran belum lunas. Tiket belum tersedia.";
        $order = null;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tiket Saya - Visit Ciamis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 650px;">
    <h2 class="text-center mb-4">Tiket Saya</h2>

    <form method="GET" class="card card-body mb-4">
        <label for="booking" class="form-label">Masukkan kode booking</label>
        <input
            type="text"
            name="booking"
            id="booking"
            class="form-control mb-3"
            value="<?= htmlspecialchars($bookingCode) ?>"
            placeholder="Contoh: VC123456"
            required
        >
        <button type="submit" class="btn btn-primary">Cari Tiket</button>
    </form>

    <?php if ($error !== ""): ?>
        <div class="alert alert-warning">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($order): ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="text-center mb-1">E-TIKET WISATA</h4>
                <p class="text-center text-success">Pembayaran Lunas</p>
                <hr>

                <p><strong>Kode Booking:</strong>
                    <?= htmlspecialchars($order["booking_code"]) ?></p>
                <p><strong>Nama:</strong>
                    <?= htmlspecialchars($order["full_name"]) ?></p>
                <p><strong>WhatsApp:</strong>
                    <?= htmlspecialchars($order["whatsapp"]) ?></p>
                <p><strong>Destinasi:</strong>
                    <?= htmlspecialchars($order["destination_name"]) ?></p>
                <p><strong>Tanggal Kunjungan:</strong>
                    <?= htmlspecialchars($order["visit_date"]) ?></p>
                <p><strong>Jumlah Tiket:</strong>
                    <?= htmlspecialchars($order["ticket_qty"]) ?></p>
                <p><strong>Total Pembayaran:</strong>
                    Rp <?= number_format((float)$order["total_price"], 0, ",", ".") ?></p>

                <button onclick="window.print()" class="btn btn-success w-100">
                    Cetak / Simpan Tiket PDF
                </button>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="index.html" class="text-decoration-none">Kembali ke Beranda</a>
    </div>
</div>
</body>
</html>
