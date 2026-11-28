// Merender formulir pemesanan top up sesuai game yang dipilih dan memvalidasi input sebelum lanjut ke pembayaran.
(() => {
const PAYMENT_URL = 'payment.php';

// Mengambil elemen DOM pertama yang cocok dengan selektor CSS.
const $ = (s, r = document) => r.querySelector(s);

// Memformat angka menjadi string mata uang Rupiah Indonesia.
const rp = n => 'Rp ' + n.toLocaleString('id-ID');

// Mengamankan karakter khusus HTML untuk mencegah serangan XSS saat merender teks.
const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

const key = new URLSearchParams(location.search).get('game');
const g = GAMES[key];
const page = $('#page');
if (!g) {
  page.innerHTML = '<div class="card pad"><h2>Game tidak ditemukan</h2><p>Pilih game dari daftar untuk mulai top up.</p><a class="btn gold" href="index.xhtml">Kembali ke daftar game</a></div>';
  return;
}
document.title = 'Top up ' + g.name + ' – GameVault';
page.innerHTML = `
<a class="back" href="index.xhtml">Kembali ke semua game</a>
<div class="card banner" style="--c:${g.color}"><img src="${g.img}" alt="" style="object-position:${g.pos}"/>
  <div class="bcap"><h1>Top up ${esc(g.name)}</h1></div></div>
<form id="f" novalidate="novalidate">
  <section class="card pad"><h2>Akun ${esc(g.name)}</h2>
    <div class="fields">${g.fields.map(f => `<label class="f">${esc(f.label)}<input type="text" name="${f.id}" placeholder="${esc(f.ph)}" autocomplete="off" required="required"/></label>`).join('')}</div>
  </section>
  <section class="card pad"><h2>Pilih nominal ${esc(g.currency)}</h2>
    <div class="packs">${g.packs.map((p, i) => `<label class="opt"><input type="radio" name="pack" value="${i}"/><span><b>${esc(p[0])}</b>${/^Rp/.test(p[0]) ? '' : ' ' + esc(g.currency)}<small>${rp(p[1])}</small></span></label>`).join('')}</div>
  </section>
  <div class="bar"><div class="tot"><small>Total bayar</small><b id="tot">Rp 0</b></div>
    <p class="err" id="err"></p>
    <button class="btn gold" type="submit">Lanjut ke pembayaran</button></div>
</form>`;
const f = $('#f');
f.addEventListener('change', () => { $('#tot').textContent = rp(f.pack.value === '' ? 0 : g.packs[f.pack.value][1]); $('#err').textContent = ''; });
f.addEventListener('submit', e => {
  e.preventDefault();
  const fd = new FormData(f), err = $('#err');
  const acc = {}; g.fields.forEach(x => acc[x.id] = (fd.get(x.id) || '').trim());
  const bad = g.fields.find(x => !new RegExp('^' + x.pattern + '$').test(acc[x.id]));
  if (bad) { err.textContent = `${bad.label} belum benar. Contoh: ${bad.ph}`; f.elements[bad.id].focus(); return; }
  if (fd.get('pack') === null) { err.textContent = 'Pilih nominal terlebih dahulu.'; return; }
  const pi = +fd.get('pack'), p = g.packs[pi];
  const data = {game: key, gameName: g.name, pack: pi, item: p[0] + (/^Rp/.test(p[0]) ? '' : ' ' + g.currency), price: p[1], account: acc};
  try { sessionStorage.setItem('gv_checkout', JSON.stringify(data)); } catch (x) {}
  const q = new URLSearchParams({game: key, pack: String(pi), item: data.item, price: String(p[1])});
  g.fields.forEach(x => q.set('acc_' + x.id, acc[x.id]));
  location.href = PAYMENT_URL + '?' + q.toString();
});
})();
