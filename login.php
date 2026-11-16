<?php
session_start();

$user_email = "admin@nightmarket.com";
$user_pass  = "123456";

$email    = isset($_POST['email']) ? trim($_POST['email']) : "";
$password = isset($_POST['password']) ? $_POST['password'] : "";

if ($email === "" || $password === "") {
    $pesan = "Email dan password harus diisi.";
    $ok = false;
} elseif ($email === $user_email && $password === $user_pass) {
    $_SESSION['email'] = $email;
    $pesan = "Login berhasil. Selamat datang, " . htmlspecialchars($email) . "!";
    $ok = true;
} else {
    $pesan = "Email atau password salah.";
    $ok = false;
}
?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="id" lang="id">
<head>
  <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
  <title>Nightmarket - Hasil Login</title>
  <link rel="stylesheet" type="text/css" href="login.css" />
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&amp;family=Rajdhani:wght@500;600;700&amp;display=swap" />
</head>
<body>
  <h1 class="brand">NIGHTMARKET</h1>
  <div class="card">
    <h2><?php echo $ok ? "Berhasil" : "Gagal"; ?></h2>
    <p class="msg"><?php echo $pesan; ?></p>
    <p class="reg"><a href="index.xhtml">Kembali ke login</a></p>
  </div>
</body>
</html>
