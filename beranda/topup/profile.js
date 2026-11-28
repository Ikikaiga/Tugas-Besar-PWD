(function () {

    function showToast(message, type) {

        var toast = document.getElementById("toast");

        if (!toast) {
            return;
        }

        toast.textContent = message;
        toast.className = "";

        if (type) {
            toast.classList.add(type);
        }

        toast.classList.add("show");

        setTimeout(function () {
            toast.classList.remove("show");
        }, 3000);

    }

    /* ===== FOTO PROFIL ===== */

    var FOTO_DIR = "uploads/avatar/";
    var FOTO_MAKS_BYTE = 2 * 1024 * 1024;
    var FOTO_TIPE_OK = [
        "image/jpeg",
        "image/png",
        "image/webp",
        "image/gif"
    ];

    var usernameSekarang = "";

    function tampilkanFoto(namaFile) {

        var img = document.getElementById("avatarImg");
        var inisial = document.getElementById("avatarInit");
        var btnHapus = document.getElementById("btnHapusFoto");

        if (!img || !inisial) {
            return;
        }

        var huruf = (usernameSekarang || "?")
            .trim()
            .charAt(0)
            .toUpperCase();

        inisial.textContent = huruf || "?";

        if (namaFile) {

            img.src = FOTO_DIR + encodeURIComponent(namaFile);
            img.classList.remove("hide");
            inisial.classList.add("hide");

            if (btnHapus) {
                btnHapus.classList.remove("hide");
            }

        } else {

            img.classList.add("hide");
            inisial.classList.remove("hide");

            if (btnHapus) {
                btnHapus.classList.add("hide");
            }

        }

    }

    function kirimFoto(formData, pesanSukses) {

        var box = document.getElementById("avatarBox");
        var input = document.getElementById("fotoInput");

        box.classList.add("uploading");

        fetch("upfoto.php", {
            method: "POST",
            body: formData
        })

            .then(function (response) {
                return response.json();
            })

            .then(function (result) {

                if (!result.success) {

                    showToast(
                        result.message || "Gagal memproses foto",
                        "error"
                    );

                    return;

                }

                tampilkanFoto(result.foto);

                showToast(pesanSukses, "success");

            })

            .catch(function () {

                showToast(
                    "Gagal terhubung ke server",
                    "error"
                );

            })

            .then(function () {

                box.classList.remove("uploading");
                input.value = "";

            });

    }

    function pilihFoto(file) {

        if (!file) {
            return;
        }

        if (FOTO_TIPE_OK.indexOf(file.type) === -1) {

            showToast(
                "Format foto harus JPG, PNG, WEBP, atau GIF",
                "error"
            );

            return;

        }

        if (file.size > FOTO_MAKS_BYTE) {

            showToast(
                "Ukuran foto maksimal 2 MB",
                "error"
            );

            return;

        }

        var formData = new FormData();

        formData.append("aksi", "upload");
        formData.append("foto", file);

        kirimFoto(formData, "Foto profil diperbarui");

    }

    function hapusFoto() {

        if (!confirm("Hapus foto profil?")) {
            return;
        }

        var formData = new FormData();

        formData.append("aksi", "hapus");

        kirimFoto(formData, "Foto profil dihapus");

    }

    function pasangFotoProfil() {

        var btnGanti = document.getElementById("btnGantiFoto");
        var btnHapus = document.getElementById("btnHapusFoto");
        var input = document.getElementById("fotoInput");
        var img = document.getElementById("avatarImg");

        if (btnGanti && input) {

            btnGanti.addEventListener("click", function () {
                input.click();
            });

            input.addEventListener("change", function () {
                pilihFoto(input.files && input.files[0]);
            });

        }

        if (btnHapus) {
            btnHapus.addEventListener("click", hapusFoto);
        }

        // Kalau file foto hilang dari server, balik ke huruf inisial
        if (img) {
            img.addEventListener("error", function () {
                tampilkanFoto(null);
            });
        }

    }

    function loadProfile() {

        fetch("getacc.php")

            .then(function (response) {
                return response.json();
            })

            .then(function (result) {

                if (!result.success) {

                    window.location.href =
                        "../../Login/Login.xhtml";

                    return;

                }

                var data = result.data;

                document.getElementById(
                    "viewUsername"
                ).textContent =
                    data.username || "User";

                usernameSekarang = data.username || "";

                tampilkanFoto(data.foto);

                var username =
                    document.getElementById(
                        "username"
                    );

                if (username) {
                    username.value =
                        data.username || "";
                }

                var email =
                    document.getElementById(
                        "email"
                    );

                if (email) {
                    email.value =
                        data.email || "";
                }

                var bio =
                    document.getElementById(
                        "bio"
                    );

                if (bio) {
                    bio.value =
                        data.bio || "";
                }

                var gameFavorit =
                    document.getElementById(
                        "gameFavorit"
                    );

                if (gameFavorit) {
                    gameFavorit.value =
                        data.game_favorit || "";
                }

            })

            .catch(function () {

                showToast(
                    "Gagal memuat profil",
                    "error"
                );

            });

    }

    document.addEventListener(
        "DOMContentLoaded",
        function () {

            pasangFotoProfil();

            loadProfile();

            var params =
                new URLSearchParams(
                    window.location.search
                );

            if (
                params.get("success") === "1"
            ) {

                showToast(
                    "Perubahan berhasil disimpan",
                    "success"
                );

            }

        }
    );

})();

function konfirmasiHapusAkun() {

    var password = prompt(
        "Masukkan password untuk menghapus akun:"
    );

    if (
        password === null ||
        password.trim() === ""
    ) {
        return;
    }

    var yakin = confirm(
        "Yakin ingin menghapus akun secara permanen?"
    );

    if (!yakin) {
        return;
    }

    var form =
        document.createElement("form");

    form.method = "POST";
    form.action = "delacc.php";

    var input =
        document.createElement("input");

    input.type = "hidden";
    input.name = "password";
    input.value = password;

    form.appendChild(input);

    document.body.appendChild(form);

    form.submit();

}