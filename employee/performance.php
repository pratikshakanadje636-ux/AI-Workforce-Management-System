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
   GET EMPLOYEE INFORMATION
========================================================= */

$sql = "
    SELECT
        employees.employee_id,
        employees.full_name,
        employees.employee_code,
        employees.designation,
        employees.performance_score,
        employees.workload,
        employees.profile_picture,
        departments.department_name

    FROM employees

    LEFT JOIN departments
        ON employees.department_id = departments.department_id

    WHERE employees.user_id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows != 1) {
    die("Employee profile not found.");
}


$employee = $result->fetch_assoc();

$employee_id = $employee['employee_id'];

$profile_picture =
    trim(
        $employee['profile_picture'] ?? ''
    );


/* =========================================================
   TASK STATISTICS
========================================================= */

$sql = "
    SELECT

        COUNT(*) AS total_tasks,

        SUM(
            CASE
                WHEN status = 'Completed'
                THEN 1
                ELSE 0
            END
        ) AS completed_tasks,

        SUM(
            CASE
                WHEN status = 'Pending'
                THEN 1
                ELSE 0
            END
        ) AS pending_tasks,

        SUM(
            CASE
                WHEN status = 'In Progress'
                THEN 1
                ELSE 0
            END
        ) AS in_progress_tasks

    FROM tasks

    WHERE employee_id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $employee_id);

$stmt->execute();

$stats = $stmt->get_result()->fetch_assoc();


$total_tasks =
    $stats['total_tasks'] ?? 0;

$completed_tasks =
    $stats['completed_tasks'] ?? 0;

$pending_tasks =
    $stats['pending_tasks'] ?? 0;

$in_progress_tasks =
    $stats['in_progress_tasks'] ?? 0;


/* =========================================================
   COMPLETION RATE
========================================================= */

if ($total_tasks > 0) {

    $completion_rate =
        ($completed_tasks / $total_tasks) * 100;

} else {

    $completion_rate = 0;

}

$completion_rate = round($completion_rate);


/* =========================================================
   PERFORMANCE SCORE
========================================================= */

$performance_score =
    (float)$employee['performance_score'];

$performance_score = min(
    100,
    max(
        0,
        $performance_score
    )
);


/* =========================================================
   PERFORMANCE MESSAGE
========================================================= */

if ($performance_score >= 90) {

    $performance_message =
        "Excellent performance! Keep up the great work.";

    $performance_color = "success";

}

elseif ($performance_score >= 75) {

    $performance_message =
        "Great work! You are performing very well.";

    $performance_color = "primary";

}

elseif ($performance_score >= 50) {

    $performance_message =
        "Good progress. Keep improving your productivity.";

    $performance_color = "warning";

}

else {

    $performance_message =
        "Focus on completing your tasks and improving consistency.";

    $performance_color = "danger";

}

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
    Performance | AI Workforce
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
        1px solid rgba(255,255,255,0.12);

    margin-bottom: 25px;

}


/* =========================================================
   BRAND ICON / LOGO
========================================================= */

.brand-icon {

    width: 45px;

    height: 45px;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 0 !important;

    margin: 0 !important;

    background: transparent !important;

    background-color: transparent !important;

    border: none !important;

    box-shadow: none !important;

    border-radius: 0 !important;

    font-size: 0;

    flex-shrink: 0;

    overflow: visible;

}


.brand-icon img {

    width: 38px;

    height: 38px;

    display: block;

    object-fit: contain;

    background: transparent !important;

    border: none !important;

    box-shadow: none !important;

}


/* =========================================================
   BRAND TEXT
========================================================= */

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


.avatar {

    width: 42px;

    height: 42px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    overflow: hidden;

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


.avatar img {

    width: 100%;

    height: 100%;

    border-radius: 50%;

    object-fit: cover;

    display: block;

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
   PERFORMANCE HERO
========================================================= */

.performance-hero {

    background:
        linear-gradient(
            120deg,
            #312e81,
            #6d28d9,
            #4338ca
        );

    color: white;

    border-radius: 20px;

    padding: 35px;

    margin-bottom: 30px;

    position: relative;

    overflow: hidden;

    box-shadow:
        0 15px 40px rgba(49,46,129,0.25);

}


.performance-hero::before {

    content: "";

    position: absolute;

    width: 280px;

    height: 280px;

    border-radius: 50%;

    background:
        rgba(255,255,255,0.06);

    right: -90px;

    top: -130px;

}


.performance-hero::after {

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


.performance-hero > * {

    position: relative;

    z-index: 2;

}


.performance-hero h3 {

    font-weight: 750;

    margin-bottom: 10px;

}


.performance-hero p {

    color: #ddd6fe;

    margin-bottom: 18px;

}


/* =========================================================
   SCORE CIRCLE
========================================================= */

.score-circle {

    width: 145px;

    height: 145px;

    border-radius: 50%;

    border:
        10px solid rgba(255,255,255,0.20);

    display: flex;

    align-items: center;

    justify-content: center;

    margin: auto;

    background:
        rgba(255,255,255,0.08);

    box-shadow:
        inset 0 0 30px rgba(255,255,255,0.05),
        0 0 30px rgba(0,0,0,0.15);

}


.score-circle span {

    font-size: 38px;

    font-weight: 800;

    color: #ffffff;

}


/* =========================================================
   STAT CARDS
========================================================= */

.stat-card {

    background:
        linear-gradient(
            145deg,
            #151a2e,
            #0d1120
        );

    border-radius: 17px;

    padding: 22px;

    border:
        1px solid rgba(139,92,246,0.12);

    box-shadow:
        0 10px 30px rgba(0,0,0,0.30);

    height: 100%;

    transition: all 0.25s ease;

}


.stat-card:hover {

    transform: translateY(-5px);

    border-color:
        rgba(139,92,246,0.35);

    box-shadow:
        0 15px 35px rgba(124,58,237,0.16);

}


.stat-icon {

    width: 48px;

    height: 48px;

    border-radius: 13px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 21px;

    margin-bottom: 14px;

}


.blue {

    background:
        rgba(59,130,246,0.13);

    color: #60a5fa;

}


.green {

    background:
        rgba(16,185,129,0.13);

    color: #34d399;

}


.orange {

    background:
        rgba(245,158,11,0.13);

    color: #fbbf24;

}


.purple {

    background:
        rgba(124,58,237,0.13);

    color: #a78bfa;

}


.stat-number {

    font-size: 29px;

    font-weight: 750;

    color: #f8fafc;

}


.stat-label {

    color: #94a3b8;

    font-size: 13px;

}


/* =========================================================
   DASHBOARD CARDS
========================================================= */

.dashboard-card {

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

    margin-right: 5px;

}


.card-body-custom {

    padding: 25px;

}


/* =========================================================
   PROGRESS
========================================================= */

.progress {

    height: 14px;

    background:
        #1c2335;

    border-radius: 20px;

    overflow: hidden;

}


.progress-bar {

    border-radius: 20px;

}


.bg-success {

    background:
        #10b981 !important;

}


.bg-primary {

    background:
        #6366f1 !important;

}


.bg-warning {

    background:
        #f59e0b !important;

}


.bg-danger {

    background:
        #ef4444 !important;

}


/* =========================================================
   INFO ROW
========================================================= */

.info-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 13px 0;

    border-bottom:
        1px solid rgba(255,255,255,0.06);

}


.info-row:last-child {

    border-bottom: none;

}


.info-label {

    color: #8993a8;

}


.info-row strong {

    color: #f1f5f9;

}


.text-muted {

    color: #8993a8 !important;

}


.text-success {

    color: #34d399 !important;

}


.text-warning {

    color: #fbbf24 !important;

}


.text-primary {

    color: #a78bfa !important;

}


/* =========================================================
   HERO BADGE
========================================================= */

.performance-hero .badge {

    background:
        rgba(255,255,255,0.12) !important;

    color: #ffffff !important;

    border:
        1px solid rgba(255,255,255,0.15);

    padding: 7px 11px;

}


/* =========================================================
   FOOTER
========================================================= */

footer,
.text-center .text-muted {

    color: #68738a !important;

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


    .performance-hero {

        padding: 25px;

    }


    .score-circle {

        width: 125px;

        height: 125px;

    }


    .score-circle span {

        font-size: 32px;

    }

}

</style>

</head>


<body>


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

            <h5>
                AI Workforce
            </h5>

            <small>
                Employee Portal
            </small>

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
        class="nav-link-custom active"
    >

        <i class="bi bi-bar-chart-fill"></i>

        <span class="nav-text">
            Performance
        </span>

    </a>


    <!-- PROFILE -->

    <a
        href="profile.php"
        class="nav-link-custom"
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

            <i class="bi bi-bar-chart-fill"></i>

            Performance

        </div>


        <div class="user-area">

            <div class="text-end d-none d-sm-block">

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $employee['full_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    ?>

                </strong>


                <div class="user-designation">

                    <?php

                    echo htmlspecialchars(
                        $employee['designation'] ?? 'Employee',
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    ?>

                </div>

            </div>


            <div class="avatar">

                <?php if (!empty($profile_picture)): ?>

                    <img
                        src="../assets/images/profiles/<?php echo htmlspecialchars($profile_picture, ENT_QUOTES, 'UTF-8'); ?>"
                        alt="Profile Picture"
                    >

                <?php else: ?>

                    <?php

                    echo strtoupper(
                        substr(
                            $employee['full_name'],
                            0,
                            1
                        )
                    );

                    ?>

                <?php endif; ?>

            </div>

        </div>

    </header>



    <!-- CONTENT -->

    <main class="content">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <h2>

                My Performance 📊

            </h2>


            <p>

                Track your productivity and task completion.

            </p>

        </div>



        <!-- =================================================
             PERFORMANCE HERO
        ================================================= -->

        <div class="performance-hero">

            <div class="row align-items-center">

                <div class="col-md-8">

                    <h3>

                        Hello,

                        <?php

                        echo htmlspecialchars(
                            $employee['full_name']
                        );

                        ?>

                        👋

                    </h3>


                    <p>

                        <?php

                        echo htmlspecialchars(
                            $performance_message
                        );

                        ?>

                    </p>


                    <span class="badge">

                        <?php

                        echo htmlspecialchars(
                            $employee['designation']
                        );

                        ?>

                    </span>

                </div>


                <div class="col-md-4 text-center mt-4 mt-md-0">

                    <div class="score-circle">

                        <span>

                            <?php

                            echo $performance_score;

                            ?>%

                        </span>

                    </div>


                    <div class="mt-2">

                        Overall Performance

                    </div>

                </div>

            </div>

        </div>



        <!-- =================================================
             STATISTICS
        ================================================= -->

        <div class="row g-4">


            <!-- TOTAL -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon blue">

                        <i class="bi bi-clipboard-check"></i>

                    </div>


                    <div class="stat-number">

                        <?php

                        echo $total_tasks;

                        ?>

                    </div>


                    <div class="stat-label">

                        Total Tasks

                    </div>

                </div>

            </div>



            <!-- COMPLETED -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon green">

                        <i class="bi bi-check-circle-fill"></i>

                    </div>


                    <div class="stat-number">

                        <?php

                        echo $completed_tasks;

                        ?>

                    </div>


                    <div class="stat-label">

                        Completed Tasks

                    </div>

                </div>

            </div>



            <!-- PENDING -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon orange">

                        <i class="bi bi-clock-fill"></i>

                    </div>


                    <div class="stat-number">

                        <?php

                        echo $pending_tasks;

                        ?>

                    </div>


                    <div class="stat-label">

                        Pending Tasks

                    </div>

                </div>

            </div>



            <!-- IN PROGRESS -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon purple">

                        <i class="bi bi-arrow-repeat"></i>

                    </div>


                    <div class="stat-number">

                        <?php

                        echo $in_progress_tasks;

                        ?>

                    </div>


                    <div class="stat-label">

                        In Progress

                    </div>

                </div>

            </div>

        </div>



        <!-- =================================================
             PERFORMANCE DETAILS
        ================================================= -->

        <div class="row g-4 mt-1">


            <!-- TASK COMPLETION -->

            <div class="col-lg-6">

                <div class="dashboard-card">

                    <div class="card-heading">

                        <h5>

                            <i class="bi bi-check2-circle text-success"></i>

                            Task Completion

                        </h5>

                    </div>


                    <div class="card-body-custom">

                        <div class="d-flex justify-content-between mb-2">

                            <span class="text-muted">

                                Completion Rate

                            </span>


                            <strong>

                                <?php

                                echo $completion_rate;

                                ?>%

                            </strong>

                        </div>


                        <div class="progress mb-4">

                            <div
                                class="progress-bar bg-success"
                                style="
                                width:
                                <?php
                                echo $completion_rate;
                                ?>%;
                                "
                            ></div>

                        </div>


                        <div class="info-row">

                            <span class="info-label">

                                Total Tasks

                            </span>


                            <strong>

                                <?php

                                echo $total_tasks;

                                ?>

                            </strong>

                        </div>


                        <div class="info-row">

                            <span class="info-label">

                                Completed

                            </span>


                            <strong class="text-success">

                                <?php

                                echo $completed_tasks;

                                ?>

                            </strong>

                        </div>


                        <div class="info-row">

                            <span class="info-label">

                                Remaining

                            </span>


                            <strong class="text-warning">

                                <?php

                                echo
                                    $pending_tasks +
                                    $in_progress_tasks;

                                ?>

                            </strong>

                        </div>

                    </div>

                </div>

            </div>



            <!-- PERFORMANCE SCORE -->

            <div class="col-lg-6">

                <div class="dashboard-card">

                    <div class="card-heading">

                        <h5>

                            <i class="bi bi-speedometer2 text-primary"></i>

                            Performance Score

                        </h5>

                    </div>


                    <div class="card-body-custom">

                        <div class="d-flex justify-content-between mb-2">

                            <span class="text-muted">

                                Overall Score

                            </span>


                            <strong>

                                <?php

                                echo $performance_score;

                                ?>%

                            </strong>

                        </div>


                        <div class="progress mb-4">

                            <div
                                class="
                                progress-bar
                                bg-<?php
                                echo $performance_color;
                                ?>
                                "
                                style="
                                width:
                                <?php
                                echo $performance_score;
                                ?>%;
                                "
                            ></div>

                        </div>


                        <div class="info-row">

                            <span class="info-label">

                                Employee

                            </span>


                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $employee['full_name']
                                );

                                ?>

                            </strong>

                        </div>


                        <div class="info-row">

                            <span class="info-label">

                                Department

                            </span>


                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $employee['department_name']
                                    ?? 'Not Assigned'
                                );

                                ?>

                            </strong>

                        </div>


                        <div class="info-row">

                            <span class="info-label">

                                Workload

                            </span>


                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $employee['workload']
                                    ?? 'Not Assigned'
                                );

                                ?>

                            </strong>

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