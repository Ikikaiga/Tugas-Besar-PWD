// Merender daftar kartu game beserta harga mulai termurah ke halaman utama.
(() => {
// Memformat angka menjadi string mata uang Rupiah Indonesia.
const rp = n => 'Rp ' + n.toLocaleString('id-ID');

// Mengamankan karakter khusus HTML untuk mencegah serangan XSS saat merender teks.
const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

document.getElementById('games').innerHTML = Object.keys(GAMES).map(k => {
  const g = GAMES[k], min = Math.min(...g.packs.map(p => p[1]));
  return `<a class="card game" href="order.xhtml?game=${k}" style="--c:${g.color}">
    <div class="ph"><img src="${g.img}" alt="${esc(g.name)}" style="object-position:${g.pos}"/></div>
    <div class="meta"><div><div class="gname">${esc(g.name)}</div><div class="gcur">${esc(g.currency)} mulai ${rp(min)}</div></div>
    <span class="go">Top up</span></div></a>`;
}).join('');
})();
