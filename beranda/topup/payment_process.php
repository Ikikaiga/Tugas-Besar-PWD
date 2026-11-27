<?php
session_start();
require __DIR__ . '/configResiPhp/payment_db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    empty($_SESSION['payment_order']) ||
    !hash_equals($_SESSION['payment_csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(400);
    exit('Permintaan tidak valid atau sesi pembayaran sudah berakhir.');
}

$order = $_SESSION['payment_order'];
$method = $_POST['method'] ?? '';
$bank = $_POST['bank'] ?? null;

if (!in_array($method, ['QRIS', 'TRANSFER_BANK'], true)) {
    exit('Metode pembayaran tidak valid.');
}
if ($method === 'TRANSFER_BANK' && !in_array($bank, ['BCA', 'BRI', 'MANDIRI'], true)) {
    exit('Bank tidak valid.');
}
if ($method === 'QRIS') {
    $bank = null;
}

$prefix = ['BCA' => '39358', 'BRI' => '88810', 'MANDIRI' => '88608'];
$va = null;
if ($bank !== null) {
    $digits = substr(
        preg_replace('/\D/', '', (string) crc32($order['invoice']) . (string) crc32(strrev($order['invoice']))),
        0, 10
    );
    $va = $prefix[$bank] . $digits;
}

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
$nama = $_SESSION['username'] ?? 'Guest';
$userGame = json_encode($order['account'], JSON_UNESCAPED_UNICODE);

try {
    $db = payment_db();
    // Kolom disesuaikan dengan tabel transactions pada db_users.sql.
    $stmt = $db->prepare(
        "INSERT INTO transactions
        (invoice_no, user_id, nama_pelanggan, game, item_name, user_game,
         amount, payment_method, bank, va_number, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PAID')"
    );
    $stmt->bind_param(
        'sissssdsss',
        $order['invoice'], $userId, $nama, $order['game'], $order['item'],
        $userGame, $order['amount'], $method, $bank, $va
    );
    $stmt->execute();
    $invoice = $order['invoice'];
    $stmt->close();

    unset($_SESSION['payment_order'], $_SESSION['payment_csrf']);
    header('Location: resi.php?invoice=' . urlencode($invoice));
    exit;
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit('Gagal menyimpan transaksi ke database. Periksa struktur tabel transactions. Detail: ' .
         htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
