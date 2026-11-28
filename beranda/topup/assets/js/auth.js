// Mengatur tampilan navigasi akun pengguna serta membatasi pengiriman formulir pesanan jika belum login.
(function () {
    // Mengambil dan mendekode nilai cookie browser berdasarkan nama yang dicari.
    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? decodeURIComponent(match[2].replace(/\+/g, ' ')) : null;
    }

    // Mengamankan karakter khusus HTML untuk mencegah serangan XSS saat menampilkan nama pengguna.
    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[c];
        });
    }

    // Memperbarui tombol navigasi akun dan mencegah submit pemesanan apabila cookie user_name tidak ditemukan.
    window.addEventListener('DOMContentLoaded', function () {
        var userName = getCookie('user_name');
        var nav = document.getElementById('userNav');
        if (nav) {
            if (userName) {
                nav.innerHTML =
                    '<a class="user-badge" href="profile.xhtml" style="text-decoration:none;color:inherit;">Halo, ' +
                    esc(userName) +
                    '</a>' +
                    '<a class="btn sm" href="../../Login/logout.php">Keluar</a>';
            } else {
                nav.innerHTML =
                    '<a class="btn gold sm" href="../../Login/Login.xhtml">Masuk</a>';
            }
        }
        var orderForm = document.getElementById('f');
        if (orderForm && !userName) {
            orderForm.addEventListener('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                alert('Silakan login terlebih dahulu sebelum melanjutkan ke pembayaran.');
                window.location.href = '../../Login/Login.xhtml';
            }, true);
        }
    });
})();
