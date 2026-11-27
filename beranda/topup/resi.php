<?php
session_start();
require __DIR__ . '/../../koneksi.php';

$invoice = trim($_GET['invoice'] ?? '');
if ($invoice === '') {
    http_response_code(400);
    exit('Nomor invoice tidak diberikan.');
}

$stmt = mysqli_prepare($conn, "SELECT * FROM transactions WHERE invoice_no = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $invoice);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    http_response_code(404);
    exit('Resi pembayaran tidak ditemukan.');
}

$account = json_decode($data['user_game'], true);
if (!is_array($account)) {
    $account = ['Akun Game' => $data['user_game']];
}
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Resi Pembayaran TOPUPIN</title>
<style>
*{box-sizing:border-box}body{font-family:Arial,sans-serif;background:#f2f2f2;padding:30px;color:#222}
.resi{max-width:560px;margin:auto;background:#fff;padding:30px;border-radius:12px;box-shadow:0 2px 10px #ccc}
h2,h3{text-align:center}.row{display:flex;justify-content:space-between;gap:16px;padding:9px 0}
.row span:last-child{text-align:right;overflow-wrap:anywhere}hr{border:0;border-top:1px dashed #aaa;margin:14px 0}
.total{font-size:21px;font-weight:bold}.status{color:green;font-weight:bold}
button,.back{display:block;width:100%;padding:13px;margin-top:12px;border:0;border-radius:7px;text-align:center;text-decoration:none;cursor:pointer;background:#dca52c;color:#111;font-weight:bold}
.back{background:#eee}@media print{body{background:#fff;padding:0}.resi{box-shadow:none}button,.back{display:none}}
</style>
</head>
<body>
<div class="resi">
<h2>TOPUPIN</h2><h3>RESI PEMBAYARAN</h3><hr>
<div class="row"><span>Invoice</span><strong><?=h($data['invoice_no'])?></strong></div>
<div class="row"><span>Tanggal</span><span><?=h($data['created_at'])?></span></div>
<hr>
<div class="row"><span>Nama pelanggan</span><strong><?=h($data['nama_pelanggan'])?></strong></div>
<div class="row"><span>Game</span><span><?=h($data['game'])?></span></div>
<div class="row"><span>Paket</span><span><?=h($data['item_name'])?></span></div>
<?php foreach ($account as $key => $value): ?>
<div class="row"><span><?=h(is_string($key) ? strtoupper($key) : 'Akun Game')?></span><span><?=h($value)?></span></div>
<?php endforeach; ?>
<hr>
<div class="row"><span>Metode pembayaran</span><span><?=h($data['payment_method'])?></span></div>
<?php if (!empty($data['bank'])): ?><div class="row"><span>Bank</span><span><?=h($data['bank'])?></span></div><?php endif; ?>
<?php if (!empty($data['va_number'])): ?><div class="row"><span>Virtual Account</span><span><?=h($data['va_number'])?></span></div><?php endif; ?>
<div class="row"><span>Status</span><span class="status"><?=h($data['status'])?></span></div>
<hr>
<div class="row total"><span>Total</span><span>Rp <?=number_format((float)$data['amount'], 0, ',', '.')?></span></div>
<p style="text-align:center">Terima kasih telah melakukan transaksi di TOPUPIN!</p>
<button onclick="window.print()">CETAK RESI</button>
<a class="back" href="index.xhtml">Kembali ke TOPUPIN</a>
</div>
</body>
</html>
