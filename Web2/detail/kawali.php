
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$sudahLogin = isset($_SESSION['user_id']);
$namaPengguna = $_SESSION['user_nama'] ?? 'Akun Saya';

// Ambil harga tiket Astana Gede Kawali dari database
$namaDestinasi = 'Astana Gede Kawali';

try {
    $stmtHarga = $pdo->prepare(
        "SELECT ticket_price
         FROM destination_prices
         WHERE destination_name = :nama
         LIMIT 1"
    );

    $stmtHarga->execute([
        'nama' => $namaDestinasi
    ]);

    $dataHarga = $stmtHarga->fetch(PDO::FETCH_ASSOC);

    if (!$dataHarga) {
        throw new RuntimeException(
            'Harga tiket Astana Gede Kawali belum tersedia.'
        );
    }

    $hargaTiket = (int) $dataHarga['ticket_price'];

} catch (Throwable $e) {
    error_log('Gagal mengambil harga tiket Kawali: ' . $e->getMessage());
    http_response_code(500);
    exit('Harga tiket belum dapat ditampilkan. Silakan periksa konfigurasi database.');
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Astana Gede Kawali | Visit Ciamis</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/detail.css">
    <link rel="stylesheet" href="../css/pemesanan.css">
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top" id="navbar">
    <div class="container">

        <a class="navbar-brand logo" href="../index.php">
            <img src="../pictures/LogoDispar.png" alt="Logo Desa Wisata Kabupaten Ciamis">
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Buka navigasi"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link" href="../index.php">Beranda</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="../destinasi.php">Destinasi</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="../berita.php">Berita</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="../pemesanan.php">Tiket</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="../pusat-bantuan.php">Pusat Bantuan</a>
                </li>

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
                            <?= htmlspecialchars($namaPengguna, ENT_QUOTES, 'UTF-8') ?>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="akunDropdown">
                            <li>
                                <a class="dropdown-item" href="../akun-saya.php">
                                    <i class="bi bi-person-circle me-2"></i>
                                    Akun Saya
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="../pemesanan.php">
                                    <i class="bi bi-ticket-perforated me-2"></i>
                                    Tiket Saya
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <a class="dropdown-item text-danger" href="../logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Keluar
                                </a>
                            </li>
                        </ul>
                    </li>

                <?php else: ?>

                    <li class="nav-item d-none d-lg-block">
                        <span class="nav-link text-secondary">|</span>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="../masuk.php">Masuk</a>
                    </li>

                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-primary px-4" href="../daftar.php">Daftar</a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="detail-hero">
    <div class="container">

        <div class="breadcrumb-detail">
            <a href="../index.php">Beranda</a>
            <i class="bi bi-chevron-right"></i>
            <a href="../destinasi.php">Destinasi</a>
            <i class="bi bi-chevron-right"></i>
            <span>Astana Gede Kawali</span>
        </div>

        <div class="detail-hero-content">

            <div class="detail-category">
                <i class="bi bi-bank2"></i>
                SEJARAH &amp; BUDAYA
            </div>

            <h1>Astana Gede Kawali</h1>

            <div class="detail-location">
                <i class="bi bi-geo-alt-fill"></i>
                Desa Kawali, Kecamatan Kawali, Kabupaten Ciamis
            </div>

        </div>
    </div>
</section>

<!-- KONTEN DETAIL -->
<main class="detail-content">
    <div class="container">
        <div class="row g-5">

            <div class="col-lg-8">

                <div class="detail-image-wrapper">
                    <img
                        src="../pictures/destinasi/AstanaGedeKawali.jpeg"
                        alt="Astana Gede Kawali"
                        class="detail-image"
                    >
                </div>

                <div class="photo-source">
                    <i class="bi bi-camera-fill"></i>
                    <span>Sumber Foto: detik.com</span>
                </div>

                <article class="detail-article">

                    <span class="article-label">TENTANG DESTINASI</span>

                    <h2>Mengenal Astana Gede Kawali</h2>

                    <p>
                        Astana Gede Kawali merupakan kawasan wisata sejarah
                        dan budaya yang berada di Desa Kawali, Kecamatan
                        Kawali, Kabupaten Ciamis.
                    </p>

                    <p>
                        Situs ini memiliki kaitan erat dengan sejarah Kerajaan
                        Galuh. Berbagai peninggalan sejarah di kawasan ini
                        menjadi sumber pembelajaran untuk mengenal perjalanan
                        sejarah dan kebudayaan masyarakat Ciamis.
                    </p>

                    <p>
                        Selain nilai sejarahnya, kawasan yang dikelilingi
                        pepohonan ini menawarkan suasana yang asri dan tenang.
                        Pengunjung dapat menikmati lingkungan situs sekaligus
                        mempelajari warisan budaya setempat.
                    </p>

                    <h3>Daya Tarik</h3>

                    <div class="attraction-list">

                        <div class="attraction-item">
                            <div class="attraction-icon">
                                <i class="bi bi-bank"></i>
                            </div>

                            <div>
                                <h4>Wisata Sejarah</h4>
                                <p>
                                    Mengenal peninggalan sejarah yang berkaitan
                                    dengan Kerajaan Galuh.
                                </p>
                            </div>
                        </div>

                        <div class="attraction-item">
                            <div class="attraction-icon">
                                <i class="bi bi-book"></i>
                            </div>

                            <div>
                                <h4>Wisata Edukasi</h4>
                                <p>
                                    Menambah wawasan mengenai sejarah dan
                                    kebudayaan Kabupaten Ciamis.
                                </p>
                            </div>
                        </div>

                        <div class="attraction-item">
                            <div class="attraction-icon">
                                <i class="bi bi-tree"></i>
                            </div>

                            <div>
                                <h4>Lingkungan Asri</h4>
                                <p>
                                    Menikmati suasana alami di kawasan situs
                                    bersejarah.
                                </p>
                            </div>
                        </div>

                    </div>
                </article>
            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-4">
                <aside class="detail-sidebar">

                    <div class="info-card">
                        <h3>Informasi Destinasi</h3>

                        <div class="info-item">
                            <i class="bi bi-geo-alt-fill"></i>

                            <div>
                                <span>Lokasi</span>

                                <a
                                    href="https://www.google.com/maps/search/?api=1&query=Astana+Gede+Kawali+Ciamis"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="location-link"
                                >
                                    <strong>Desa Kawali</strong>

                                    <small>
                                        <i class="bi bi-box-arrow-up-right"></i>
                                        Lihat di Google Maps
                                    </small>
                                </a>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="bi bi-map"></i>

                            <div>
                                <span>Kecamatan</span>
                                <strong>Kawali</strong>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="bi bi-tag"></i>

                            <div>
                                <span>Kategori</span>
                                <strong>Sejarah &amp; Budaya</strong>
                            </div>
                        </div>
                    </div>

                    <!-- PESAN TIKET -->
                    <div class="booking-card">

                        <div class="booking-header">
                            <div class="booking-icon">
                                <i class="bi bi-ticket-perforated"></i>
                            </div>

                            <div>
                                <h3>Informasi Kunjungan</h3>
                                <p>
                                    Rencanakan kunjunganmu ke Astana Gede Kawali.
                                </p>
                            </div>
                        </div>

                        <p class="booking-description">
                            Isi data pengunjung, pilih tanggal kunjungan,
                            dan tentukan jumlah tiket pada halaman pemesanan.
                        </p>

                        <!-- HARGA TIKET DINAMIS DARI DATABASE -->
                        <div class="ticket-price-info">
                            <span>
                                <i class="bi bi-tag-fill"></i>
                                Harga Tiket Masuk
                            </span>

                            <strong>
                                Rp<?= number_format($hargaTiket, 0, ',', '.') ?>
                                <small>/ orang</small>
                            </strong>
                        </div>

                        <!-- TOMBOL MENUJU FORM PEMESANAN KAWALI -->
                        <a
                            href="../pemesanan%20tiket/kawali.html"
                            class="booking-submit"
                        >
                            Pesan Tiket Sekarang
                            <i class="bi bi-arrow-right"></i>
                        </a>

                    </div>

                    <!-- AJAKAN BERKUNJUNG -->
                    <div class="visit-card">

                        <div class="visit-icon">
                            <i class="bi bi-compass"></i>
                        </div>

                        <h3>Jelajahi Ciamis</h3>

                        <p>
                            Temukan destinasi wisata lain dengan keunikan
                            dan daya tariknya masing-masing.
                        </p>

                        <a href="../destinasi.php" class="btn btn-light">
                            Jelajahi Destinasi
                            <i class="bi bi-arrow-right"></i>
                        </a>

                    </div>

                </aside>
            </div>

        </div>
    </div>
</main>

<!-- DESTINASI LAINNYA -->
<section class="related-destination">
    <div class="container">

        <div class="section-heading">
            <span class="section-label">JELAJAHI LAGI</span>
            <h2>Destinasi Lainnya</h2>

            <p>
                Temukan berbagai destinasi menarik di Kabupaten Ciamis.
            </p>
        </div>

        <div class="text-center">
            <a href="../destinasi.php" class="btn btn-primary">
                Lihat Semua Destinasi
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="row g-4">

            <div class="col-lg-5">
                <a href="../index.php" class="footer-logo">
                    <img
                        src="../pictures/LogoDispar.png"
                        alt="Logo Desa Wisata Kabupaten Ciamis"
                    >
                </a>

                <p>
                    Media informasi dan pengenalan potensi Desa Wisata
                    Kabupaten Ciamis.
                </p>
            </div>

            <div class="col-lg-3 col-md-6">
                <h4>Navigasi</h4>

                <ul>
                    <li><a href="../index.php">Beranda</a></li>
                    <li><a href="../destinasi.php">Destinasi</a></li>
                    <li><a href="../berita.php">Berita</a></li>
                    <li><a href="../pemesanan.php">Pemesanan</a></li>
                    <li><a href="../pusat-bantuan.php">Pusat Bantuan</a></li>
                </ul>
            </div>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/main.js"></script>

</body>
</html>
