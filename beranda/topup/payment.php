<?php
session_start();
require __DIR__ . '/configResiPhp/payment_db.php';

// Mendefinisikan katalog paket dan harga resmi di sisi server untuk mencegah manipulasi harga.
$catalog = [
 'mlbb'=>['name'=>'Mobile Legends','packs'=>[['86 Diamond',22000],['172 Diamond',44000],['257 Diamond',65000],['344 Diamond',87000],['514 Diamond',130000],['706 Diamond',175000]],'fields'=>['uid'=>'User ID','zone'=>'Zone ID']],
 'valorant'=>['name'=>'Valorant','packs'=>[['475 VP',49000],['1000 VP',100000],['2050 VP',200000],['3650 VP',350000],['5350 VP',500000]],'fields'=>['riot'=>'Riot ID']],
 'steam'=>['name'=>'Steam Wallet','packs'=>[['Rp 12.000',14000],['Rp 45.000',50000],['Rp 60.000',66000],['Rp 90.000',98000],['Rp 120.000',130000],['Rp 250.000',265000]],'fields'=>['steam'=>'Steam ID / Email']],
 'roblox'=>['name'=>'Roblox','packs'=>[['80 Robux',15000],['400 Robux',75000],['800 Robux',150000],['1.700 Robux',300000],['4.500 Robux',750000]],'fields'=>['user'=>'Username Roblox']],
 'warframe'=>['name'=>'Warframe','packs'=>[['75 Platinum',15000],['170 Platinum',33000],['370 Platinum',68000],['1.000 Platinum',170000],['2.100 Platinum',340000]],'fields'=>['ign'=>'Nama in-game']]
];

// Memvalidasi parameter game, indeks paket, dan kelengkapan data akun game dari URL.
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

// Membuat nomor invoice, token CSRF, serta menyimpan detail pesanan ke dalam sesi server.
$invoice='TPN'.date('ymd').strtoupper(bin2hex(random_bytes(3)));
$_SESSION['payment_order']=['invoice'=>$invoice,'game_key'=>$gameKey,'game'=>$game['name'],'item'=>$item,'amount'=>$amount,'account'=>$account];
$_SESSION['payment_csrf']=bin2hex(random_bytes(24));
$banks=['BCA'=>'39358','BRI'=>'88810','MANDIRI'=>'88608'];
$digits=substr(preg_replace('/\D/','',(string)crc32($invoice).(string)crc32(strrev($invoice))),0,10);
$va=[];
foreach($banks as $b=>$prefix) $va[$b]=$prefix.$digits;
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pembayaran TOPUPIN</title>
<link rel="stylesheet" href="assets/css/style.css"><link rel="stylesheet" href="assets/css/pembayaran.css"><link rel="stylesheet" href="assets/css/payment_extra.css">
</head><body>
<div class="bg" aria-hidden="true"><i></i><i></i><i></i></div>
<header class="top"><div class="wrap bar0"><a class="logo" href="index.xhtml"><span class="logo-box">G</span>TOPUPIN</a></div></header>

<main class="wrap narrow">
  <section class="card pad simple-payment">

    <h1>Pembayaran TOPUPIN</h1>

    <p>Total yang Harus Dibayar</p>
    <h2 class="payment-total">
      <?= rupiah($amount) ?>
    </h2>

    <hr>

    <h2>Metode Pembayaran</h2>

    <form method="post"
          action="payment_process.php"
          id="paymentForm">

      <input type="hidden"
             name="csrf"
             value="<?= h($_SESSION['payment_csrf']) ?>">

      <div class="method-grid">

        <label class="method-card">
          <input type="radio"
                 name="method"
                 value="TRANSFER_BANK"
                 checked required>
          <span>Transfer Bank</span>
        </label>

        <label class="method-card">
          <input type="radio"
                 name="method"
                 value="QRIS"
                 required>
          <span>QRIS</span>
        </label>

      </div>

      <div id="bankPanel" class="simple-panel">

        <h2>Pilih Bank Tujuan</h2>

        <div class="bank-grid">

          <label class="bank-card">
            <input type="radio"
                   name="bank"
                   value="BCA">
            <span>
              <img src="assets/img/BCA.jpg" alt="BCA">
              BCA MOBILE
            </span>
          </label>

          <label class="bank-card">
            <input type="radio"
                   name="bank"
                   value="BRI">
            <span>
              <img src="assets/img/BRI.jpg" alt="BRI">
              BRImo
            </span>
          </label>

          <label class="bank-card">
            <input type="radio"
                   name="bank"
                   value="MANDIRI">
            <span>
              <img src="assets/img/mandiri.jpg" alt="Mandiri">
              Livin' by Mandiri
            </span>
          </label>

        </div>
      </div>

      <div id="qrisPanel" class="simple-panel" hidden>

        <h2>Scan QRIS</h2>

        <img class="payment-qr"
             src="assets/img/QR_Bayar.png"
             alt="Kode QR Pembayaran">

      </div>

      <p id="errorMsg" class="err"></p>

      <button class="btn-confirm" type="submit">
        Konfirmasi Pembayaran
      </button>

      <a class="cancel-payment" href="index.xhtml">
        Batal Pembayaran
      </a>

      <p class="simulation-note">
        Pembayaran simulasi untuk tugas PWD.
      </p>

    </form>

  </section>
</main>

<script>
const form = document.getElementById('paymentForm');
const bankPanel = document.getElementById('bankPanel');
const qrisPanel = document.getElementById('qrisPanel');
const errorMsg = document.getElementById('errorMsg');

// Mengatur tampilan panel pilihan bank atau QRIS sesuai metode pembayaran yang dipilih.
function updateMethod() {
    const method = form.querySelector(
        '[name="method"]:checked'
    ).value;

    const isTransfer = method === 'TRANSFER_BANK';

    bankPanel.hidden = !isTransfer;
    qrisPanel.hidden = isTransfer;

    form.querySelectorAll('[name="bank"]').forEach(bank => {
        bank.disabled = !isTransfer;
    });

    errorMsg.textContent = '';
}

form.querySelectorAll('[name="method"]').forEach(el => {
    el.addEventListener('change', updateMethod);
});

form.querySelectorAll('[name="bank"]').forEach(el => {
    el.addEventListener('change', () => {
        errorMsg.textContent = '';
    });
});

// Memastikan bank tujuan sudah dipilih sebelum formulir pembayaran dikirimkan.
form.addEventListener('submit', function(e) {
    const method = form.querySelector(
        '[name="method"]:checked'
    );

    const bank = form.querySelector(
        '[name="bank"]:checked'
    );

    if (method.value === 'TRANSFER_BANK' && !bank) {
        e.preventDefault();
        errorMsg.textContent = 'Silakan pilih bank tujuan.';
    }
});

updateMethod();
</script>

</body>
</html>
