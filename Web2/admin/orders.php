
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$destinasi = trim($_GET['destinasi'] ?? '');

$destinasiStmt = $pdo->query("
    SELECT DISTINCT destination_name
    FROM orders
    ORDER BY destination_name ASC
");
$daftarDestinasi = $destinasiStmt->fetchAll(PDO::FETCH_COLUMN);

if ($destinasi !== '') {
    $stmt = $pdo->prepare("
        SELECT *
        FROM orders
        WHERE destination_name = :destinasi
        ORDER BY created_at DESC
    ");
    $stmt->execute([':destinasi' => $destinasi]);
} else {
    $stmt = $pdo->query("
        SELECT *
        FROM orders
        ORDER BY created_at DESC
    ");
}

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($destinasi !== '') {
    $summaryStmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total_orders,
            COALESCE(SUM(ticket_qty), 0) AS total_tickets,
            COALESCE(SUM(
                CASE WHEN payment_status = 'Lunas'
                THEN total_price ELSE 0 END
            ), 0) AS total_sales
        FROM orders
        WHERE destination_name = :destinasi
    ");
    $summaryStmt->execute([':destinasi' => $destinasi]);
} else {
    $summaryStmt = $pdo->query("
        SELECT
            COUNT(*) AS total_orders,
            COALESCE(SUM(ticket_qty), 0) AS total_tickets,
            COALESCE(SUM(
                CASE WHEN payment_status = 'Lunas'
                THEN total_price ELSE 0 END
            ), 0) AS total_sales
        FROM orders
    ");
}

$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah($value) {
    return 'Rp' . number_format((int) $value, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Daftar Pesanan - Admin Visit Ciamis</title>
<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f4;
    color: #26352d;
}
header {
    background: #245c42;
    color: white;
    padding: 20px 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}
header h2 { margin: 0; }
header a { color: white; }
main { padding: 28px 5%; }
.admin-nav {
    margin: 20px 0;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.admin-nav a {
    padding: 10px 16px;
    background: #245c42;
    color: white;
    text-decoration: none;
    border-radius: 6px;
}
.admin-nav a.active { background: #17432e; }
.cards {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin: 24px 0;
}
.card, .table-box {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px #0000000a;
}
.card p { color: #68756d; }
.number { font-size: 25px; font-weight: bold; }
.filter {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 20px 0;
}
select, .filter button {
    padding: 11px;
    border: 1px solid #ccd8cf;
    border-radius: 6px;
}
.filter button {
    background: #245c42;
    color: white;
    cursor: pointer;
}
.table-box { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th, td {
    text-align: left;
    padding: 12px;
    border-bottom: 1px solid #e5e9e6;
    white-space: nowrap;
}
th { background: #f7f9f7; }
.proof {
    color: #176b45;
    font-weight: bold;
    text-decoration: none;
}
.proof:hover { text-decoration: underline; }
.no-proof { color: #999; }
.status {
    display: inline-block;
    padding: 6px 9px;
    border-radius: 20px;
    background: #fff3cd;
    color: #856404;
    font-size: 12px;
}
.status.paid {
    background: #e3f5e9;
    color: #176b45;
}
@media(max-width:700px) {
    .cards { grid-template-columns: 1fr; }
    header { align-items: flex-start; flex-direction: column; }
}
</style>
</head>
<body>

<header>
    <h2>Visit Ciamis | Admin</h2>
    <div>
        <?= e($_SESSION['admin_email'] ?? '') ?>
        &nbsp; | &nbsp;
        <a href="logout.php">Logout</a>
    </div>
</header>

<main>
    <h1>Daftar Pesanan</h1>

    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="orders.php" class="active">Daftar Pesanan</a>
        <a href="pusat-bantuan.php">Pusat Bantuan</a>
        <a href="logout.php">Logout</a>
    </nav>

    <p>Data pembelian tiket dan bukti pembayaran pengunjung.</p>

    <section class="cards">
        <div class="card">
            <p>Total Pesanan</p>
            <div class="number">
                <?= (int) $summary['total_orders'] ?>
            </div>
        </div>

        <div class="card">
            <p>Total Tiket Terjual</p>
            <div class="number">
                <?= (int) $summary['total_tickets'] ?>
            </div>
        </div>

        <div class="card">
            <p>Total Penjualan Lunas</p>
            <div class="number">
                <?= rupiah($summary['total_sales']) ?>
            </div>
        </div>
    </section>

    <h2>Pesanan Berdasarkan Destinasi</h2>

    <form method="GET" class="filter">
        <select name="destinasi">
            <option value="">Semua Destinasi</option>
            <?php foreach ($daftarDestinasi as $nama): ?>
                <option value="<?= e($nama) ?>"
                    <?= $destinasi === $nama ? 'selected' : '' ?>>
                    <?= e($nama) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Tampilkan</button>
        <a href="orders.php">Reset</a>
    </form>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>Kode Booking</th>
                    <th>Nama Pemesan</th>
                    <th>WhatsApp</th>
                    <th>Destinasi</th>
                    <th>Tanggal Kunjungan</th>
                    <th>Jumlah Tiket</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Bukti Pembayaran</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$orders): ?>
                <tr>
                    <td colspan="9">Belum ada pesanan.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= e($order['booking_code']) ?></td>
                        <td><?= e($order['full_name']) ?></td>
                        <td><?= e($order['whatsapp']) ?></td>
                        <td><?= e($order['destination_name']) ?></td>
                        <td><?= e($order['visit_date']) ?></td>
                        <td><?= (int) $order['ticket_qty'] ?></td>
                        <td><?= rupiah($order['total_price']) ?></td>
                        <td>
                            <span class="status <?= strtolower($order['payment_status']) === 'lunas' ? 'paid' : '' ?>">
                                <?= e($order['payment_status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($order['payment_proof'])): ?>
                                <a class="proof"
                                   href="../<?= e($order['payment_proof']) ?>"
                                   target="_blank"
                                   rel="noopener">
                                    Lihat Bukti
                                </a>
                            <?php else: ?>
                                <span class="no-proof">Belum diunggah</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
