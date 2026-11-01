
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sudahLogin = isset($_SESSION['user_id']);
$namaPengguna = $_SESSION['user_nama'] ?? 'Akun Saya';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kampung Sawah | Visit Ciamis</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/detail.css">
    <link rel="stylesheet" href="../css/pemesanan.css">

    <style>
        /* Kartu harga tiket */
        .destination-price-card {
            position: relative;
            overflow: hidden;
            padding: 22px;
            margin: 22px 0;
            border: 1px solid #d8e9dc;
            border-radius: 16px;
            background: linear-gradient(135deg, #f2faf4 0%, #ffffff 100%);
        }

        .destination-price-header {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 18px;
        }

        .destination-price-icon {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #176b45;
            color: #fff;
            font-size: 23px;
        }

        .destination-price-label {
            display: block;
            margin-bottom: 4px;
            color: #668071;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.2px;
        }

        .destination-price-title {
            margin: 0;
            color: #244a34;
            font-size: 18px;
            font-weight: 700;
        }

        .destination-price-divider {
            border: 0;
            border-top: 1px solid #d8e9dc;
            margin: 0 0 17px;
            opacity: 1;
        }

        .destination-price-amount {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 7px;
            margin-bottom: 15px;
        }

        .destination-price-amount strong {
            color: #176b45;
            font-size: clamp(28px, 5vw, 36px);
            line-height: 1.2;
            font-weight: 800;
        }

        .destination-price-amount span {
            color: #668071;
            font-size: 14px;
        }

        .destination-price-note {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #52705d;
            font-size: 13px;
            margin: 0;
        }

        .destination-price-note i {
            color: #176b45;
            font-size: 16px;
        }
    </style>
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
            <span>Kampung Sawah</span>
        </div>

        <div class="detail-hero-content">

            <div class="detail-category">
                <i class="bi bi-building"></i>
                WISATA BUATAN
            </div>

            <h1>Kampung Sawah</h1>

            <div class="detail-location">
                <i class="bi bi-geo-alt-fill"></i>
                Desa Talagasari, Kecamatan Kawali, Kabupaten Ciamis
            </div>

        </div>
    </div>
</section>

<!-- KONTEN DETAIL -->
<main class="detail-content">
    <div class="container">
        <div class="row g-5">

            <!-- KONTEN UTAMA -->
            <div class="col-lg-8">

                <div class="detail-image-wrapper">
                    <img
                        src="../pictures/destinasi/KampungSawah.jpeg"
                        alt="Kampung Sawah Desa Talagasari"
                        class="detail-image"
                    >
                </div>

                <div class="photo-source">
                    <i class="bi bi-camera-fill"></i>
                    <span>Sumber Foto: Galuh ID</span>
                </div>

                <article class="detail-article">

                    <span class="article-label">TENTANG DESTINASI</span>

                    <h2>Mengenal Kampung Sawah</h2>

                    <p>
                        Kampung Sawah merupakan salah satu destinasi wisata
                        yang berada di Dusun Cigintung, Desa Talagasari,
                        Kecamatan Kawali, Kabupaten Ciamis.
                    </p>

                    <p>
                        Kampung Sawah dikembangkan sebagai wisata yang
                        memanfaatkan lingkungan persawahan desa. Kawasan
                        ini memiliki suasana pedesaan yang asri dan menjadi
                        salah satu potensi wisata Desa Talagasari.
                    </p>

                    <p>
                        Destinasi ini mengusung konsep wisata keluarga dan
                        edukasi. Pengunjung dapat menikmati pemandangan
                        persawahan sekaligus mengenal kegiatan pertanian
                        dan lingkungan pedesaan.
                    </p>

                    <p>
                        Kampung Sawah dikelola oleh BUMDes Wibawamukti
                        Desa Talagasari. Kawasan wisata ini juga
                        dikembangkan untuk memberikan manfaat bagi
                        masyarakat dan perekonomian desa.
                    </p>

                    <h3>Daya Tarik</h3>

                    <div class="attraction-list">

                        <div class="attraction-item">
                            <div class="attraction-icon">
                                <i class="bi bi-flower1"></i>
                            </div>

                            <div>
                                <h4>Taman Bunga</h4>
                                <p>
                                    Area taman bunga menjadi salah satu
                                    daya tarik Kampung Sawah dan cocok
                                    untuk menikmati suasana wisata pedesaan.
                                </p>
                            </div>
                        </div>

                        <div class="attraction-item">
                            <div class="attraction-icon">
                                <i class="bi bi-camera"></i>
                            </div>

                            <div>
                                <h4>Spot Foto</h4>
                                <p>
                                    Pemandangan persawahan dan taman dapat
                                    menjadi latar untuk mengabadikan momen
                                    bersama keluarga.
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
                                    Pengunjung dapat mengenal lingkungan
                                    pertanian dan suasana kehidupan
                                    masyarakat pedesaan.
                                </p>
                            </div>
                        </div>

                        <div class="attraction-item">
                            <div class="attraction-icon">
                                <i class="bi bi-people"></i>
                            </div>

                            <div>
                                <h4>Wisata Keluarga</h4>
                                <p>
                                    Suasana Kampung Sawah cocok untuk
                                    bersantai dan menghabiskan waktu
                                    bersama keluarga.
                                </p>
                            </div>
                        </div>

                    </div>
                </article>
            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-4">
                <aside class="detail-sidebar">

                    <!-- INFORMASI DESTINASI -->
                    <div class="info-card">
                        <h3>Informasi Destinasi</h3>

                        <div class="info-item">
                            <i class="bi bi-geo-alt-fill"></i>

                            <div>
                                <span>Lokasi</span>

                                <a
                                    href="https://www.google.com/maps/search/?api=1&query=Kampung+Sawah+Talagasari+Kawali+Ciamis"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="location-link"
                                >
                                    <strong>Desa Talagasari</strong>

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
                                <strong>Wisata Buatan</strong>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="bi bi-ticket-perforated-fill"></i>

                            <div>
                                <span>Harga Tiket Masuk</span>
                                <strong>Rp5.000 / orang</strong>
                            </div>
                        </div>
                    </div>

                    <!-- INFORMASI KUNJUNGAN -->
                    <div class="booking-card">

                        <div class="booking-header">
                            <div class="booking-icon">
                                <i class="bi bi-ticket-perforated"></i>
                            </div>

                            <div>
                                <h3>Informasi Kunjungan</h3>
                                <p>
                                    Rencanakan kunjunganmu ke Kampung Sawah.
                                </p>
                            </div>
                        </div>

                        <p class="booking-description">
                            Isi data pengunjung, pilih tanggal kunjungan,
                            dan tentukan jumlah tiket pada halaman pemesanan.
                        </p>

                        <!-- KARTU HARGA TIKET -->
                        <div class="destination-price-card">

                            <div class="destination-price-header">
                                <div class="destination-price-icon">
                                    <i class="bi bi-ticket-perforated-fill"></i>
                                </div>

                                <div>
                                    <span class="destination-price-label">
                                        TIKET MASUK
                                    </span>

                                    <h4 class="destination-price-title">
                                        Kampung Sawah
                                    </h4>
                                </div>
                            </div>

                            <hr class="destination-price-divider">

                            <div class="destination-price-amount">
                                <strong>Rp5.000</strong>
                                <span>/ orang</span>
                            </div>

                            <p class="destination-price-note">
                                <i class="bi bi-check-circle-fill"></i>
                                Harga tiket per pengunjung
                            </p>

                        </div>

                        <a
                            href="../pemesanan%20tiket/talagasari.html"
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
