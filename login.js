(function buildGeo(){
  var canvas = document.getElementById('geoCanvas');
  var rects = [
    { x: -20,  top: '52%', w: 260, h: 200, accent: false },
    { x:  50,  top: '35%', w: 220, h: 180, accent: false },
    { x: -10,  top: '18%', w: 180, h: 150, accent: true  },
    { x: 120,  top: '58%', w: 190, h: 155, accent: false },
    { x: 180,  top: '30%', w: 170, h: 140, accent: false },
    { x:  70,  top: '10%', w: 150, h: 120, accent: false },
    { x: 240,  top: '48%', w: 130, h: 105, accent: true  },
    { x: 150,  top: '5%',  w: 110, h:  90, accent: false },
    { x: 300,  top: '25%', w: 100, h:  80, accent: false },
    { x:  90,  top: '68%', w:  95, h:  75, accent: false },
    { x: 220,  top: '15%', w:  85, h:  65, accent: true  },
    { x: 320,  top: '52%', w:  75, h:  60, accent: false },
    { x: -30,  top: '78%', w: 240, h: 170, accent: false },
    { x: 360,  top: '10%', w:  65, h:  50, accent: false }
  ];

  var travels = [-14,-22,-10,-18,-26,-12,-20,-8,-24,-16,-28,-11,-19,-7];
  var durs    = [5.2, 6.5, 4.8, 7.2, 5.8, 4.5, 6.8, 6.0, 4.3, 7.0, 5.5, 7.4, 4.9, 6.2];
  var delays  = [0, 0.9, 1.6, 0.4, 1.3, 2.1, 0.6, 1.9, 1.0, 1.7, 2.3, 0.5, 1.1, 2.6];

  rects.forEach(function(r, i) {
    var el = document.createElement('div');
    el.className = 'geo-rect' + (r.accent ? ' accent' : '');
    el.style.cssText = 'left:' + r.x + 'px; top:' + r.top + ';' +
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
      if (locked > len) {
        clearInterval(scrambleId);
        clearInterval(lockId);
        inp.value = realValue;
        inp._scrambling = false;
        inp.readOnly = false;
      }
    }, perChar);
  } else {
    inp.type = 'password';
    ico.textContent = 'visibility';
    inp._scrambling = false;
    inp.readOnly = false;
  }
}

function showToast(msg, type) {
  var toast = document.getElementById('toast');
  toast.textContent = msg;
  toast.className = (type || 'error') + ' show';
  setTimeout(function() {
    toast.className = '';
  }, 3500);
}

window.addEventListener('DOMContentLoaded', function() {
  var params = new URLSearchParams(window.location.search);
  var error = params.get('error');
  var msg = params.get('msg');

  if (error === 'empty') {
    showToast('Harap lengkapi semua username dan kata sandi!', 'error');
  } else if (error === 'wrong_password') {
    showToast('Kata sandi yang Anda masukkan salah!', 'error');
  } else if (error === 'user_not_found') {
    showToast('Username atau Email tidak ditemukan!', 'error');
  }

  if (msg === 'logged_out') {
    showToast('Anda telah berhasil keluar (Logout).', 'success');
  }
});
