<?php

session_start();

require_once "../config/database.php";


/* =========================================================
   EMPLOYEE LOGIN CHECK
   ========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['role_id'] != 3
) {
    header("Location: ../authentication/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


/* =========================================================
   GET EMPLOYEE PROFILE
   ========================================================= */

$sql = "
    SELECT
        employees.employee_id,
        employees.user_id,
        employees.employee_code,
        employees.full_name,
        employees.gender,
        employees.phone,
        employees.designation,
        employees.department_id,
        employees.joining_date,
        employees.salary,
        employees.created_at,
        employees.performance_score,
        employees.workload,
        employees.completed_projects,
        departments.department_name,
        users.email,
        users.status AS account_status

    FROM employees

    LEFT JOIN departments
        ON employees.department_id = departments.department_id

    LEFT JOIN users
        ON employees.user_id = users.user_id

    WHERE employees.user_id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Employee profile not found.");
}

$employee = $result->fetch_assoc();


/* =========================================================
   INITIAL
   ========================================================= */

$initial = strtoupper(
    substr(
        $employee['full_name'],
        0,
        1
    )
);


/* =========================================================
   PERFORMANCE SCORE
   ========================================================= */

$performance_score =
    (float)($employee['performance_score'] ?? 0);

$performance_score = min(
    100,
    max(
        0,
        $performance_score
    )
);


/* =========================================================
   ACCOUNT STATUS
   ========================================================= */

$account_status =
    strtolower(
        trim(
            $employee['account_status'] ?? 'inactive'
        )
    );

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


<style>

/* =========================================================
   GLOBAL
   ========================================================= */

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family:
        "Segoe UI",
        Tahoma,
        Geneva,
        Verdana,
        sans-serif;

    background: #080b14;

    color: #e5e7eb;

}


/* =========================================================
   SIDEBAR
   ========================================================= */

.sidebar {

    position: fixed;

    left: 0;

    top: 0;

    width: 250px;

    height: 100vh;

    background:
        linear-gradient(
            180deg,
            #0b0d18 0%,
            #111329 55%,
            #0d1020 100%
        );

    color: white;

    padding: 25px 18px;

    z-index: 1000;

    border-right:
        1px solid rgba(139,92,246,0.15);

    box-shadow:
        8px 0 30px rgba(0,0,0,0.25);

}


/* =========================================================
   BRAND
   ========================================================= */

.brand {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 10px 12px 30px;

    border-bottom:
        1px solid rgba(255,255,255,0.08);

    margin-bottom: 25px;

}


.brand {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 10px 12px 30px;

    border-bottom:
    1px solid rgba(255,255,255,0.12);

    margin-bottom: 25px;
}


.brand-icon {
    
    width: 45px;
    height: 45px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 0;

    background: transparent !important;

    padding: 0;

    flex-shrink: 0;
}

.brand-icon img {
    width: 38px;
    height: 38px;

    object-fit: contain;

    display: block;
}

.brand h5 {

    margin: 0;

    font-weight: 700;

    color: #ffffff;

}


.brand small {

    color: #94a3b8;

}


/* =========================================================
   NAVIGATION
   ========================================================= */

.nav-link-custom {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 13px 15px;

    margin-bottom: 7px;

    border-radius: 10px;

    color: #9ca3af;

    text-decoration: none;

    transition: all 0.25s ease;

}


.nav-link-custom:hover {

    background:
        rgba(124,58,237,0.12);

    color: #ffffff;

    transform: translateX(4px);

}


.nav-link-custom.active {

    background:
        linear-gradient(
            90deg,
            #7c3aed,
            #4f46e5
        );

    color: #ffffff;

    box-shadow:
        0 6px 20px rgba(124,58,237,0.25);

}


.nav-link-custom i {

    font-size: 19px;

}


/* =========================================================
   MAIN
   ========================================================= */

.main {

    margin-left: 250px;

    min-height: 100vh;

    background: #080b14;

}


/* =========================================================
   TOPBAR
   ========================================================= */

.topbar {

    height: 75px;

    background:
        rgba(10,13,25,0.94);

    border-bottom:
        1px solid rgba(255,255,255,0.07);

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 35px;

    position: sticky;

    top: 0;

    z-index: 500;

    backdrop-filter: blur(12px);

}


.portal-title {

    font-weight: 700;

    color: #f8fafc;

}


.portal-title i {

    color: #8b5cf6 !important;

}


.user-area {

    display: flex;

    align-items: center;

    gap: 14px;

}


.user-area strong {

    color: #f8fafc;

}


.user-designation {

    font-size: 12px;

    color: #8993a8;

}


.avatar-small {

    width: 42px;

    height: 42px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        linear-gradient(
            135deg,
            #7c3aed,
            #2563eb
        );

    color: white;

    font-weight: 700;

    box-shadow:
        0 0 18px rgba(124,58,237,0.25);

}


/* =========================================================
   CONTENT
   ========================================================= */

.content {

    padding: 35px;

}


/* =========================================================
   PAGE HEADER
   ========================================================= */

.page-header {

    margin-bottom: 30px;

}


.page-header h2 {

    font-weight: 750;

    margin-bottom: 6px;

    color: #f8fafc;

}


.page-header p {

    color: #8993a8;

    margin: 0;

}


/* =========================================================
   PROFILE HERO
   ========================================================= */

.profile-hero {

    background:
        linear-gradient(
            120deg,
            #312e81,
            #6d28d9,
            #4338ca
        );

    color: white;

    border-radius: 22px;

    padding: 35px;

    margin-bottom: 30px;

    position: relative;

    overflow: hidden;

    box-shadow:
        0 15px 40px rgba(49,46,129,0.25);

}


.profile-hero::before {

    content: "";

    position: absolute;

    width: 300px;

    height: 300px;

    border-radius: 50%;

    background:
        rgba(255,255,255,0.06);

    right: -100px;

    top: -140px;

}


.profile-hero::after {

    content: "";

    position: absolute;

    width: 180px;

    height: 180px;

    border-radius: 50%;

    background:
        rgba(255,255,255,0.04);

    left: 40%;

    bottom: -130px;

}


.profile-hero > * {

    position: relative;

    z-index: 2;

}


/* =========================================================
   PROFILE AVATAR
   ========================================================= */

.profile-avatar {

    width: 110px;

    height: 110px;

    border-radius: 50%;

    background:
        linear-gradient(
            135deg,
            #ffffff,
            #e0e7ff
        );

    color: #5b21b6;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 43px;

    font-weight: 800;

    box-shadow:
        0 10px 35px rgba(0,0,0,0.20);

    border:
        5px solid rgba(255,255,255,0.18);

}


.profile-hero h3 {

    font-weight: 750;

    margin-bottom: 8px;

}


.profile-hero p {

    color: #ddd6fe;

    margin-bottom: 15px;

}


/* =========================================================
   STATUS BADGES
   ========================================================= */

.status-active {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    background:
        rgba(16,185,129,0.18);

    color: #6ee7b7;

    border:
        1px solid rgba(52,211,153,0.25);

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;

}


.status-inactive {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    background:
        rgba(239,68,68,0.18);

    color: #fca5a5;

    border:
        1px solid rgba(239,68,68,0.25);

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;

}


/* =========================================================
   HERO SCORE
   ========================================================= */

.hero-score-label {

    font-size: 13px;

    color: #ddd6fe;

}


.hero-score {

    font-size: 40px;

    font-weight: 800;

    color: #ffffff;

}


/* =========================================================
   PROFILE CARDS
   ========================================================= */

.profile-card {

    background:
        linear-gradient(
            145deg,
            #111525,
            #0d1120
        );

    border:
        1px solid rgba(255,255,255,0.06);

    border-radius: 18px;

    box-shadow:
        0 10px 30px rgba(0,0,0,0.25);

    height: 100%;

    overflow: hidden;

}


.card-heading {

    padding: 22px 24px;

    border-bottom:
        1px solid rgba(255,255,255,0.06);

}


.card-heading h5 {

    margin: 0;

    font-weight: 700;

    color: #f8fafc;

}


.card-heading h5 i {

    margin-right: 6px;

}


.card-body-custom {

    padding: 25px;

}


/* =========================================================
   INFORMATION ROWS
   ========================================================= */

.info-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 15px 0;

    border-bottom:
        1px solid rgba(255,255,255,0.06);

}


.info-row:last-child {

    border-bottom: none;

}


.info-label {

    color: #8993a8;

    display: flex;

    align-items: center;

    gap: 9px;

}


.info-label i {

    color: #a78bfa;

    font-size: 17px;

}


.info-value {

    font-weight: 600;

    color: #f1f5f9;

    text-align: right;

    word-break: break-word;

}


/* =========================================================
   PERFORMANCE
   ========================================================= */

.score-box {

    text-align: center;

    padding: 15px;

}


.score-number {

    font-size: 45px;

    font-weight: 800;

    color: #a78bfa;

}


.progress {

    height: 12px;

    border-radius: 20px;

    background:
        #1c2335;

    overflow: hidden;

}


.progress-bar {

    border-radius: 20px;

}


.bg-primary {

    background:
        linear-gradient(
            90deg,
            #7c3aed,
            #6366f1
        ) !important;

}


/* =========================================================
   BUTTON
   ========================================================= */

.btn-outline-primary {

    color: #a78bfa;

    border-color: #7c3aed;

    border-radius: 10px;

    padding: 9px 17px;

    transition: all 0.25s ease;

}


.btn-outline-primary:hover {

    color: white;

    background:
        linear-gradient(
            90deg,
            #7c3aed,
            #4f46e5
        );

    border-color: #7c3aed;

    box-shadow:
        0 8px 20px rgba(124,58,237,0.25);

}


/* =========================================================
   TEXT
   ========================================================= */

.text-muted {

    color: #8993a8 !important;

}


.text-primary {

    color: #a78bfa !important;

}


/* =========================================================
   SCROLLBAR
   ========================================================= */

::-webkit-scrollbar {

    width: 8px;

}


::-webkit-scrollbar-track {

    background: #080b14;

}


::-webkit-scrollbar-thumb {

    background: #292442;

    border-radius: 10px;

}


::-webkit-scrollbar-thumb:hover {

    background: #6d28d9;

}


/* =========================================================
   TABLET
   ========================================================= */

@media (max-width: 991px) {

    .sidebar {

        width: 80px;

        padding: 20px 10px;

    }


    .brand-text,
    .nav-text {

        display: none;

    }


    .brand {

        justify-content: center;

    }


    .nav-link-custom {

        justify-content: center;

    }


    .main {

        margin-left: 80px;

    }

}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767px) {

    .sidebar {

        display: none;

    }


    .main {

        margin-left: 0;

    }


    .content {

        padding: 20px;

    }


    .topbar {

        padding: 0 20px;

    }


    .page-header h2 {

        font-size: 24px;

    }


    .profile-hero {

        padding: 25px;

        text-align: center;

    }


    .profile-avatar {

        margin:
            0 auto 20px;

    }


    .info-row {

        flex-direction: column;

        align-items: flex-start;

        gap: 6px;

    }


    .info-value {

        text-align: left;

    }

}

</style>

</head>


<body>
<?php include "../config/page_actions.php"; ?>

<!-- =====================================================
     SIDEBAR
     ===================================================== -->

<aside class="sidebar">


     <div class="brand">

    <div class="brand-icon">

        <img
            src="../assets/images/logo/logo-sidebar.png"
            alt="AI Workforce"
        >

    </div>

    <div class="brand-text">

        <h5>AI Workforce</h5>

        <small>Employee Portal</small>

    </div>

</div>
    </div>


    <!-- DASHBOARD -->

    <a
        href="dashboard.php"
        class="nav-link-custom"
    >

        <i class="bi bi-grid-1x2-fill"></i>

        <span class="nav-text">
            Dashboard
        </span>

    </a>


    <!-- TASKS -->

    <a
        href="tasks.php"
        class="nav-link-custom"
    >

        <i class="bi bi-list-task"></i>

        <span class="nav-text">
            My Tasks
        </span>

    </a>


    <!-- PERFORMANCE -->

    <a
        href="performance.php"
        class="nav-link-custom"
    >

        <i class="bi bi-bar-chart-fill"></i>

        <span class="nav-text">
            Performance
        </span>

    </a>


    <!-- PROFILE -->

    <a
        href="profile.php"
        class="nav-link-custom active"
    >

        <i class="bi bi-person-fill"></i>

        <span class="nav-text">
            My Profile
        </span>

    </a>


    <div style="height:45%;"></div>


    <!-- LOGOUT -->

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



<!-- =====================================================
     MAIN
     ===================================================== -->

<div class="main">


    <!-- TOPBAR -->

    <header class="topbar">


        <div class="portal-title">

            <i class="bi bi-person-circle"></i>

            My Profile

        </div>


        <div class="user-area">


            <div class="text-end d-none d-sm-block">

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $employee['full_name']
                    );

                    ?>

                </strong>


                <div class="user-designation">

                    <?php

                    echo htmlspecialchars(
                        $employee['designation']
                    );

                    ?>

                </div>

            </div>


            <div class="avatar-small">

                <?php

                echo $initial;

                ?>

            </div>


        </div>


    </header>



    <!-- CONTENT -->

    <main class="content">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <h2>

                My Profile 👤

            </h2>


            <p>

                View your employee information and account details.

            </p>

        </div>



        <!-- =================================================
             PROFILE HERO
             ================================================= -->

        <div class="profile-hero">


            <div class="row align-items-center">


                <!-- AVATAR -->

                <div class="col-md-2 text-center text-md-start">

                    <div class="profile-avatar">

                        <?php

                        echo $initial;

                        ?>

                    </div>

                </div>



                <!-- EMPLOYEE -->

                <div class="col-md-7 mt-4 mt-md-0">


                    <h3>

                        <?php

                        echo htmlspecialchars(
                            $employee['full_name']
                        );

                        ?>

                    </h3>


                    <p>

                        <?php

                        echo htmlspecialchars(
                            $employee['designation']
                            ?? 'Employee'
                        );

                        ?>

                        &nbsp;•&nbsp;

                        <?php

                        echo htmlspecialchars(
                            $employee['department_name']
                            ?? 'Not Assigned'
                        );

                        ?>

                    </p>


                    <?php

                    if ($account_status === 'active') {

                    ?>

                        <span class="status-active">

                            <i class="bi bi-check-circle-fill"></i>

                            Active Employee

                        </span>

                    <?php

                    } else {

                    ?>

                        <span class="status-inactive">

                            <i class="bi bi-x-circle-fill"></i>

                            Inactive

                        </span>

                    <?php

                    ?>


                    <?php

                    }

                    ?>


                </div>



                <!-- SCORE -->

                <div class="col-md-3 mt-4 mt-md-0">


                    <div class="text-center">


                        <div class="hero-score-label">

                            Performance Score

                        </div>


                        <div class="hero-score">

                            <?php

                            echo $performance_score;

                            ?>%

                        </div>


                    </div>


                </div>


            </div>


        </div>



        <!-- =================================================
             INFORMATION CARDS
             ================================================= -->

        <div class="row g-4">


            <!-- PERSONAL INFORMATION -->

            <div class="col-lg-7">


                <div class="profile-card">


                    <div class="card-heading">

                        <h5>

                            <i class="bi bi-person-vcard text-primary"></i>

                            Personal Information

                        </h5>

                    </div>


                    <div class="card-body-custom">


                        <!-- NAME -->

                        <div class="info-row">


                            <div class="info-label">

                                <i class="bi bi-person"></i>

                                Full Name

                            </div>


                            <div class="info-value">

                                <?php

                                echo htmlspecialchars(
                                    $employee['full_name']
                                );

                                ?>

                            </div>


                        </div>



                        <!-- EMPLOYEE CODE -->

                        <div class="info-row">


                            <div class="info-label">

                                <i class="bi bi-hash"></i>

                                Employee ID

                            </div>


                            <div class="info-value">

                                <?php

                                echo htmlspecialchars(
                                    $employee['employee_code']
                                    ?? $employee['employee_id']
                                );

                                ?>

                            </div>


                        </div>



                        <!-- EMAIL -->

                        <div class="info-row">


                            <div class="info-label">

                                <i class="bi bi-envelope"></i>

                                Email

                            </div>


                            <div class="info-value">

                                <?php

                                echo htmlspecialchars(
                                    $employee['email']
                                    ?? 'Not provided'
                                );

                                ?>

                            </div>


                        </div>



                        <!-- PHONE -->

                        <div class="info-row">


                            <div class="info-label">

                                <i class="bi bi-telephone"></i>

                                Phone

                            </div>


                            <div class="info-value">

                                <?php

                                echo htmlspecialchars(
                                    $employee['phone']
                                    ?? 'Not provided'
                                );

                                ?>

                            </div>


                        </div>



                        <!-- GENDER -->

                        <div class="info-row">


                            <div class="info-label">

                                <i class="bi bi-gender-ambiguous"></i>

                                Gender

                            </div>


                            <div class="info-value">

                                <?php

                                echo htmlspecialchars(
                                    $employee['gender']
                                    ?? 'Not provided'
                                );

                                ?>

                            </div>


                        </div>


                    </div>


                </div>


            </div>



            <!-- WORK INFORMATION -->

            <div class="col-lg-5">


                <div class="profile-card">


                    <div class="card-heading">

                        <h5>

                            <i class="bi bi-briefcase text-primary"></i>

                            Work Information

                        </h5>

                    </div>


                    <div class="card-body-custom">


                        <!-- DESIGNATION -->

                        <div class="info-row">


                            <div class="info-label">

                                <i class="bi bi-person-badge"></i>

                                Designation

                            </div>


                            <div class="info-value">

                                <?php

                                echo htmlspecialchars(
                                    $employee['designation']
                                    ?? 'Not Assigned'
                                );

                                ?>

                            </div>


                        </div>



                        <!-- DEPARTMENT -->

                        <div class="info-row">


                            <div class="info-label">

                                <i class="bi bi-building"></i>

                                Department

                            </div>


                            <div class="info-value">

                                <?php

                                echo htmlspecialchars(
                                    $employee['department_name']
                                    ?? 'Not Assigned'
                                );

                                ?>

                            </div>


                        </div>



                        <!-- WORKLOAD -->

                        <div class="info-row">


                            <div class="info-label">

                                <i class="bi bi-speedometer2"></i>

                                Workload

                            </div>


                            <div class="info-value">

                                <?php

                                echo htmlspecialchars(
                                    $employee['workload']
                                    ?? 'Not Assigned'
                                );

                                ?>

                            </div>


                        </div>



                        <!-- STATUS -->

                        <div class="info-row">


                            <div class="info-label">

                                <i class="bi bi-shield-check"></i>

                                Status

                            </div>


                            <div class="info-value">


                                <?php

                                if ($account_status === 'active') {

                                ?>

                                    <span class="status-active">

                                        Active

                                    </span>

                                <?php

                                } else {

                                ?>

                                    <span class="status-inactive">

                                        Inactive

                                    </span>

                                <?php

                                }

                                ?>


                            </div>


                        </div>



                        <!-- JOINING DATE -->

                        <div class="info-row">


                            <div class="info-label">

                                <i class="bi bi-calendar3"></i>

                                Joined

                            </div>


                            <div class="info-value">

                                <?php

                                if (
                                    !empty(
                                        $employee['joining_date']
                                    )
                                ) {

                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $employee['joining_date']
                                        )
                                    );

                                }

                                elseif (
                                    !empty(
                                        $employee['created_at']
                                    )
                                ) {

                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $employee['created_at']
                                        )
                                    );

                                }

                                else {

                                    echo "Not available";

                                }

                                ?>

                            </div>


                        </div>


                    </div>


                </div>


            </div>


        </div>



        <!-- =================================================
             PERFORMANCE OVERVIEW
             ================================================= -->

        <div class="profile-card mt-4">


            <div class="card-heading">

                <h5>

                    <i class="bi bi-graph-up-arrow text-primary"></i>

                    Performance Overview

                </h5>

            </div>


            <div class="card-body-custom">


                <div class="d-flex justify-content-between mb-2">


                    <span class="text-muted">

                        Overall Performance

                    </span>


                    <strong>

                        <?php

                        echo $performance_score;

                        ?>%

                    </strong>


                </div>


                <div class="progress">


                    <div
                        class="progress-bar bg-primary"
                        style="
                        width:
                        <?php
                        echo $performance_score;
                        ?>%;
                        "
                    ></div>


                </div>


                <div class="text-center mt-4">


                    <a
                        href="performance.php"
                        class="btn btn-outline-primary"
                    >

                        <i class="bi bi-bar-chart"></i>

                        View Detailed Performance

                    </a>


                </div>


            </div>


        </div>



        <!-- =================================================
             ADDITIONAL WORK SUMMARY
             ================================================= -->

        <div class="row g-4 mt-1">


            <div class="col-md-4">


                <div class="profile-card">


                    <div class="card-body-custom text-center">


                        <div
                            style="
                            font-size:32px;
                            color:#a78bfa;
                            "
                        >

                            <i class="bi bi-folder-check"></i>

                        </div>


                        <div
                            style="
                            font-size:28px;
                            font-weight:800;
                            color:#f8fafc;
                            "
                        >

                            <?php

                            echo
                                htmlspecialchars(
                                    $employee['completed_projects']
                                    ?? 0
                                );

                            ?>

                        </div>


                        <div class="text-muted">

                            Completed Projects

                        </div>


                    </div>


                </div>


            </div>



            <div class="col-md-4">


                <div class="profile-card">


                    <div class="card-body-custom text-center">


                        <div
                            style="
                            font-size:32px;
                            color:#60a5fa;
                            "
                        >

                            <i class="bi bi-calendar-check"></i>

                        </div>


                        <div
                            style="
                            font-size:28px;
                            font-weight:800;
                            color:#f8fafc;
                            "
                        >

                            <?php

                            if (
                                !empty(
                                    $employee['joining_date']
                                )
                            ) {

                                echo date(
                                    "Y",
                                    strtotime(
                                        $employee['joining_date']
                                    )
                                );

                            } else {

                                echo "—";

                            }

                            ?>

                        </div>


                        <div class="text-muted">

                            Joining Year

                        </div>


                    </div>


                </div>


            </div>



            <div class="col-md-4">


                <div class="profile-card">


                    <div class="card-body-custom text-center">


                        <div
                            style="
                            font-size:32px;
                            color:#34d399;
                            "
                        >

                            <i class="bi bi-person-check"></i>

                        </div>


                        <div
                            style="
                            font-size:28px;
                            font-weight:800;
                            color:#f8fafc;
                            "
                        >

                            <?php

                            echo
                                $account_status === 'active'
                                ? 'Active'
                                : 'Inactive';

                            ?>

                        </div>


                        <div class="text-muted">

                            Account Status

                        </div>


                    </div>


                </div>


            </div>


        </div>



        <!-- FOOTER -->

        <div class="text-center mt-4">


            <small class="text-muted">

                © <?php echo date("Y"); ?>

                AI Workforce Management System

            </small>


        </div>


    </main>


</div>


</body>

</html>