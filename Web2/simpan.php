
<?php
session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/config/database.php";

function sendResponse($success, $message, $data = [], $httpCode = 200)
{
    http_response_code($httpCode);

    echo json_encode(array_merge([
        "success" => $success,
        "message" => $message
    ], $data), JSON_UNESCAPED_UNICODE);

    exit;
}

// Hanya menerima POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Metode permintaan tidak diizinkan.", [], 405);
}

// Pastikan pengguna sudah login.
if (empty($_SESSION["user_id"])) {
    sendResponse(false, "Silakan masuk terlebih dahulu.", [], 401);
}

$userId = (int) $_SESSION["user_id"];

// Ambil data JSON dari JavaScript.
$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    sendResponse(false, "Data pemesanan tidak valid.", [], 400);
}

$destinationKey = trim($input["destination"] ?? "");
$fullName = trim($input["name"] ?? "");
$whatsapp = trim($input["whatsapp"] ?? "");
$visitDate = trim($input["date"] ?? "");
$ticketQty = filter_var(
    $input["quantity"] ?? null,
    FILTER_VALIDATE_INT
);

// Validasi data wajib.
if (
    $destinationKey === "" ||
    $fullName === "" ||
    $whatsapp === "" ||
    $visitDate === "" ||
    $ticketQty === false ||
    $ticketQty === null
) {
    sendResponse(false, "Semua data pemesanan wajib diisi.", [], 400);
}

if (strlen($fullName) > 120) {
    sendResponse(false, "Nama maksimal 120 karakter.", [], 400);
}

if (
    !preg_match('/^[0-9+\-\s]{9,30}$/', $whatsapp) ||
    strlen(preg_replace('/\D/', '', $whatsapp)) < 9
) {
    sendResponse(false, "Nomor WhatsApp tidak valid.", [], 400);
}

if ($ticketQty < 1 || $ticketQty > 100) {
    sendResponse(false, "Jumlah tiket harus antara 1 sampai 100.", [], 400);
}

// Validasi tanggal kunjungan.
$dateObject = DateTime::createFromFormat("!Y-m-d", $visitDate);

if (
    !$dateObject ||
    $dateObject->format("Y-m-d") !== $visitDate ||
    $visitDate < date("Y-m-d")
) {
    sendResponse(false, "Tanggal kunjungan tidak valid.", [], 400);
}

// Samakan format nama destinasi untuk pencocokan.
function makeSlug($value)
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);

    return trim($value, '-');
}

try {
    // Cari harga resmi destinasi dari database.
    $stmt = $pdo->query("
        SELECT destination_name, ticket_price
        FROM destination_prices
    ");

    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $selectedDestination = null;

    foreach ($destinations as $destination) {
        if (
            makeSlug($destination["destination_name"]) ===
            makeSlug($destinationKey)
        ) {
            $selectedDestination = $destination;
            break;
        }
    }

    if (!$selectedDestination) {
        sendResponse(
            false,
            "Destinasi belum terdaftar atau harga belum diatur oleh admin.",
            [],
            404
        );
    }

    $destinationName = $selectedDestination["destination_name"];
    $ticketPrice = (int) $selectedDestination["ticket_price"];

    if ($ticketPrice < 0) {
        sendResponse(false, "Harga destinasi tidak valid.", [], 400);
    }

    // Hitung total berdasarkan harga dari database.
    $totalPrice = $ticketPrice * $ticketQty;

    // Tentukan status berdasarkan harga.
    $paymentStatus = ($totalPrice === 0)
        ? "Gratis"
        : "Menunggu Pembayaran";

    // Buat kode booking unik.
    do {
        $bookingCode = "VC-" .
            date("Ymd") . "-" .
            strtoupper(bin2hex(random_bytes(4)));

        $check = $pdo->prepare("
            SELECT id
            FROM orders
            WHERE booking_code = :booking_code
            LIMIT 1
        ");

        $check->execute([
            ":booking_code" => $bookingCode
        ]);

        $exists = $check->fetchColumn();

    } while ($exists);

    // Simpan pesanan.
    $insert = $pdo->prepare("
        INSERT INTO orders (
            user_id,
            booking_code,
            full_name,
            whatsapp,
            destination_name,
            visit_date,
            ticket_qty,
            ticket_price,
            total_price,
            payment_status,
            payment_proof
        ) VALUES (
            :user_id,
            :booking_code,
            :full_name,
            :whatsapp,
            :destination_name,
            :visit_date,
            :ticket_qty,
            :ticket_price,
            :total_price,
            :payment_status,
            NULL
        )
    ");

    $insert->execute([
        ":user_id" => $userId,
        ":booking_code" => $bookingCode,
        ":full_name" => $fullName,
        ":whatsapp" => $whatsapp,
        ":destination_name" => $destinationName,
        ":visit_date" => $visitDate,
        ":ticket_qty" => $ticketQty,
        ":ticket_price" => $ticketPrice,
        ":total_price" => $totalPrice,
        ":payment_status" => $paymentStatus
    ]);

    sendResponse(true, "Pemesanan berhasil disimpan.", [
        "booking_code" => $bookingCode,
        "destination" => $destinationName,
        "ticket_price" => $ticketPrice,
        "ticket_qty" => $ticketQty,
        "total_price" => $totalPrice,
        "payment_status" => $paymentStatus,
        "is_free" => ($totalPrice === 0)
    ]);

} catch (PDOException $e) {
    error_log("Kesalahan simpan pesanan: " . $e->getMessage());

    sendResponse(
        false,
        "Terjadi kesalahan pada database. Silakan coba kembali.",
        [],
        500
    );
}
?>
