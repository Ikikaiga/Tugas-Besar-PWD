<?php
session_start();
require '../../koneksi.php';

if (!isset($_SESSION['user_id'])) {
    die('Belum login');
}

$id = $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT username, email, bio, game_favorit, password
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

/*
 * Ada dua form yang mengirim ke file ini:
 *   - "Profil Gamer"    -> hanya mengirim bio & game_favorit
 *   - "Akun & Keamanan" -> mengirim username, email, password_baru, current_password
 *
 * Jadi mulai dari data yang sudah tersimpan, lalu hanya ganti field
 * yang memang dikirim oleh form tersebut. Dengan begini menyimpan satu
 * form tidak menghapus isi form yang lain.
 */
$username = $user['username'];
$email = $user['email'];
$bio = $user['bio'];
$gameFavorit = $user['game_favorit'];
$passwordFinal = $user['password'];

// ===== Form "Profil Gamer" =====
if (isset($_POST['bio']) || isset($_POST['game_favorit'])) {

    $bio = trim($_POST['bio'] ?? '');
    $gameFavorit = trim($_POST['game_favorit'] ?? '');

}

// ===== Form "Akun & Keamanan" =====
if (isset($_POST['username'])) {

    $username = trim($_POST['username']);
    $email = trim($_POST['email'] ?? '');

    $passwordBaru = $_POST['password_baru'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';

    if ($username === '') {
        die('Username tidak boleh kosong');
    }

    if (
        $email !== '' &&
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        die('Format email tidak valid');
    }

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

try {

    mysqli_stmt_execute($update);

} catch (mysqli_sql_exception $e) {

    // 1062 = Duplicate entry (username sudah dipakai akun lain)
    if ((int) $e->getCode() === 1062) {
        die('Username sudah dipakai, silakan pilih username lain');
    }

    die('Gagal menyimpan perubahan');

}

$_SESSION['username'] = $username;

setcookie(
    'user_name',
    $username,
    time() + (86400 * 30),
    '/'
);

mysqli_stmt_close($stmt);
mysqli_stmt_close($update);
mysqli_close($conn);

header('Location: profile.xhtml?success=1');
exit;
?>