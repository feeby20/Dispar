
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/koneksi.php';

$sudahLogin = isset($_SESSION['user_id']);
$namaPengguna = $_SESSION['user_nama'] ?? 'Akun Saya';
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Mengambil berita dari database menggunakan PDO
$berita = [];
$errorBerita = false;

try {
    $sql = "SELECT id, judul, ringkasan, isi, gambar, tanggal
            FROM berita
            ORDER BY tanggal DESC, id DESC";

    $stmt = $pdo->query($sql);
    $berita = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    $errorBerita = true;
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Berita | Desa Wisata Kabupaten Ciamis</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        /* Penyesuaian khusus halaman berita */
        body {
            padding-top: 80px;
        }

        .berita-intro {
            padding: 55px 0 35px;
            background: #fff;
            text-align: center;
        }

        .berita-intro-content {
            max-width: 850px;
            margin: 0 auto;
        }

        .berita-intro-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #198754;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .berita-intro h1 {
            font-size: 42px;
            font-weight: 700;
            color: #222;
            margin-bottom: 12px;
        }

        .berita-intro p {
            max-width: 700px;
            margin: 0 auto;
            color: #666;
            font-size: 16px;
            line-height: 1.7;
        }

        .berita-divider {
            width: 65px;
            height: 3px;
            background: #198754;
            margin: 22px auto 0;
            border-radius: 10px;
        }

        .berita-section {
            padding: 25px 0 70px;
        }

        .berita-section .section-title {
            margin-bottom: 30px;
        }

        .berita-section .section-title h2 {
            margin-bottom: 8px;
        }

        .berita-section .section-title p {
            margin-bottom: 0;
        }

        .berita-card {
            height: 100%;
            border: 1px solid #e5e5e5;
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            transition: all 0.25s ease;
        }

        .berita-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.10);
        }

        .berita-card .card-img-top {
            width: 100%;
            height: 190px;
            object-fit: cover;
            display: block;
        }

        .berita-card .card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .berita-card h4 {
            font-size: 1.15rem;
            line-height: 1.4;
            margin: 8px 0 10px;
        }

        .berita-card p {
            font-size: 0.94rem;
            line-height: 1.65;
            color: #555;
            margin-bottom: 18px;
        }

        .berita-tanggal {
            color: #198754;
            font-size: 0.9rem;
        }

        .berita-card .btn {
            align-self: flex-start;
            margin-top: auto;
            border-radius: 8px;
            padding: 9px 16px;
        }

        .berita-more-wrap {
            margin-top: 35px;
            text-align: center;
        }

        .berita-more-btn {
            border-radius: 9px;
            padding: 10px 22px;
        }

        .berita-item[hidden] {
            display: none !important;
        }

        @media (max-width: 767.98px) {
            body {
                padding-top: 70px;
            }

            .berita-intro {
                padding: 45px 20px 30px;
            }

            .berita-intro h1 {
                font-size: 32px;
            }

            .berita-intro p {
                font-size: 15px;
            }

            .berita-section {
                padding: 20px 0 55px;
            }

            .berita-card .card-img-top {
                height: 200px;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR: DISESUAIKAN DENGAN INDEX.PHP -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top" id="navbar">
    <div class="container">

        <!-- LOGO -->
        <a class="navbar-brand logo" href="index.php">
            <img src="pictures/LogoDispar.png" alt="Logo Desa Wisata Ciamis">
        </a>

        <!-- BUTTON MOBILE -->
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- MENU NAVBAR -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">

                <!-- BERANDA -->
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        Beranda
                    </a>
                </li>

                <!-- DESTINASI -->
                <li class="nav-item dropdown">
                    <a
                        class="nav-link dropdown-toggle"
                        href="destinasi.php"
                        id="destinasiDropdown"
                        role="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        Destinasi
                    </a>

                    <ul class="dropdown-menu" aria-labelledby="destinasiDropdown">
                        <li>
                            <a class="dropdown-item" href="destinasi.php">
                                <i class="bi bi-grid-fill me-2"></i>
                                Semua Wisata
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="destinasi.php?kategori=Alam">
                                <i class="bi bi-tree-fill me-2"></i>
                                Wisata Alam
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="destinasi.php?kategori=Buatan">
                                <i class="bi bi-building me-2"></i>
                                Wisata Buatan
                            </a>
                        </li>

                        <li>
                            <a
                                class="dropdown-item"
                                href="destinasi.php?kategori=Sejarah%20%26%20Budaya"
                            >
                                <i class="bi bi-bank me-2"></i>
                                Sejarah &amp; Budaya
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- BERITA -->
                <li class="nav-item">
                    <a class="nav-link active" href="berita.php" aria-current="page">
                        Berita
                    </a>
                </li>

                <!-- PEMESANAN -->
                <li class="nav-item">
                    <a class="nav-link" href="pemesanan.php">
                        Tiket 
                    </a>
                </li>

                <!-- PUSAT BANTUAN -->
                <li class="nav-item">
                    <a class="nav-link" href="pusat-bantuan.php">
                        Pusat Bantuan
                    </a>
                </li>

                <!-- JIKA SUDAH LOGIN -->
                <?php if ($sudahLogin): ?>

                    <li class="nav-item dropdown ms-lg-2">
                        <a
                            class="nav-link dropdown-toggle"
                            href="#"
                            id="akunDropdown"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="bi bi-person-circle me-1"></i>
                            <?= e($namaPengguna) ?>
                        </a>

                        <ul
                            class="dropdown-menu dropdown-menu-end"
                            aria-labelledby="akunDropdown"
                        >
                            <li class="nav-item">
    <a class="nav-link" href="akun-saya.php">
        <i class="bi bi-person-circle me-1"></i>
        Akun Saya
    </a>
</li>

                            <?php if ($isAdmin): ?>
                                <li>
                                    <a class="dropdown-item" href="admin/berita.php">
                                        <i class="bi bi-pencil-square me-2"></i>
                                        Kelola Berita
                                    </a>
                                </li>
                            <?php endif; ?>

    
                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Keluar
                                </a>
                            </li>
                        </ul>
                    </li>

                <!-- JIKA BELUM LOGIN -->
                <?php else: ?>

                    <li class="nav-item d-none d-lg-block">
                        <span class="nav-link text-secondary">|</span>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="masuk.php">
                            Masuk
                        </a>
                    </li>

                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-primary px-4" href="daftar.php">
                            Daftar
                        </a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<!-- PEMBUKA BERITA -->
<section class="berita-intro">
    <div class="container">
        <div class="berita-intro-content">

            <span class="berita-intro-label">
                <i class="bi bi-newspaper"></i>
                INFORMASI DESA WISATA
            </span>

            <h1>Berita Desa Wisata</h1>

            <p>
                Informasi terbaru mengenai kegiatan, potensi,
                dan perkembangan Desa Wisata Kabupaten Ciamis.
            </p>

            <div class="berita-divider"></div>
        </div>
    </div>
</section>

<!-- DAFTAR BERITA -->
<section class="berita-section">
    <div class="container">

        <div class="section-title">
            <h2>Berita Terbaru</h2>
            <p>
                Informasi terbaru mengenai kegiatan dan
                perkembangan desa wisata di Kabupaten Ciamis.
            </p>
        </div>

        <?php if ($isAdmin): ?>
            <div class="mb-4 text-end">
                <a href="admin/berita.php" class="btn btn-success">
                    <i class="bi bi-gear me-1"></i>
                    Kelola Berita
                </a>
            </div>
        <?php endif; ?>

        <div class="row g-4" id="gridBerita">

            <?php if ($errorBerita): ?>

                <div class="col-12">
                    <div class="alert alert-warning">
                        Berita belum dapat ditampilkan. Silakan coba kembali nanti.
                    </div>
                </div>

            <?php elseif (count($berita) === 0): ?>

                <div class="col-12">
                    <div class="alert alert-info">
                        Belum ada berita yang tersedia.
                    </div>
                </div>

            <?php else: ?>

                <?php foreach ($berita as $index => $item): ?>

                    <div
                        class="col-md-6 col-lg-4 berita-item"
                        <?= $index >= 3 ? 'hidden' : '' ?>
                    >
                        <div class="card berita-card">

                            <img
                                src="<?= e(!empty($item['gambar']) ? $item['gambar'] : 'pictures/LogoDispar.png') ?>"
                                class="card-img-top"
                                alt="<?= e($item['judul']) ?>"
                                loading="lazy"
                                onerror="this.onerror=null;this.src='pictures/LogoDispar.png';"
                            >

                            <div class="card-body">

                                <small class="berita-tanggal">
                                    <i class="fa-regular fa-calendar"></i>

                                    <?php
                                    $timestamp = strtotime($item['tanggal'] ?? '');
                                    echo e($timestamp ? date('d M Y', $timestamp) : '-');
                                    ?>
                                </small>

                                <h4><?= e($item['judul']) ?></h4>

                                <p><?= e($item['ringkasan']) ?></p>

                                <a
                                    href="detail-berita.php?id=<?= (int) $item['id'] ?>"
                                    class="btn btn-outline-primary"
                                >
                                    Baca Selengkapnya
                                </a>

                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

        <?php if (!$errorBerita && count($berita) > 3): ?>

            <div class="berita-more-wrap">
                <button
                    type="button"
                    class="btn btn-outline-success berita-more-btn"
                    id="lihatBeritaLainnya"
                >
                    Lihat Berita Lainnya
                    <i class="bi bi-arrow-down ms-1"></i>
                </button>
            </div>

        <?php endif; ?>

    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="row g-4">

            <!-- BRAND -->
            <div class="col-lg-5">
                <h4>Desa Wisata Kabupaten Ciamis</h4>

                <p>
                    Media informasi dan pengenalan potensi Desa Wisata
                    Kabupaten Ciamis.
                </p>
            </div>

            <!-- NAVIGASI -->
            <div class="col-lg-3 col-md-6">
                <h4>Navigasi</h4>

                <ul>
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="destinasi.php">Destinasi</a></li>
                    <li><a href="berita.php">Berita</a></li>
                    <li><a href="destinasi.php">Pemesanan</a></li>
                    <li><a href="pusat-bantuan.php">Pusat Bantuan</a></li>
                </ul>
            </div>

            <!-- KONTAK -->
            <div class="col-lg-4 col-md-6">
                <h4>Kontak</h4>

                <p>
                    <i class="bi bi-geo-alt-fill"></i>
                    Kabupaten Ciamis, Jawa Barat
                </p>

                <p>
                    <i class="bi bi-envelope-fill"></i>
                    info@desawisataciamis.id
                </p>
            </div>

        </div>

        <hr>

        <!-- FOOTER BOTTOM -->
        <div class="footer-bottom">
            <p>
                © 2026 Desa Wisata Kabupaten Ciamis.
                Semua Hak Dilindungi.
            </p>

            <div class="social-links">
                <a href="#" aria-label="Facebook">
                    <i class="bi bi-facebook"></i>
                </a>

                <a href="#" aria-label="Instagram">
                    <i class="bi bi-instagram"></i>
                </a>

                <a href="#" aria-label="YouTube">
                    <i class="bi bi-youtube"></i>
                </a>
            </div>
        </div>
    </div>
</footer>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- TOMBOL LIHAT BERITA LAINNYA -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tombol = document.getElementById('lihatBeritaLainnya');

    if (!tombol) {
        return;
    }

    const semuaBerita = Array.from(
        document.querySelectorAll('#gridBerita .berita-item')
    );

    let terbuka = false;

    tombol.addEventListener('click', function () {
        terbuka = !terbuka;

        semuaBerita.forEach(function (item, index) {
            item.hidden = !terbuka && index >= 3;
        });

        tombol.innerHTML = terbuka
            ? 'Sembunyikan Berita <i class="bi bi-arrow-up ms-1"></i>'
            : 'Lihat Berita Lainnya <i class="bi bi-arrow-down ms-1"></i>';
    });
});
</script>

</body>
</html>
