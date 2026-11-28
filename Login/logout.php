<?php
// Menghapus seluruh data sesi dan cookie pengguna yang aktif lalu mengarahkan kembali ke halaman login.
session_start();
session_unset();
session_destroy();

if (isset($_COOKIE['user_name'])) {
    setcookie('user_name', '', time() - 3600, '/');
}
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/');
}

header("Location: Login.xhtml?msg=logged_out");
exit;
?>
