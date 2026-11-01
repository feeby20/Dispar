
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    exit('Koneksi database belum tersedia. Periksa config/database.php.');
}

$sudahLogin = isset($_SESSION['user_id']);
$namaPengguna = $_SESSION['user_nama'] ?? 'Akun Saya';

if (empty($_SESSION['support_csrf'])) {
    $_SESSION['support_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['support_csrf'], $token)) {
        $_SESSION['support_error'] = 'Sesi formulir tidak valid. Silakan coba lagi.';
        header('Location: pusat-bantuan.php#form-bantuan');
        exit;
    }

    $nama = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $topik = trim($_POST['topic'] ?? '');
    $subjek = trim($_POST['subject'] ?? '');
    $pesan = trim($_POST['message'] ?? '');

    $topikValid = [
        'Pemesanan Tiket',
        'Pembayaran',
        'Akun',
        'Informasi Destinasi',
        'Keluhan Lainnya'
    ];

    if (
        $nama === '' ||
        mb_strlen($nama) > 120 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        mb_strlen($email) > 190 ||
        !in_array($topik, $topikValid, true) ||
        $subjek === '' ||
        mb_strlen($subjek) > 200 ||
        $pesan === '' ||
        mb_strlen($pesan) > 5000
    ) {
        $_SESSION['support_error'] =
            'Periksa kembali data. Pastikan semua kolom terisi dengan benar.';
        header('Location: pusat-bantuan.php#form-bantuan');
        exit;
    }

    try {
        $kodeTiket = 'BANTUAN-' .
            date('Ymd') . '-' .
            strtoupper(bin2hex(random_bytes(4)));

        $stmt = $pdo->prepare("
            INSERT INTO support_messages
                (ticket_code, name, email, topic, subject, message)
            VALUES
                (:ticket_code, :name, :email, :topic, :subject, :message)
        ");

        $stmt->execute([
            ':ticket_code' => $kodeTiket,
            ':name' => $nama,
            ':email' => $email,
            ':topic' => $topik,
            ':subject' => $subjek,
            ':message' => $pesan
        ]);

        $_SESSION['support_success'] = $kodeTiket;
        $_SESSION['support_csrf'] = bin2hex(random_bytes(32));

    } catch (PDOException $e) {
        error_log('Pusat bantuan: ' . $e->getMessage());
        $_SESSION['support_error'] =
            'Pesan belum dapat dikirim. Silakan coba kembali.';
    }

    header('Location: pusat-bantuan.php#form-bantuan');
    exit;
}

$faqStmt = $pdo->query("
    SELECT id, category, question, answer
    FROM support_faqs
    WHERE is_active = 1
    ORDER BY sort_order ASC, id DESC
");

$faqs = $faqStmt->fetchAll(PDO::FETCH_ASSOC);

$successCode = $_SESSION['support_success'] ?? null;
$errorMessage = $_SESSION['support_error'] ?? null;

unset($_SESSION['support_success'], $_SESSION['support_error']);

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Pusat Bantuan - Desa Wisata Ciamis</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >
    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            background: #f7f9fc;
            color: #263238;
        }

        .help-hero {
            padding: 145px 0 65px;
            background: linear-gradient(135deg, #075b43, #13845e);
            color: white;
            text-align: center;
        }

        .help-hero h1 {
            font-size: clamp(30px, 4vw, 44px);
            font-weight: 750;
        }

        .help-hero p {
            color: #e3f3eb;
        }

        .help-search {
            position: relative;
            max-width: 680px;
            margin: 25px auto 0;
        }

        .help-search input {
            width: 100%;
            padding: 17px 60px 17px 20px;
            border: 0;
            border-radius: 12px;
        }

        .help-search button {
            position: absolute;
            right: 7px;
            top: 6px;
            height: 42px;
            width: 45px;
            border: 0;
            border-radius: 9px;
            background: #087f5b;
            color: white;
        }

        .help-section {
            padding: 55px 0;
        }

        .help-heading {
            text-align: center;
            margin-bottom: 30px;
        }

        .help-heading h2 {
            font-weight: 750;
        }

        .help-heading p,
        .help-muted {
            color: #718096;
        }

        .help-category,
        .help-panel {
            background: white;
            border: 1px solid #e5ebf0;
            border-radius: 14px;
        }

        .help-category {
            display: block;
            height: 100%;
            padding: 22px;
            color: #263238;
            text-decoration: none;
            transition: .2s;
        }

        .help-category:hover {
            border-color: #13845e;
            transform: translateY(-3px);
        }

        .help-icon {
            display: grid;
            place-items: center;
            width: 48px;
            height: 48px;
            margin-bottom: 15px;
            border-radius: 12px;
            background: #e8f7f0;
            color: #087f5b;
            font-size: 23px;
        }

        .help-category h3 {
            font-size: 17px;
            font-weight: 700;
        }

        .help-category p {
            margin: 0;
            font-size: 14px;
            color: #718096;
        }

        .faq-wrap {
            max-width: 850px;
            margin: auto;
        }

        .faq-item {
            border: 1px solid #e5ebf0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .accordion-button {
            font-weight: 600;
            padding: 19px;
        }

        .accordion-button:not(.collapsed) {
            color: #087f5b;
            background: #f0faf5;
            box-shadow: none;
        }

        .accordion-body {
            color: #596579;
            line-height: 1.8;
        }

        .help-panel {
            padding: 28px;
        }

        .help-panel h3 {
            font-weight: 700;
            font-size: 21px;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            padding: 12px;
            border-radius: 9px;
        }

        .help-empty {
            display: none;
            padding: 24px;
            text-align: center;
            color: #718096;
        }

        .help-footer {
            padding: 24px 0;
            background: #123b30;
            color: white;
            text-align: center;
            font-size: 13px;
        }

        @media (max-width: 767px) {
            .help-hero {
                padding-top: 120px;
            }

            .help-section {
                padding: 40px 0;
            }

            .help-panel {
                padding: 20px;
            }
        }
    </style>
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
                    <a class="nav-link dropdown-toggle"
                       href="destinasi.php"
                       id="destinasiDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        Destinasi
                    </a>

                    <ul class="dropdown-menu" aria-labelledby="destinasiDropdown">
                        <li>
                            <a class="dropdown-item" href="destinasi.php">
                                <i class="bi bi-grid-fill me-2"></i>Semua Wisata
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="destinasi.php?kategori=Alam">
                                <i class="bi bi-tree-fill me-2"></i>Wisata Alam
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="destinasi.php?kategori=Buatan">
                                <i class="bi bi-building me-2"></i>Wisata Buatan
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item"
                               href="destinasi.php?kategori=Sejarah%20%26%20Budaya">
                                <i class="bi bi-bank me-2"></i>Sejarah &amp; Budaya
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
                    <a class="nav-link active" href="pusat-bantuan.php">
                        Pusat Bantuan
                    </a>
                </li>

                <?php if ($sudahLogin): ?>
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle"
                           href="#"
                           id="akunDropdown"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= h($namaPengguna) ?>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end"
                            aria-labelledby="akunDropdown">
                           <li class="nav-item">
    <a class="nav-link" href="akun-saya.php">
        <i class="bi bi-person-circle me-1"></i>
        Akun Saya
    </a>
</li>

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Keluar
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

<!-- HERO -->
<section class="help-hero">
    <div class="container">
        <h1>Ada yang bisa kami bantu?</h1>
        <p>Temukan jawaban seputar wisata, tiket, pembayaran, dan akunmu.</p>

        <form class="help-search" id="helpSearchForm">
            <input
                type="search"
                id="helpSearch"
                placeholder="Cari pertanyaan atau topik bantuan..."
                aria-label="Cari pertanyaan bantuan"
            >
            <button type="submit" aria-label="Cari">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>
</section>

<!-- KATEGORI -->
<section class="help-section">
    <div class="container">
        <div class="help-heading">
            <h2>Jelajahi Pusat Bantuan</h2>
            <p>Pilih kategori yang sesuai dengan pertanyaanmu.</p>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <a href="#faq-list" class="help-category" data-category="Pemesanan Tiket">
                    <div class="help-icon"><i class="bi bi-ticket-perforated"></i></div>
                    <h3>Pemesanan Tiket</h3>
                    <p>Cara memesan dan memeriksa status pesanan.</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="#faq-list" class="help-category" data-category="Pembayaran">
                    <div class="help-icon"><i class="bi bi-credit-card"></i></div>
                    <h3>Pembayaran</h3>
                    <p>Informasi pembayaran dan kendala transaksi.</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="#faq-list" class="help-category" data-category="Akun">
                    <div class="help-icon"><i class="bi bi-person-circle"></i></div>
                    <h3>Akun Saya</h3>
                    <p>Bantuan login dan pendaftaran akun.</p>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="#faq-list" class="help-category" data-category="Informasi Destinasi">
                    <div class="help-icon"><i class="bi bi-geo-alt"></i></div>
                    <h3>Informasi Destinasi</h3>
                    <p>Informasi tempat wisata di Kabupaten Ciamis.</p>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- FAQ DARI DATABASE -->
<section class="help-section pt-0" id="faq-list">
    <div class="container">
        <div class="help-heading">
            <h2>Pertanyaan yang Sering Diajukan</h2>
            <p>Jawaban yang disediakan oleh pengelola website.</p>
        </div>

        <div class="faq-wrap">
            <div class="accordion" id="faqAccordion">
                <?php if (empty($faqs)): ?>
                    <div class="help-panel text-center">
                        Belum ada pertanyaan bantuan yang dipublikasikan.
                    </div>
                <?php else: ?>
                    <?php foreach ($faqs as $index => $faq): ?>
                        <?php
                        $collapseId = 'faqAnswer' . (int) $faq['id'];
                        ?>
                        <div class="accordion-item faq-item"
                             data-category="<?= h($faq['category']) ?>"
                             data-search="<?= h(
                                 $faq['category'] . ' ' .
                                 $faq['question'] . ' ' .
                                 $faq['answer']
                             ) ?>">
                            <h3 class="accordion-header">
                                <button
                                    class="accordion-button collapsed"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?= h($collapseId) ?>"
                                    aria-expanded="false"
                                    aria-controls="<?= h($collapseId) ?>"
                                >
                                    <?= h($faq['question']) ?>
                                </button>
                            </h3>

                            <div id="<?= h($collapseId) ?>"
                                 class="accordion-collapse collapse"
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?= nl2br(h($faq['answer'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="help-empty" id="faqEmpty">
                <i class="bi bi-search fs-2"></i>
                <p class="mt-2 mb-0">
                    Tidak ada FAQ yang cocok. Kamu bisa mengirim pertanyaan
                    melalui formulir di bawah.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- FORMULIR PERTANYAAN -->
<section class="help-section pt-0" id="form-bantuan">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="help-panel">
                    <h3><i class="bi bi-chat-square-text me-2"></i>Kirim Pertanyaan atau Keluhan</h3>
                    <p class="help-muted">
                        Isi formulir berikut. Pesanmu akan diterima oleh admin.
                    </p>

                    <?php if ($successCode): ?>
                        <div class="alert alert-success">
                            <strong>Pesan berhasil dikirim.</strong><br>
                            Simpan kode tiket bantuanmu:
                            <span class="fw-bold"><?= h($successCode) ?></span>
                            <br>
                            Kode ini dapat digunakan saat menghubungi pengelola.
                            Halaman ini belum menyediakan pelacakan balasan
                            mandiri berdasarkan kode tiket.
                        </div>
                    <?php endif; ?>

                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger">
                            <?= h($errorMessage) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="pusat-bantuan.php#form-bantuan">
                        <input type="hidden"
                               name="csrf_token"
                               value="<?= h($_SESSION['support_csrf']) ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Nama lengkap</label>
                                <input class="form-control"
                                       id="name"
                                       name="name"
                                       maxlength="120"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="email">Email aktif</label>
                                <input class="form-control"
                                       type="email"
                                       id="email"
                                       name="email"
                                       maxlength="190"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="topic">Kategori bantuan</label>
                                <select class="form-select" id="topic" name="topic" required>
                                    <option value="">Pilih kategori</option>
                                    <option>Pemesanan Tiket</option>
                                    <option>Pembayaran</option>
                                    <option>Akun</option>
                                    <option>Informasi Destinasi</option>
                                    <option>Keluhan Lainnya</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="subject">Subjek</label>
                                <input class="form-control"
                                       id="subject"
                                       name="subject"
                                       maxlength="200"
                                       required>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="message">Detail pertanyaan atau keluhan</label>
                                <textarea class="form-control"
                                          id="message"
                                          name="message"
                                          rows="5"
                                          maxlength="5000"
                                          required></textarea>
                                <div class="form-text">
                                    Jangan sertakan kata sandi atau informasi rahasia.
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="bi bi-send me-1"></i>Kirim Pesan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="help-footer">
    <div class="container">
        © 2026 Desa Wisata Kabupaten Ciamis. Semua Hak Dilindungi.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('helpSearch');
    const form = document.getElementById('helpSearchForm');
    const items = Array.from(document.querySelectorAll('.faq-item'));
    const empty = document.getElementById('faqEmpty');

    function filterFaq() {
        const keyword = input.value.toLowerCase().trim();
        let visible = 0;

        items.forEach(function (item) {
            const text = (
                item.dataset.search + ' ' + item.innerText
            ).toLowerCase();

            const show = text.includes(keyword);
            item.style.display = show ? '' : 'none';

            if (show) visible++;
        });

        empty.style.display = visible === 0 ? 'block' : 'none';
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        filterFaq();
        document.getElementById('faq-list').scrollIntoView({
            behavior: 'smooth'
        });
    });

    input.addEventListener('input', filterFaq);

    document.querySelectorAll('.help-category').forEach(function (link) {
        link.addEventListener('click', function () {
            const category = link.dataset.category;
            input.value = '';

            items.forEach(function (item) {
                item.style.display =
                    item.dataset.category === category ? '' : 'none';
            });

            empty.style.display = 'none';
        });
    });
});
</script>

</body>
</html>
