<?php
session_start();
require __DIR__ . '/configResiPhp/payment_db.php';
date_default_timezone_set('Asia/Jakarta');
if (empty($_SESSION['user_id'])) { header('Location: ../../Login/Login.xhtml'); exit; }

// Harga dan paket harus sama dengan games.js. Harga ditentukan ulang di server.
$catalog = [
 'mlbb'=>['name'=>'Mobile Legends','packs'=>[['86 Diamond',22000],['172 Diamond',44000],['257 Diamond',65000],['344 Diamond',87000],['514 Diamond',130000],['706 Diamond',175000]],'fields'=>['uid'=>'User ID','zone'=>'Zone ID']],
 'valorant'=>['name'=>'Valorant','packs'=>[['475 VP',49000],['1000 VP',100000],['2050 VP',200000],['3650 VP',350000],['5350 VP',500000]],'fields'=>['riot'=>'Riot ID']],
 'steam'=>['name'=>'Steam Wallet','packs'=>[['Rp 12.000',14000],['Rp 45.000',50000],['Rp 60.000',66000],['Rp 90.000',98000],['Rp 120.000',130000],['Rp 250.000',265000]],'fields'=>['steam'=>'Steam ID / Email']],
 'roblox'=>['name'=>'Roblox','packs'=>[['80 Robux',15000],['400 Robux',75000],['800 Robux',150000],['1.700 Robux',300000],['4.500 Robux',750000]],'fields'=>['user'=>'Username Roblox']],
 'warframe'=>['name'=>'Warframe','packs'=>[['75 Platinum',15000],['170 Platinum',33000],['370 Platinum',68000],['1.000 Platinum',170000],['2.100 Platinum',340000]],'fields'=>['ign'=>'Nama in-game']]
];
$gameKey=$_GET['game']??''; $packIndex=filter_input(INPUT_GET,'pack',FILTER_VALIDATE_INT);
if (!is_string($gameKey) || !isset($catalog[$gameKey]) || $packIndex===false || $packIndex===null || !isset($catalog[$gameKey]['packs'][$packIndex])) {
 http_response_code(400); exit('Data pesanan tidak valid. Silakan ulangi pemesanan.');
}
$game=$catalog[$gameKey]; [$item,$amount]=$game['packs'][$packIndex];
$account=[];
$patterns=['uid'=>'/^[0-9]{5,12}$/D','zone'=>'/^[0-9]{3,6}$/D','riot'=>'/^.{3,16}#.{3,5}$/uD','steam'=>'/^.{3,60}$/uD','user'=>'/^[A-Za-z0-9_]{3,20}$/D','ign'=>'/^[A-Za-z0-9_-]{3,24}$/D'];
foreach($game['fields'] as $key=>$label) {
 $raw=$_GET['acc_'.$key]??'';
 $v=is_string($raw)?trim($raw):'';
 if(!preg_match($patterns[$key], $v)) { http_response_code(400); exit('Data '.$label.' tidak valid.'); }
 $account[$key]=$v;
}
$invoice='INV-'.date('Ymd-His').'-'.strtoupper(bin2hex(random_bytes(6)));
unset($_SESSION['payment_va']);
$_SESSION['payment_order']=['invoice'=>$invoice,'game_key'=>$gameKey,'game'=>$game['name'],'item'=>$item,'amount'=>$amount,'account'=>$account];
$_SESSION['payment_csrf']=bin2hex(random_bytes(24));
$banks=['BCA','BRI','MANDIRI'];
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pembayaran TOPUPIN</title>
<link rel="stylesheet" href="assets/css/style.css"><link rel="stylesheet" href="assets/css/pembayaran.css"><link rel="stylesheet" href="assets/css/payment_extra.css">
</head><body>
<div class="bg" aria-hidden="true"><i></i><i></i><i></i></div>
<header class="top"><div class="wrap bar0"><a class="logo" href="index.xhtml"><span class="logo-box">G</span>TOPUPIN</a><div class="user-nav"><span class="user-badge">Halo, <?=h($_SESSION['username'] ?? 'Pengguna')?></span><a class="btn sm" href="../../Login/logout.php">Keluar</a></div></div></header>
<main class="wrap narrow"><section class="card pad">
<h1 class="h2">Pembayaran</h1><p>Periksa detail pesanan, lalu pilih metode pembayaran. Ini simulasi; jangan melakukan transfer uang sungguhan.</p>
<dl class="dl">
<dt>Invoice</dt><dd><?=h($invoice)?></dd><dt>Game</dt><dd><?=h($game['name'])?></dd><dt>Nominal</dt><dd><?=h($item)?></dd>
<?php foreach($account as $k=>$v): ?><dt><?=h($game['fields'][$k])?></dt><dd><?=h($v)?></dd><?php endforeach; ?>
</dl><h2>Total pembayaran</h2><h2><?=rupiah($amount)?></h2>
<form method="post" action="payment_process.php" id="paymentForm">
<input type="hidden" name="csrf" value="<?=h($_SESSION['payment_csrf'])?>">
<div class="packs pembayaran payment-methods">
<label class="opt payment-method">
  <input type="radio" name="method" value="TRANSFER_BANK" required>
  <span><b>Transfer Bank</b><span class="logoBayar">
    <img src="assets/img/BCA.jpg" alt="BCA"/>
    <img src="assets/img/BRI.jpg" alt="BRI"/>
    <img src="assets/img/mandiri.jpg" alt="Mandiri"/>
  </span></span>
</label>
<label class="opt payment-method">
  <input type="radio" name="method" value="QRIS" required>
  <span><b>QRIS</b><span class="logoBayar">
    <img src="assets/img/QRIS.jpg" alt="QRIS"/>
  </span></span>
</label>
</div>
<div id="qrisPanel" class="payment-panel" hidden>
  <h2>Scan QRIS</h2><p>Kode QR untuk simulasi saja. Tidak perlu mengirim uang.</p>
  <img class="payment-qr" src="assets/img/QR_Bayar.png" alt="Kode QR pembayaran QRIS"/>
  <p><b><?=rupiah($amount)?></b></p>
</div>
<div id="bankPanel" class="payment-panel" hidden>
  <h2>Pilih Bank Tujuan</h2><div class="bank-options">
  <?php foreach($banks as $bank): ?>
    <label class="bank-choice">
      <input type="radio" name="bank" value="<?=h($bank)?>" />
      <img src="assets/img/<?= $bank === 'MANDIRI' ? 'mandiri' : h($bank) ?>.jpg" alt="<?=h($bank)?>"/>
      <span><?= $bank === 'MANDIRI' ? 'Bank Mandiri' : 'Bank '.h($bank) ?></span>
    </label>
  <?php endforeach; ?>
  </div>
  <div id="vaPanel" hidden><p>Nomor Virtual Account (Simulasi)</p><strong id="vaNumber"></strong> <button type="button" id="copyVa" class="btn sm">Salin</button><p>Total: <b><?=rupiah($amount)?></b></p><p id="copyStatus" role="status"></p></div>
</div>
<p class="err" id="errorMsg"></p><button class="btn gold" type="submit" id="confirmPayment">Konfirmasi Pembayaran</button>
<a class="btn" href="index.xhtml">Batal</a></form></section></main>
<script src="assets/js/payment_flow.js"></script></body></html>
