
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

    <title>Destinasi Wisata | Desa Wisata Kabupaten Ciamis</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/destinasi.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="navbar">
        <div class="container">

            <a class="navbar-brand logo" href="index.php">
                <img src="pictures/LogoDispar.png" alt="Logo Desa Wisata Ciamis">
            </a>

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

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">

                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a
                            class="nav-link dropdown-toggle active"
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

                    <li class="nav-item">
                        <a class="nav-link" href="berita.php">Berita</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="pemesanan.php">Tiket</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="pusat-bantuan.php">Pusat Bantuan</a>
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
                                    <a class="dropdown-item" href="akun-saya.php">
                                        <i class="bi bi-person-circle me-2"></i>
                                        Akun Saya
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item text-danger" href="logout.php">
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
                            <a class="nav-link" href="masuk.php">Masuk</a>
                        </li>

                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-primary px-4" href="daftar.php">Daftar</a>
                        </li>

                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>

    <!-- HEADER DESTINASI -->
    <header class="destination-header">
        <div class="container">
            <div class="destination-header-content">

                <span class="section-label">
                    <i class="bi bi-compass"></i>
                    DESTINASI WISATA
                </span>

                <h1>
                    Jelajahi Desa Wisata
                    <span>Ciamis</span>
                </h1>

                <p>
                    Temukan berbagai destinasi menarik dari
                    57 desa wisata yang tersebar di Kabupaten Ciamis.
                </p>

            </div>
        </div>
    </header>

    <!-- INFORMASI HASIL -->
    <section class="destination-filter">
        <div class="container">
            <div class="filter-result">
                <span>
                    Menampilkan
                    <strong id="resultCount">57</strong>
                    destinasi
                </span>
            </div>
        </div>
    </section>

    <!-- DAFTAR DESTINASI -->
    <main class="destination-list">
        <div class="container">

            <div class="row g-4" id="destinationContainer"></div>

            <div
                id="emptyResult"
                class="empty-result"
                style="display: none;"
            >
                <div class="empty-icon">
                    <i class="bi bi-search"></i>
                </div>

                <h3>Destinasi Tidak Ditemukan</h3>

                <p>Belum ada destinasi wisata pada kategori ini.</p>

                <a href="destinasi.php" class="btn btn-primary">
                    <i class="bi bi-grid-fill me-2"></i>
                    Tampilkan Semua Destinasi
                </a>
            </div>

        </div>
    </main>

    <!-- CTA -->
    <section class="destination-cta">
        <div class="container">
            <div class="destination-cta-box">

                <div>
                    <span class="section-label">JELAJAHI CIAMIS</span>

                    <h2>
                        Temukan Pengalaman
                        <span>Wisata Baru</span>
                    </h2>

                    <p>
                        Setiap desa memiliki cerita,
                        keindahan, dan pengalaman yang berbeda.
                    </p>
                </div>

                <a href="pusat-bantuan.php" class="btn btn-light">
                    Pusat Bantuan
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
                    <p>
                        Media informasi dan pengenalan
                        potensi Desa Wisata Kabupaten Ciamis.
                    </p>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h4>Navigasi</h4>

                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="destinasi.php">Destinasi</a></li>
                        <li><a href="berita.php">Berita</a></li>
                        <li><a href="pemesanan.php">Pemesanan</a></li>
                        <li><a href="pusat-bantuan.php">Pusat Bantuan</a></li>
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
    <script src="js/main.js"></script>
    <script src="js/destinasi.js?v=20261029"></script>

</body>
</html>
