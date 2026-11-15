<?php
    session_start();
    require 'koneksi.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            echo '<p style="color: red;">!! Harap lengkapi semua data. !!</p>';
            echo '<a href="Login.xhtml">Kembali</a>';
            exit;
        }

        $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM akun WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if ($password === $row['password']) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];

                echo "<h2>Login Berhasil</h2>";
                echo "<p>Selamat datang, " . htmlspecialchars($row['username']) . "!</p>";
                echo '<a href="index.xhtml">Masuk ke Halaman Utama</a>';
                exit;
            } else {
                echo "<p style='color:red;'>!! Password yang Anda masukkan salah. !!</p>";
                echo '<a href="Login.xhtml">Kembali ke Halaman Login</a>';
            }
        } else {
            echo "<p style='color:red;'>!! Username atau Email tidak terdaftar. !!</p>";
            echo '<a href="Login.xhtml">Kembali ke Halaman Login</a>';
        }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    } else {
        echo "<p>Halaman tidak valid.</p>";
        echo '<a href="Login.xhtml">Kembali ke Login</a>';
        exit;
    }
?>

