<?php

/*
|--------------------------------------------------------------------------
| COMMON NAVBAR
| AI Workforce Management System
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| BASE URL
|--------------------------------------------------------------------------
*/

$base_url = "/AI-Workforce-Management";


/*
|--------------------------------------------------------------------------
| GET USER ROLE
|--------------------------------------------------------------------------
|
| IMPORTANT:
| session_start() should NOT be called here.
| Start the session at the top of each PHP page.
|
*/

$role_id = $_SESSION['role_id'] ?? null;


/*
|--------------------------------------------------------------------------
| HOME PAGE BASED ON ROLE
|--------------------------------------------------------------------------
|
| HOME = Main project home page
| DASHBOARD = Role-specific dashboard
|
*/

$home_url = $base_url . "/index.php";

switch ((int)$role_id) {

    case 1:
        // Admin
        $dashboard_url = $base_url . "/admin/dashboard_new.php";
        break;

    case 2:
        // Manager
        $dashboard_url = $base_url . "/manager/dashboard.php";
        break;

    case 3:
        // Employee
        $dashboard_url = $base_url . "/employee/dashboard.php";
        break;

    case 4:
        // Client
        $dashboard_url = $base_url . "/client/dashboard.php";
        break;

    default:
        // Public / not logged in
        $dashboard_url = $base_url . "/index.php";
        break;
}


/*
|--------------------------------------------------------------------------
| LOGOUT URL
|--------------------------------------------------------------------------
*/

$logout_url = $base_url . "/authentication/logout.php";

?>

<nav class="top-navbar">

    <!-- =====================================================
         LOGO
    ====================================================== -->

    <div class="logo-section">

        <a
            href="<?php echo $home_url; ?>"
            class="logo-link"
        >

            <img
                src="<?php echo $base_url; ?>/assets/images/logo1.png"
                alt="AI Workforce Management"
                class="main-logo"
            >

        </a>

    </div>


    <!-- =====================================================
         MAIN NAVIGATION
    ====================================================== -->

    <ul class="nav-menu">

        <!-- DASHBOARD -->

        <li>
            <a href="<?php echo $dashboard_url; ?>">

                <i class="fa-solid fa-gauge-high"></i>

                Dashboard

            </a>
        </li>


        <!-- EMPLOYEES -->

        <li>
            <a href="<?php echo $base_url; ?>/employee/view.php">

                <i class="fa-solid fa-users"></i>

                Employees

            </a>
        </li>


        <!-- DEPARTMENTS -->

        <li>
            <a href="<?php echo $base_url; ?>/department/view.php">

                <i class="fa-solid fa-building"></i>

                Departments

            </a>
        </li>


        <!-- PROJECTS -->

        <li>
            <a href="<?php echo $base_url; ?>/project/view.php">

                <i class="fa-solid fa-folder"></i>

                Projects

            </a>
        </li>


        <!-- TASKS -->

        <li>
            <a href="<?php echo $base_url; ?>/task/view.php">

                <i class="fa-solid fa-list-check"></i>

                Tasks

            </a>
        </li>


        <!-- REPORTS -->

        <li>
            <a href="<?php echo $base_url; ?>/reports/index.php">

                <i class="fa-solid fa-chart-column"></i>

                Reports

            </a>
        </li>

    </ul>


    <!-- =====================================================
         RIGHT SIDE
    ====================================================== -->

    <div class="nav-right">

        <!-- DARK MODE -->

        <button
            type="button"
            class="dark-mode-btn"
            onclick="toggleDarkMode()"
            title="Toggle Dark Mode"
        >

            <i class="fa-solid fa-moon"></i>

        </button>


        <!-- HOME -->

        <a
            href="<?php echo $home_url; ?>"
            class="home-btn"
        >

            <i class="fa-solid fa-house"></i>

            Home

        </a>


        <!-- LOGOUT -->

        <a
            href="<?php echo $logout_url; ?>"
            class="logout-btn"
        >

            <i class="fa-solid fa-right-from-bracket"></i>

            Logout

        </a>

    </div>

</nav>


<script>

src="<?php echo $base_url; ?>/assets/js/dark-mode.js">

</script>

