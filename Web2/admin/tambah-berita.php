
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

$judul = '';
$ringkasan = '';
$isi = '';
$gambar = '';
$tanggal = date('Y-m-d');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $ringkasan = trim($_POST['ringkasan'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $gambar = trim($_POST['gambar'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';

    $tanggalValid = DateTime::createFromFormat('!Y-m-d', $tanggal);

    if (
        $judul === '' ||
        $ringkasan === '' ||
        $isi === '' ||
        !$tanggalValid ||
        $tanggalValid->format('Y-m-d') !== $tanggal
    ) {
        $error = 'Judul, ringkasan, isi, dan tanggal harus diisi dengan benar.';
    } elseif (strlen($judul) > 255 || strlen($gambar) > 500) {
        $error = 'Judul maksimal 255 karakter dan lokasi gambar maksimal 500 karakter.';
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO berita (judul, ringkasan, isi, gambar, tanggal)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "sssss",
            $judul,
            $ringkasan,
            $isi,
            $gambar,
            $tanggal
        );

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: berita.php?status=tambah');
            exit;
        }

        $error = 'Berita gagal disimpan. Silakan coba lagi.';
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Berita</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7f5; color: #333; }
        .form-container {
            max-width: 760px;
            margin: 35px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
        }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input, textarea {
            box-sizing: border-box;
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font: inherit;
        }
        textarea { min-height: 120px; resize: vertical; }
        .tombol {
            display: inline-block;
            margin-top: 18px;
            padding: 11px 16px;
            border: 0;
            border-radius: 5px;
            background: #24613c;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }
        .error { background: #ffe7e7; padding: 12px; border-radius: 5px; }
        .catatan { color: #666; font-size: 13px; }
    </style>
</head>
<body>
<div class="form-container">
    <h1>Tambah Berita</h1>

    <?php if ($error !== ''): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="judul">Judul berita</label>
        <input id="judul" name="judul" maxlength="255"
               value="<?= e($judul) ?>" required>

        <label for="ringkasan">Ringkasan</label>
        <textarea id="ringkasan" name="ringkasan"
                  required><?= e($ringkasan) ?></textarea>

        <label for="isi">Isi berita</label>
        <textarea id="isi" name="isi" required><?= e($isi) ?></textarea>

        <label for="gambar">Lokasi gambar</label>
        <input id="gambar" name="gambar" maxlength="500"
               value="<?= e($gambar) ?>"
               placeholder="Contoh: pictures/berita-kegiatan.jpg">
        <p class="catatan">Masukkan lokasi file gambar yang sudah ada di folder website. Form ini belum mengunggah file gambar.</p>

        <label for="tanggal">Tanggal berita</label>
        <input id="tanggal" type="date" name="tanggal"
               value="<?= e($tanggal) ?>" required>

        <button class="tombol" type="submit">Simpan Berita</button>
        <a class="tombol" href="berita.php">Batal</a>
    </form>
</div>
</body>
</html>
