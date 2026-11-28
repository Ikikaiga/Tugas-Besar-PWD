<?php
// Membuka koneksi ke database MySQL db_users dan menghentikan program jika koneksi gagal.
$conn = mysqli_connect('localhost', 'root', '', 'db_users');

if(!$conn) {
    die('Koneksi Gagal: ' . mysqli_connect_error());
}
?>
