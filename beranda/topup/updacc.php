<?php
session_start();
require '../../koneksi.php';

if (!isset($_SESSION['user_id'])) {
    die('Belum login');
}

$id = $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT password
     FROM akun
     WHERE id = ?"
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

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$gameFavorit = trim($_POST['game_favorit'] ?? '');

$passwordBaru = $_POST['password_baru'] ?? '';
$currentPassword = $_POST['current_password'] ?? '';

if (
    $email !== '' &&
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    die('Format email tidak valid');
}

$passwordFinal = $user['password'];

if ($passwordBaru !== '') {

    $isMatch =
        ($currentPassword === $user['password']) ||
        password_verify(
            $currentPassword,
            $user['password']
        );

    if (!$isMatch) {
        die('Password saat ini salah');
    }

    $passwordFinal = password_hash(
        $passwordBaru,
        PASSWORD_DEFAULT
    );
}

$update = mysqli_prepare(
    $conn,
    "UPDATE akun
     SET username = ?,
         email = ?,
         bio = ?,
         game_favorit = ?,
         password = ?
     WHERE id = ?"
);

if (!$update) {
    die('Query error: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $update,
    "sssssi",
    $username,
    $email,
    $bio,
    $gameFavorit,
    $passwordFinal,
    $id
);

if (mysqli_stmt_execute($update)) {

    $_SESSION['username'] = $username;

    setcookie(
        'user_name',
        $username,
        time() + (86400 * 30),
        '/'
    );

    header('Location: profile.xhtml?success=1');
    exit;

} else {

    die('Gagal menyimpan perubahan');

}

mysqli_stmt_close($stmt);
mysqli_stmt_close($update);
mysqli_close($conn);
?>