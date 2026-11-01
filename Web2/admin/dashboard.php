
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../config/database.php";

$totalStmt = $pdo->query("SELECT COUNT(*) FROM orders");
$totalOrders = $totalStmt->fetchColumn();

$pendingStmt = $pdo->query(
    "SELECT COUNT(*) FROM orders
     WHERE payment_status = 'Menunggu Pembayaran'"
);
$pendingOrders = $pendingStmt->fetchColumn();

$paidStmt = $pdo->query(
    "SELECT COUNT(*) FROM orders
     WHERE payment_status = 'Lunas'"
);
$paidOrders = $paidStmt->fetchColumn();

$recentStmt = $pdo->query(
    "SELECT booking_code, full_name, destination_name,
            visit_date, ticket_qty, total_price, payment_status
     FROM orders
     ORDER BY created_at DESC
     LIMIT 10"
);
$orders = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin - Visit Ciamis</title>

    <style>
        * {
            box-sizing: border-box;
        }

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

        header h2 {
            margin: 0;
        }

        header a {
            color: white;
        }

        main {
            padding: 28px 5%;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin: 24px 0;
        }

        .card,
        .table-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px #0000000a;
        }

        .card p {
            color: #68756d;
            margin-bottom: 8px;
        }

        .number {
            font-size: 28px;
            font-weight: bold;
        }

        .table-box {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e5e9e6;
            white-space: nowrap;
        }

        th {
            background: #f7f9f7;
        }

        /* Navigasi dashboard admin */
        .admin-nav {
            margin: 20px 0;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .admin-nav a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #245c42;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .admin-nav a:hover {
            background: #194630;
        }

        .admin-nav a.active {
            background: #17432e;
            outline: 2px solid #8bbda0;
        }

        .admin-nav a.logout {
            background: #b84040;
        }

        .admin-nav a.logout:hover {
            background: #963333;
        }

        @media (max-width: 700px) {
            .cards {
                grid-template-columns: 1fr;
            }

            header {
                align-items: flex-start;
                flex-direction: column;
            }

            .admin-nav {
                gap: 8px;
            }
        }
    </style>
</head>

<body>

<header>
    <h2>Visit Ciamis | Admin</h2>

    <div>
        <?= htmlspecialchars($_SESSION["admin_email"] ?? "", ENT_QUOTES, "UTF-8") ?>
        &nbsp; | &nbsp;
        <a href="logout.php">Logout</a>
    </div>
</header>

<main>
    <h1>Dashboard</h1>

    <nav class="admin-nav">
        <a href="dashboard.php" class="active">
            <span aria-hidden="true">&#127968;</span>
            Dashboard
        </a>

        <a href="orders.php">
            <span aria-hidden="true">&#127915;</span>
            Daftar Pesanan
        </a>

        <!-- Menu baru Pusat Bantuan -->
        <a href="pusat-bantuan.php">
            <span aria-hidden="true">&#128172;</span>
            Pusat Bantuan
        </a>

        <a href="logout.php" class="logout">
            <span aria-hidden="true">&#10162;</span>
            Logout
        </a>
    </nav>

    <p>Ringkasan pemesanan tiket wisata.</p>

    <section class="cards">
        <div class="card">
            <p>Total Pesanan</p>
            <div class="number">
                <?= (int) $totalOrders ?>
            </div>
        </div>

        <div class="card">
            <p>Menunggu Pembayaran</p>
            <div class="number">
                <?= (int) $pendingOrders ?>
            </div>
        </div>

        <div class="card">
            <p>Pesanan Lunas</p>
            <div class="number">
                <?= (int) $paidOrders ?>
            </div>
        </div>
    </section>

    <h2>Pesanan Terbaru</h2>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Pemesan</th>
                    <th>Destinasi</th>
                    <th>Tanggal</th>
                    <th>Jumlah Tiket</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!$orders): ?>
                    <tr>
                        <td colspan="7">
                            Belum ada pesanan tiket.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars(
                                    $order["booking_code"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $order["full_name"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $order["destination_name"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $order["visit_date"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>

                            <td>
                                <?= (int) $order["ticket_qty"] ?>
                            </td>

                            <td>
                                Rp<?= number_format(
                                    (int) $order["total_price"],
                                    0,
                                    ",",
                                    "."
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $order["payment_status"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
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
