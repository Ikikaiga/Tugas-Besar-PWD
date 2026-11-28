<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Membuka dan menyimpan koneksi tunggal database MySQL untuk modul pembayaran.
function payment_db(): mysqli {
    static $db = null;
    if ($db === null) {
        $db = new mysqli('localhost', 'root', '', 'db_users');
        $db->set_charset('utf8mb4');
    }
    return $db;
}

// Mengamankan karakter khusus HTML pada string untuk mencegah serangan XSS.
function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Memformat angka nominal menjadi teks mata uang Rupiah Indonesia.
function rupiah($value): string {
    return 'Rp ' . number_format((int)$value, 0, ',', '.');
}
