<?php

require_once "../config/database.php";

/* =========================================================
   DASHBOARD COUNTS
========================================================= */

$employees = (int)$conn->query(
    "SELECT COUNT(*) total FROM employees"
)->fetch_assoc()['total'];

$departments = (int)$conn->query(
    "SELECT COUNT(*) total FROM departments"
)->fetch_assoc()['total'];

$projects = (int)$conn->query(
    "SELECT COUNT(*) total FROM projects"
)->fetch_assoc()['total'];

$tasks = (int)$conn->query(
    "SELECT COUNT(*) total FROM tasks"
)->fetch_assoc()['total'];

$pending = (int)$conn->query(
    "SELECT COUNT(*) total FROM tasks WHERE status='Pending'"
)->fetch_assoc()['total'];

$completed = (int)$conn->query(
    "SELECT COUNT(*) total FROM tasks WHERE status='Completed'"
)->fetch_assoc()['total'];


/* =========================================================
   PROJECT PROGRESS
========================================================= */

$project_sql = "
SELECT
    projects.project_name,
    COUNT(tasks.task_id) AS total_tasks,
    SUM(
        CASE
            WHEN tasks.status = 'Completed' THEN 1
            ELSE 0
        END
    ) AS completed_tasks
FROM projects
LEFT JOIN tasks
    ON projects.project_id = tasks.project_id
GROUP BY projects.project_id, projects.project_name
ORDER BY projects.project_name
";

$project_result = $conn->query($project_sql);


/* =========================================================
   EMPLOYEE PERFORMANCE
========================================================= */

$employee_sql = "
SELECT
    employees.full_name,
    COUNT(tasks.task_id) AS total_tasks,
    SUM(
        CASE
            WHEN tasks.status = 'Completed' THEN 1
            ELSE 0
        END
    ) AS completed_tasks
FROM employees
LEFT JOIN tasks
    ON employees.employee_id = tasks.employee_id
GROUP BY employees.employee_id, employees.full_name
ORDER BY employees.full_name
";

$employee_result = $conn->query($employee_sql);


/* =========================================================
   PROJECT CHART DATA
========================================================= */

$projectNames = [];
$projectTaskCounts = [];

$chart_sql = "
SELECT
    projects.project_name,
    COUNT(tasks.task_id) AS total_tasks
FROM projects
LEFT JOIN tasks
    ON projects.project_id = tasks.project_id
GROUP BY projects.project_id, projects.project_name
ORDER BY projects.project_name
";

$chart_result = $conn->query($chart_sql);

if ($chart_result) {

    while ($chart = $chart_result->fetch_assoc()) {

        $projectNames[] = $chart['project_name'];

        $projectTaskCounts[] =
            (int)$chart['total_tasks'];
    }
}


/* =========================================================
   AI INSIGHTS
========================================================= */

$completionRate =
    $tasks > 0
        ? (int)round(
            ($completed / $tasks) * 100
        )
        : 0;


if ($completionRate >= 80) {

    $performance = "Excellent";

} elseif ($completionRate >= 60) {

    $performance = "Good";

} elseif ($completionRate >= 40) {

    $performance = "Average";

} else {

    $performance = "Needs Improvement";
}


/* =========================================================
   PIE CHART DATA
========================================================= */

$totalPieTasks =
    $pending + $completed;

$pendingPercent = 0;
$completedPercent = 0;

if ($totalPieTasks > 0) {

    $pendingPercent =
        round(
            ($pending / $totalPieTasks) * 100,
            2
        );

    $completedPercent =
        round(
            ($completed / $totalPieTasks) * 100,
            2
        );
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
    Reports Dashboard | AI Workforce
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


<!-- Chart.js -->

<script
    src="https://cdn.jsdelivr.net/npm/chart.js"
></script>


<style>

/* =========================================================
   GLOBAL
========================================================= */

* {
    box-sizing: border-box;
}

body {

    background: #f8fafc;

    color: #1f2937;

    transition:
        background 0.25s ease,
        color 0.25s ease;

}

.reports-container {

    max-width: 1400px;

    margin: 0 auto;

    padding-bottom: 60px;

}

.page-title {

    color: #1f2937;

    font-weight: 700;

}


/* =========================================================
   SUMMARY CARDS
========================================================= */

.report-card {

    border: none;

    border-radius: 16px;

    box-shadow:
        0 6px 20px rgba(0,0,0,0.07);

    transition: 0.25s;

}

.report-card:hover {

    transform: translateY(-3px);

}


/* =========================================================
   SECTION CARDS
========================================================= */

.report-section-card {

    background: #ffffff;

    border: none;

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 6px 20px rgba(0,0,0,0.07);

}

.report-section-header {

    padding: 18px 22px;

    border-bottom:
        1px solid #e5e7eb;

}

.report-section-header h4 {

    margin: 0;

    color: #1f2937;

    font-weight: 700;

}

.report-section-body {

    padding: 22px;

}


/* =========================================================
   TABLE
========================================================= */

.report-table {

    margin-bottom: 0;

}

.report-table thead th {

    background: #111827;

    color: #ffffff;

    border: none;

    padding: 14px;

    white-space: nowrap;

}

.report-table tbody td {

    padding: 14px;

    color: #374151;

    border-color: #e5e7eb;

    vertical-align: middle;

}

.report-table tbody tr:hover td {

    background: #f8fbff;

}


/* =========================================================
   AI INSIGHTS
========================================================= */

.ai-insights {

    background: #ffffff;

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 6px 20px rgba(0,0,0,0.07);

}

.ai-insights-header {

    background:
        linear-gradient(
            135deg,
            #0d6efd,
            #6610f2
        );

    color: #ffffff;

    padding: 18px 22px;

}

.ai-insights-header h4 {

    margin: 0;

    font-weight: 700;

}

.ai-list .list-group-item {

    padding: 16px 20px;

    background: #ffffff;

    color: #374151;

    border-color: #e5e7eb;

}


/* =========================================================
   PROGRESS
========================================================= */

.progress {

    height: 10px;

    background: #e5e7eb;

    border-radius: 20px;

    overflow: hidden;

}

.progress-bar {

    border-radius: 20px;

}


/* =========================================================
   CHART CARDS
========================================================= */

.chart-card {

    background: #ffffff;

    border: none;

    border-radius: 16px;

    box-shadow:
        0 6px 20px rgba(0,0,0,0.07);

}

.chart-card .card-body {

    padding: 22px;

}

.chart-title {

    color: #1f2937;

    font-weight: 700;

}


/* =========================================================
   CSS PIE CHART
========================================================= */

.pie-wrapper {

    width: 300px;

    height: 300px;

    margin: 0 auto 25px;

    display: flex;

    align-items: center;

    justify-content: center;

    position: relative;

}

.css-pie-chart {

    width: 260px;

    height: 260px;

    border-radius: 50%;

    position: relative;

}

.css-pie-chart::after {

    content: "";

    position: absolute;

    width: 130px;

    height: 130px;

    left: 50%;

    top: 50%;

    transform:
        translate(-50%, -50%);

    background: #ffffff;

    border-radius: 50%;

}

.pie-center {

    position: absolute;

    z-index: 2;

    left: 50%;

    top: 50%;

    transform:
        translate(-50%, -50%);

    text-align: center;

    color: #1f2937;

}

.pie-center-number {

    display: block;

    font-size: 28px;

    font-weight: 800;

}

.pie-center-label {

    font-size: 12px;

    color: #6b7280;

}

.pie-legend {

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 28px;

    flex-wrap: wrap;

}

.legend-item {

    display: flex;

    align-items: center;

    gap: 8px;

    color: #374151;

}

.legend-dot {

    width: 12px;

    height: 12px;

    border-radius: 50%;

    display: inline-block;

}

.pending-dot {

    background: #f59e0b;

}

.completed-dot {

    background: #10b981;

}


/* =========================================================
   BAR CHART CONTAINER
========================================================= */

.bar-chart-container {

    position: relative;

    height: 320px;

    width: 100%;

}


/* =========================================================
   TEXT
========================================================= */

.text-muted {

    color: #6b7280 !important;

}


/* =========================================================
   DARK MODE
========================================================= */

body.admin-dark-mode {

    background: #080b14 !important;

    color: #e5e7eb !important;

}

body.admin-dark-mode .reports-container {

    color: #e5e7eb !important;

}

body.admin-dark-mode .page-title {

    color: #f8fafc !important;

}


/* Summary */

body.admin-dark-mode .report-card {

    color: #ffffff !important;

    box-shadow:
        0 10px 30px rgba(0,0,0,0.30) !important;

}


/* Sections */

body.admin-dark-mode .report-section-card {

    background:
        linear-gradient(
            145deg,
            #111525,
            #0d1120
        ) !important;

    color: #e5e7eb !important;

    border:
        1px solid rgba(255,255,255,0.06);

}

body.admin-dark-mode .report-section-header {

    background: #111525 !important;

    border-color: #334155 !important;

}

body.admin-dark-mode .report-section-header h4 {

    color: #f8fafc !important;

}


/* Tables */

body.admin-dark-mode .report-table {

    --bs-table-bg: #111525;

    --bs-table-color: #e5e7eb;

    --bs-table-border-color: #334155;

}

body.admin-dark-mode .report-table thead th {

    background: #080b14 !important;

    color: #ffffff !important;

    border-color: #334155 !important;

}

body.admin-dark-mode .report-table tbody td {

    background: #111525 !important;

    color: #d1d5db !important;

    border-color: #334155 !important;

}

body.admin-dark-mode .report-table tbody tr:hover td {

    background: #182033 !important;

}


/* AI */

body.admin-dark-mode .ai-insights {

    background:
        linear-gradient(
            145deg,
            #111525,
            #0d1120
        ) !important;

    border:
        1px solid rgba(255,255,255,0.06) !important;

}

body.admin-dark-mode .ai-list .list-group-item {

    background: #111525 !important;

    color: #d1d5db !important;

    border-color: #334155 !important;

}


/* Chart cards */

body.admin-dark-mode .chart-card {

    background:
        linear-gradient(
            145deg,
            #111525,
            #0d1120
        ) !important;

    color: #e5e7eb !important;

    border:
        1px solid rgba(255,255,255,0.06) !important;

}

body.admin-dark-mode .chart-title {

    color: #f8fafc !important;

}


/* Pie */

body.admin-dark-mode .css-pie-chart::after {

    background: #111525;

}

body.admin-dark-mode .pie-center {

    color: #f8fafc;

}

body.admin-dark-mode .pie-center-label {

    color: #94a3b8;

}

body.admin-dark-mode .legend-item {

    color: #e5e7eb;

}


/* Progress */

body.admin-dark-mode .progress {

    background: #334155 !important;

}


/* Muted */

body.admin-dark-mode .text-muted {

    color: #94a3b8 !important;

}


/* HR */

body.admin-dark-mode hr {

    border-color: #475569 !important;

    opacity: 1;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 768px) {

    .reports-container {

        padding-left: 12px;

        padding-right: 12px;

    }

    .pie-wrapper {

        width: 260px;

        height: 260px;

    }

    .css-pie-chart {

        width: 230px;

        height: 230px;

    }

    .css-pie-chart::after {

        width: 115px;

        height: 115px;

    }

    .bar-chart-container {

        height: 280px;

    }

}

</style>

</head>


<body class="bg-light">

<?php include "../config/page_actions.php"; ?>


<div class="container mt-5 reports-container">


<!-- =========================================================
     TITLE
========================================================= -->

<h2 class="text-center mb-5 page-title">

    📊 Reports Dashboard

</h2>


<!-- =========================================================
     SUMMARY
========================================================= -->

<div class="row">


    <div class="col-md-4 mb-3">

        <div class="card report-card bg-primary text-white">

            <div class="card-body text-center">

                <h5>
                    Total Employees
                </h5>

                <h2>
                    <?php echo $employees; ?>
                </h2>

            </div>

        </div>

    </div>


    <div class="col-md-4 mb-3">

        <div class="card report-card bg-success text-white">

            <div class="card-body text-center">

                <h5>
                    Total Departments
                </h5>

                <h2>
                    <?php echo $departments; ?>
                </h2>

            </div>

        </div>

    </div>


    <div class="col-md-4 mb-3">

        <div class="card report-card bg-info text-white">

            <div class="card-body text-center">

                <h5>
                    Total Projects
                </h5>

                <h2>
                    <?php echo $projects; ?>
                </h2>

            </div>

        </div>

    </div>


    <div class="col-md-4 mb-3">

        <div class="card report-card bg-dark text-white">

            <div class="card-body text-center">

                <h5>
                    Total Tasks
                </h5>

                <h2>
                    <?php echo $tasks; ?>
                </h2>

            </div>

        </div>

    </div>


    <div class="col-md-4 mb-3">

        <div class="card report-card bg-warning">

            <div class="card-body text-center">

                <h5>
                    Pending Tasks
                </h5>

                <h2>
                    <?php echo $pending; ?>
                </h2>

            </div>

        </div>

    </div>


    <div class="col-md-4 mb-3">

        <div class="card report-card bg-danger text-white">

            <div class="card-body text-center">

                <h5>
                    Completed Tasks
                </h5>

                <h2>
                    <?php echo $completed; ?>
                </h2>

            </div>

        </div>

    </div>

</div>


<hr class="my-5">


<!-- =========================================================
     PROJECT PROGRESS
========================================================= -->

<div class="report-section-card mb-5">

    <div class="report-section-header">

        <h4>
            📁 Project Progress
        </h4>

    </div>


    <div class="report-section-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover report-table">

                <thead>

                    <tr>

                        <th>
                            Project
                        </th>

                        <th>
                            Total Tasks
                        </th>

                        <th>
                            Completed
                        </th>

                        <th>
                            Progress
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php

                if ($project_result) {

                    while (
                        $row =
                        $project_result->fetch_assoc()
                    ) {

                        $total =
                            (int)$row['total_tasks'];

                        $done =
                            (int)$row['completed_tasks'];

                        $percent =
                            $total > 0
                                ? (int)round(
                                    ($done / $total) * 100
                                )
                                : 0;

                ?>

                    <tr>

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row['project_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            );

                            ?>

                        </td>

                        <td>
                            <?php echo $total; ?>
                        </td>

                        <td>
                            <?php echo $done; ?>
                        </td>

                        <td>

                            <div class="progress">

                                <div
                                    class="progress-bar bg-success"
                                    style="
                                        width:
                                        <?php echo $percent; ?>%;
                                    "
                                >

                                    <?php echo $percent; ?>%

                                </div>

                            </div>

                        </td>

                    </tr>

                <?php

                    }

                }

                ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<hr class="my-5">


<!-- =========================================================
     EMPLOYEE PERFORMANCE
========================================================= -->

<div class="report-section-card mb-5">

    <div class="report-section-header">

        <h4>
            👨‍💼 Employee Performance
        </h4>

    </div>


    <div class="report-section-body">

        <div class="table-responsive">

            <table class="table table-bordered table-striped report-table">

                <thead>

                    <tr>

                        <th>
                            Employee
                        </th>

                        <th>
                            Total Tasks
                        </th>

                        <th>
                            Completed Tasks
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php

                if ($employee_result) {

                    while (
                        $row =
                        $employee_result->fetch_assoc()
                    ) {

                ?>

                    <tr>

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row['full_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            );

                            ?>

                        </td>

                        <td>

                            <?php

                            echo (int)
                                $row['total_tasks'];

                            ?>

                        </td>

                        <td>

                            <?php

                            echo (int)
                                $row['completed_tasks'];

                            ?>

                        </td>

                    </tr>

                <?php

                    }

                }

                ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<hr class="my-5">


<!-- =========================================================
     AI INSIGHTS
========================================================= -->

<div class="ai-insights mb-5">

    <div class="ai-insights-header">

        <h4>
            🤖 AI Insights
        </h4>

    </div>


    <div class="ai-insights-body">

        <ul class="list-group ai-list">

            <li class="list-group-item">

                📊 Total Tasks:

                <strong>
                    <?php echo $tasks; ?>
                </strong>

            </li>


            <li class="list-group-item">

                ✅ Completed Tasks:

                <strong>
                    <?php echo $completed; ?>
                </strong>

            </li>


            <li class="list-group-item">

                ⏳ Pending Tasks:

                <strong>
                    <?php echo $pending; ?>
                </strong>

            </li>


            <li class="list-group-item">

                📈 Completion Rate:

                <strong>
                    <?php echo $completionRate; ?>%
                </strong>

            </li>


            <li class="list-group-item">

                🧠 Performance:

                <strong>
                    <?php echo $performance; ?>
                </strong>

            </li>


            <li class="list-group-item">

                💡 Recommendation:

                <?php

                if ($completionRate >= 80) {

                    echo
                        "Excellent progress! Continue maintaining the current productivity.";

                } elseif ($completionRate >= 60) {

                    echo
                        "Project is progressing well. Focus on completing the remaining tasks.";

                } elseif ($completionRate >= 40) {

                    echo
                        "Average progress. Consider assigning more resources to pending tasks.";

                } else {

                    echo
                        "Project needs immediate attention. Increase task completion rate.";

                }

                ?>

            </li>


            <li class="list-group-item">

                🤖 AI Prediction:

                <strong>

                <?php

                if ($completionRate >= 80) {

                    echo
                        "Project is likely to finish on time.";

                } elseif ($completionRate >= 50) {

                    echo
                        "Project is on track but needs regular monitoring.";

                } else {

                    echo
                        "High risk of project delay.";

                }

                ?>

                </strong>

            </li>

        </ul>

    </div>

</div>


<hr class="my-5">


<!-- =========================================================
     ANALYTICS CHARTS
========================================================= -->

<h3 class="mb-4 page-title">

    📈 Analytics Charts

</h3>


<div class="row">


    <!-- =====================================================
         TASK STATUS
    ====================================================== -->

    <div class="col-md-6 mb-4">

        <div class="card chart-card shadow">

            <div class="card-body">

                <h5 class="text-center chart-title mb-4">

                    Task Status

                </h5>


                <div class="pie-wrapper">

                    <div
                        class="css-pie-chart"
                        style="
                            background:
                            conic-gradient(
                                #f59e0b
                                0%
                                <?php
                                echo $pendingPercent;
                                ?>%,

                                #10b981
                                <?php
                                echo $pendingPercent;
                                ?>%
                                100%
                            );
                        "
                    ></div>


                    <div class="pie-center">

                        <span class="pie-center-number">

                            <?php
                            echo $totalPieTasks;
                            ?>

                        </span>

                        <span class="pie-center-label">

                            Total Tasks

                        </span>

                    </div>

                </div>


                <div class="pie-legend">


                    <div class="legend-item">

                        <span
                            class="legend-dot pending-dot"
                        ></span>

                        <span>
                            Pending
                        </span>

                        <strong>

                            <?php
                            echo $pending;
                            ?>

                        </strong>

                    </div>


                    <div class="legend-item">

                        <span
                            class="legend-dot completed-dot"
                        ></span>

                        <span>
                            Completed
                        </span>

                        <strong>

                            <?php
                            echo $completed;
                            ?>

                        </strong>

                    </div>


                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         PROJECT TASKS
    ====================================================== -->

    <div class="col-md-6 mb-4">

        <div class="card chart-card shadow">

            <div class="card-body">

                <h5 class="text-center chart-title mb-4">

                    Tasks Per Project

                </h5>


                <div class="bar-chart-container">

                    <canvas
                        id="projectChart"
                    ></canvas>

                </div>

            </div>

        </div>

    </div>


</div>


</div>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>


<!-- Project Bar Chart -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const projectCanvas =
            document.getElementById(
                "projectChart"
            );


        if (
            !projectCanvas ||
            typeof Chart === "undefined"
        ) {

            return;

        }


        const projectNames =
            <?php
            echo json_encode(
                $projectNames
            );
            ?>;


        const projectTaskCounts =
            <?php
            echo json_encode(
                $projectTaskCounts
            );
            ?>;


        const darkMode =
            document.body.classList.contains(
                "admin-dark-mode"
            );


        const textColor =
            darkMode
                ? "#e5e7eb"
                : "#374151";


        const gridColor =
            darkMode
                ? "rgba(148,163,184,0.15)"
                : "rgba(0,0,0,0.08)";


        new Chart(
            projectCanvas,
            {

                type: "bar",

                data: {

                    labels:
                        projectNames,

                    datasets: [

                        {

                            label:
                                "Total Tasks",

                            data:
                                projectTaskCounts,

                            backgroundColor:
                                "#6366f1",

                            borderRadius:
                                8

                        }

                    ]

                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    plugins: {

                        legend: {

                            labels: {

                                color:
                                    textColor

                            }

                        }

                    },

                    scales: {

                        y: {

                            beginAtZero: true,

                            ticks: {

                                color:
                                    textColor

                            },

                            grid: {

                                color:
                                    gridColor

                            }

                        },

                        x: {

                            ticks: {

                                color:
                                    textColor

                            },

                            grid: {

                                display:
                                    false

                            }

                        }

                    }

                }

            }
        );

    }
);

</script>


</body>

</html>