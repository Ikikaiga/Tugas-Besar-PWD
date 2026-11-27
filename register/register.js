if (sessionStorage.getItem('toRegister') === '1') {
  document.body.classList.add('from-login');
  sessionStorage.removeItem('toRegister');
}

// Build Neo-Brutalist 3D wall blocks — clustered bottom-right (mirror of login)
(function buildGeo(){
  var canvas = document.getElementById('geoCanvas');
  var rects = [
    { right: -20,  top: '52%', w: 260, h: 200, accent: false },
    { right:  50,  top: '35%', w: 220, h: 180, accent: false },
    { right: -10,  top: '18%', w: 180, h: 150, accent: true  },
    { right: 120,  top: '58%', w: 190, h: 155, accent: false },
    { right: 180,  top: '30%', w: 170, h: 140, accent: false },
    { right:  70,  top: '10%', w: 150, h: 120, accent: false },
    { right: 240,  top: '48%', w: 130, h: 105, accent: true  },
    { right: 150,  top: '5%',  w: 110, h:  90, accent: false },
    { right: 300,  top: '25%', w: 100, h:  80, accent: false },
    { right:  90,  top: '68%', w:  95, h:  75, accent: false },
    { right: 220,  top: '15%', w:  85, h:  65, accent: true  },
    { right: 320,  top: '52%', w:  75, h:  60, accent: false },
    { right: -30,  top: '78%', w: 240, h: 170, accent: false },
    { right: 360,  top: '10%', w:  65, h:  50, accent: false }
  ];

  var travels = [-14,-22,-10,-18,-26,-12,-20,-8,-24,-16,-28,-11,-19,-7];
  var durs    = [5.2, 6.5, 4.8, 7.2, 5.8, 4.5, 6.8, 6.0, 4.3, 7.0, 5.5, 7.4, 4.9, 6.2];
  var delays  = [0, 0.9, 1.6, 0.4, 1.3, 2.1, 0.6, 1.9, 1.0, 1.7, 2.3, 0.5, 1.1, 2.6];

  rects.forEach(function(r, i) {
    var el = document.createElement('div');
    el.className = 'geo-rect' + (r.accent ? ' accent' : '');
    el.style.cssText = 'right:' + r.right + 'px; top:' + r.top + ';' +
      'width:' + r.w + 'px; height:' + r.h + 'px;' +
      '--dur:' + durs[i] + 's;' +
      '--delay:' + delays[i] + 's;' +
      '--travel:' + travels[i] + 'px;';
    canvas.appendChild(el);
  });
})();

function togglePw(id, btn) {
  var inp = document.getElementById(id);
  var ico = btn.querySelector('.material-symbols-outlined');
  if (inp._scrambling) return;

  var realValue = inp.value;
  if (!realValue.length) {
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.textContent = inp.type === 'password' ? 'visibility' : 'visibility_off';
    return;
  }

  var glyphs = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#$%&*!?~^+=_';
  var len = realValue.length;
  inp._scrambling = true;
  inp.readOnly = true;

  if (inp.type === 'password') {
    inp.type = 'text';
    ico.textContent = 'visibility_off';

    var locked = 0;
    var perChar = Math.max(12, 100 / len);

    var scrambleId = setInterval(function() {
      var out = '';
      for (var i = 0; i < len; i++) {
        out += i < locked
          ? realValue[i]
          : glyphs[Math.floor(Math.random() * glyphs.length)];
      }
      inp.value = out;
    }, 20);

    var lockId = setInterval(function() {
      locked++;
      if (locked >= len) {
        clearInterval(scrambleId);
        clearInterval(lockId);
        inp.value = realValue;
        inp._scrambling = false;
        inp.readOnly = false;
      }
    }, perChar);
  } else {
    var ticks = 0;
    var maxTicks = 3;

    var scrambleId = setInterval(function() {
      var out = '';
      for (var i = 0; i < len; i++) {
        out += glyphs[Math.floor(Math.random() * glyphs.length)];
      }
      inp.value = out;
      ticks++;
      if (ticks >= maxTicks) {
        clearInterval(scrambleId);
        inp.value = realValue;
        inp.type = 'password';
        ico.textContent = 'visibility';
        inp._scrambling = false;
        inp.readOnly = false;
      }
    }, 30);
  }
}

function showToast(msg, type) {
  var t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'show ' + (type || 'info');
  clearTimeout(t._t);
  t._t = setTimeout(function() {
    t.className = '';
  }, 3500);
}

function goTo(url) {
  sessionStorage.setItem('fromRegister', '1');
  document.body.classList.add('leaving');
  setTimeout(function() {
    window.location.href = url;
  }, 460);
}

function launchExpandTransition(btnEl, targetUrl) {
  var overlay = document.getElementById('expandOverlay');
  var rect = btnEl.getBoundingClientRect();

  overlay.querySelector('.expand-label').innerHTML = btnEl.innerHTML;
  overlay.style.top = rect.top + 'px';
  overlay.style.left = rect.left + 'px';
  overlay.style.width = rect.width + 'px';
  overlay.style.height = rect.height + 'px';
  overlay.style.boxShadow = 'var(--shadow)';

  void overlay.offsetWidth;
  overlay.classList.add('animating');

  setTimeout(function() {
    window.location.href = targetUrl;
  }, 850);
}

window.addEventListener('DOMContentLoaded', function() {
  var params = new URLSearchParams(window.location.search);
  var error = params.get('error');
  var msg = params.get('msg');

  if (error === 'empty') {
    showToast('Harap lengkapi semua kolom pendaftaran!', 'error');
  } else if (error === 'password_mismatch') {
    showToast('Konfirmasi kata sandi tidak cocok!', 'error');
  } else if (error === 'short_username') {
    showToast('Nama pengguna minimal 3 karakter!', 'error');
  } else if (error === 'short_password') {
    showToast('Kata sandi minimal 6 karakter!', 'error');
  } else if (error === 'user_exists') {
    showToast('Nama pengguna sudah terdaftar!', 'error');
  }

  if (msg === 'registered') {
    var registerBtn = document.querySelector('#registerForm .f-btn');
    showToast('Pendaftaran berhasil — Silakan masuk', 'success');
    setTimeout(function() {
      launchExpandTransition(registerBtn, '../Login/Login.xhtml');
    }, 800);
  }

  var form = document.getElementById('registerForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      var u = document.getElementById('regUser').value.trim();
      var p = document.getElementById('regPass').value;
      var c = document.getElementById('regConfirm').value;

      if (!u || !p || !c) {
        e.preventDefault();
        showToast('Harap lengkapi semua kolom pendaftaran!', 'error');
        return;
      }
      if (u.length < 3) {
        e.preventDefault();
        showToast('Nama pengguna minimal 3 karakter!', 'error');
        return;
      }
      if (p.length < 6) {
        e.preventDefault();
        showToast('Kata sandi minimal 6 karakter!', 'error');
        return;
      }
      if (p !== c) {
        e.preventDefault();
        showToast('Konfirmasi password tidak cocok!', 'error');
        return;
      }
    });
  }
});
