
<?php
session_start();

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require_once __DIR__ . "/config/database.php";

function sendJson($success, $message, $data = [], $httpCode = 200)
{
    http_response_code($httpCode);

    echo json_encode(
        array_merge([
            "success" => $success,
            "message" => $message
        ], $data),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

// Wajib login untuk mengecek pesanan.
if (!isset($_SESSION["user_id"])) {
    sendJson(
        false,
        "Silakan login terlebih dahulu.",
        [],
        401
    );
}

// Kode booking wajib disertakan.
$bookingCode = trim($_GET["booking"] ?? "");

if ($bookingCode === "") {
    sendJson(
        false,
        "Kode booking tidak ditemukan.",
        [],
        400
    );
}

try {
    // Pengguna hanya dapat melihat status pesanan miliknya.
    $stmt = $pdo->prepare(
        "SELECT
            booking_code,
            destination_name,
            visit_date,
            ticket_qty,
            total_price,
            payment_status
         FROM orders
         WHERE booking_code = :booking_code
           AND user_id = :user_id
         LIMIT 1"
    );

    $stmt->execute([
        "booking_code" => $bookingCode,
        "user_id" => (int) $_SESSION["user_id"]
    ]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        sendJson(
            false,
            "Pesanan tidak ditemukan atau bukan milik akun kamu.",
            [],
            404
        );
    }

    $paymentStatus = strtolower(
        trim((string) $order["payment_status"])
    );

    sendJson(
        true,
        "Status pesanan berhasil diambil.",
        [
            "bookingCode" => $order["booking_code"],
            "paymentStatus" => $paymentStatus,
            "order" => [
                "booking_code" => $order["booking_code"],
                "destination_name" => $order["destination_name"],
                "visit_date" => $order["visit_date"],
                "ticket_qty" => (int) $order["ticket_qty"],
                "total_price" => (float) $order["total_price"],
                "payment_status" => $paymentStatus
            ]
        ]
    );

} catch (Throwable $exception) {
    error_log($exception->getMessage());

    sendJson(
        false,
        "Terjadi kesalahan saat memeriksa status pembayaran.",
        [],
        500
    );
}
