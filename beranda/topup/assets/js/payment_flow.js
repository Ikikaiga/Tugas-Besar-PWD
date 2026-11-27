var form = document.getElementById("paymentForm");
var bankPanel = document.getElementById("bankPanel");
var qrisPanel = document.getElementById("qrisPanel");
var vaPanel = document.getElementById("vaPanel");
var vaNumber = document.getElementById("vaNumber");
var errorMsg = document.getElementById("errorMsg");
var confirmPayment = document.getElementById("confirmPayment");
var copyStatus = document.getElementById("copyStatus");
var methods = form.querySelectorAll('[name="method"]');
var banks = form.querySelectorAll('[name="bank"]');
var busy = false;
function pilihMetode() {
    var method = form.querySelector('[name="method"]:checked');
    bankPanel.hidden = !method || method.value !== "TRANSFER_BANK";
    qrisPanel.hidden = !method || method.value !== "QRIS";
    vaPanel.hidden = true;
    vaNumber.textContent = "";
    errorMsg.textContent = "";
    copyStatus.textContent = "";
    for (var i=0; i<banks.length; i++) { banks[i].checked = false; banks[i].disabled = bankPanel.hidden; }
}
function kunciPilihan(lock) {
    busy = lock;
    confirmPayment.disabled = lock;
    for (var i=0; i<methods.length; i++) methods[i].disabled = lock;
    for (var j=0; j<banks.length; j++) banks[j].disabled = lock || bankPanel.hidden;
}
async function pilihBank() {
    var bank = form.querySelector('[name="bank"]:checked');
    if (!bank || busy) return;
    var namaBank = bank.value;
    vaPanel.hidden = true;
    vaNumber.textContent = "";
    errorMsg.textContent = "";
    copyStatus.textContent = "";
    kunciPilihan(true);
    try {
        var data = new URLSearchParams({bank:namaBank, csrf:form.elements.csrf.value});
        var response = await fetch("payment_va.php", {method:"POST", body:data});
        if (!response.ok) throw new Error("VA gagal dibuat");
        var result = await response.json();
        if (!/^\d{15}$/.test(result.va)) throw new Error("VA tidak valid");
        vaNumber.textContent = result.va;
        vaPanel.hidden = false;
    } catch (error) {
        bank.checked = false;
        errorMsg.textContent = "Nomor VA gagal dibuat. Silakan pilih bank lagi.";
    } finally { kunciPilihan(false); }
}
async function salinVA() {
    try {
        await navigator.clipboard.writeText(vaNumber.textContent);
        copyStatus.textContent = "Nomor VA berhasil disalin.";
    } catch (error) {
        var input = document.createElement("textarea");
        input.value = vaNumber.textContent;
        document.body.appendChild(input); input.select();
        var ok = false;
        try { ok = document.execCommand("copy"); } catch (e) { }
        input.remove();
        copyStatus.textContent = ok ? "Nomor VA berhasil disalin." : "Salin nomor VA secara manual.";
    }
}
for (var i=0; i<methods.length; i++) methods[i].onchange = pilihMetode;
for (var j=0; j<banks.length; j++) banks[j].onchange = pilihBank;
document.getElementById("copyVa").onclick = salinVA;
form.onsubmit = function(event) {
    var method = form.querySelector('[name="method"]:checked');
    var bank = form.querySelector('[name="bank"]:checked');
    if (busy || !method || (method.value === "TRANSFER_BANK" && (!bank || !vaNumber.textContent))) {
        event.preventDefault();
        errorMsg.textContent = "Pilih metode dan bank terlebih dahulu.";
    } else { confirmPayment.disabled = true; }
};
pilihMetode();
