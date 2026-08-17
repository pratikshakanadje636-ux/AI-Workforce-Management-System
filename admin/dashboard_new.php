<?php

session_start();

/* ===========================
   LOGIN CHECK
=========================== */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../authentication/login.php");
    exit();
}

/* ===========================
   DATABASE CONNECTION
=========================== */

require_once "../config/database.php";
/* ===========================
   DASHBOARD COUNTS
=========================== */

$employees = 0;
$projects = 0;
$total_tasks = 0;
$completed_tasks = 0;
$pending_tasks = 0;
$progress_tasks = 0;


/* Total Employees */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM employees
");

if ($result) {
    $employees = $result->fetch_assoc()['total'];
}


/* Total Projects */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM projects
");

if ($result) {
    $projects = $result->fetch_assoc()['total'];
}


/* Total Tasks */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM tasks
");

if ($result) {
    $total_tasks = $result->fetch_assoc()['total'];
}


/* Completed Tasks */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM tasks
    WHERE status = 'Completed'
");

if ($result) {
    $completed_tasks = $result->fetch_assoc()['total'];
}


/* Pending Tasks */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM tasks
    WHERE status = 'Pending'
");

if ($result) {
    $pending_tasks = $result->fetch_assoc()['total'];
}


/* In Progress Tasks */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM tasks
    WHERE status = 'In Progress'
");

if ($result) {
    $progress_tasks = $result->fetch_assoc()['total'];
}


/* ===========================
   MONTHLY PERFORMANCE CHART
=========================== */

$months = [];
$completed = [];

$chart = $conn->query("
    SELECT
        MONTHNAME(start_date) AS month,
        COUNT(task_id) AS total
    FROM tasks
    WHERE status = 'Completed'
    GROUP BY MONTH(start_date)
    ORDER BY MONTH(start_date)
");

if ($chart) {

    while ($row = $chart->fetch_assoc()) {

        $months[] = $row['month'];
        $completed[] = (int)$row['total'];

    }

}


/* ===========================
   DEPARTMENT ANALYTICS
=========================== */

$dept_name = [];
$dept_tasks = [];

$deptChart = $conn->query("
    SELECT
        departments.department_name,
        COUNT(tasks.task_id) AS total
    FROM departments

    LEFT JOIN employees
        ON departments.department_id = employees.department_id

    LEFT JOIN tasks
        ON employees.employee_id = tasks.employee_id

    GROUP BY
        departments.department_id,
        departments.department_name

    ORDER BY departments.department_name
");

if ($deptChart) {

    while ($row = $deptChart->fetch_assoc()) {

        $dept_name[] = $row['department_name'];
        $dept_tasks[] = (int)$row['total'];

    }

}


/* ===========================
   TOP 5 EMPLOYEES
=========================== */

$leaderboard = $conn->query("
    SELECT
        employees.full_name,
        COUNT(tasks.task_id) AS total_tasks,
        SUM(
            CASE
                WHEN tasks.status = 'Completed'
                THEN 1
                ELSE 0
            END
        ) AS completed_tasks

    FROM employees

    LEFT JOIN tasks
        ON employees.employee_id = tasks.employee_id

    GROUP BY
        employees.employee_id,
        employees.full_name

    ORDER BY completed_tasks DESC

    LIMIT 5
");


/* ===========================
   AI RECOMMENDED EMPLOYEE
=========================== */

$recommended = [];

$recommendedResult = $conn->query("
    SELECT *
    FROM employees

    ORDER BY
        performance_score DESC,
        completed_projects DESC,
        workload ASC

    LIMIT 1
");

if ($recommendedResult) {

    $recommended = $recommendedResult->fetch_assoc();

}


/* ===========================
   RECENT TASKS
=========================== */

$recentTasks = $conn->query("
    SELECT
        tasks.task_title,
        tasks.status,
        employees.full_name

    FROM tasks

    LEFT JOIN employees
        ON tasks.employee_id = employees.employee_id

    ORDER BY tasks.task_id DESC

    LIMIT 5
");


/* ===========================
   RECENT PROJECTS
=========================== */

$recentProjects = $conn->query("
    SELECT
        project_id,
        project_name,
        status,
        start_date,
        end_date

    FROM projects

    ORDER BY project_id DESC

    LIMIT 5
");


/* ===========================
   AI NOTIFICATIONS
=========================== */

$notifications = [];


if ($pending_tasks > 5) {

    $notifications[] =
        "⚠ High number of pending tasks.";

}


if ($completed_tasks > 10) {

    $notifications[] =
        "✅ Excellent productivity this month.";

}


if ($projects == 0) {

    $notifications[] =
        "📂 No projects available.";

}


if ($progress_tasks > 0) {

    $notifications[] =
        "🔄 $progress_tasks task(s) currently in progress.";

}


if (empty($notifications)) {

    $notifications[] =
        "🤖 System is running normally.";

}


/* ===========================
   END OF PHP DATA SECTION
=========================== */


require_once "../config/database.php";


/* ===========================
   Dashboard Counts
=========================== */

$employees = 0;
$projects = 0;
$total_tasks = 0;
$pending_tasks = 0;
$completed_tasks = 0;
$progress_tasks = 0;


/* Total Employees */

$result = $conn->query("SELECT COUNT(*) AS total FROM employees");

if ($result) {
    $employees = $result->fetch_assoc()['total'];
}


/* Total Projects */

$result = $conn->query("SELECT COUNT(*) AS total FROM projects");

if ($result) {
    $projects = $result->fetch_assoc()['total'];
}


/* Total Tasks */

$result = $conn->query("SELECT COUNT(*) AS total FROM tasks");

if ($result) {
    $total_tasks = $result->fetch_assoc()['total'];
}


/* Pending Tasks */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE status='Pending'"
);

if ($result) {
    $pending_tasks = $result->fetch_assoc()['total'];
}


/* Completed Tasks */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE status='Completed'"
);

if ($result) {
    $completed_tasks = $result->fetch_assoc()['total'];
}


/* In Progress Tasks */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE status='In Progress'"
);

if ($result) {
    $progress_tasks = $result->fetch_assoc()['total'];
}


/* ===========================
   Recent Tasks
=========================== */

$recentTasks = $conn->query("
    SELECT
        tasks.task_title,
        employees.full_name,
        tasks.status
    FROM tasks
    JOIN employees
        ON tasks.employee_id = employees.employee_id
    ORDER BY tasks.task_id DESC
    LIMIT 5
");


/* ===========================
   Recent Projects
=========================== */

$recentProjects = $conn->query("
    SELECT
        project_name,
        status,
        end_date
    FROM projects
    ORDER BY project_id DESC
    LIMIT 5
");

/* ===========================
   Missing Dashboard Data
=========================== */

/* In Progress Tasks */
$progress_tasks = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM tasks
    WHERE status='In Progress'
");

if ($result) {
    $progress_tasks = $result->fetch_assoc()['total'];
}


/* ===========================
   Recent Tasks
=========================== */

$recentTasks = $conn->query("
    SELECT
        t.task_title,
        e.full_name,
        t.status
    FROM tasks t
    JOIN employees e
        ON t.employee_id = e.employee_id
    ORDER BY t.task_id DESC
    LIMIT 5
");


/* ===========================
   Recent Projects
=========================== */

$recentProjects = $conn->query("
    SELECT
        project_name,
        status,
        end_date
    FROM projects
    ORDER BY project_id DESC
    LIMIT 5
");


/* ===========================
   Top 5 Employees
=========================== */

$leaderboard = $conn->query("
    SELECT
        e.full_name,
        COUNT(t.task_id) AS completed_tasks
    FROM employees e
    LEFT JOIN tasks t
        ON e.employee_id = t.employee_id
        AND t.status='Completed'
    GROUP BY e.employee_id, e.full_name
    ORDER BY completed_tasks DESC
    LIMIT 5
");


/* ===========================
   AI Recommended Employee
=========================== */

$recommended = $conn->query("
    SELECT
        e.full_name,
        e.designation,
        e.performance_score,
        e.completed_projects,
        e.workload
    FROM employees e
    ORDER BY
        e.performance_score DESC,
        e.completed_projects DESC,
        e.workload ASC
    LIMIT 1
")->fetch_assoc();


/* ===========================
   AI Notifications
=========================== */

$notifications = [];

if ($pending_tasks > 0) {
    $notifications[] = "$pending_tasks pending task(s) need attention.";
}

if ($completed_tasks > 0) {
    $notifications[] = "$completed_tasks task(s) completed successfully.";
}

if ($progress_tasks > 0) {
    $notifications[] = "$progress_tasks task(s) are currently in progress.";
}


/* ===========================
   Employee Performance Chart
=========================== */

$months = [];
$completed = [];

$chartResult = $conn->query("
    SELECT
        DATE_FORMAT(created_at, '%b') AS month,
        COUNT(*) AS total
    FROM tasks
    WHERE status='Completed'
    GROUP BY MONTH(created_at), DATE_FORMAT(created_at, '%b')
    ORDER BY MONTH(created_at)
");

if ($chartResult) {
    while ($row = $chartResult->fetch_assoc()) {
        $months[] = $row['month'];
        $completed[] = (int)$row['total'];
    }
}


/* ===========================
   Department Task Chart
=========================== */

$dept_name = [];
$dept_tasks = [];

$deptResult = $conn->query("
    SELECT
        d.department_name,
        COUNT(t.task_id) AS total_tasks
    FROM departments d
    LEFT JOIN employees e
        ON d.department_id = e.department_id
    LEFT JOIN tasks t
        ON e.employee_id = t.employee_id
    GROUP BY d.department_id, d.department_name
    ORDER BY d.department_name
");

if ($deptResult) {
    while ($row = $deptResult->fetch_assoc()) {
        $dept_name[] = $row['department_name'];
        $dept_tasks[] = (int)$row['total_tasks'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>AI Workforce Management Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<link rel="stylesheet"
href="../assets/css/style.css">
<link rel="stylesheet"
href="../assets/css/dashboard.css">
<link rel="stylesheet" href="../assets/css/dark-mode.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<?php include "navbar.php"; ?>

<div class="container-fluid p-4">

<!-- =========================
     DASHBOARD SEARCH
========================= -->

<div class="dashboard-search">

    <form
        action="../search.php"
        method="GET"
        class="search-wrapper"
    >

        <i class="fa-solid fa-magnifying-glass search-icon"></i>

        <input
            type="text"
            name="search"
            id="dashboardSearch"
            class="dashboard-search-input"
            placeholder="Search employees, projects, tasks, reports..."
            autocomplete="off"
        >

    </form>

</div>


<!-- ===========================
ROW 1 : DASHBOARD CARDS
=========================== -->

<div class="row g-4 mb-4">

    <!-- Employees -->

    <div class="col-lg-3 col-md-6">

        <div class="dashboard-card">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h6>Total Employees</h6>

                    <h2 class="dashboard-number">

                        <?php echo $employees; ?>

                    </h2>

                    <small class="text-muted">Registered Employees</small>

                </div>

                <div class="stat-icon">

                    <i class="fa-solid fa-users"></i>

                </div>

            </div>

        </div>

    </div>

    <!-- Projects -->

    <div class="col-lg-3 col-md-6">

        <div class="dashboard-card">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h6>Total Projects</h6>

                    <h2 class="dashboard-number">

                        <?php echo $projects; ?>

                    </h2>

                    <small class="text-muted">Active Projects</small>

                </div>

                <div class="stat-icon">

                    <i class="fa-solid fa-folder-open"></i>

                </div>

            </div>

        </div>

    </div>

    <!-- Tasks -->

    <div class="col-lg-3 col-md-6">

        <div class="dashboard-card">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h6>Total Tasks</h6>

                    <h2 class="dashboard-number">

                        <?php echo $total_tasks; ?>

                    </h2>

                    <small class="text-muted">Assigned Tasks</small>

                </div>

                <div class="stat-icon">

                    <i class="fa-solid fa-list-check"></i>

                </div>

            </div>

        </div>

    </div>

    <!-- AI Card -->

    <div class="col-lg-3 col-md-6">

        <div class="ai-card">

            <h5>🤖 AI Overview</h5>

            <hr>

            <div class="d-flex justify-content-between">

                <span>Completed</span>

                <strong><?php echo $completed_tasks; ?></strong>

            </div>

            <div class="d-flex justify-content-between mt-2">

                <span>Pending</span>

                <strong><?php echo $pending_tasks; ?></strong>

            </div>

            <div class="d-flex justify-content-between mt-2">

                <span>In Progress</span>

                <strong><?php echo $progress_tasks; ?></strong>

            </div>

        </div>

    </div>

</div>
<!-- ===========================
ROW 2 : CHARTS
=========================== -->

<div class="row g-4 mb-4">

    <!-- Employee Performance Chart -->

    <div class="col-lg-8">

        <div class="card shadow border-0">

            <div class="card-header bg-primary text-white">

                📈 Employee Performance

            </div>

            <div class="card-body">

                <canvas id="employeeChart" height="100"></canvas>

            </div>

        </div>

    </div>

    <!-- Task Status Chart -->

    <div class="col-lg-4">

        <div class="card shadow border-0">

            <div class="card-header bg-success text-white">

                📊 Task Status

            </div>

            <div class="card-body">

                <canvas id="taskChart"></canvas>

            </div>

        </div>

    </div>

</div>

<!-- ===========================
ROW 3 : DEPARTMENT ANALYTICS
=========================== -->

<div class="row g-4 mb-4">

    <div class="col-lg-12">

        <div class="card shadow border-0">

            <div class="card-header bg-dark text-white">

                🏢 Department Wise Task Distribution

            </div>

            <div class="card-body">

                <canvas id="departmentChart" height="80"></canvas>

            </div>

        </div>

    </div>

</div><!-- ===========================
ROW 4 : AI PRODUCTIVITY
=========================== -->

<div class="row g-4 mb-4">

    <!-- Productivity Score -->

    <div class="col-lg-3">

        <div class="card shadow border-0 text-center">

            <div class="card-body">

                <h2 class="text-success">

                    <?php
                    if($total_tasks > 0){
                        echo round(($completed_tasks/$total_tasks)*100);
                    }else{
                        echo 0;
                    }
                    ?>%

                </h2>

                <p class="mb-0">AI Productivity Score</p>

            </div>

        </div>

    </div>

    <!-- Completed -->

    <div class="col-lg-3">

        <div class="card shadow border-0 text-center">

            <div class="card-body">

                <h2 class="text-primary">

                    <?php echo $completed_tasks; ?>

                </h2>

                <p class="mb-0">Completed Tasks</p>

            </div>

        </div>

    </div>

    <!-- In Progress -->

    <div class="col-lg-3">

        <div class="card shadow border-0 text-center">

            <div class="card-body">

                <h2 class="text-warning">

                    <?php echo $progress_tasks; ?>

                </h2>

                <p class="mb-0">In Progress</p>

            </div>

        </div>

    </div>

    <!-- Pending -->

    <div class="col-lg-3">

        <div class="card shadow border-0 text-center">

            <div class="card-body">

                <h2 class="text-danger">

                    <?php echo $pending_tasks; ?>

                </h2>

                <p class="mb-0">Pending Tasks</p>

            </div>

        </div>

    </div>

</div>

<!-- ===========================
AI ANALYSIS
=========================== -->

<div class="card shadow border-0 mb-4">

    <div class="card-header bg-dark text-white">

        🤖 AI Productivity Analysis

    </div>

    <div class="card-body">

        <?php
        $score = ($total_tasks > 0)
            ? round(($completed_tasks/$total_tasks)*100)
            : 0;
        ?>

        <div class="progress mb-3">

            <div
                class="progress-bar bg-success"
                style="width:<?php echo $score; ?>%">

                <?php echo $score; ?>%

            </div>

        </div>

        <?php

        if($score >= 80){

            echo "<div class='alert alert-success'>
            🚀 Excellent Workforce Productivity
            </div>";

        }
        elseif($score >= 60){

            echo "<div class='alert alert-warning'>
            ⚡ Average Productivity
            </div>";

        }
        else{

            echo "<div class='alert alert-danger'>
            ⚠ Low Productivity - Needs Attention
            </div>";

        }

        ?>

    </div>

</div>
<!-- ===========================
ROW 5 : AI RECOMMENDED EMPLOYEE + RECENT TASKS
=========================== -->

<div class="row g-4 mb-4">

    <!-- AI Recommended Employee -->

    <div class="col-lg-4">

        <div class="card shadow border-0">

            <div class="card-header bg-primary text-white">

                🤖 AI Recommended Employee

            </div>

            <div class="card-body text-center">

                <i class="fa-solid fa-user-check fa-4x text-primary mb-3"></i>

                <h4>

                    <?php echo $recommended['full_name']; ?>

                </h4>

                <p class="text-muted">

                    <?php echo $recommended['designation']; ?>

                </p>

                <hr>

                <div class="row mb-2">

                    <div class="col-7 text-start">

                        Performance

                    </div>

                    <div class="col-5 text-end">

                        <strong>

                            <?php echo $recommended['performance_score']; ?>%

                        </strong>

                    </div>

                </div>

                <div class="progress mb-3">

                    <div
                    class="progress-bar bg-success"
                    style="width:<?php echo $recommended['performance_score']; ?>%">

                    </div>

                </div>

                <div class="row mb-2">

                    <div class="col-7 text-start">

                        Projects Completed

                    </div>

                    <div class="col-5 text-end">

                        <?php echo $recommended['completed_projects']; ?>

                    </div>

                </div>

                <div class="row mb-2">

                    <div class="col-7 text-start">

                        Workload

                    </div>

                    <div class="col-5 text-end">

                        <?php echo $recommended['workload']; ?>%

                    </div>

                </div>

                <div class="progress">

                    <div
                    class="progress-bar bg-warning"
                    style="width:<?php echo $recommended['workload']; ?>%">

                    </div>

                </div>

                <div class="mt-4">

                    <span class="badge bg-success">

                        AI Selected Best Employee

                    </span>

                </div>

            </div>

        </div>

    </div>

    <!-- Recent Tasks -->

    <div class="col-lg-8">

        <div class="card shadow border-0">

            <div class="card-header bg-success text-white d-flex justify-content-between">

                <span>📋 Recent Tasks</span>

                <a href="../task/view.php" class="btn btn-light btn-sm">

                    View All

                </a>

            </div>

            <div class="card-body">

                <table class="table table-hover">

                    <thead>

                        <tr>

                            <th>Task</th>

                            <th>Employee</th>

                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while($row = $recentTasks->fetch_assoc()){ ?>

                        <tr>

                            <td>

                                <?php echo $row['task_title']; ?>

                            </td>

                            <td>

                                <?php echo $row['full_name']; ?>

                            </td>

                            <td>

                                <?php

                                if($row['status']=="Completed"){

                                    echo "<span class='badge bg-success'>Completed</span>";

                                }
                                elseif($row['status']=="Pending"){

                                    echo "<span class='badge bg-warning text-dark'>Pending</span>";

                                }
                                else{

                                    echo "<span class='badge bg-info'>In Progress</span>";

                                }

                                ?>

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>
<!-- ===========================
ROW 6 : RECENT PROJECTS + TOP EMPLOYEES
=========================== -->

<div class="row g-4 mb-4">

    <!-- Recent Projects -->

    <div class="col-lg-6">

        <div class="card shadow border-0">

            <div class="card-header bg-primary text-white d-flex justify-content-between">

                <span>📁 Recent Projects</span>

                <a href="../project/view.php" class="btn btn-light btn-sm">

                    View All

                </a>

            </div>

            <div class="card-body">

                <table class="table table-hover">

                    <thead>

                        <tr>

                            <th>Project</th>

                            <th>Status</th>

                            <th>End Date</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while($project = $recentProjects->fetch_assoc()){ ?>

                        <tr>

                            <td>

                                <?php echo $project['project_name']; ?>

                            </td>

                            <td>

                                <?php

                                if($project['status']=="Completed"){

                                    echo "<span class='badge bg-success'>Completed</span>";

                                }

                                elseif($project['status']=="Active"){

                                    echo "<span class='badge bg-primary'>Active</span>";

                                }

                                elseif($project['status']=="Planning"){

                                    echo "<span class='badge bg-warning text-dark'>Planning</span>";

                                }

                                else{

                                    echo "<span class='badge bg-danger'>On Hold</span>";

                                }

                                ?>

                            </td>

                            <td>

                                <?php echo $project['end_date']; ?>

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- Top Employees -->

    <div class="col-lg-6">

        <div class="card shadow border-0">

            <div class="card-header bg-success text-white">

                🏆 Top 5 Employees

            </div>

            <div class="card-body">

                <table class="table table-hover">

                    <thead>

                        <tr>

                            <th>Rank</th>

                            <th>Employee</th>

                            <th>Completed</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $rank = 1;

                    while($emp = $leaderboard->fetch_assoc()){

                    ?>

                        <tr>

                            <td>

                                <?php

                                if($rank==1){

                                    echo "🥇";

                                }

                                elseif($rank==2){

                                    echo "🥈";

                                }

                                elseif($rank==3){

                                    echo "🥉";

                                }

                                else{

                                    echo "#".$rank;

                                }

                                ?>

                            </td>

                            <td>

                                <?php echo $emp['full_name']; ?>

                            </td>

                            <td>

                                <?php echo $emp['completed_tasks']; ?>

                            </td>

                        </tr>

                    <?php

                    $rank++;

                    }

                    ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>
<!-- ===========================
ROW 7 : AI NOTIFICATIONS + RECENT ACTIVITY
=========================== -->

<div class="row g-4 mb-4">

    <!-- AI Notifications -->

    <div class="col-lg-6">

        <div class="card shadow border-0">

            <div class="card-header bg-primary text-white">

                🔔 AI Notifications

            </div>

            <div class="card-body">

                 <ul class="list-group">

                    <?php foreach($notifications as $note){ ?>

                    <li class="list-group-item">

                        <?php echo $note; ?>

                    </li>

                    <?php } ?>

                </ul>

            </div>

        </div>

    </div>

    <!-- Recent Activity -->

    <div class="col-lg-6">

        <div class="card shadow border-0">

            <div class="card-header bg-success text-white">

                📈 Recent Activity

            </div>

            <div class="card-body">

                <ul class="list-group list-group-flush">

                    <li class="list-group-item">

                        👥 Total Employees :
                        <strong><?php echo $employees; ?></strong>

                    </li>

                    <li class="list-group-item">

                        📁 Total Projects :
                        <strong><?php echo $projects; ?></strong>

                    </li>

                    <li class="list-group-item">

                        📋 Total Tasks :
                        <strong><?php echo $total_tasks; ?></strong>

                    </li>

                    <li class="list-group-item">

                        ✅ Completed Tasks :
                        <strong><?php echo $completed_tasks; ?></strong>

                    </li>

                    <li class="list-group-item">

                        ⏳ Pending Tasks :
                        <strong><?php echo $pending_tasks; ?></strong>

                    </li>

                </ul>

            </div>

        </div>

    </div>

</div>
<!-- ===========================
FOOTER
=========================== -->

<footer class="mt-5">

    <div class="card shadow border-0">

        <div class="card-body text-center">

            <h6 class="mb-2">

                AI Workforce Management System

            </h6>

            <p class="text-muted mb-1">

                Developed using PHP, MySQL, Bootstrap 5 & Chart.js

            </p>

            <small class="text-secondary">

                © <?php echo date("Y"); ?> AI Workforce Management System.
                All Rights Reserved.

            </small>

        </div>

    </div>

</footer>

</div>
<script>

// =========================
// Employee Performance Chart
// =========================

const employeeChart = new Chart(
document.getElementById('employeeChart'),
{
    type:'line',

    data:{
        labels: <?php echo json_encode($months); ?>,

        datasets:[{

            label:'Completed Tasks',

            data: <?php echo json_encode($completed); ?>,

            borderColor:'#2563eb',

            backgroundColor:'rgba(37,99,235,0.15)',

            fill:true,

            tension:0.4

        }]
    },

    options:{
        responsive:true,

        plugins:{
            legend:{
                display:false
            }
        }
    }

});


// =========================
// Task Status Chart
// =========================

const taskChart = new Chart(
document.getElementById('taskChart'),
{

    type:'doughnut',

    data:{

        labels:[
            'Completed',
            'Pending',
            'In Progress'
        ],

        datasets:[{

            data:[

                <?php echo $completed_tasks; ?>,

                <?php echo $pending_tasks; ?>,

                <?php echo $progress_tasks; ?>

            ],

            backgroundColor:[

                '#22c55e',

                '#ef4444',

                '#f59e0b'

            ]

        }]

    },

    options:{

        responsive:true,

        plugins:{

            legend:{

                position:'bottom'

            }

        }

    }

});


// =========================
// Department Chart
// =========================

const departmentChart = new Chart(
document.getElementById('departmentChart'),
{

    type:'bar',

    data:{

        labels: <?php echo json_encode($dept_name); ?>,

        datasets:[{

            label:'Tasks',

            data: <?php echo json_encode($dept_tasks); ?>,

            backgroundColor:'#2563eb'

        }]

    },

    options:{

        responsive:true,

        plugins:{

            legend:{

                display:false

            }

        }

    }

});


// =========================
// Dark Mode
// =========================

function toggleDarkMode() {
    document.body.classList.toggle("dark-mode");
    return false;
}


</script>
<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>