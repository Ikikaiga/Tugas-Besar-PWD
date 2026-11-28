<?php
session_start();
require '../../koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Belum login'
    ]);
    exit;
}

$id = $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT username, email, bio, game_favorit, foto
     FROM akun
     WHERE id = ?"
);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => mysqli_error($conn)
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user) {

    echo json_encode([
        'success' => true,
        'data' => $user
    ]);

} else {

    echo json_encode([
        'success' => false,
        'message' => 'User tidak ditemukan'
    ]);

}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>