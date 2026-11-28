<?php
session_start();
include "koneksi.php";

// Memastikan skrip hanya dapat diakses melalui pengiriman formulir POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Akses tidak valid");
}

// Mengambil dan memvalidasi data pesanan serta metode pembayaran dari input POST.
$invoice = "INV" . date("YmdHis") .
           rand(100, 999);

$nama = trim($_POST['nama'] ?? '');
$game = trim($_POST['game'] ?? '');
$item = trim($_POST['item'] ?? '');
$user_game = trim($_POST['user_game'] ?? '');

$amount = filter_var(
    $_POST['amount'] ?? null,
    FILTER_VALIDATE_FLOAT
);

$method = $_POST['payment_method'] ?? '';
$bank = $_POST['bank'] ?? null;
$va = $_POST['va_number'] ?? null;

if (
    $nama === '' ||
    $game === '' ||
    $item === '' ||
    $user_game === '' ||
    $amount === false ||
    $amount <= 0 ||
    !in_array($method, ['QRIS', 'TRANSFER_BANK'], true)
) {
    exit("Data transaksi tidak valid");
}

// Memvalidasi pilihan bank jika metode transfer dipilih atau mengosongkan data bank jika menggunakan QRIS.
if ($method === 'TRANSFER_BANK') {
    if (!in_array($bank, ['BCA', 'BRI', 'MANDIRI'], true)) {
        exit("Bank tidak valid");
    }
} else {
    $bank = null;
    $va = null;
}

$user_id = $_SESSION['user_id'] ?? null;

// Menyimpan data transaksi yang sudah tervalidasi ke tabel transactions lalu mengarahkan ke halaman resi.
$stmt = mysqli_prepare($koneksi, "
    INSERT INTO transactions
    (
        invoice_no,
        user_id,
        nama_pelanggan,
        game,
        item_name,
        user_game,
        amount,
        payment_method,
        bank,
        va_number,
        status
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PAID')
");

mysqli_stmt_bind_param(
    $stmt,
    "sissssdsss",
    $invoice,
    $user_id,
    $nama,
    $game,
    $item,
    $user_game,
    $amount,
    $method,
    $bank,
    $va
);

if (mysqli_stmt_execute($stmt)) {
    header(
        "Location: resi.php?invoice=" .
        urlencode($invoice)
    );
    exit;
}

echo "Gagal menyimpan transaksi: " .
     mysqli_error($koneksi);
?>
