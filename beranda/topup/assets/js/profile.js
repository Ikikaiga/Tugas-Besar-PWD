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

    function loadProfile() {

        fetch("getacc.php")

            .then(function (response) {
                return response.json();
            })

            .then(function (result) {

                if (!result.success) {

                    window.location.href =
                        "../../Login/login/Login.xhtml";

                    return;

                }

                var data = result.data;

                document.getElementById(
                    "viewUsername"
                ).textContent =
                    data.username || "User";

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