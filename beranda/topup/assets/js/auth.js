(function () {
  function getCookie(name) {
    var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2].replace(/\+/g, ' ')) : null;
  }

  function esc(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  window.addEventListener('DOMContentLoaded', function () {
    var userName = getCookie('user_name');
    var nav = document.getElementById('userNav');

    if (nav) {
      if (userName) {
        nav.innerHTML =
          '<span class="user-badge">Halo, ' + esc(userName) + '</span>' +
          '<a class="btn sm" href="../../Login/login/logout.php">Keluar</a>';
      } else {
        nav.innerHTML =
          '<a class="btn gold sm" href="../../Login/login/Login.xhtml">Masuk</a>';
      }
    }

    var orderForm = document.getElementById('f');
    if (orderForm && !userName) {
      orderForm.addEventListener('submit', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        alert('Silakan login terlebih dahulu sebelum melanjutkan ke pembayaran.');
        window.location.href = '../../Login/login/Login.xhtml';
      }, true);
    }
  });
})();