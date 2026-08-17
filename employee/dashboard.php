<?php

session_start();

require_once "../config/database.php";

/* ===========================
   EMPLOYEE LOGIN CHECK
=========================== */

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../authentication/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../authentication/login.php");
    exit();
}


/* ===========================
   GET LOGGED-IN EMPLOYEE
=========================== */

$sql = "
SELECT
    employees.employee_id,
    employees.full_name,
    employees.employee_code,
    employees.designation,
    employees.phone,
    employees.performance_score,
    employees.workload,
    departments.department_name

FROM employees

LEFT JOIN departments
    ON employees.department_id = departments.department_id

WHERE employees.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$employee_result = $stmt->get_result();

if ($employee_result->num_rows != 1) {
    die("Employee profile not found.");
}

$employee = $employee_result->fetch_assoc();

$employee_id = $employee['employee_id'];


/* ===========================
   TASK SUMMARY
=========================== */

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
            WHEN status != 'Completed'
            THEN 1
            ELSE 0
        END
    ) AS active_tasks

FROM tasks

WHERE employee_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();

$summary = $stmt->get_result()->fetch_assoc();

$total_tasks = $summary['total_tasks'] ?? 0;
$completed_tasks = $summary['completed_tasks'] ?? 0;
$pending_tasks = $summary['pending_tasks'] ?? 0;
$active_tasks = $summary['active_tasks'] ?? 0;


/* ===========================
   TODAY'S COMPLETED TASKS
=========================== */

$sql = "
SELECT COUNT(*) AS today_completed

FROM tasks

WHERE employee_id = ?

AND status = 'Completed'

AND DATE(due_date) = CURDATE()
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();

$today_completed =
    $stmt->get_result()->fetch_assoc()['today_completed'] ?? 0;


/* ===========================
   GET EMPLOYEE TASKS
=========================== */

$sql = "
SELECT

    tasks.task_id,
    tasks.task_title,
    tasks.description,
    tasks.priority,
    tasks.status,
    tasks.start_date,
    tasks.due_date,
    projects.project_name

FROM tasks

LEFT JOIN projects
    ON tasks.project_id = projects.project_id

WHERE tasks.employee_id = ?

ORDER BY

    CASE
        WHEN tasks.status = 'Pending' THEN 1
        ELSE 2
    END,

    tasks.due_date ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();

$tasks = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Employee Dashboard | AI Workforce</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">


<style>

/* ===========================
   GLOBAL
=========================== */

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

    background: #f5f7fb;

    color: #1f2937;
}


/* ===========================
   SIDEBAR
=========================== */

.sidebar {

    position: fixed;

    left: 0;
    top: 0;

    width: 250px;

    height: 100vh;

    background:
    linear-gradient(
        180deg,
        #111827 0%,
        #172554 100%
    );

    color: white;

    padding: 25px 18px;

    z-index: 1000;
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

}

.brand small {

    color: #94a3b8;

}

.nav-link-custom {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 13px 15px;

    margin-bottom: 7px;

    border-radius: 10px;

    color: #cbd5e1;

    text-decoration: none;

    transition: 0.25s;

}

.nav-link-custom:hover,
.nav-link-custom.active {

    background: #2563eb;

    color: white;

    transform: translateX(3px);

}

.nav-link-custom i {

    font-size: 19px;

}


/* ===========================
   MAIN AREA
=========================== */

.main {

    margin-left: 250px;

    min-height: 100vh;

}


/* ===========================
   TOPBAR
=========================== */

.topbar {

    height: 75px;

    background: white;

    border-bottom:
    1px solid #e5e7eb;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 35px;

    position: sticky;

    top: 0;

    z-index: 500;
}

.portal-title {

    font-weight: 700;

    color: #111827;

}

.user-area {

    display: flex;

    align-items: center;

    gap: 14px;

}

.avatar {

    width: 42px;
    height: 42px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #dbeafe;

    color: #1d4ed8;

    font-weight: 700;

}


/* ===========================
   CONTENT
=========================== */

.content {

    padding: 35px;

}


/* ===========================
   WELCOME BANNER
=========================== */

.welcome {

    background:
    linear-gradient(
        120deg,
        #2563eb,
        #4f46e5
    );

    color: white;

    border-radius: 20px;

    padding: 30px;

    margin-bottom: 30px;

    position: relative;

    overflow: hidden;

}

.welcome::after {

    content: "";

    position: absolute;

    width: 220px;
    height: 220px;

    border-radius: 50%;

    background:
    rgba(255,255,255,0.08);

    right: -70px;
    top: -80px;

}

.welcome h2 {

    font-weight: 700;

    margin-bottom: 8px;

}

.welcome p {

    margin: 0;

    color: #dbeafe;

}


/* ===========================
   STAT CARDS
=========================== */

.stat-card {

    background: white;

    border: none;

    border-radius: 17px;

    padding: 22px;

    box-shadow:
    0 5px 20px rgba(15,23,42,0.06);

    transition: 0.25s;

    height: 100%;

}

.stat-card:hover {

    transform: translateY(-5px);

    box-shadow:
    0 12px 30px rgba(15,23,42,0.10);

}

.stat-icon {

    width: 48px;
    height: 48px;

    border-radius: 13px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 22px;

    margin-bottom: 15px;

}

.blue {

    background: #dbeafe;
    color: #2563eb;

}

.green {

    background: #dcfce7;
    color: #16a34a;

}

.orange {

    background: #fef3c7;
    color: #d97706;

}

.red {

    background: #fee2e2;
    color: #dc2626;

}

.stat-number {

    font-size: 30px;

    font-weight: 750;

    margin-bottom: 3px;

}

.stat-label {

    color: #64748b;

    font-size: 14px;

}


/* ===========================
   CONTENT CARDS
=========================== */

.dashboard-card {

    background: white;

    border: none;

    border-radius: 18px;

    box-shadow:
    0 5px 20px rgba(15,23,42,0.06);

    height: 100%;

}

.card-heading {

    padding: 22px 24px;

    border-bottom:
    1px solid #eef2f7;

    display: flex;

    align-items: center;

    justify-content: space-between;

}

.card-heading h5 {

    margin: 0;

    font-weight: 700;

}

.card-body-custom {

    padding: 24px;

}


/* ===========================
   PROFILE
=========================== */

.profile-top {

    display: flex;

    align-items: center;

    gap: 15px;

    margin-bottom: 25px;

}

.profile-avatar {

    width: 65px;
    height: 65px;

    border-radius: 18px;

    background:
    linear-gradient(
        135deg,
        #2563eb,
        #4f46e5
    );

    color: white;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 25px;

    font-weight: 700;

}

.profile-name {

    font-size: 20px;

    font-weight: 700;

}

.profile-role {

    color: #64748b;

    font-size: 14px;

}

.info-row {

    display: flex;

    justify-content: space-between;

    padding: 12px 0;

    border-bottom:
    1px solid #f1f5f9;

}

.info-row:last-child {

    border-bottom: none;

}

.info-label {

    color: #64748b;

}


/* ===========================
   PERFORMANCE
=========================== */

.performance-score {

    font-size: 40px;

    font-weight: 800;

    color: #2563eb;

}

.progress {

    height: 12px;

    border-radius: 20px;

    background: #e5e7eb;

}

.progress-bar {

    border-radius: 20px;

}


/* ===========================
   TASK TABLE
=========================== */

.task-card {

    margin-top: 30px;

}

.table {

    margin: 0;

}

.table thead th {

    background: #f8fafc;

    color: #64748b;

    font-size: 13px;

    text-transform: uppercase;

    letter-spacing: 0.4px;

    border-bottom: 1px solid #e5e7eb;

    padding: 16px;

    white-space: nowrap;

}

.table tbody td {

    padding: 17px 16px;

    border-bottom:
    1px solid #f1f5f9;

}

.task-title {

    font-weight: 650;

    color: #111827;

}

.task-description {

    color: #94a3b8;

    font-size: 12px;

    display: block;

    max-width: 250px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;

}

.badge-custom {

    padding: 7px 11px;

    border-radius: 8px;

    font-weight: 600;

    font-size: 12px;

}


/* ===========================
   EMPTY TASK
=========================== */

.empty-state {

    text-align: center;

    padding: 60px 20px;

}

.empty-icon {

    width: 70px;
    height: 70px;

    border-radius: 50%;

    background: #eff6ff;

    color: #2563eb;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 30px;

    margin: auto auto 20px;

}


/* ===========================
   MOBILE
=========================== */

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

    .portal-title {

        font-size: 16px;

    }

    .welcome {

        padding: 25px;

    }

}
/* =========================================================
   AI WORKFORCE - DARK PURPLE / BLUE THEME
   ========================================================= */

/* ---------- GLOBAL ---------- */

body {
    background: #080b14 !important;
    color: #e5e7eb !important;
}

a {
    color: inherit;
}


/* ---------- SIDEBAR ---------- */

.sidebar {
    background:
        linear-gradient(
            180deg,
            #0b0d18 0%,
            #111329 55%,
            #0d1020 100%
        ) !important;

    border-right: 1px solid rgba(139, 92, 246, 0.15);
    box-shadow: 8px 0 30px rgba(0, 0, 0, 0.25);
}


/* ---------- BRAND ---------- */

.brand {
    border-bottom: 1px solid rgba(255,255,255,0.08) !important;
}

.brand-icon {
    background:
        linear-gradient(
            135deg,
            #7c3aed,
            #4f46e5
        ) !important;

    box-shadow:
        0 0 20px rgba(124, 58, 237, 0.35);
}


/* ---------- SIDEBAR LINKS ---------- */

.nav-link-custom {
    color: #9ca3af !important;
    transition: all 0.25s ease;
}

.nav-link-custom:hover {
    background: rgba(124, 58, 237, 0.12) !important;
    color: #ffffff !important;
    transform: translateX(4px);
}

.nav-link-custom.active {
    background:
        linear-gradient(
            90deg,
            rgba(124, 58, 237, 0.95),
            rgba(79, 70, 229, 0.85)
        ) !important;

    color: #ffffff !important;

    box-shadow:
        0 5px 20px rgba(124, 58, 237, 0.25);
}


/* ---------- MAIN AREA ---------- */

.main {
    background: #080b14 !important;
}


/* ---------- TOPBAR ---------- */

.topbar {
    background: rgba(10, 13, 25, 0.92) !important;

    border-bottom:
        1px solid rgba(255,255,255,0.07) !important;

    backdrop-filter: blur(12px);
}

.portal-title {
    color: #f8fafc !important;
}

.user-area {
    color: #e5e7eb;
}

.avatar {
    background:
        linear-gradient(
            135deg,
            #7c3aed,
            #2563eb
        ) !important;

    color: white !important;

    box-shadow:
        0 0 18px rgba(124, 58, 237, 0.25);
}


/* ---------- CONTENT ---------- */

.content {
    background: #080b14 !important;
}


/* ---------- WELCOME ---------- */

.welcome {
    background:
        radial-gradient(
            circle at 90% 20%,
            rgba(139, 92, 246, 0.30),
            transparent 35%
        ),
        linear-gradient(
            135deg,
            #17142d,
            #11152d 55%,
            #101a35
        ) !important;

    border:
        1px solid rgba(139, 92, 246, 0.18);

    box-shadow:
        0 15px 45px rgba(0,0,0,0.25);
}

.welcome h2 {
    color: #ffffff !important;
}

.welcome p {
    color: #a5b4fc !important;
}


/* ---------- STAT CARDS ---------- */

.stat-card {
    background:
        linear-gradient(
            145deg,
            #111525,
            #0d1120
        ) !important;

    border:
        1px solid rgba(255,255,255,0.06) !important;

    box-shadow:
        0 10px 30px rgba(0,0,0,0.20) !important;
}

.stat-card:hover {
    transform: translateY(-5px);

    border-color:
        rgba(139, 92, 246, 0.30) !important;

    box-shadow:
        0 15px 35px rgba(124,58,237,0.15) !important;
}

.stat-number {
    color: #f8fafc !important;
}

.stat-label {
    color: #8b95aa !important;
}


/* ---------- STAT ICONS ---------- */

.blue {
    background: rgba(59,130,246,0.12) !important;
    color: #60a5fa !important;
}

.green {
    background: rgba(16,185,129,0.12) !important;
    color: #34d399 !important;
}

.orange {
    background: rgba(245,158,11,0.12) !important;
    color: #fbbf24 !important;
}

.red {
    background: rgba(239,68,68,0.12) !important;
    color: #f87171 !important;
}


/* ---------- CONTENT CARDS ---------- */

.dashboard-card {
    background:
        linear-gradient(
            145deg,
            #111525,
            #0d1120
        ) !important;

    border:
        1px solid rgba(255,255,255,0.06) !important;

    box-shadow:
        0 10px 30px rgba(0,0,0,0.20) !important;
}

.card-heading {
    border-bottom:
        1px solid rgba(255,255,255,0.06) !important;
}

.card-heading h5 {
    color: #f8fafc !important;
}

.card-body-custom {
    color: #d1d5db;
}


/* ---------- PROFILE ---------- */

.profile-avatar {
    background:
        linear-gradient(
            135deg,
            #7c3aed,
            #2563eb
        ) !important;

    box-shadow:
        0 0 25px rgba(124,58,237,0.25);
}

.profile-name {
    color: #f8fafc !important;
}

.profile-role {
    color: #8b95aa !important;
}

.info-row {
    border-bottom:
        1px solid rgba(255,255,255,0.06) !important;
}

.info-label {
    color: #8b95aa !important;
}


/* ---------- PERFORMANCE ---------- */

.performance-score {
    color: #a78bfa !important;

    text-shadow:
        0 0 20px rgba(167,139,250,0.25);
}

.progress {
    background: #1b2032 !important;
}

.progress-bar {
    background:
        linear-gradient(
            90deg,
            #7c3aed,
            #3b82f6
        ) !important;

    box-shadow:
        0 0 12px rgba(124,58,237,0.30);
}


/* ---------- TABLE ---------- */

.table {
    color: #d1d5db !important;
}

.table thead th {
    background: #0d1120 !important;

    color: #8993a8 !important;

    border-bottom:
        1px solid rgba(255,255,255,0.07) !important;
}

.table tbody td {
    background: #111525 !important;

    color: #d1d5db !important;

    border-bottom:
        1px solid rgba(255,255,255,0.05) !important;
}

.table tbody tr:hover td {
    background: #151a2c !important;
}

.task-title {
    color: #f8fafc !important;
}

.task-description {
    color: #7f8aa3 !important;
}


/* ---------- BADGES ---------- */

.badge-custom {
    border: 1px solid transparent;
}

.badge-custom.blue {
    background: rgba(59,130,246,0.12) !important;
    color: #60a5fa !important;
}

.badge-custom.green {
    background: rgba(16,185,129,0.12) !important;
    color: #34d399 !important;
}

.badge-custom.orange {
    background: rgba(245,158,11,0.12) !important;
    color: #fbbf24 !important;
}

.badge-custom.red {
    background: rgba(239,68,68,0.12) !important;
    color: #f87171 !important;
}


/* ---------- EMPTY TASK ---------- */

.empty-state {
    color: #d1d5db;
}

.empty-icon {
    background: rgba(124,58,237,0.12) !important;

    color: #a78bfa !important;

    box-shadow:
        0 0 25px rgba(124,58,237,0.12);
}


/* ---------- BUTTONS ---------- */

.btn-primary {
    background:
        linear-gradient(
            135deg,
            #7c3aed,
            #4f46e5
        ) !important;

    border: none !important;

    box-shadow:
        0 6px 18px rgba(124,58,237,0.25);
}

.btn-primary:hover {
    background:
        linear-gradient(
            135deg,
            #8b5cf6,
            #6366f1
        ) !important;

    transform: translateY(-1px);
}


/* ---------- SCROLLBAR ---------- */

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


/* ---------- MOBILE ---------- */

@media (max-width: 767px) {

    body,
    .main,
    .content {
        background: #080b14 !important;
    }

    .topbar {
        background: #0b0f1d !important;
    }

    .welcome {
        background:
            linear-gradient(
                135deg,
                #17142d,
                #11152d
            ) !important;
    }

    .stat-card,
    .dashboard-card {
        background:
            linear-gradient(
                145deg,
                #111525,
                #0d1120
            ) !important;
    }
}
/* ===========================
   EMPLOYEE BRAND
=========================== */

.brand {
    display: flex;

    align-items: center;

    gap: 12px;

    padding: 10px 12px 30px;

    border-bottom:
    1px solid rgba(255,255,255,0.12);

    margin-bottom: 25px;
}


/* LOGO CONTAINER */

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


/* ACTUAL LOGO */

.brand-icon img {
    width: 38px;
    height: 38px;

    display: block;

    object-fit: contain;

    background: transparent !important;

    border: none !important;

    box-shadow: none !important;
}
</style>

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

        <h5>AI Workforce</h5>

        <small>Employee Portal</small>

    </div>

</div>


    <a
        href="dashboard.php"
        class="nav-link-custom active"
    >

        <i class="bi bi-grid-1x2-fill"></i>

        <span class="nav-text">
            Dashboard
        </span>

    </a>


    <a
    href="tasks.php"
    class="nav-link-custom"
>
    <i class="bi bi-list-task"></i>

    <span class="nav-text">
        My Tasks
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
        href="profile.php"
        class="nav-link-custom"
    >

        <i class="bi bi-person-fill"></i>

        <span class="nav-text">
            My Profile
        </span>

    </a>


    <div style="height: 45%;"></div>


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

            Employee Dashboard

        </div>


        <div class="user-area">

            <div class="text-end d-none d-sm-block">

                <strong>
                    <?php echo htmlspecialchars($employee['full_name']); ?>
                </strong>

                <div
                    style="
                    font-size:12px;
                    color:#64748b;
                    "
                >

                    <?php
                    echo htmlspecialchars(
                        $employee['designation']
                    );
                    ?>

                </div>

            </div>


            <div class="avatar">

                <?php

                echo strtoupper(
                    substr(
                        $employee['full_name'],
                        0,
                        1
                    )
                );

                ?>

            </div>

        </div>

    </header>


    <!-- CONTENT -->

    <main class="content">


        <!-- ===========================
             WELCOME
        =========================== -->

        <section class="welcome">

            <h2>

                Welcome,
                <?php
                echo htmlspecialchars(
                    $employee['full_name']
                );
                ?>
                👋

            </h2>

            <p>

                Here's your work overview.
                Stay productive and keep moving forward!

            </p>

        </section>


        <!-- ===========================
             STATISTICS
        =========================== -->

        <div class="row g-4">


            <!-- TOTAL -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon blue">

                        <i class="bi bi-clipboard-check"></i>

                    </div>

                    <div class="stat-number">

                        <?php echo $total_tasks; ?>

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

                        <?php echo $completed_tasks; ?>

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

                        <?php echo $pending_tasks; ?>

                    </div>

                    <div class="stat-label">

                        Pending Tasks

                    </div>

                </div>

            </div>


            <!-- TODAY -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon red">

                        <i class="bi bi-calendar-check-fill"></i>

                    </div>

                    <div class="stat-number">

                        <?php echo $today_completed; ?>

                    </div>

                    <div class="stat-label">

                        Completed Today

                    </div>

                </div>

            </div>


        </div>


        <!-- ===========================
             PROFILE + PERFORMANCE
        =========================== -->

        <div class="row g-4 mt-1">


            <!-- PROFILE -->

            <div
                class="col-lg-6"
                id="profile"
            >

                <div class="dashboard-card">

                    <div class="card-heading">

                        <h5>

                            <i class="bi bi-person-circle text-primary"></i>

                            My Profile

                        </h5>

                    </div>


                    <div class="card-body-custom">


                        <div class="profile-top">

                            <div class="profile-avatar">

                                <?php

                                echo strtoupper(
                                    substr(
                                        $employee['full_name'],
                                        0,
                                        1
                                    )
                                );

                                ?>

                            </div>


                            <div>

                                <div class="profile-name">

                                    <?php
                                    echo htmlspecialchars(
                                        $employee['full_name']
                                    );
                                    ?>

                                </div>

                                <div class="profile-role">

                                    <?php
                                    echo htmlspecialchars(
                                        $employee['designation']
                                    );
                                    ?>

                                </div>

                            </div>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                Employee Code
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $employee['employee_code']
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
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                Phone
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $employee['phone']
                                );
                                ?>
                            </strong>

                        </div>


                    </div>

                </div>

            </div>


            <!-- PERFORMANCE -->

            <div
                class="col-lg-6"
                id="performance"
            >

                <div class="dashboard-card">

                    <div class="card-heading">

                        <h5>

                            <i class="bi bi-graph-up-arrow text-success"></i>

                            My Performance

                        </h5>

                    </div>


                    <div class="card-body-custom">


                        <div class="d-flex justify-content-between align-items-end mb-2">

                            <div>

                                <div class="text-muted small">
                                    Overall Performance
                                </div>

                                <div class="performance-score">

                                    <?php
                                    echo $employee['performance_score'];
                                    ?>%

                                </div>

                            </div>


                            <span class="badge bg-success">

                                Active

                            </span>

                        </div>


                        <div class="progress mb-4">

                            <div
                                class="progress-bar bg-success"
                                role="progressbar"
                                style="
                                width:
                                <?php
                                echo min(
                                    100,
                                    max(
                                        0,
                                        $employee['performance_score']
                                    )
                                );
                                ?>%;
                                "
                            >

                            </div>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                Current Workload
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $employee['workload']
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                Active Tasks
                            </span>

                            <strong>
                                <?php
                                echo $active_tasks;
                                ?>
                            </strong>

                        </div>


                        <div class="info-row">

                            <span class="info-label">
                                Completed Tasks
                            </span>

                            <strong class="text-success">
                                <?php
                                echo $completed_tasks;
                                ?>
                            </strong>

                        </div>


                    </div>

                </div>

            </div>


        </div>


        <!-- ===========================
             TASKS
        =========================== -->

        <div
            class="dashboard-card task-card"
            id="tasks"
        >


            <div class="card-heading">

                <div>

                    <h5>

                        <i class="bi bi-list-check text-primary"></i>

                        My Assigned Tasks

                    </h5>

                    <small class="text-muted">

                        Tasks assigned to you by the administrator

                    </small>

                </div>


                <span class="badge bg-primary">

                    <?php echo $total_tasks; ?> Tasks

                </span>

            </div>


            <div class="card-body-custom p-0">


                <?php if ($tasks->num_rows > 0) { ?>


                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">


                        <thead>

                            <tr>

                                <th>Task</th>

                                <th>Project</th>

                                <th>Priority</th>

                                <th>Status</th>

                                <th>Start Date</th>

                                <th>Due Date</th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while ($task = $tasks->fetch_assoc()) { ?>


                            <tr>


                                <!-- TASK -->

                                <td>

                                    <div class="task-title">

                                        <?php

                                        echo htmlspecialchars(
                                            $task['task_title']
                                        );

                                        ?>

                                    </div>


                                    <span class="task-description">

                                        <?php

                                        echo htmlspecialchars(
                                            $task['description']
                                        );

                                        ?>

                                    </span>

                                </td>


                                <!-- PROJECT -->

                                <td>

                                    <span class="text-muted">

                                        <i class="bi bi-folder2-open"></i>

                                        <?php

                                        echo htmlspecialchars(
                                            $task['project_name']
                                            ?? 'No Project'
                                        );

                                        ?>

                                    </span>

                                </td>


                                <!-- PRIORITY -->

                                <td>


                                <?php

                                $priority_class = "secondary";

                                if (
                                    $task['priority']
                                    == "High"
                                ) {

                                    $priority_class = "danger";

                                }

                                elseif (
                                    $task['priority']
                                    == "Medium"
                                ) {

                                    $priority_class = "warning";

                                }

                                elseif (
                                    $task['priority']
                                    == "Low"
                                ) {

                                    $priority_class = "success";

                                }

                                ?>


                                <span
                                    class="
                                    badge
                                    badge-custom
                                    bg-<?php
                                    echo $priority_class;
                                    ?>
                                    "
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $task['priority']
                                    );

                                    ?>

                                </span>


                                </td>


                                <!-- STATUS -->

<td>

    <form
        action="update_task_status.php"
        method="POST"
    >

        <input
            type="hidden"
            name="task_id"
            value="<?php echo $task['task_id']; ?>"
        >


        <select
            name="status"
            class="form-select form-select-sm"
            onchange="this.form.submit()"
            style="min-width: 130px;"
        >

            <option
                value="Pending"
                <?php
                echo ($task['status'] == 'Pending')
                    ? 'selected'
                    : '';
                ?>
            >
                Pending
            </option>


            <option
                value="In Progress"
                <?php
                echo ($task['status'] == 'In Progress')
                    ? 'selected'
                    : '';
                ?>
            >
                In Progress
            </option>


            <option
                value="Completed"
                <?php
                echo ($task['status'] == 'Completed')
                    ? 'selected'
                    : '';
                ?>
            >
                Completed
            </option>

        </select>

    </form>

</td>

                                <!-- START -->

                                <td>

                                    <small>

                                        <?php

                                        echo htmlspecialchars(
                                            $task['start_date']
                                        );

                                        ?>

                                    </small>

                                </td>


                                <!-- DUE -->

                                <td>

                                    <small>

                                        <?php

                                        echo htmlspecialchars(
                                            $task['due_date']
                                        );

                                        ?>

                                    </small>

                                </td>


                            </tr>


                        <?php } ?>


                        </tbody>

                    </table>

                </div>


                <?php } else { ?>


                    <div class="empty-state">

                        <div class="empty-icon">

                            <i class="bi bi-clipboard-x"></i>

                        </div>


                        <h5>

                            No Tasks Assigned

                        </h5>


                        <p class="text-muted">

                            You currently don't have any assigned tasks.

                        </p>

                    </div>


                <?php } ?>


            </div>

        </div>


        <!-- FOOTER -->

        <div class="text-center mt-4 mb-2">

            <small class="text-muted">

                © <?php echo date("Y"); ?>
                AI Workforce Management System

            </small>

        </div>


    </main>

</div>


</body>

</html>