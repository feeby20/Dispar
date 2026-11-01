
<?php
session_start();
require_once __DIR__ . '/koneksi.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: berita.php');
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, judul, ringkasan, isi, gambar, tanggal
     FROM berita WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$berita = $result->fetch_assoc();
$stmt->close();

if (!$berita) {
    http_response_code(404);
    exit('Berita tidak ditemukan.');
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$sudahLogin = isset($_SESSION['user_id']);
$namaPengguna = $_SESSION['user_nama'] ?? 'Akun Saya';
$isAdmin = isset($_SESSION['role'])
    && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($berita['judul']) ?> | Berita</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .detail-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 0 20px;
        }
        .detail-berita {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,.08);
        }
        .detail-berita h1 {
            color: #245c3a;
            margin-bottom: 12px;
        }
        .tanggal-berita {
            color: #777;
            font-size: 14px;
            margin-bottom: 25px;
        }
        .gambar-berita {
            width: 100%;
            max-height: 450px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .isi-berita {
            line-height: 1.8;
            color: #333;
            overflow-wrap: anywhere;
        }
        .tombol-kembali {
            display: inline-block;
            margin-bottom: 20px;
            color: #245c3a;
            text-decoration: none;
            font-weight: bold;
        }
        @media (max-width: 600px) {
            .detail-berita { padding: 20px; }
            .detail-berita h1 { font-size: 25px; }
        }
    </style>
</head>
<body>

<header>
    <nav>
        <a href="index.php">Beranda</a>
        <a href="destinasi.php">Destinasi</a>
        <a href="berita.php">Berita</a>
        <a href="kontak.html">Kontak</a>

        <?php if ($isAdmin): ?>
            <a href="admin/berita.php">Kelola Berita</a>
        <?php endif; ?>

        <?php if ($sudahLogin): ?>
            <span><?= e($namaPengguna) ?></span>
            <a href="logout.php">Keluar</a>
        <?php else: ?>
            <a href="masuk.php">Masuk</a>
            <a href="daftar.php">Daftar</a>
        <?php endif; ?>
    </nav>
</header>

<main class="detail-container">
    <a class="tombol-kembali" href="berita.php">
        &larr; Kembali ke Berita
    </a>

    <article class="detail-berita">
        <h1><?= e($berita['judul']) ?></h1>

        <div class="tanggal-berita">
            <?= e(date('d F Y', strtotime($berita['tanggal']))) ?>
        </div>

        <?php if (!empty($berita['gambar'])): ?>
            <img
                class="gambar-berita"
                src="<?= e($berita['gambar']) ?>"
                alt="<?= e($berita['judul']) ?>"
            >
        <?php endif; ?>

        <?php if (!empty($berita['ringkasan'])): ?>
            <p><strong><?= e($berita['ringkasan']) ?></strong></p>
        <?php endif; ?>

        <div class="isi-berita">
            <?= nl2br(e($berita['isi'])) ?>
        </div>
    </article>
</main>

</body>
</html>
