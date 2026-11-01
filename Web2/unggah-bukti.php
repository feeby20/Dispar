
<?php
session_start();
require_once __DIR__ . '/config/database.php';

function kembaliKePembayaran($booking, $pesan) {
    header(
        'Location: pembayaran.php?booking=' .
        urlencode($booking) . '&pesan=' . urlencode($pesan)
    );
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Permintaan tidak valid.');
}

$bookingCode = trim($_POST['booking_code'] ?? '');

if ($bookingCode === '') {
    die('Kode booking tidak ditemukan.');
}

// Ambil data pesanan
$stmt = $pdo->prepare("
    SELECT id, user_id, payment_status
    FROM orders
    WHERE booking_code = :booking
    LIMIT 1
");
$stmt->execute([':booking' => $bookingCode]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Pesanan tidak ditemukan.');
}

// Pastikan pesanan milik pengguna yang sedang login
if (
    !empty($order['user_id']) &&
    (
        empty($_SESSION['user_id']) ||
        (int) $_SESSION['user_id'] !== (int) $order['user_id']
    )
) {
    die('Kamu tidak memiliki akses ke pesanan ini.');
}

if (strtolower(trim($order['payment_status'])) === 'lunas') {
    kembaliKePembayaran(
        $bookingCode,
        'Bukti pembayaran sudah pernah dikirim.'
    );
}

// Validasi file
if (
    !isset($_FILES['payment_proof']) ||
    $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK
) {
    kembaliKePembayaran(
        $bookingCode,
        'Silakan pilih bukti pembayaran terlebih dahulu.'
    );
}

$file = $_FILES['payment_proof'];

if ($file['size'] > 3 * 1024 * 1024) {
    kembaliKePembayaran(
        $bookingCode,
        'Ukuran file maksimal 3 MB.'
    );
}

// Periksa tipe file berdasarkan isi sebenarnya
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);

$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

if (!isset($allowedTypes[$mime])) {
    kembaliKePembayaran(
        $bookingCode,
        'Format file harus JPG, PNG, atau WEBP.'
    );
}

// Buat folder upload jika belum tersedia
$uploadDir = __DIR__ . '/uploads/bukti-pembayaran/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mime];
$filePath = $uploadDir . $fileName;

if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    kembaliKePembayaran(
        $bookingCode,
        'Bukti pembayaran gagal disimpan.'
    );
}

$relativePath = 'uploads/bukti-pembayaran/' . $fileName;

try {
    // MODE PERCOBAAN:
    // Status Lunas diberikan setelah bukti berhasil diunggah.
    $update = $pdo->prepare("
        UPDATE orders
        SET payment_proof = :proof,
            payment_status = 'Lunas'
        WHERE id = :id
    ");

    $update->execute([
        ':proof' => $relativePath,
        ':id' => $order['id']
    ]);

} catch (PDOException $e) {
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    error_log($e->getMessage());

    kembaliKePembayaran(
        $bookingCode,
        'Terjadi kesalahan saat menyimpan bukti pembayaran.'
    );
}

// Kembali ke halaman pembayaran.
// Halaman tersebut akan menampilkan tombol Lihat E-Ticket.
kembaliKePembayaran(
    $bookingCode,
    'Bukti pembayaran berhasil diunggah.'
);
