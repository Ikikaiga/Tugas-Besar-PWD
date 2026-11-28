<?php
session_start();
require '../koneksi.php';

// Memproses permintaan pendaftaran akun baru jika dikirim melalui metode POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Memvalidasi kelengkapan input, panjang minimal karakter, serta kecocokan konfirmasi kata sandi.
    if ($username === '' || $password === '' || $confirm === '') {
        header("Location: register.xhtml?error=empty");
        exit;
    }

    if (strlen($username) < 3) {
        header("Location: register.xhtml?error=short_username");
        exit;
    }

    if (strlen($password) < 6) {
        header("Location: register.xhtml?error=short_password");
        exit;
    }

    if ($password !== $confirm) {
        header("Location: register.xhtml?error=password_mismatch");
        exit;
    }

    // Memeriksa apakah username sudah digunakan oleh akun lain di dalam database.
    $checkStmt = mysqli_prepare($conn, "SELECT id FROM akun WHERE username = ?");
    if (!$checkStmt) {
        die("Query error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($checkStmt, "s", $username);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);

    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        mysqli_stmt_close($checkStmt);
        mysqli_close($conn);
        header("Location: register.xhtml?error=user_exists");
        exit;
    }
    mysqli_stmt_close($checkStmt);

    // Mengenkripsi kata sandi dan menyimpan data akun baru ke dalam tabel akun.
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insertStmt = mysqli_prepare($conn, "INSERT INTO akun (username, password) VALUES (?, ?)");
    if (!$insertStmt) {
        die("Query error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($insertStmt, "ss", $username, $hashedPassword);

    if (mysqli_stmt_execute($insertStmt)) {
        mysqli_stmt_close($insertStmt);
        mysqli_close($conn);
        header("Location: register.xhtml?msg=registered");
        exit;
    } else {
        $err = mysqli_error($conn);
        mysqli_stmt_close($insertStmt);
        mysqli_close($conn);
        die("Gagal mendaftar: " . $err);
    }
} else {
    header("Location: register.xhtml");
    exit;
}
?>
