
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    exit('Koneksi database tidak tersedia.');
}

if (empty($_SESSION['admin_support_csrf'])) {
    $_SESSION['admin_support_csrf'] = bin2hex(random_bytes(32));
}

function adminH($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function adminRedirect($anchor = '') {
    header('Location: pusat-bantuan.php' . $anchor);
    exit;
}

$adminError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['admin_support_csrf'], $token)) {
        $adminError = 'Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.';
    } else {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'save_faq') {
                $id = (int) ($_POST['id'] ?? 0);
                $category = trim($_POST['category'] ?? '');
                $question = trim($_POST['question'] ?? '');
                $answer = trim($_POST['answer'] ?? '');
                $sortOrder = (int) ($_POST['sort_order'] ?? 0);
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                $allowedCategories = [
                    'Pemesanan Tiket',
                    'Pembayaran',
                    'Akun',
                    'Informasi Destinasi',
                    'Keluhan Lainnya'
                ];

                if (
                    !in_array($category, $allowedCategories, true) ||
                    $question === '' ||
                    mb_strlen($question) > 255 ||
                    $answer === '' ||
                    mb_strlen($answer) > 10000
                ) {
                    throw new RuntimeException(
                        'Kategori, pertanyaan, dan jawaban wajib diisi dengan benar.'
                    );
                }

                if ($id > 0) {
                    $stmt = $pdo->prepare("
                        UPDATE support_faqs
                        SET category = :category,
                            question = :question,
                            answer = :answer,
                            sort_order = :sort_order,
                            is_active = :is_active
                        WHERE id = :id
                    ");

                    $stmt->execute([
                        ':category' => $category,
                        ':question' => $question,
                        ':answer' => $answer,
                        ':sort_order' => $sortOrder,
                        ':is_active' => $isActive,
                        ':id' => $id
                    ]);

                    $_SESSION['admin_support_notice'] = 'FAQ berhasil diperbarui.';
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO support_faqs
                            (category, question, answer, sort_order, is_active)
                        VALUES
                            (:category, :question, :answer, :sort_order, :is_active)
                    ");

                    $stmt->execute([
                        ':category' => $category,
                        ':question' => $question,
                        ':answer' => $answer,
                        ':sort_order' => $sortOrder,
                        ':is_active' => $isActive
                    ]);

                    $_SESSION['admin_support_notice'] = 'FAQ berhasil ditambahkan.';
                }

                $_SESSION['admin_support_csrf'] = bin2hex(random_bytes(32));
                adminRedirect('#kelola-faq');
            }

            if ($action === 'delete_faq') {
                $id = (int) ($_POST['id'] ?? 0);

                if ($id < 1) {
                    throw new RuntimeException('FAQ tidak valid.');
                }

                $stmt = $pdo->prepare('DELETE FROM support_faqs WHERE id = :id');
                $stmt->execute([':id' => $id]);

                $_SESSION['admin_support_notice'] = 'FAQ berhasil dihapus.';
                $_SESSION['admin_support_csrf'] = bin2hex(random_bytes(32));
                adminRedirect('#kelola-faq');
            }

            if ($action === 'reply_message') {
                $id = (int) ($_POST['id'] ?? 0);
                $reply = trim($_POST['admin_reply'] ?? '');
                $status = $_POST['status'] ?? 'diproses';

                $allowedStatuses = [
                    'baru',
                    'diproses',
                    'menunggu_pengunjung',
                    'selesai'
                ];

                if ($id < 1 || !in_array($status, $allowedStatuses, true)) {
                    throw new RuntimeException('Data pesan atau status tidak valid.');
                }

                if (mb_strlen($reply) > 10000) {
                    throw new RuntimeException('Balasan terlalu panjang.');
                }

                if ($reply !== '') {
                    $stmt = $pdo->prepare("
                        UPDATE support_messages
                        SET admin_reply = :reply,
                            status = :status,
                            replied_at = NOW()
                        WHERE id = :id
                    ");

                    $stmt->execute([
                        ':reply' => $reply,
                        ':status' => $status,
                        ':id' => $id
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE support_messages
                        SET status = :status
                        WHERE id = :id
                    ");

                    $stmt->execute([
                        ':status' => $status,
                        ':id' => $id
                    ]);
                }

                $_SESSION['admin_support_notice'] =
                    'Status dan balasan pesan berhasil disimpan.';

                $_SESSION['admin_support_csrf'] = bin2hex(random_bytes(32));
                adminRedirect('#pesan-pengunjung');
            }

            throw new RuntimeException('Aksi tidak dikenal.');

        } catch (RuntimeException $e) {
            $adminError = $e->getMessage();
        } catch (PDOException $e) {
            error_log('Admin pusat bantuan: ' . $e->getMessage());
            $adminError = 'Perubahan gagal disimpan. Periksa database dan coba lagi.';
        }
    }
}

$notice = $_SESSION['admin_support_notice'] ?? '';
unset($_SESSION['admin_support_notice']);

$editFaq = null;
if (isset($_GET['edit_faq'])) {
    $stmt = $pdo->prepare('SELECT * FROM support_faqs WHERE id = :id');
    $stmt->execute([':id' => (int) $_GET['edit_faq']]);
    $editFaq = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$faqs = $pdo->query("
    SELECT *
    FROM support_faqs
    ORDER BY sort_order ASC, id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$messages = $pdo->query("
    SELECT *
    FROM support_messages
    ORDER BY
        CASE status
            WHEN 'baru' THEN 1
            WHEN 'diproses' THEN 2
            WHEN 'menunggu_pengunjung' THEN 3
            ELSE 4
        END,
        created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$jumlahBaru = 0;
$jumlahProses = 0;
$jumlahSelesai = 0;

foreach ($messages as $message) {
    if ($message['status'] === 'baru') $jumlahBaru++;
    if ($message['status'] === 'diproses') $jumlahProses++;
    if ($message['status'] === 'selesai') $jumlahSelesai++;
}

$categories = [
    'Pemesanan Tiket',
    'Pembayaran',
    'Akun',
    'Informasi Destinasi',
    'Keluhan Lainnya'
];

$statusLabels = [
    'baru' => 'Baru',
    'diproses' => 'Sedang diproses',
    'menunggu_pengunjung' => 'Menunggu pengunjung',
    'selesai' => 'Selesai'
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Pusat Bantuan - Visit Ciamis</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>
        body {
            background: #f4f7fb;
            color: #253247;
        }

        .admin-header {
            background: #075b43;
            color: white;
            padding: 25px 0;
        }

        .admin-card {
            background: white;
            border: 1px solid #e4eaf0;
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 22px;
        }

        .stat-card {
            border-radius: 12px;
            padding: 18px;
            background: white;
            border: 1px solid #e4eaf0;
            height: 100%;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 750;
        }

        .message-card {
            border: 1px solid #e5eaf0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            background: white;
        }

        .message-text {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .status-badge {
            font-size: 12px;
            padding: 6px 9px;
            border-radius: 20px;
            background: #edf2f7;
        }

        .status-baru {
            background: #fff0c2;
            color: #855b00;
        }

        .status-diproses {
            background: #dcecff;
            color: #174f8c;
        }

        .status-menunggu_pengunjung {
            background: #eee5ff;
            color: #6237a0;
        }

        .status-selesai {
            background: #dcf7e8;
            color: #176a3a;
        }

        .admin-nav {
            position: sticky;
            top: 0;
            z-index: 10;
            background: white;
            border-bottom: 1px solid #e5eaf0;
        }

        .admin-nav a {
            text-decoration: none;
            display: inline-block;
            padding: 14px 12px;
            color: #344054;
            font-weight: 600;
        }

        .admin-nav a:hover {
            color: #087f5b;
        }

        .form-label {
            font-weight: 600;
        }

        .faq-answer {
            white-space: pre-wrap;
        }

        section {
            scroll-margin-top: 80px;
        }
    </style>
</head>
<body>

<header class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1">Pusat Bantuan</h1>
                <p class="mb-0">Kelola FAQ dan pertanyaan pengunjung.</p>
            </div>
            <a href="dashboard.php" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Dashboard
            </a>
        </div>
    </div>
</header>

<nav class="admin-nav">
    <div class="container">
        <a href="#ringkasan"><i class="bi bi-speedometer2 me-1"></i>Ringkasan</a>
        <a href="#pesan-pengunjung"><i class="bi bi-chat-dots me-1"></i>Pesan Pengunjung</a>
        <a href="#kelola-faq"><i class="bi bi-question-circle me-1"></i>Kelola FAQ</a>
    </div>
</nav>

<main class="container py-4">

    <?php if ($notice): ?>
        <div class="alert alert-success"><?= adminH($notice) ?></div>
    <?php endif; ?>

    <?php if ($adminError): ?>
        <div class="alert alert-danger"><?= adminH($adminError) ?></div>
    <?php endif; ?>

    <!-- RINGKASAN -->
    <section id="ringkasan" class="mb-4">
        <h2 class="h4 mb-3">Ringkasan Bantuan</h2>

        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="text-muted small">Total FAQ</div>
                    <div class="stat-number"><?= count($faqs) ?></div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="text-muted small">Pesan Baru</div>
                    <div class="stat-number"><?= $jumlahBaru ?></div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="text-muted small">Sedang Diproses</div>
                    <div class="stat-number"><?= $jumlahProses ?></div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="text-muted small">Selesai</div>
                    <div class="stat-number"><?= $jumlahSelesai ?></div>
                </div>
            </div>
        </div>
    </section>

    <!-- PESAN PENGUNJUNG -->
    <section id="pesan-pengunjung" class="mb-5">
        <h2 class="h4 mb-3">Pesan dan Keluhan Pengunjung</h2>

        <?php if (!$messages): ?>
            <div class="admin-card text-muted">
                Belum ada pesan dari pengunjung.
            </div>
        <?php endif; ?>

        <?php foreach ($messages as $message): ?>
            <article class="message-card">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <div class="fw-bold">
                            <?= adminH($message['subject']) ?>
                        </div>
                        <div class="small text-muted">
                            <?= adminH($message['ticket_code']) ?>
                            ·
                            <?= adminH($message['created_at']) ?>
                        </div>
                    </div>

                    <span class="status-badge status-<?= adminH($message['status']) ?>">
                        <?= adminH($statusLabels[$message['status']] ?? $message['status']) ?>
                    </span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Nama pengunjung</div>
                        <div><?= adminH($message['name']) ?></div>
                    </div>

                    <div class="col-md-6">
                        <div class="small text-muted">Email</div>
                        <div>
                            <a href="mailto:<?= adminH($message['email']) ?>">
                                <?= adminH($message['email']) ?>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="small text-muted">Kategori</div>
                        <div><?= adminH($message['topic']) ?></div>
                    </div>
                </div>

                <div class="small text-muted mb-1">Pesan pengunjung</div>
                <div class="message-text mb-3"><?= adminH($message['message']) ?></div>

                <hr>

                <form method="post" action="pusat-bantuan.php#pesan-pengunjung">
                    <input type="hidden" name="csrf_token"
                           value="<?= adminH($_SESSION['admin_support_csrf']) ?>">
                    <input type="hidden" name="action" value="reply_message">
                    <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label"
                               for="reply<?= (int) $message['id'] ?>">
                            Balasan admin
                        </label>
                        <textarea class="form-control"
                                  id="reply<?= (int) $message['id'] ?>"
                                  name="admin_reply"
                                  rows="3"
                                  maxlength="10000"
                                  placeholder="Tulis balasan untuk pengunjung..."><?= adminH($message['admin_reply'] ?? '') ?></textarea>
                        <div class="form-text">
                            Balasan disimpan di dashboard. Email balasan tidak dikirim otomatis.
                        </div>
                    </div>

                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label"
                                   for="status<?= (int) $message['id'] ?>">
                                Status penanganan
                            </label>
                            <select class="form-select"
                                    id="status<?= (int) $message['id'] ?>"
                                    name="status">
                                <?php foreach ($statusLabels as $value => $label): ?>
                                    <option value="<?= adminH($value) ?>"
                                        <?= $message['status'] === $value ? 'selected' : '' ?>>
                                        <?= adminH($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 text-md-end">
                            <button class="btn btn-success" type="submit">
                                <i class="bi bi-save me-1"></i>Simpan Penanganan
                            </button>
                        </div>
                    </div>
                </form>
            </article>
        <?php endforeach; ?>
    </section>

    <!-- FORM FAQ -->
    <section id="kelola-faq" class="mb-5">
        <h2 class="h4 mb-3">Kelola FAQ</h2>

        <div class="admin-card">
            <h3 class="h5 mb-3">
                <?= $editFaq ? 'Edit FAQ' : 'Tambah FAQ Baru' ?>
            </h3>

            <form method="post" action="pusat-bantuan.php#kelola-faq">
                <input type="hidden" name="csrf_token"
                       value="<?= adminH($_SESSION['admin_support_csrf']) ?>">
                <input type="hidden" name="action" value="save_faq">
                <input type="hidden" name="id"
                       value="<?= (int) ($editFaq['id'] ?? 0) ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="category">Kategori</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Pilih kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= adminH($category) ?>"
                                    <?= ($editFaq['category'] ?? '') === $category ? 'selected' : '' ?>>
                                    <?= adminH($category) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="sort_order">Urutan tampilan</label>
                        <input class="form-control"
                               type="number"
                               id="sort_order"
                               name="sort_order"
                               value="<?= (int) ($editFaq['sort_order'] ?? 0) ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="question">Pertanyaan</label>
                        <input class="form-control"
                               id="question"
                               name="question"
                               maxlength="255"
                               value="<?= adminH($editFaq['question'] ?? '') ?>"
                               required>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="answer">Jawaban</label>
                        <textarea class="form-control"
                                  id="answer"
                                  name="answer"
                                  rows="5"
                                  maxlength="10000"
                                  required><?= adminH($editFaq['answer'] ?? '') ?></textarea>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   <?= !isset($editFaq['is_active']) || (int) $editFaq['is_active'] === 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">
                                Tampilkan FAQ di halaman pengunjung
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i>
                            <?= $editFaq ? 'Simpan Perubahan' : 'Tambah FAQ' ?>
                        </button>

                        <?php if ($editFaq): ?>
                            <a href="pusat-bantuan.php#kelola-faq"
                               class="btn btn-outline-secondary">
                                Batal Edit
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <div class="admin-card">
            <h3 class="h5 mb-3">Daftar FAQ</h3>

            <?php if (!$faqs): ?>
                <p class="text-muted mb-0">Belum ada FAQ.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Pertanyaan</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($faqs as $faq): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            <?= adminH($faq['question']) ?>
                                        </div>
                                        <div class="small text-muted faq-answer">
                                            <?= adminH($faq['answer']) ?>
                                        </div>
                                    </td>
                                    <td><?= adminH($faq['category']) ?></td>
                                    <td>
                                        <?php if ((int) $faq['is_active'] === 1): ?>
                                            <span class="badge text-bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">Disembunyikan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a class="btn btn-sm btn-outline-primary"
                                               href="pusat-bantuan.php?edit_faq=<?= (int) $faq['id'] ?>#kelola-faq">
                                                Edit
                                            </a>

                                            <form method="post"
                                                  onsubmit="return confirm('Yakin ingin menghapus FAQ ini?')">
                                                <input type="hidden"
                                                       name="csrf_token"
                                                       value="<?= adminH($_SESSION['admin_support_csrf']) ?>">
                                                <input type="hidden"
                                                       name="action"
                                                       value="delete_faq">
                                                <input type="hidden"
                                                       name="id"
                                                       value="<?= (int) $faq['id'] ?>">
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
