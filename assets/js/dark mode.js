(function () {

    const DARK_MODE_KEY = "adminDarkMode";

    function applyDarkMode() {

        if (localStorage.getItem(DARK_MODE_KEY) === "enabled") {
            document.body.classList.add("dark-mode");
        } else {
            document.body.classList.remove("dark-mode");
        }

    }

    window.toggleDarkMode = function () {

        document.body.classList.toggle("dark-mode");

        if (document.body.classList.contains("dark-mode")) {
            localStorage.setItem(DARK_MODE_KEY, "enabled");
        } else {
            localStorage.setItem(DARK_MODE_KEY, "disabled");
        }

    };

    document.addEventListener("DOMContentLoaded", applyDarkMode);

})();
