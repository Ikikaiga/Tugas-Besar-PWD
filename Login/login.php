<?php
session_start();
require '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        header("Location: Login.xhtml?error=empty");
        exit;
    }

    $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM akun WHERE username = ? OR email = ?");
    if (!$stmt) {
        die("Query error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ss", $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $isMatch = ($password === $row['password']) || (password_verify($password, $row['password']));

        if ($isMatch) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];

            $remember = !empty($_POST['remember']);
            $expire = $remember ? (time() + (3600 * 24 * 30)) : (time() + (3600 * 24));
            setcookie('user_name', $row['username'], $expire, '/');
            setcookie('user_id', (string)$row['id'], $expire, '/');

            header("Location: ../index.xhtml");
            exit;
        } else {
            header("Location: Login.xhtml?error=wrong_password");
            exit;
        }
    } else {
        header("Location: Login.xhtml?error=user_not_found");
        exit;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    header("Location: Login.xhtml");
    exit;
}
?>
