
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sudahLogin = isset($_SESSION['user_id']);
$namaPengguna = $_SESSION['user_nama'] ?? 'Akun Saya';

$loginError = $_SESSION['login_error'] ?? '';
$registerError = $_SESSION['register_error'] ?? '';

$registerNama = $_SESSION['register_nama'] ?? '';
$registerEmail = $_SESSION['register_email'] ?? '';

unset(
    $_SESSION['login_error'],
    $_SESSION['register_error'],
    $_SESSION['register_nama'],
    $_SESSION['register_email']
);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Desa Wisata Kabupaten Ciamis</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/beranda.css">

    <style>
        .login-modal-content {
            border: none;
            border-radius: 18px;
            overflow: hidden;
        }

        .login-modal-content .form-control {
            padding: 12px;
            border-radius: 8px;
        }

        .login-modal-content .btn-login {
            width: 100%;
            padding: 12px;
            background: #176b52;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
        }

        .login-modal-content .btn-login:hover {
            background: #11543f;
            color: #fff;
        }

        .google-login-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 11px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: #fff;
            color: #333;
            text-decoration: none;
            font-weight: 500;
        }

        .google-login-btn:hover {
            background: #f8f9fa;
            color: #333;
        }

        .login-modal-title {
            color: #173f35;
            font-weight: 700;
        }

        .login-modal-link {
            color: #176b52;
            text-decoration: none;
        }

        .login-modal-link:hover {
            text-decoration: underline;
        }

        .login-modal-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
        }

        .auth-modal .modal-dialog {
            max-width: 480px;
        }

        .auth-modal .modal-body {
            max-height: 85vh;
            overflow-y: auto;
        }

        .navbar .auth-nav-button {
            border: none;
            background: transparent;
            cursor: pointer;
        }

        .navbar .auth-nav-button:focus-visible {
            outline: 2px solid #176b52;
            outline-offset: 3px;
        }

        .forgot-password {
            display: block;
            margin-top: 10px;
            margin-bottom: 18px;
            text-align: right;
            font-size: 14px;
        }

        @media (max-width: 991.98px) {
            .navbar .auth-nav-button {
                padding-left: 0;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top" id="navbar">
    <div class="container">

        <a class="navbar-brand logo" href="index.php">
            <img
                src="pictures/LogoDispar.png"
                alt="Logo Desa Wisata Ciamis"
            >
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
                    <a class="nav-link active" href="index.php">
                        Beranda
                    </a>
                </li>

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

                <li class="nav-item">
                    <a class="nav-link" href="berita.php">Berita</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="pemesanan.php">Tiket</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="pusat-bantuan.php">
                        Pusat Bantuan
                    </a>
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

                        <ul
                            class="dropdown-menu dropdown-menu-end"
                            aria-labelledby="akunDropdown"
                        >
                            <li>
                                <a class="dropdown-item" href="akun-saya.php">
                                    <i class="bi bi-person-circle me-2"></i>
                                    Akun Saya
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <a
                                    class="dropdown-item text-danger"
                                    href="keluar.php"
                                    onclick="return confirm('Yakin ingin keluar dari akun?');"
                                >
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
                        <button
                            type="button"
                            class="nav-link auth-nav-button"
                            data-bs-toggle="modal"
                            data-bs-target="#loginModal"
                        >
                            Masuk
                        </button>
                    </li>

                    <li class="nav-item ms-lg-2">
                        <button
                            type="button"
                            class="btn btn-primary px-4"
                            data-bs-toggle="modal"
                            data-bs-target="#registerModal"
                        >
                            Daftar
                        </button>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero" id="beranda">
    <div class="hero-overlay"></div>

    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-8">

                <span class="hero-badge">
                    <i class="bi bi-geo-alt-fill"></i>
                    Kabupaten Ciamis
                </span>

                <h1>
                    Eksplor Wisata
                    <span>Desa Wisata Ciamis</span>
                </h1>

                <p>
                    Temukan keindahan alam, budaya, sejarah, dan berbagai
                    potensi wisata yang dimiliki desa-desa di Kabupaten Ciamis.
                </p>

                <div class="hero-buttons">
                    <a href="destinasi.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-compass"></i>
                        Jelajahi Destinasi
                    </a>
                </div>

            </div>
        </div>
    </div>

    <div class="scroll-down">
        <span>Scroll untuk menjelajah</span>
        <i class="bi bi-chevron-down"></i>
    </div>
</section>

<!-- PENGANTAR -->
<section class="intro-section" id="tentang-singkat">
    <div class="container">
        <div class="row align-items-center g-5">

            <div class="col-lg-6">
                <div class="intro-image">
                    <img
                        src="pictures/Dispar.png"
                        alt="Desa Wisata Kabupaten Ciamis"
                    >

                    <div class="intro-image-card">
                        <i class="bi bi-geo-alt-fill"></i>
                        <div>
                            <strong>57 Desa Wisata</strong>
                            <small>Kabupaten Ciamis</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <span class="section-label">DESA WISATA CIAMIS</span>

                <h2>
                    Kenali Pesona Desa
                    <span>Ciamis</span>
                </h2>

                <p>
                    Kabupaten Ciamis memiliki berbagai desa dengan potensi
                    wisata yang menarik untuk dikunjungi. Mulai dari wisata
                    alam, budaya, sejarah, hingga wisata buatan.
                </p>

                <p>
                    Website ini hadir untuk membantu masyarakat dan wisatawan
                    mengenal lebih dekat berbagai destinasi wisata yang ada
                    di desa-desa Kabupaten Ciamis.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- KATEGORI -->
<section class="category-section">
    <div class="container">

        <div class="section-heading text-center">
            <span class="section-label">JENIS WISATA</span>

            <h2>
                Jelajahi Berdasarkan
                <span>Kategori</span>
            </h2>

            <p>
                Temukan berbagai jenis wisata yang tersedia di Kabupaten Ciamis.
            </p>
        </div>

        <div class="row g-4 mt-3">

            <div class="col-md-4">
                <div class="category-card">
                    <div class="category-icon">
                        <i class="bi bi-tree-fill"></i>
                    </div>

                    <h3>Wisata Alam</h3>

                    <p>
                        Nikmati keindahan alam Ciamis seperti curug, bukit,
                        sungai, hutan, situ, dan berbagai panorama alam lainnya.
                    </p>

                    <a href="destinasi.php?kategori=Alam">
                        Jelajahi <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="category-card">
                    <div class="category-icon">
                        <i class="bi bi-buildings-fill"></i>
                    </div>

                    <h3>Wisata Buatan</h3>

                    <p>
                        Berbagai tempat wisata yang dikembangkan masyarakat
                        desa untuk memberikan pengalaman wisata yang menarik
                        dan edukatif.
                    </p>

                    <a href="destinasi.php?kategori=Buatan">
                        Jelajahi <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="category-card">
                    <div class="category-icon">
                        <i class="bi bi-bank2"></i>
                    </div>

                    <h3>Sejarah &amp; Budaya</h3>

                    <p>
                        Kenali situs bersejarah, budaya, tradisi, kampung adat,
                        dan peninggalan sejarah yang ada di Ciamis.
                    </p>

                    <a href="destinasi.php?kategori=Sejarah%20%26%20Budaya">
                        Jelajahi <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- DESTINASI UNGGULAN -->
<section class="featured-section">
    <div class="container">

        <div class="section-heading">
            <div>
                <span class="section-label">DESTINASI</span>

                <h2>
                    Destinasi
                    <span>Unggulan</span>
                </h2>
            </div>

            <a href="destinasi.php" class="btn btn-outline-primary">
                Lihat Semua <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="row g-4 mt-2">

            <div class="col-lg-4 col-md-6">
                <div class="destination-card">
                    <div class="destination-image">
                        <img
                            src="pictures/destinasi/AstanaGedeKawali.jpeg"
                            alt="Astana Gede Kawali"
                        >

                        <span class="destination-category">
                            Sejarah &amp; Budaya
                        </span>
                    </div>

                    <div class="destination-content">
                        <span class="location">
                            <i class="bi bi-geo-alt-fill"></i>
                            Kawali
                        </span>

                        <h3>Astana Gede Kawali</h3>

                        <p>
                            Situs bersejarah yang menjadi salah satu peninggalan
                            Kerajaan Galuh.
                        </p>

                        <a href="detail/kawali.php">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="destination-card">
                    <div class="destination-image">
                        <img
                            src="pictures/destinasi/SituLengkongPanjalu.jpeg"
                            alt="Situ Lengkong Panjalu"
                        >

                        <span class="destination-category">Wisata Alam</span>
                    </div>

                    <div class="destination-content">
                        <span class="location">
                            <i class="bi bi-geo-alt-fill"></i>
                            Panjalu
                        </span>

                        <h3>Situ Lengkong Panjalu</h3>

                        <p>
                            Destinasi wisata alam dengan suasana danau yang
                            indah dan sejuk.
                        </p>

                        <a href="detail/panjalu.html">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="destination-card">
                    <div class="destination-image">
                        <img
                            src="pictures/destinasi/CadasNgampar.webp"
                            alt="Cadas Ngampar"
                        >

                        <span class="destination-category">Wisata Alam</span>
                    </div>

                    <div class="destination-content">
                        <span class="location">
                            <i class="bi bi-geo-alt-fill"></i>
                            Sadananya
                        </span>

                        <h3>Cadas Ngampar</h3>

                        <p>
                            Wisata alam dengan panorama sungai dan bebatuan
                            yang menarik.
                        </p>

                        <a href="detail/gunungsari.html">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-box">

            <div>
                <span class="section-label">TEMUKAN DESTINASIMU</span>

                <h2>
                    Siap Menjelajahi
                    <span>Ciamis?</span>
                </h2>

                <p>
                    Temukan berbagai destinasi menarik dari 57 desa wisata
                    di Kabupaten Ciamis.
                </p>
            </div>

            <a href="destinasi.php" class="btn btn-light btn-lg">
                Mulai Menjelajah <i class="bi bi-compass"></i>
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
                    Media informasi dan pengenalan potensi Desa Wisata
                    Kabupaten Ciamis.
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
                    <li><a href="akun-saya.php">Akun Saya</a></li>
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

<?php if (!$sudahLogin): ?>

<!-- MODAL MASUK -->
<div
    class="modal fade auth-modal"
    id="loginModal"
    tabindex="-1"
    aria-labelledby="loginModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content login-modal-content">
            <div class="modal-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <img
                        src="pictures/LogoDispar.png"
                        alt="Logo Visit Ciamis"
                        class="login-modal-logo mb-3"
                    >

                    <h3 id="loginModalLabel" class="login-modal-title">
                        Masuk
                    </h3>

                    <p class="text-muted mb-0">
                        Masuk ke akun Visit Ciamis kamu
                    </p>
                </div>

                <?php if ($loginError !== ''): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <a href="google-login.php" class="google-login-btn mb-3">
                    <img
                        src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg"
                        alt=""
                        width="20"
                        height="20"
                    >
                    Masuk dengan Google
                </a>

                <div class="d-flex align-items-center my-3">
                    <hr class="flex-grow-1">
                    <span class="text-muted small px-3">atau</span>
                    <hr class="flex-grow-1">
                </div>

                <form action="masuk.php" method="POST">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="loginEmail"
                            name="email"
                            placeholder="Masukkan email"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="mb-0">
                        <label for="loginPassword" class="form-label">
                            Password
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="loginPassword"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <a href="reset-password.php" class="login-modal-link forgot-password">
                        Lupa Password?
                    </a>

                    <button type="submit" class="btn btn-login">
                        Masuk
                    </button>
                </form>

                <p class="text-center mt-4 mb-2">
                    Belum punya akun?
                    <a
                        href="#"
                        class="login-modal-link"
                        data-bs-toggle="modal"
                        data-bs-target="#registerModal"
                        data-bs-dismiss="modal"
                    >
                        Daftar di sini
                    </a>
                </p>

                <div class="text-center">
                    <button
                        type="button"
                        class="btn btn-link text-muted text-decoration-none"
                        data-bs-dismiss="modal"
                    >
                        Nanti saja
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- MODAL DAFTAR -->
<div
    class="modal fade auth-modal"
    id="registerModal"
    tabindex="-1"
    aria-labelledby="registerModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content login-modal-content">
            <div class="modal-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <img
                        src="pictures/LogoDispar.png"
                        alt="Logo Visit Ciamis"
                        class="login-modal-logo mb-3"
                    >

                    <h3 id="registerModalLabel" class="login-modal-title">
                        Daftar Akun
                    </h3>

                    <p class="text-muted mb-0">
                        Buat akun Visit Ciamis kamu
                    </p>
                </div>

                <?php if ($registerError !== ''): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($registerError, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form action="daftar.php" method="POST">

                    <div class="mb-3">
                        <label for="registerNama" class="form-label">
                            Nama Lengkap
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="registerNama"
                            name="nama"
                            value="<?= htmlspecialchars($registerNama, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Masukkan nama lengkap"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="registerEmail"
                            name="email"
                            value="<?= htmlspecialchars($registerEmail, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Masukkan email"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">
                            Password
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="registerPassword"
                            name="password"
                            placeholder="Minimal 8 karakter"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label for="registerKonfirmasi" class="form-label">
                            Konfirmasi Password
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="registerKonfirmasi"
                            name="konfirmasi_password"
                            placeholder="Ulangi password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-login">
                        Daftar
                    </button>

                </form>

                <p class="text-center mt-4 mb-2">
                    Sudah punya akun?
                    <a
                        href="#"
                        class="login-modal-link"
                        data-bs-toggle="modal"
                        data-bs-target="#loginModal"
                        data-bs-dismiss="modal"
                    >
                        Masuk di sini
                    </a>
                </p>

                <div class="text-center">
                    <button
                        type="button"
                        class="btn btn-link text-muted text-decoration-none"
                        data-bs-dismiss="modal"
                    >
                        Nanti saja
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS UTAMA -->
<script src="js/main.js"></script>

<!-- BUKA KEMBALI MODAL JIKA LOGIN ATAU DAFTAR GAGAL -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');

    <?php if (!$sudahLogin && $loginError !== ''): ?>
    if (loginModal) {
        bootstrap.Modal.getOrCreateInstance(loginModal).show();
    }
    <?php elseif (!$sudahLogin && $registerError !== ''): ?>
    if (registerModal) {
        bootstrap.Modal.getOrCreateInstance(registerModal).show();
    }
    <?php endif; ?>
});
</script>

</body>
</html>
