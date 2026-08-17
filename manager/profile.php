<?php

session_start();

require_once "../config/database.php";

/* ===========================
   MANAGER LOGIN CHECK
=========================== */

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['role_id'] != 2
) {
    header("Location: ../authentication/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ===========================
   GET MANAGER PROFILE
=========================== */

$sql = "
SELECT
    users.user_id,
    users.name,
    users.email,
    users.status,
    users.role_id,

    employees.employee_id,
    employees.employee_code,
    employees.full_name,
    employees.gender,
    employees.phone,
    employees.designation,
    employees.department_id,
    employees.joining_date,

    departments.department_name

FROM users

LEFT JOIN employees
    ON users.user_id = employees.user_id

LEFT JOIN departments
    ON employees.department_id = departments.department_id

WHERE users.user_id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows != 1) {
    die("Manager profile not found.");
}

$manager = $result->fetch_assoc();

/* ===========================
   BASIC INFORMATION
=========================== */

$manager_name =
    $manager['full_name']
    ?: $manager['name']
    ?: 'Manager';

$manager_email =
    $manager['email']
    ?: 'Not available';

$manager_initial =
    strtoupper(
        substr($manager_name, 0, 1)
    );

$manager_status =
    $manager['status']
    ?: 'Active';

$employee_code =
    $manager['employee_code']
    ?: 'Not assigned';

$designation =
    $manager['designation']
    ?: 'Manager';

$department =
    $manager['department_name']
    ?: 'Not assigned';

$gender =
    $manager['gender']
    ?: 'Not specified';

$phone =
    $manager['phone']
    ?: 'Not available';

$joining_date =
    $manager['joining_date']
    ?: '';

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
My Profile | AI Workforce
</title>

<!-- Bootstrap -->

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
rel="stylesheet"
>

<!-- Bootstrap Icons -->

<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
>

<link rel="stylesheet" href="manager.css">

</head>

<body>
   
<?php include "../config/page_actions.php"; ?>
<!-- ===========================
     SIDEBAR
=========================== -->

<aside class="sidebar">

    <div class="brand">

    <div class="brand-icon">

        <img
            src="../assets/images/logo/logo-sidebar.png"
            alt="AI Workforce"
        >

    </div>

    <div class="brand-text">

        <h5>
            AI Workforce
        </h5>

        <small>
            Manager Portal
        </small>

    </div>

</div>
        </div>

    </div>


    <a
        href="dashboard.php"
        class="nav-link-custom"
    >

        <i class="bi bi-grid-1x2-fill"></i>

        <span class="nav-text">
            Dashboard
        </span>

    </a>


    <a
        href="team.php"
        class="nav-link-custom"
    >

        <i class="bi bi-people-fill"></i>

        <span class="nav-text">
            My Team
        </span>

    </a>


    <a
        href="tasks.php"
        class="nav-link-custom"
    >

        <i class="bi bi-list-task"></i>

        <span class="nav-text">
            Tasks
        </span>

    </a>


    <a
        href="performance.php"
        class="nav-link-custom"
    >

        <i class="bi bi-bar-chart-fill"></i>

        <span class="nav-text">
            Performance
        </span>

    </a>


    <a
        href="projects.php"
        class="nav-link-custom"
    >

        <i class="bi bi-kanban-fill"></i>

        <span class="nav-text">
            Projects
        </span>

    </a>


    <a
        href="profile.php"
        class="nav-link-custom active"
    >

        <i class="bi bi-person-fill"></i>

        <span class="nav-text">
            My Profile
        </span>

    </a>


    <div style="height: 30%;"></div>


    <a
        href="../authentication/logout.php"
        class="nav-link-custom"
    >

        <i class="bi bi-box-arrow-right"></i>

        <span class="nav-text">
            Logout
        </span>

    </a>

</aside>


<!-- ===========================
     MAIN
=========================== -->

<div class="main">


    <!-- TOPBAR -->

    <header class="topbar">

        <div class="portal-title">

            <i class="bi bi-person-badge-fill text-primary"></i>

            Manager Profile

        </div>


        <div class="user-area">

            <div class="text-end d-none d-sm-block">

                <strong>

                    <?php
                    echo htmlspecialchars($manager_name);
                    ?>

                </strong>

                <div
                    style="
                    font-size:12px;
                    color:#64748b;
                    "
                >
                    Manager
                </div>

            </div>


            <div class="avatar">

                <?php
                echo htmlspecialchars($manager_initial);
                ?>

            </div>

        </div>

    </header>


    <!-- CONTENT -->

    <main class="content">


        <!-- ===========================
             PROFILE HEADER
        =========================== -->

        <div class="profile-header">


            <div class="profile-avatar">

                <?php
                echo htmlspecialchars($manager_initial);
                ?>

            </div>


            <div>

                <h2>

                    <?php
                    echo htmlspecialchars($manager_name);
                    ?>

                </h2>


                <p>

                    <?php
                    echo htmlspecialchars($manager_email);
                    ?>

                </p>


                <span class="profile-role">

                    <i class="bi bi-shield-check"></i>

                    Manager

                </span>

            </div>

        </div>


        <!-- ===========================
             PROFILE INFORMATION
        =========================== -->

        <div class="row g-4">


            <!-- PERSONAL INFORMATION -->

            <div class="col-lg-7">

                <div class="dashboard-card">


                    <div class="card-heading">

                        <h5>

                            <i class="bi bi-person-lines-fill text-primary"></i>

                            Personal Information

                        </h5>

                    </div>


                    <div class="card-body-custom">


                        <!-- NAME -->

                        <div class="info-row">

                            <div class="info-icon">

                                <i class="bi bi-person"></i>

                            </div>

                            <div>

                                <div class="info-label">
                                    Full Name
                                </div>

                                <div class="info-value">

                                    <?php
                                    echo htmlspecialchars($manager_name);
                                    ?>

                                </div>

                            </div>

                        </div>


                        <!-- EMAIL -->

                        <div class="info-row">

                            <div class="info-icon">

                                <i class="bi bi-envelope"></i>

                            </div>

                            <div>

                                <div class="info-label">
                                    Email Address
                                </div>

                                <div class="info-value">

                                    <?php
                                    echo htmlspecialchars($manager_email);
                                    ?>

                                </div>

                            </div>

                        </div>


                        <!-- PHONE -->

                        <div class="info-row">

                            <div class="info-icon">

                                <i class="bi bi-telephone"></i>

                            </div>

                            <div>

                                <div class="info-label">
                                    Phone Number
                                </div>

                                <div class="info-value">

                                    <?php
                                    echo htmlspecialchars($phone);
                                    ?>

                                </div>

                            </div>

                        </div>


                        <!-- GENDER -->

                        <div class="info-row">

                            <div class="info-icon">

                                <i class="bi bi-gender-ambiguous"></i>

                            </div>

                            <div>

                                <div class="info-label">
                                    Gender
                                </div>

                                <div class="info-value">

                                    <?php
                                    echo htmlspecialchars($gender);
                                    ?>

                                </div>

                            </div>

                        </div>


                    </div>

                </div>

            </div>


            <!-- WORK INFORMATION -->

            <div class="col-lg-5">

                <div class="dashboard-card">


                    <div class="card-heading">

                        <h5>

                            <i class="bi bi-briefcase-fill text-primary"></i>

                            Work Information

                        </h5>

                    </div>


                    <div class="card-body-custom">


                        <!-- DESIGNATION -->

                        <div class="info-row">

                            <div class="info-icon">

                                <i class="bi bi-person-workspace"></i>

                            </div>

                            <div>

                                <div class="info-label">
                                    Designation
                                </div>

                                <div class="info-value">

                                    <?php
                                    echo htmlspecialchars($designation);
                                    ?>

                                </div>

                            </div>

                        </div>


                        <!-- DEPARTMENT -->

                        <div class="info-row">

                            <div class="info-icon">

                                <i class="bi bi-building"></i>

                            </div>

                            <div>

                                <div class="info-label">
                                    Department
                                </div>

                                <div class="info-value">

                                    <?php
                                    echo htmlspecialchars($department);
                                    ?>

                                </div>

                            </div>

                        </div>


                        <!-- EMPLOYEE CODE -->

                        <div class="info-row">

                            <div class="info-icon">

                                <i class="bi bi-upc-scan"></i>

                            </div>

                            <div>

                                <div class="info-label">
                                    Employee Code
                                </div>

                                <div class="info-value">

                                    <?php
                                    echo htmlspecialchars($employee_code);
                                    ?>

                                </div>

                            </div>

                        </div>


                        <!-- JOINING DATE -->

                        <div class="info-row">

                            <div class="info-icon">

                                <i class="bi bi-calendar-event"></i>

                            </div>

                            <div>

                                <div class="info-label">
                                    Joining Date
                                </div>

                                <div class="info-value">

                                    <?php

                                    if (!empty($joining_date)) {

                                        echo date(
                                            'd M Y',
                                            strtotime($joining_date)
                                        );

                                    } else {

                                        echo "Not available";

                                    }

                                    ?>

                                </div>

                            </div>

                        </div>


                    </div>

                </div>

            </div>


        </div>


        <!-- ===========================
             ACCOUNT INFORMATION
        =========================== -->

        <div class="dashboard-card mt-4">


            <div class="card-heading">

                <h5>

                    <i class="bi bi-shield-lock-fill text-primary"></i>

                    Account Information

                </h5>

            </div>


            <div class="card-body-custom">


                <div class="row g-3">


                    <div class="col-md-4">

                        <div class="account-box">

                            <i class="bi bi-person-badge"></i>

                            <h6>
                                Account Role
                            </h6>

                            <p>
                                Manager
                            </p>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="account-box">

                            <i class="bi bi-envelope-check"></i>

                            <h6>
                                Login Email
                            </h6>

                            <p>

                                <?php
                                echo htmlspecialchars($manager_email);
                                ?>

                            </p>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="account-box">

                            <i class="bi bi-check-circle"></i>

                            <h6>
                                Account Status
                            </h6>

                            <p>

                                <?php

                                if ($manager_status == 'Active') {

                                    echo '<span class="status-badge status-active">
                                            Active
                                          </span>';

                                } else {

                                    echo '<span class="status-badge status-inactive">
                                            Inactive
                                          </span>';
                                }

                                ?>

                            </p>

                        </div>

                    </div>


                </div>

            </div>

        </div>


    </main>

</div>

</body>

</html>