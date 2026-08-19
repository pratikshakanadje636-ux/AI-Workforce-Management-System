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
   GET EMPLOYEE
========================================================= */

$sql = "
    SELECT
        employees.employee_id,
        employees.full_name,
        employees.designation,
        employees.profile_picture,
        departments.department_name

    FROM employees

    LEFT JOIN departments
        ON employees.department_id = departments.department_id

    WHERE employees.user_id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $user_id
);

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
   GET EMPLOYEE TASKS
========================================================= */

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
            WHEN tasks.status = 'In Progress' THEN 2
            WHEN tasks.status = 'Completed' THEN 3
            ELSE 4
        END,

        tasks.due_date ASC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $employee_id
);

$stmt->execute();

$tasks = $stmt->get_result();


/* =========================================================
   TASK COUNTS
========================================================= */

$total_tasks = 0;
$completed_tasks = 0;
$pending_tasks = 0;
$in_progress_tasks = 0;

$task_rows = [];


while ($task = $tasks->fetch_assoc()) {

    $task_rows[] = $task;

    $total_tasks++;

    if ($task['status'] == 'Completed') {

        $completed_tasks++;

    }

    elseif ($task['status'] == 'Pending') {

        $pending_tasks++;

    }

    elseif ($task['status'] == 'In Progress') {

        $in_progress_tasks++;

    }

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

<title>My Tasks | AI Workforce</title>


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

    object-fit: contain;

    display: block;

    background: transparent !important;

    border: none !important;

    box-shadow: none !important;

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


.user-area div[style] {

    color: #8b95aa !important;

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

    background: #080b14;

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

    padding: 20px;

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


/* =========================================================
   STAT ICONS
========================================================= */

.stat-icon {

    width: 45px;

    height: 45px;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 20px;

    margin-bottom: 13px;

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


/* =========================================================
   STAT TEXT
========================================================= */

.stat-number {

    font-size: 28px;

    font-weight: 750;

    color: #f8fafc;

}


.stat-label {

    color: #94a3b8;

    font-size: 13px;

}


/* =========================================================
   TASK CARD
========================================================= */

.task-card {

    background:
        linear-gradient(
            145deg,
            #111525,
            #0d1120
        );

    border-radius: 18px;

    border:
        1px solid rgba(255,255,255,0.06);

    box-shadow:
        0 10px 30px rgba(0,0,0,0.25);

    margin-top: 30px;

    overflow: hidden;

}


.task-card-header {

    padding: 23px 25px;

    border-bottom:
        1px solid rgba(255,255,255,0.06);

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.task-card-header h5 {

    margin: 0;

    font-weight: 700;

    color: #f8fafc;

}


.task-card-header h5 i {

    color: #8b5cf6 !important;

}


.task-card-header .text-muted {

    color: #8993a8 !important;

}


.task-card-header .badge {

    background:
        rgba(124,58,237,0.15) !important;

    color: #a78bfa !important;

    border:
        1px solid rgba(124,58,237,0.25);

}


/* =========================================================
   TABLE
========================================================= */

.table {

    margin: 0;

    color: #d1d5db;

}


.table thead th {

    background: #0b0f1d;

    color: #8993a8;

    font-size: 12px;

    text-transform: uppercase;

    letter-spacing: 0.4px;

    padding: 16px;

    white-space: nowrap;

    border-bottom:
        1px solid rgba(255,255,255,0.07);

}


.table tbody td {

    padding: 17px 16px;

    vertical-align: middle;

    background: #111525;

    color: #d1d5db;

    border-bottom:
        1px solid rgba(255,255,255,0.05);

}


.table tbody tr:hover td {

    background: #151a2c;

}


/* =========================================================
   TASK TEXT
========================================================= */

.task-title {

    font-weight: 650;

    color: #f8fafc;

}


.task-description {

    display: block;

    color: #7f8aa3;

    font-size: 12px;

    max-width: 260px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;

}


/* =========================================================
   PROJECT
========================================================= */

.table .text-muted {

    color: #9aa5ba !important;

}


.table .text-muted i {

    color: #8b5cf6;

}


/* =========================================================
   PRIORITY BADGES
========================================================= */

.badge {

    border-radius: 7px;

    padding: 6px 9px;

    font-weight: 600;

}


.bg-danger {

    background:
        rgba(239,68,68,0.14) !important;

    color: #f87171 !important;

}


.bg-warning {

    background:
        rgba(245,158,11,0.14) !important;

    color: #fbbf24 !important;

}


.bg-success {

    background:
        rgba(16,185,129,0.14) !important;

    color: #34d399 !important;

}


.bg-secondary {

    background:
        rgba(100,116,139,0.14) !important;

    color: #94a3b8 !important;

}


/* =========================================================
   STATUS SELECT
========================================================= */

.status-select {

    min-width: 135px;

    border-radius: 9px;

    font-size: 13px;

    font-weight: 600;

    background: #0d1120 !important;

    color: #e5e7eb !important;

    border:
        1px solid rgba(255,255,255,0.10) !important;

}


.status-select:focus {

    background: #111525 !important;

    color: #ffffff !important;

    border-color:
        #7c3aed !important;

    box-shadow:
        0 0 0 3px rgba(124,58,237,0.15) !important;

}


.status-select option {

    background: #111525;

    color: #ffffff;

}


/* =========================================================
   DATES
========================================================= */

.table tbody td small {

    color: #a5afc1;

}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {

    text-align: center;

    padding: 70px 20px;

    color: #d1d5db;

}


.empty-state h5 {

    color: #f8fafc;

}


.empty-state .text-muted {

    color: #8993a8 !important;

}


.empty-icon {

    width: 75px;

    height: 75px;

    margin: 0 auto 20px;

    border-radius: 50%;

    background:
        rgba(124,58,237,0.12);

    color: #a78bfa;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 32px;

    box-shadow:
        0 0 25px rgba(124,58,237,0.12);

}


/* =========================================================
   BUTTON
========================================================= */

.btn-primary {

    background:
        linear-gradient(
            135deg,
            #7c3aed,
            #4f46e5
        ) !important;

    border: none !important;

    color: white !important;

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
   TABLE RESPONSIVE
========================================================= */

.table-responsive {

    border-radius: 0 0 18px 18px;

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


    .stat-card {

        padding: 18px;

    }


    .task-card-header {

        padding: 18px;

        align-items: flex-start;

        gap: 15px;

    }


    .task-card-header .badge {

        display: none;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar">


    <!-- BRAND -->

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
        class="nav-link-custom active"
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
        class="nav-link-custom"
    >

        <i class="bi bi-person-fill"></i>

        <span class="nav-text">
            My Profile
        </span>

    </a>


    <!-- SPACER -->

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

            <i class="bi bi-list-task"></i>

            My Tasks

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


                <div
                    style="
                    font-size:12px;
                    color:#8b95aa;
                    "
                >

                    <?php

                    echo htmlspecialchars(
                        $employee['designation'] ?? 'Employee',
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    ?>

                </div>

            </div>


            <!-- EMPLOYEE PROFILE AVATAR -->

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

                My Assigned Tasks 📋

            </h2>


            <p>

                View and manage the tasks assigned to you.

            </p>

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

                        Completed

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

                        Pending

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
             TASK TABLE
        ================================================= -->

        <div class="task-card">


            <!-- HEADER -->

            <div class="task-card-header">


                <div>

                    <h5>

                        <i class="bi bi-list-check"></i>

                        All My Tasks

                    </h5>


                    <small class="text-muted">

                        <?php

                        echo $total_tasks;

                        ?>

                        task(s) assigned to you

                    </small>

                </div>


                <span class="badge">

                    <?php

                    echo htmlspecialchars(
                        $employee['full_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    ?>

                </span>


            </div>



            <?php if ($total_tasks > 0) { ?>


            <!-- TABLE -->

            <div class="table-responsive">

                <table class="table table-hover">


                    <thead>

                        <tr>

                            <th>
                                Task
                            </th>

                            <th>
                                Project
                            </th>

                            <th>
                                Priority
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Start Date
                            </th>

                            <th>
                                Due Date
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach ($task_rows as $task) { ?>


                        <tr>


                            <!-- TASK -->

                            <td>

                                <div class="task-title">

                                    <?php

                                    echo htmlspecialchars(
                                        $task['task_title'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );

                                    ?>

                                </div>


                                <span class="task-description">

                                    <?php

                                    echo htmlspecialchars(
                                        $task['description'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
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
                                        ?? 'No Project',
                                        ENT_QUOTES,
                                        'UTF-8'
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
                                    bg-<?php
                                    echo $priority_class;
                                    ?>
                                    "
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $task['priority'] ?? 'Medium',
                                        ENT_QUOTES,
                                        'UTF-8'
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
                                        value="<?php
                                        echo $task['task_id'];
                                        ?>"
                                    >


                                    <select
                                        name="status"
                                        class="
                                        form-select
                                        form-select-sm
                                        status-select
                                        "
                                        onchange="this.form.submit()"
                                    >

                                        <option
                                            value="Pending"

                                            <?php

                                            echo (
                                                $task['status']
                                                == 'Pending'
                                            )
                                            ? 'selected'
                                            : '';

                                            ?>
                                        >

                                            Pending

                                        </option>


                                        <option
                                            value="In Progress"

                                            <?php

                                            echo (
                                                $task['status']
                                                == 'In Progress'
                                            )
                                            ? 'selected'
                                            : '';

                                            ?>
                                        >

                                            In Progress

                                        </option>


                                        <option
                                            value="Completed"

                                            <?php

                                            echo (
                                                $task['status']
                                                == 'Completed'
                                            )
                                            ? 'selected'
                                            : '';

                                            ?>
                                        >

                                            Completed

                                        </option>

                                    </select>

                                </form>

                            </td>



                            <!-- START DATE -->

                            <td>

                                <small>

                                    <?php

                                    echo htmlspecialchars(
                                        $task['start_date'] ?? 'Not set',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );

                                    ?>

                                </small>

                            </td>



                            <!-- DUE DATE -->

                            <td>

                                <small>

                                    <?php

                                    echo htmlspecialchars(
                                        $task['due_date'] ?? 'Not set',
                                        ENT_QUOTES,
                                        'UTF-8'
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


                <!-- EMPTY STATE -->

                <div class="empty-state">


                    <div class="empty-icon">

                        <i class="bi bi-clipboard-x"></i>

                    </div>


                    <h5>

                        No Tasks Assigned

                    </h5>


                    <p class="text-muted">

                        You currently don't have any
                        tasks assigned.

                    </p>


                    <a
                        href="dashboard.php"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-arrow-left"></i>

                        Back to Dashboard

                    </a>


                </div>


            <?php } ?>


        </div>



        <!-- =================================================
             FOOTER
        ================================================= -->

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