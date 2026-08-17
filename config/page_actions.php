<?php
/*
=========================================================
COMMON SYSTEM HEADER
AI Workforce Management System

Includes:
- Logo
- Dark Mode
- Home
- Logout
- Persistent Dark Mode

Only ONE logo is visible at a time.
=========================================================
*/
?>

<header class="system-header">

    <!-- =================================================
         LEFT SIDE - LOGO
    ================================================= -->

    <div class="system-header-left">

        <div class="system-logo">

            <!-- LIGHT MODE LOGO -->
            <img
                src="../assets/images/logo/logo-horizontal-light.png"
                class="header-logo-light"
                alt="AI Workforce Management System"
            >

            <!-- DARK MODE LOGO -->
            <img
                src="../assets/images/logo/logo-horizontal-dark.png"
                class="header-logo-dark"
                alt="AI Workforce Management System"
            >

        </div>

    </div>


    <!-- =================================================
         RIGHT SIDE - BUTTONS
    ================================================= -->

    <div class="system-header-right">

        <!-- DARK MODE -->
        <button
            type="button"
            class="system-dark-btn"
            id="darkModeButton"
            onclick="toggleAdminDarkMode()"
            title="Toggle Dark Mode"
        >
            <span id="darkModeIcon">🌙</span>
            <span id="darkModeText">Dark</span>
        </button>


        <?php
$current_page = basename($_SERVER['PHP_SELF']);

$home_link = ($current_page === 'login.php')
    ? '../index.php'
    : '../admin/dashboard_new.php';
?>

<a
    href="<?php echo $home_link; ?>"
    class="system-home-btn"
>
    🏠 Home
</a>


        <!-- LOGOUT -->
        <a
            href="../authentication/logout.php"
            class="system-logout-btn"
        >
            🚪 Logout
        </a>

    </div>

</header>


<style>

/* =========================================================
   COMMON HEADER
========================================================= */

.system-header {

    width: 100%;
    min-height: 64px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    box-sizing: border-box;

    padding: 10px 18px;

    margin: 0 0 20px 0;

    background: transparent;

    border: none;

    border-radius: 0;

    box-shadow: none;

    position: relative;

    z-index: 100;

}


/* =========================================================
   LEFT SIDE
========================================================= */

.system-header-left {

    display: flex;

    align-items: center;

    flex-shrink: 0;

}


/* =========================================================
   LOGO CONTAINER
========================================================= */

.system-logo {

    position: relative;

    width: 220px;

    height: 42px;

    display: flex;

    align-items: center;

}


/* =========================================================
   BOTH LOGOS
========================================================= */

.system-logo img {

    position: absolute;

    left: 0;

    top: 50%;

    transform: translateY(-50%);

    width: auto;

    height: 42px;

    max-width: 220px;

    object-fit: contain;

}


/* =========================================================
   LIGHT MODE
   ONLY LIGHT LOGO IS VISIBLE
========================================================= */

.system-logo .header-logo-light {

    display: block !important;

}


.system-logo .header-logo-dark {

    display: none !important;

}


/* =========================================================
   DARK MODE
   ONLY DARK LOGO IS VISIBLE
========================================================= */

body.admin-dark-mode .system-logo .header-logo-light {

    display: none !important;

}


body.admin-dark-mode .system-logo .header-logo-dark {

    display: block !important;

}


/* =========================================================
   RIGHT SIDE
========================================================= */

.system-header-right {

    display: flex;

    align-items: center;

    gap: 8px;

    flex-shrink: 0;

}


.system-header-right a,
.system-header-right button {

    text-decoration: none;

    white-space: nowrap;

}


/* =========================================================
   DARK MODE BUTTON
========================================================= */

.system-dark-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 5px;

    padding: 8px 12px;

    background: #f1f5f9;

    color: #1e293b;

    border: 1px solid #cbd5e1;

    border-radius: 7px;

    font-size: 13px;

    font-weight: 600;

    cursor: pointer;

    transition: 0.2s ease;

}


.system-dark-btn:hover {

    background: #e2e8f0;

    transform: translateY(-1px);

}


/* =========================================================
   HOME BUTTON
========================================================= */

.system-home-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 8px 14px;

    background: #2563eb;

    color: #ffffff;

    border-radius: 7px;

    font-size: 13px;

    font-weight: 600;

    transition: 0.2s ease;

}


.system-home-btn:hover {

    background: #1d4ed8;

    color: #ffffff;

    transform: translateY(-1px);

}


/* =========================================================
   LOGOUT BUTTON
========================================================= */

.system-logout-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 8px 14px;

    background: #dc2626;

    color: #ffffff;

    border-radius: 7px;

    font-size: 13px;

    font-weight: 600;

    transition: 0.2s ease;

}


.system-logout-btn:hover {

    background: #b91c1c;

    color: #ffffff;

    transform: translateY(-1px);

}


/* =========================================================
   ADMIN DARK MODE
========================================================= */

body.admin-dark-mode {

    background: #0f172a !important;

    color: #e5e7eb !important;

}


/* =========================================================
   HEADER DARK MODE
========================================================= */

body.admin-dark-mode .system-header {

    background: #1e293b;

    border-color: #334155;

    box-shadow:
        0 4px 15px rgba(0, 0, 0, 0.35);

}


/* =========================================================
   DARK MODE BUTTON
========================================================= */

body.admin-dark-mode .system-dark-btn {

    background: #334155;

    color: #f8fafc;

    border-color: #475569;

}


body.admin-dark-mode .system-dark-btn:hover {

    background: #475569;

}


/* =========================================================
   DARK MODE - COMMON PAGE ELEMENTS
========================================================= */

body.admin-dark-mode .card {

    background: #1e293b;

    color: #e5e7eb;

    border-color: #334155;

}


body.admin-dark-mode .table {

    color: #e5e7eb;

}


body.admin-dark-mode .table tbody tr {

    background: #1e293b;

    color: #e5e7eb;

}


body.admin-dark-mode .table tbody tr:hover {

    background: #334155;

}


body.admin-dark-mode .form-control,
body.admin-dark-mode .form-select {

    background: #1e293b;

    color: #f8fafc;

    border-color: #475569;

}


body.admin-dark-mode .form-control::placeholder {

    color: #94a3b8;

}


body.admin-dark-mode .text-muted {

    color: #94a3b8 !important;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 600px) {

    .system-header {

        padding: 8px 10px;

        min-height: 58px;

        margin-bottom: 15px;

    }


    .system-logo {

        width: 170px;

        height: 36px;

    }


    .system-logo img {

        height: 36px;

        max-width: 170px;

    }


    .system-header-right {

        gap: 5px;

    }


    .system-dark-btn,
    .system-home-btn,
    .system-logout-btn {

        padding: 7px 9px;

        font-size: 12px;

    }


    #darkModeText {

        display: none;

    }

}

</style>


<script>

/*
=========================================================
ADMIN DARK MODE
=========================================================
*/

function updateAdminDarkModeButton() {

    const button =
        document.getElementById("darkModeButton");

    const icon =
        document.getElementById("darkModeIcon");

    const text =
        document.getElementById("darkModeText");


    if (!button || !icon || !text) {

        return;

    }


    if (
        document.body.classList.contains(
            "admin-dark-mode"
        )
    ) {

        icon.textContent = "☀️";

        text.textContent = "Light";

    } else {

        icon.textContent = "🌙";

        text.textContent = "Dark";

    }

}


/*
=========================================================
TOGGLE DARK MODE
=========================================================
*/

function toggleAdminDarkMode() {

    document.body.classList.toggle(
        "admin-dark-mode"
    );


    if (
        document.body.classList.contains(
            "admin-dark-mode"
        )
    ) {

        localStorage.setItem(
            "adminDarkMode",
            "enabled"
        );

    } else {

        localStorage.setItem(
            "adminDarkMode",
            "disabled"
        );

    }


    updateAdminDarkModeButton();

}


/*
=========================================================
LOAD SAVED DARK MODE
=========================================================
*/

document.addEventListener(
    "DOMContentLoaded",
    function () {

        if (
            localStorage.getItem(
                "adminDarkMode"
            ) === "enabled"
        ) {

            document.body.classList.add(
                "admin-dark-mode"
            );

        } else {

            document.body.classList.remove(
                "admin-dark-mode"
            );

        }


        updateAdminDarkModeButton();

    }
);

</script>