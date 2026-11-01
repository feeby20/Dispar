
<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'admin'
) {
    http_response_code(403);
    exit('Akses ditolak. Silakan masuk menggunakan akun admin.');
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$pesan = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'tambah') {
        $pesan = 'Berita berhasil ditambahkan.';
    } elseif ($_GET['status'] === 'edit') {
        $pesan = 'Berita berhasil diperbarui.';
    } elseif ($_GET['status'] === 'hapus') {
        $pesan = 'Berita berhasil dihapus.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['hapus_id'])) {

    $hapusId = filter_input(
        INPUT_POST,
        'hapus_id',
        FILTER_VALIDATE_INT
    );

    if ($hapusId && $hapusId > 0) {
        $stmt = $conn->prepare("DELETE FROM berita WHERE id = ?");
        $stmt->bind_param("i", $hapusId);
        $stmt->execute();
        $stmt->close();

        header('Location: berita.php?status=hapus');
        exit;
    }
}

$result = $conn->query(
    "SELECT id, judul, tanggal
     FROM berita
     ORDER BY tanggal DESC, id DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .admin-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .tombol {
            display: inline-block;
            padding: 10px 15px;
            background: #24613c;
            color: white;
            text-decoration: none;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }
        .tombol-edit { background: #2877b8; }
        .tombol-hapus { background: #c0392b; }
        .tabel-wrapper { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th { background: #24613c; color: white; }
        .pesan {
            padding: 12px;
            background: #e5f4e8;
            border-radius: 6px;
            margin-bottom: 18px;
        }
        .aksi { display: flex; gap: 8px; flex-wrap: wrap; }
        form { margin: 0; }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-header">
        <h1>Kelola Berita</h1>
        <div>
            <a class="tombol" href="tambah-berita.php">+ Tambah Berita</a>
            <a class="tombol" href="../berita.php">Lihat Website</a>
        </div>
    </div>

    <?php if ($pesan !== ''): ?>
        <div class="pesan"><?= e($pesan) ?></div>
    <?php endif; ?>

    <div class="tabel-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Judul Berita</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php $no = 1; ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= e($row['judul']) ?></td>
                        <td><?= e($row['tanggal']) ?></td>
                        <td>
                            <div class="aksi">
                                <a
                                    class="tombol tombol-edit"
                                    href="edit-berita.php?id=<?= (int) $row['id'] ?>"
                                >Edit</a>

                                <form method="post"
                                      onsubmit="return confirm('Yakin ingin menghapus berita ini?')">
                                    <input
                                        type="hidden"
                                        name="hapus_id"
                                        value="<?= (int) $row['id'] ?>"
                                    >
                                    <button class="tombol tombol-hapus"
                                            type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Belum ada berita. Silakan tambahkan berita.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
