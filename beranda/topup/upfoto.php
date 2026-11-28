<?php
session_start();
require '../../koneksi.php';

header('Content-Type: application/json');

const FOTO_MAKS_BYTE = 2097152; // 2 MB

function keluar($sukses, $pesan, $extra = [])
{
    echo json_encode(array_merge(
        ['success' => $sukses, 'message' => $pesan],
        $extra
    ));
    exit;
}

function hapusFile($dir, $nama)
{
    // Hanya hapus nama file "aman" (tanpa slash) agar tidak keluar dari folder
    if ($nama && preg_match('/^[A-Za-z0-9_.-]+$/', $nama) && is_file($dir . $nama)) {
        @unlink($dir . $nama);
    }
}

function simpanFoto($conn, $id, $nama)
{
    $up = mysqli_prepare(
        $conn,
        "UPDATE akun
         SET foto = ?
         WHERE id = ?"
    );

    if (!$up) {
        return false;
    }

    mysqli_stmt_bind_param($up, "si", $nama, $id);
    $ok = mysqli_stmt_execute($up);
    mysqli_stmt_close($up);

    return $ok;
}

if (!isset($_SESSION['user_id'])) {
    keluar(false, 'Belum login');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    keluar(false, 'Metode tidak valid');
}

$id = (int) $_SESSION['user_id'];
$aksi = $_POST['aksi'] ?? 'upload';

$dir = __DIR__ . '/uploads/avatar/';

if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
    keluar(false, 'Folder penyimpanan foto tidak bisa dibuat');
}

// Ambil nama foto lama (untuk dihapus setelah foto baru tersimpan)
$stmt = mysqli_prepare(
    $conn,
    "SELECT foto
     FROM akun
     WHERE id = ?"
);

if (!$stmt) {
    keluar(false, 'Query error: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    keluar(false, 'User tidak ditemukan');
}

$fotoLama = $user['foto'];

/* ===== HAPUS FOTO ===== */

if ($aksi === 'hapus') {

    if (!simpanFoto($conn, $id, null)) {
        keluar(false, 'Gagal menghapus foto');
    }

    hapusFile($dir, $fotoLama);

    keluar(true, 'Foto profil dihapus', ['foto' => null]);
}

/* ===== UPLOAD FOTO ===== */

if (!isset($_FILES['foto'])) {
    keluar(false, 'Tidak ada file yang dikirim');
}

$file = $_FILES['foto'];

if ($file['error'] !== UPLOAD_ERR_OK) {

    if (
        $file['error'] === UPLOAD_ERR_INI_SIZE ||
        $file['error'] === UPLOAD_ERR_FORM_SIZE
    ) {
        keluar(false, 'Ukuran foto maksimal 2 MB');
    }

    keluar(false, 'Upload gagal (kode ' . $file['error'] . ')');
}

if ($file['size'] > FOTO_MAKS_BYTE) {
    keluar(false, 'Ukuran foto maksimal 2 MB');
}

if (!is_uploaded_file($file['tmp_name'])) {
    keluar(false, 'File tidak valid');
}

// Cek isi file benar-benar gambar (bukan cuma ekstensinya)
$info = @getimagesize($file['tmp_name']);
$tipe = $info ? $info['mime'] : '';

$ekstensi = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif'
];

if (!isset($ekstensi[$tipe])) {
    keluar(false, 'Format foto harus JPG, PNG, WEBP, atau GIF');
}

// Nama acak: aman dari path traversal & tidak kena cache browser
$namaBaru = 'u' . $id . '_' . bin2hex(random_bytes(8)) . '.' . $ekstensi[$tipe];

if (!move_uploaded_file($file['tmp_name'], $dir . $namaBaru)) {
    keluar(false, 'Gagal menyimpan file foto');
}

if (!simpanFoto($conn, $id, $namaBaru)) {
    @unlink($dir . $namaBaru);
    keluar(false, 'Gagal menyimpan foto ke database');
}

hapusFile($dir, $fotoLama);

mysqli_close($conn);

keluar(true, 'Foto profil diperbarui', ['foto' => $namaBaru]);
