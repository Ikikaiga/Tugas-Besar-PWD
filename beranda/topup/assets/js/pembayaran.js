const detailPesanan = document.getElementById("detailPesanan");
const totalHarga = document.getElementById("totalHarga");
const pesanError = document.getElementById("pesanError");
const btnLanjut = document.getElementById("btnLanjut");
const btnBatal = document.getElementById("btnBatal");
const areaQRIS = document.getElementById("areaQRIS");
const areaBerhasil = document.getElementById("areaBerhasil");

// Mengambil data rincian pesanan dari sessionStorage atau parameter URL.
const params = new URLSearchParams(window.location.search);
let dataPesanan = null;

try {
    dataPesanan = JSON.parse(sessionStorage.getItem("gv_checkout"));
} catch (error) {
    dataPesanan = null;
}

if (!dataPesanan || !dataPesanan.game) {
    const account = {};

    params.forEach(function(value, key) {
        if (key.startsWith("acc_")) {
            account[key.substring(4)] = value;
        }
    });

    dataPesanan = {
        game: params.get("game"),
        pack: params.get("pack"),
        item: params.get("item"),
        account: account
    };
}

const game = window.GAMES[dataPesanan.game];
const packIndex = Number(dataPesanan.pack);

if (!game || dataPesanan.pack == null ||
    dataPesanan.pack === "" ||
    !Number.isInteger(packIndex) ||
    packIndex < 0 || 
    !game.packs[packIndex]) { 
        pesanError.textContent = "Data pesanan tidak ditemukan. Silakan ulangi pemesanan.";
        btnLanjut.disabled = true;
} else {
    const paket = game.packs[packIndex];
    const harga = paket[1];

    // Menambahkan baris label dan nilai rincian pesanan ke dalam daftar ringkasan pembayaran.
    function tambahDetail(label, nilai) {
        const dt = document.createElement("dt");
        const dd = document.createElement("dd");
        dt.textContent = label;
        dd.textContent = nilai;
        detailPesanan.appendChild(dt);
        detailPesanan.appendChild(dd);
    }

    tambahDetail("Game", game.name);

    if (dataPesanan.game == "steam") {
        tambahDetail("Nominal", paket[0]);
    }else {
        tambahDetail("Nominal", paket[0] + " " + game.currency);
    }

    const account = dataPesanan.account || {};
    for (const key in account) {
        tambahDetail(key.toUpperCase(), account[key]);
    }

    totalHarga.textContent =
        "Rp " + harga.toLocaleString("id-ID");
}

// Memproses pilihan metode pembayaran (Transfer Bank atau QRIS) saat tombol lanjut ditekan.
function lanjutBayar() {
    var transfer = document.getElementById("transfer");
    var qris = document.getElementById("qris");

    if (transfer.checked == true) {
        pesanError.textContent = "";
        areaQRIS.hidden = true;
        areaBerhasil.hidden = false;

        document.getElementById("metodeBerhasil").textContent =
            "Pembayaran melalui Transfer Bank berhasil!";

        btnLanjut.hidden = true;
        btnBatal.hidden = true;

        transfer.disabled = true;
        qris.disabled = true;

        sessionStorage.removeItem("gv_checkout");
    }
    else if (qris.checked == true) {
        pesanError.textContent = "";
        areaBerhasil.hidden = true;
        areaQRIS.hidden = false;

        btnLanjut.hidden = true;
    }
    else {
        pesanError.textContent =
            "Silahkan pilih metode pembayaran!";
    }
}

// Menyelesaikan transaksi QRIS dan menampilkan status pembayaran berhasil.
function konfirmasiQRIS() {
    areaQRIS.hidden = true;
    areaBerhasil.hidden = false;

    document.getElementById("metodeBerhasil").textContent =
        "Pembayaran melalui QRIS berhasil!";

    btnLanjut.hidden = true;
    btnBatal.hidden = true;

    document.getElementById("transfer").disabled = true;
    document.getElementById("qris").disabled = true;

    sessionStorage.removeItem("gv_checkout");
}

// Menghapus data checkout sementara dan mengembalikan pengguna ke halaman utama.
function batalBayar() {
    sessionStorage.removeItem("gv_checkout");
    window.location.href = "index.xhtml";
}
