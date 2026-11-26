<?php
session_start();
require '../../koneksi.php';

if (!isset($_SESSION['user_id'])) {
    die('Belum login');
}

$id = $_SESSION['user_id'];
$passwordInput = trim($_POST['password'] ?? '');

if ($passwordInput === '') {
    die('Password wajib diisi');
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT password
     FROM akun
     WHERE id=?"
);

if (!$stmt) {
    die('Query error: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die('User tidak ditemukan');
}

$isMatch =
    ($passwordInput === $user['password']) ||
    password_verify(
        $passwordInput,
        $user['password']
    );

if (!$isMatch) {
    die('Password salah');
}

$delete = mysqli_prepare(
    $conn,
    "DELETE FROM akun
     WHERE id=?"
);

if (!$delete) {
    die('Query error: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($delete, "i", $id);

if (!mysqli_stmt_execute($delete)) {
    die('Gagal menghapus akun');
}

session_unset();
session_destroy();

if (isset($_COOKIE['user_name'])) {
    setcookie(
        'user_name',
        '',
        time() - 3600,
        '/'
    );
}

if (isset($_COOKIE['user_id'])) {
    setcookie(
        'user_id',
        '',
        time() - 3600,
        '/'
    );
}

header(
    'Location: ../../Login/login/Login.xhtml?msg=account_deleted'
);

exit;
?>