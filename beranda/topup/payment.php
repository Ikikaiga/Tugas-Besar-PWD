<?php
session_start();
require __DIR__ . '/config/payment_db.php';

// Harga dan paket harus sama dengan games.js. Harga ditentukan ulang di server.
$catalog = [
 'mlbb'=>['name'=>'Mobile Legends','packs'=>[['86 Diamond',22000],['172 Diamond',44000],['257 Diamond',65000],['344 Diamond',87000],['514 Diamond',130000],['706 Diamond',175000]],'fields'=>['uid'=>'User ID','zone'=>'Zone ID']],
 'valorant'=>['name'=>'Valorant','packs'=>[['475 VP',49000],['1000 VP',100000],['2050 VP',200000],['3650 VP',350000],['5350 VP',500000]],'fields'=>['riot'=>'Riot ID']],
 'steam'=>['name'=>'Steam Wallet','packs'=>[['Rp 12.000',14000],['Rp 45.000',50000],['Rp 60.000',66000],['Rp 90.000',98000],['Rp 120.000',130000],['Rp 250.000',265000]],'fields'=>['steam'=>'Steam ID / Email']],
 'roblox'=>['name'=>'Roblox','packs'=>[['80 Robux',15000],['400 Robux',75000],['800 Robux',150000],['1.700 Robux',300000],['4.500 Robux',750000]],'fields'=>['user'=>'Username Roblox']],
 'warframe'=>['name'=>'Warframe','packs'=>[['75 Platinum',15000],['170 Platinum',33000],['370 Platinum',68000],['1.000 Platinum',170000],['2.100 Platinum',340000]],'fields'=>['ign'=>'Nama in-game']]
];
$gameKey=$_GET['game']??''; $packIndex=filter_input(INPUT_GET,'pack',FILTER_VALIDATE_INT);
if (!isset($catalog[$gameKey]) || $packIndex===false || $packIndex===null || !isset($catalog[$gameKey]['packs'][$packIndex])) {
 http_response_code(400); exit('Data pesanan tidak valid. Silakan ulangi pemesanan.');
}
$game=$catalog[$gameKey]; [$item,$amount]=$game['packs'][$packIndex];
$account=[];
foreach($game['fields'] as $key=>$label) {
 $v=trim($_GET['acc_'.$key]??'');
 if($v==='') { http_response_code(400); exit('Data '.$label.' wajib diisi.'); }
 $account[$key]=$v;
}
$invoice='TPN'.date('ymd').strtoupper(bin2hex(random_bytes(3)));
$_SESSION['payment_order']=['invoice'=>$invoice,'game_key'=>$gameKey,'game'=>$game['name'],'item'=>$item,'amount'=>$amount,'account'=>$account];
$_SESSION['payment_csrf']=bin2hex(random_bytes(24));
$banks=['BCA'=>'39358','BRI'=>'88810','MANDIRI'=>'88608'];
$digits=substr(preg_replace('/\D/','',(string)crc32($invoice).(string)crc32(strrev($invoice))),0,10);
$va=[];
foreach($banks as $b=>$prefix) $va[$b]=$prefix.$digits;
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pembayaran TOPUPIN</title>
<link rel="stylesheet" href="assets/css/style.css"><link rel="stylesheet" href="assets/css/pembayaran.css"><link rel="stylesheet" href="assets/css/payment-extra.css">
</head><body>
<div class="bg" aria-hidden="true"><i></i><i></i><i></i></div>
<header class="top"><div class="wrap bar0"><a class="logo" href="index.xhtml"><span class="logo-box">G</span>TOPUPIN</a></div></header>
<main class="wrap narrow"><section class="card pad">
<h1 class="h2">Pembayaran</h1><p>Periksa detail pesanan, lalu pilih metode pembayaran.</p>
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
  <h2>Scan QRIS</h2><p>Scan kode QR berikut menggunakan aplikasi pembayaran yang mendukung QRIS.</p>
  <img class="payment-qr" src="assets/img/QR_Bayar.png" alt="Kode QR pembayaran QRIS"/>
  <p><b><?=rupiah($amount)?></b></p>
</div>
<div id="bankPanel" class="payment-panel" hidden>
  <h2>Pilih Bank Tujuan</h2><div class="bank-options">
  <?php foreach($va as $bank=>$number): ?>
    <label class="bank-choice">
      <input type="radio" name="bank" value="<?=h($bank)?>" data-va="<?=h($number)?>" />
      <img src="assets/img/<?= $bank === 'MANDIRI' ? 'mandiri' : h($bank) ?>.jpg" alt="<?=h($bank)?>"/>
      <span><?= $bank === 'MANDIRI' ? 'Bank Mandiri' : 'Bank '.h($bank) ?></span>
    </label>
  <?php endforeach; ?>
  </div>
  <div id="vaPanel" hidden><p>Nomor Virtual Account:</p><strong id="vaNumber"></strong> <button type="button" id="copyVa">Salin</button><p>Total: <?=rupiah($amount)?></p></div>
</div>
<p class="err" id="errorMsg"></p><button class="btn gold" type="submit">Konfirmasi dan Buat Resi</button>
<a class="btn" href="index.xhtml">Batal</a></form></section></main>
<script>
const form=document.getElementById('paymentForm');
form.querySelectorAll('[name=method]').forEach(el=>el.addEventListener('change',()=>{
 const isQr=el.value==='QRIS'&&el.checked, isBank=el.value==='TRANSFER_BANK'&&el.checked;
 document.getElementById('qrisPanel').hidden=!isQr; document.getElementById('bankPanel').hidden=!isBank;

}));
form.querySelectorAll('[name=bank]').forEach(el=>el.addEventListener('change',()=>{
 document.getElementById('vaNumber').textContent=el.dataset.va;document.getElementById('vaPanel').hidden=false;
}));
document.getElementById('copyVa').addEventListener('click',()=>navigator.clipboard.writeText(document.getElementById('vaNumber').textContent));
form.addEventListener('submit',e=>{
 const method=form.querySelector('[name=method]:checked'), bank=form.querySelector('[name=bank]:checked');
 let msg='';if(!method)msg='Pilih metode pembayaran.';else if(method.value==='TRANSFER_BANK'&&!bank)msg='Pilih bank terlebih dahulu.';
 if(msg){e.preventDefault();document.getElementById('errorMsg').textContent=msg;}
});
</script></body></html>