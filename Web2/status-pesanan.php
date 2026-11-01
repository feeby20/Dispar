
<?php
session_start();

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

require_once __DIR__ . "/config/database.php";

function sendJson(int $httpCode, array $data): void
{
    http_response_code($httpCode);
    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

// Pastikan pengguna sudah login.
if (empty($_SESSION["user_id"])) {
    sendJson(401, [
        "success" => false,
        "message" => "Silakan login terlebih dahulu."
    ]);
}

// Ambil kode booking dari URL.
$bookingCode = trim((string) ($_GET["booking"] ?? ""));

if ($bookingCode === "") {
    sendJson(400, [
        "success" => false,
        "message" => "Kode booking tidak ditemukan."
    ]);
}

try {
    // Pengguna hanya boleh melihat status pesanannya sendiri.
    $stmt = $pdo->prepare(
        "SELECT
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
         LIMIT 1"
    );

    $stmt->execute([
        "booking_code" => $bookingCode,
        "user_id" => $_SESSION["user_id"]
    ]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        sendJson(404, [
            "success" => false,
            "message" => "Pesanan tidak ditemukan."
        ]);
    }

    sendJson(200, [
        "success" => true,
        "bookingCode" => $order["booking_code"],
        "paymentStatus" => strtolower(
            trim((string) $order["payment_status"])
        ),
        "order" => $order
    ]);

} catch (Throwable $exception) {
    error_log(
        "Gagal memeriksa status pembayaran: " .
        $exception->getMessage()
    );

    sendJson(500, [
        "success" => false,
        "message" => "Status pembayaran gagal diperiksa."
    ]);
}


<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/config/database.php';

function jsonResponse($success, $message, $extra = [], $status = 200)
{
    http_response_code($status);
    echo json_encode(
        array_merge([
            'success' => $success,
            'message' => $message
        ], $extra),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

$bookingCode = trim($_GET['booking'] ?? '');

if ($bookingCode === '' || strlen($bookingCode) > 30) {
    jsonResponse(false, 'Kode pemesanan tidak valid.', [], 400);
}

try {
    $stmt = $pdo->prepare(
        'SELECT
            booking_code,
            full_name,
            destination_name,
            visit_date,
            ticket_qty,
            ticket_price,
            total_price,
            payment_status
         FROM orders
         WHERE booking_code = :booking_code
         LIMIT 1'
    );

    $stmt->execute([
        ':booking_code' => $bookingCode
    ]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        jsonResponse(false, 'Pesanan tidak ditemukan.', [], 404);
    }

    jsonResponse(true, 'Data pesanan berhasil diambil.', [
        'bookingCode' => $order['booking_code'],
        'paymentStatus' => $order['payment_status'],
        'order' => [
            'booking_code' => $order['booking_code'],
            'full_name' => $order['full_name'],
            'destination_name' => $order['destination_name'],
            'visit_date' => $order['visit_date'],
            'ticket_qty' => (int) $order['ticket_qty'],
            'ticket_price' => (int) $order['ticket_price'],
            'total_price' => (int) $order['total_price'],
            'payment_status' => $order['payment_status']
        ]
    ]);

} catch (PDOException $e) {
    error_log('Status pesanan: ' . $e->getMessage());

    jsonResponse(
        false,
        'Gagal mengambil status pesanan.',
        [],
        500
    );
}
?>
