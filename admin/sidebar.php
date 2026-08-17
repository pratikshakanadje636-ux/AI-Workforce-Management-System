<?php
/*
|--------------------------------------------------------------------------
| COMMON SIDEBAR
| AI Workforce Management System
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = "/AI-Workforce-Management";

$role_id = $_SESSION['role_id'] ?? null;


/*
|--------------------------------------------------------------------------
| ROLE-BASED DASHBOARD
|--------------------------------------------------------------------------
*/

switch ((int)$role_id) {

    case 1:
        $dashboard_url = $base_url . "/admin/dashboard_new.php";
        break;

    case 2:
        $dashboard_url = $base_url . "/manager/dashboard.php";
        break;

    case 3:
        $dashboard_url = $base_url . "/employee/dashboard.php";
        break;

    case 4:
        $dashboard_url = $base_url . "/client/dashboard.php";
        break;

    default:
        $dashboard_url = $base_url . "/index.php";
        break;
}


$home_url = $base_url . "/index.php";

$logout_url = $base_url . "/authentication/logout.php";

?>

<div class="sidebar">

    <!-- =====================================================
         LOGO
    ====================================================== -->

    <div class="text-center py-4 border-bottom border-secondary">

        <h3 class="fw-bold text-white">
            🤖 AI Workforce
        </h3>

        <small class="text-light">
            Management System
        </small>

    </div>


    <!-- =====================================================
         NAVIGATION
    ====================================================== -->

    <div class="mt-4">


        <!-- HOME -->

        <a href="<?php echo $home_url; ?>">

            🏠

            <span>
                Home
            </span>

        </a>


        <!-- DASHBOARD -->

        <a href="<?php echo $dashboard_url; ?>">

            📊

            <span>
                Dashboard
            </span>

        </a>


        <!-- EMPLOYEES -->

        <a href="<?php echo $base_url; ?>/employee/view.php">

            👨‍💼

            <span>
                Employees
            </span>

        </a>


        <!-- DEPARTMENTS -->

        <a href="<?php echo $base_url; ?>/department/view.php">

            🏢

            <span>
                Departments
            </span>

        </a>


        <!-- PROJECTS -->

        <a href="<?php echo $base_url; ?>/project/view.php">

            📁

            <span>
                Projects
            </span>

        </a>


        <!-- TASKS -->

        <a href="<?php echo $base_url; ?>/task/view.php">

            ✅

            <span>
                Tasks
            </span>

        </a>


        <!-- REPORTS -->

        <a href="<?php echo $base_url; ?>/reports/index.php">

            📊

            <span>
                Reports
            </span>

        </a>


        <hr class="text-secondary">


        <!-- LOGOUT -->

        <a href="<?php echo $logout_url; ?>">

            🚪

            <span>
                Logout
            </span>

        </a>

    </div>

</div>