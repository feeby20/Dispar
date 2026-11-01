
<?php
session_start();
require_once __DIR__ . '/config/database.php';

$bookingCode = trim($_GET['booking'] ?? '');

if ($bookingCode === '') {
    die('Kode booking tidak ditemukan.');
}

$stmt = $pdo->prepare("
    SELECT
        id,
        user_id,
        booking_code,
        full_name,
        destination_name,
        visit_date,
        ticket_qty,
        ticket_price,
        total_price,
        payment_status,
        payment_proof
    FROM orders
    WHERE booking_code = :booking
    LIMIT 1
");

$stmt->execute([':booking' => $bookingCode]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Pesanan tidak ditemukan.');
}

// Pemeriksaan kepemilikan pesanan
if (
    !empty($order['user_id']) &&
    (
        empty($_SESSION['user_id']) ||
        (int) $_SESSION['user_id'] !== (int) $order['user_id']
    )
) {
    die('Kamu tidak memiliki akses ke pesanan ini.');
}

$isPaid = strtolower(trim($order['payment_status'])) === 'lunas';

function rupiah($angka) {
    return 'Rp' . number_format((int) $angka, 0, ',', '.');
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$pesan = trim($_GET['pesan'] ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Pembayaran Tiket - Visit Ciamis</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 25px 15px;
    font-family: Arial, sans-serif;
    background: #f4f7f5;
    color: #26332c;
}

.container {
    max-width: 650px;
    margin: 30px auto;
    padding: 30px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 18px rgba(0,0,0,.08);
}

h2 {
    text-align: center;
    color: #176b45;
}

.booking-code {
    text-align: center;
    padding: 12px;
    background: #edf7f0;
    border-radius: 8px;
    font-weight: bold;
    margin-bottom: 25px;
}

.detail {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.detail span:first-child {
    color: #666;
}

.total {
    font-size: 20px;
    color: #176b45;
    font-weight: bold;
}

.qris {
    text-align: center;
    margin: 25px 0;
}

.qris img {
    display: block;
    width: 100%;
    max-width: 300px;
    height: auto;
    margin: 15px auto;
    border: 1px solid #ddd;
    border-radius: 10px;
}

.status,
.message {
    padding: 13px;
    text-align: center;
    border-radius: 8px;
    margin: 20px 0;
    line-height: 1.5;
}

.status {
    background: #fff3cd;
    color: #856404;
}

.paid,
.success {
    background: #e3f5e9;
    color: #176b45;
}

.message {
    background: #edf2ff;
    color: #294c87;
}

.note {
    padding: 13px;
    background: #fff8e5;
    border-radius: 8px;
    font-size: 14px;
    line-height: 1.6;
}

.upload-box {
    margin-top: 22px;
    padding: 18px;
    border: 1px solid #dce5df;
    border-radius: 10px;
}

input[type=file] {
    display: block;
    width: 100%;
    margin: 15px 0;
}

.button {
    display: block;
    width: 100%;
    padding: 14px;
    margin-top: 15px;
    text-align: center;
    text-decoration: none;
    border: none;
    border-radius: 8px;
    background: #176b45;
    color: white;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

.button:hover {
    background: #105536;
}

.back {
    background: #68756d;
}

.back:hover {
    background: #515c55;
}

@media(max-width:480px) {
    .container {
        padding: 22px 17px;
    }

    .detail {
        font-size: 14px;
    }
}
</style>
</head>

<body>

<div class="container">

    <h2>Pembayaran Tiket</h2>

    <div class="booking-code">
        Kode Booking: <?= e($order['booking_code']) ?>
    </div>

    <?php if ($pesan !== ''): ?>
        <div class="message">
            <?= e($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="detail">
        <span>Nama Pemesan</span>
        <strong><?= e($order['full_name']) ?></strong>
    </div>

    <div class="detail">
        <span>Destinasi</span>
        <strong><?= e($order['destination_name']) ?></strong>
    </div>

    <div class="detail">
        <span>Tanggal Kunjungan</span>
        <strong><?= date('d-m-Y', strtotime($order['visit_date'])) ?></strong>
    </div>

    <div class="detail">
        <span>Jumlah Tiket</span>
        <strong><?= (int) $order['ticket_qty'] ?> tiket</strong>
    </div>

    <div class="detail">
        <span>Harga Tiket</span>
        <strong><?= rupiah($order['ticket_price']) ?></strong>
    </div>

    <div class="detail total">
        <span>Total Pembayaran</span>
        <strong><?= rupiah($order['total_price']) ?></strong>
    </div>

    <?php if ($isPaid): ?>

        <div class="status paid">
            <strong>Bukti pembayaran berhasil dikirim!</strong><br>
            Terima kasih telah melakukan pemesanan.
        </div>

        <a
            class="button"
            href="tiket.php?booking=<?= urlencode($order['booking_code']) ?>"
        >
            Lihat E-Ticket
        </a>

    <?php else: ?>

        <div class="status">
            Menunggu Pembayaran
        </div>

        <div class="qris">
            <h3>Scan QRIS untuk Membayar</h3>

            <img
                src="pictures/qris.jpeg"
                alt="QRIS Visit Ciamis"
            >

            <p>
                Scan QRIS menggunakan aplikasi pembayaran
                yang mendukung QRIS.
            </p>
        </div>

        <div class="note">
            <strong>Petunjuk Pembayaran:</strong><br>
            1. Scan QRIS di atas.<br>
            2. Bayar sesuai total pembayaran.<br>
            3. Simpan bukti transaksi.<br>
            4. Unggah bukti pembayaran di bawah ini.
        </div>

        <form
            class="upload-box"
            action="unggah-bukti.php"
            method="POST"
            enctype="multipart/form-data"
        >

            <h3>Upload Bukti Pembayaran</h3>

            <p>
                Pilih foto bukti pembayaran
                (JPG, PNG, atau WEBP; maksimal 3 MB).
            </p>

            <input
                type="hidden"
                name="booking_code"
                value="<?= e($order['booking_code']) ?>"
            >

            <input
                type="file"
                name="payment_proof"
                accept="image/jpeg,image/png,image/webp"
                required
            >

            <button type="submit" class="button">
                Unggah Bukti Pembayaran
            </button>

        </form>

    <?php endif; ?>

    <a class="button back" href="index.php">
        Kembali ke Beranda
    </a>

</div>

</body>
</html>
