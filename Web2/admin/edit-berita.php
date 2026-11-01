
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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: berita.php');
    exit;
}

$stmt = $conn->prepare(
    "SELECT judul, ringkasan, isi, gambar, tanggal
     FROM berita WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$berita = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$berita) {
    http_response_code(404);
    exit('Berita tidak ditemukan.');
}

$judul = $berita['judul'];
$ringkasan = $berita['ringkasan'];
$isi = $berita['isi'];
$gambar = $berita['gambar'] ?? '';
$tanggal = $berita['tanggal'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $ringkasan = trim($_POST['ringkasan'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $gambar = trim($_POST['gambar'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';

    $tanggalValid