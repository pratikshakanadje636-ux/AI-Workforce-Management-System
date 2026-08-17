<?php

session_start();

require_once "../config/database.php";

/* =========================================================
   MANAGER LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role_id']) ||
    (int)$_SESSION['role_id'] !== 2
) {
    header("Location: ../authentication/login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];


/* =========================================================
   GET MANAGER INFORMATION
========================================================= */

$sql = "
    SELECT
        u.user_id,
        u.name AS user_name,
        u.email,
        u.status,

        e.employee_id,
        e.full_name,
        e.employee_code,
        e.designation,
        e.department_id,

        d.department_name

    FROM users u

    LEFT JOIN employees e
        ON u.user_id = e.user_id

    LEFT JOIN departments d
        ON e.department_id = d.department_id

    WHERE u.user_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Manager profile not found.");
}

$manager = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   MANAGER DATA
========================================================= */

$manager_name = !empty($manager['full_name'])
    ? $manager['full_name']
    : (!empty($manager['user_name'])
        ? $manager['user_name']
        : 'Manager');

$manager_initial = strtoupper(
    substr(trim($manager_name), 0, 1)
);

$department_id = !empty($manager['department_id'])
    ? (int)$manager['department_id']
    : 0;

$department_name = !empty($manager['department_name'])
    ? $manager['department_name']
    : 'Department';


/* =========================================================
   TEAM PERFORMANCE SUMMARY
========================================================= */

$total_employees = 0;
$average_performance = 0;
$total_workload = 0;
$total_completed_projects = 0;


/* =========================================================
   TOTAL EMPLOYEES
========================================================= */

if ($department_id > 0) {

    $sql = "
        SELECT COUNT(*) AS total
        FROM employees
        WHERE department_id = ?
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param("i", $department_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $total_employees = (int)($row['total'] ?? 0);

        $stmt->close();
    }
}


/* =========================================================
   PERFORMANCE + WORKLOAD + PROJECTS
========================================================= */

if ($department_id > 0) {

    $sql = "
        SELECT
            COALESCE(AVG(performance_score), 0) AS average_performance,
            COALESCE(SUM(workload), 0) AS total_workload,
            COALESCE(SUM(completed_projects), 0) AS total_completed_projects
        FROM employees
        WHERE department_id = ?
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param("i", $department_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $summary = $result->fetch_assoc();

        $average_performance = round(
            (float)($summary['average_performance'] ?? 0)
        );

        $total_workload = (int)(
            $summary['total_workload'] ?? 0
        );

        $total_completed_projects = (int)(
            $summary['total_completed_projects'] ?? 0
        );

        $stmt->close();
    }
}


/* =========================================================
   EMPLOYEE PERFORMANCE DATA
========================================================= */

$employees = [];

if ($department_id > 0) {

    $sql = "
        SELECT
            employee_id,
            full_name,
            employee_code,
            designation,
            performance_score,
            workload,
            completed_projects

        FROM employees

        WHERE department_id = ?

        ORDER BY
            performance_score DESC,
            full_name ASC
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param("i", $department_id);
        $stmt->execute();

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {

            $employees[] = $row;
        }

        $stmt->close();
    }
}


/* =========================================================
   TOP PERFORMER
========================================================= */

$top_performer = null;

if (!empty($employees)) {
    $top_performer = $employees[0];
}


/* =========================================================
   CHART DATA
========================================================= */

$chart_names = [];
$chart_scores = [];

foreach ($employees as $employee) {

    $chart_names[] = $employee['full_name'];

    $chart_scores[] = (int)(
        $employee['performance_score'] ?? 0
    );
}


/* =========================================================
   PERFORMANCE LEVEL
========================================================= */

function performanceLevel($score)
{
    $score = (int)$score;

    if ($score >= 90) {
        return [
            'Excellent',
            'success'
        ];
    }

    if ($score >= 75) {
        return [
            'Good',
            'primary'
        ];
    }

    if ($score >= 50) {
        return [
            'Average',
            'warning'
        ];
    }

    return [
        'Needs Attention',
        'danger'
    ];
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


    <!-- Chart.js -->

    <script
        src="https://cdn.jsdelivr.net/npm/chart.js"
    ></script>


    <!-- Manager CSS -->

    <link
        rel="stylesheet"
        href="manager.css"
    >

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

        <h5>
            AI Workforce
        </h5>

        <small>
            Manager Portal
        </small>

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
        class="nav-link-custom active"
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
        class="nav-link-custom"
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



<!-- =====================================================
     MAIN
===================================================== -->

<div class="main">


    <!-- =================================================
         TOPBAR
    ================================================= -->

    <header class="topbar">

        <div class="portal-title">

            <i class="bi bi-bar-chart-fill text-primary"></i>

            Manager Performance

        </div>


        <div class="user-area">

            <div class="text-end d-none d-sm-block">

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $manager_name
                    );
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
                echo htmlspecialchars(
                    $manager_initial
                );
                ?>

            </div>

        </div>

    </header>



    <!-- =================================================
         CONTENT
    ================================================= -->

    <main class="content">


        <!-- =================================================
             PAGE HEADER
        ================================================= -->

        <div class="page-header">

            <h2>
                Team Performance 📊
            </h2>

            <p>
                Monitor employee performance and workload
                in your department.
            </p>

        </div>



        <!-- =================================================
             STATISTICS
        ================================================= -->

        <div class="row g-4 mb-4">


            <!-- TEAM MEMBERS -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon blue">

                        <i class="bi bi-people-fill"></i>

                    </div>

                    <div class="stat-number">

                        <?php
                        echo $total_employees;
                        ?>

                    </div>

                    <div class="stat-label">

                        Team Members

                    </div>

                </div>

            </div>



            <!-- AVERAGE PERFORMANCE -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon green">

                        <i class="bi bi-graph-up-arrow"></i>

                    </div>

                    <div class="stat-number">

                        <?php
                        echo $average_performance;
                        ?>%

                    </div>

                    <div class="stat-label">

                        Average Performance

                    </div>

                </div>

            </div>



            <!-- TOTAL WORKLOAD -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon orange">

                        <i class="bi bi-speedometer2"></i>

                    </div>

                    <div class="stat-number">

                        <?php
                        echo $total_workload;
                        ?>

                    </div>

                    <div class="stat-label">

                        Total Workload

                    </div>

                </div>

            </div>



            <!-- COMPLETED PROJECTS -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon purple">

                        <i class="bi bi-check2-circle"></i>

                    </div>

                    <div class="stat-number">

                        <?php
                        echo $total_completed_projects;
                        ?>

                    </div>

                    <div class="stat-label">

                        Completed Projects

                    </div>

                </div>

            </div>

        </div>



        <!-- =================================================
             CHART + TOP PERFORMER
        ================================================= -->

        <div class="row g-4 mb-4">


            <!-- PERFORMANCE CHART -->

            <div class="col-lg-8">

                <div class="dashboard-card">

                    <div class="card-heading">

                        <h5>

                            <i class="bi bi-bar-chart-line text-primary"></i>

                            Employee Performance

                        </h5>


                        <span class="badge bg-primary">

                            <?php
                            echo $total_employees;
                            ?>

                            Members

                        </span>

                    </div>


                    <div class="card-body-custom">

                        <?php if (!empty($employees)): ?>

                            <canvas
                                id="performanceChart"
                                height="120"
                            ></canvas>

                        <?php else: ?>

                            <div class="empty-state">

                                <div class="empty-icon">

                                    <i class="bi bi-bar-chart"></i>

                                </div>

                                <h5>
                                    No Performance Data
                                </h5>

                                <p>
                                    There are currently no employees
                                    in your department.
                                </p>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>



            <!-- TOP PERFORMER -->

            <div class="col-lg-4">

                <div class="dashboard-card">

                    <div class="card-heading">

                        <h5>

                            <i class="bi bi-trophy-fill text-warning"></i>

                            Top Performer

                        </h5>

                    </div>


                    <div class="card-body-custom">


                        <?php if ($top_performer): ?>


                            <div class="top-performer">

                                <div class="top-avatar">

                                    <?php

                                    echo strtoupper(
                                        substr(
                                            $top_performer['full_name'],
                                            0,
                                            1
                                        )
                                    );

                                    ?>

                                </div>


                                <div>

                                    <div class="top-name">

                                        <?php

                                        echo htmlspecialchars(
                                            $top_performer['full_name']
                                        );

                                        ?>

                                    </div>


                                    <div class="top-role">

                                        <?php

                                        echo htmlspecialchars(
                                            $top_performer['designation']
                                            ?? 'Employee'
                                        );

                                        ?>

                                    </div>

                                </div>


                                <div class="score-circle">

                                    <?php

                                    echo (int)(
                                        $top_performer['performance_score']
                                        ?? 0
                                    );

                                    ?>%

                                </div>

                            </div>


                            <hr>


                            <div class="d-flex justify-content-between">

                                <span class="text-muted">
                                    Employee Code
                                </span>

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $top_performer['employee_code']
                                        ?? '-'
                                    );

                                    ?>

                                </strong>

                            </div>


                            <div class="d-flex justify-content-between mt-3">

                                <span class="text-muted">
                                    Workload
                                </span>

                                <strong>

                                    <?php

                                    echo (int)(
                                        $top_performer['workload']
                                        ?? 0
                                    );

                                    ?>

                                </strong>

                            </div>


                            <div class="d-flex justify-content-between mt-3">

                                <span class="text-muted">
                                    Completed Projects
                                </span>

                                <strong>

                                    <?php

                                    echo (int)(
                                        $top_performer['completed_projects']
                                        ?? 0
                                    );

                                    ?>

                                </strong>

                            </div>


                        <?php else: ?>


                            <div class="empty-state">

                                <div class="empty-icon">

                                    <i class="bi bi-person-x"></i>

                                </div>

                                <h6>
                                    No Team Members
                                </h6>

                                <p class="small">

                                    Add employees to this department
                                    to view performance.

                                </p>

                            </div>


                        <?php endif; ?>


                    </div>

                </div>

            </div>

        </div>



        <!-- =================================================
             EMPLOYEE PERFORMANCE TABLE
        ================================================= -->

        <div class="dashboard-card">

            <div class="card-heading">

                <h5>

                    <i class="bi bi-people text-primary"></i>

                    Employee Performance Details

                </h5>


                <span class="badge bg-primary">

                    <?php
                    echo count($employees);
                    ?>

                    Employees

                </span>

            </div>


            <div class="card-body-custom">


                <?php if (!empty($employees)): ?>


                    <div class="table-responsive">

                        <table class="table align-middle">

                            <thead>

                                <tr>

                                    <th>
                                        Employee
                                    </th>

                                    <th>
                                        Designation
                                    </th>

                                    <th>
                                        Performance
                                    </th>

                                    <th>
                                        Workload
                                    </th>

                                    <th>
                                        Projects
                                    </th>

                                    <th>
                                        Level
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php foreach ($employees as $employee): ?>


                                    <?php

                                    $score = (int)(
                                        $employee['performance_score']
                                        ?? 0
                                    );

                                    $workload = (int)(
                                        $employee['workload']
                                        ?? 0
                                    );

                                    $projects = (int)(
                                        $employee['completed_projects']
                                        ?? 0
                                    );

                                    [$level, $badge] =
                                        performanceLevel($score);

                                    ?>


                                    <tr>


                                        <!-- EMPLOYEE -->

                                        <td>

                                            <div
                                                class="d-flex align-items-center gap-3"
                                            >

                                                <div class="employee-avatar">

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

                                                    <strong>

                                                        <?php

                                                        echo htmlspecialchars(
                                                            $employee['full_name']
                                                        );

                                                        ?>

                                                    </strong>


                                                    <div class="small text-muted">

                                                        <?php

                                                        echo htmlspecialchars(
                                                            $employee['employee_code']
                                                            ?? '-'
                                                        );

                                                        ?>

                                                    </div>

                                                </div>

                                            </div>

                                        </td>



                                        <!-- DESIGNATION -->

                                        <td>

                                            <?php

                                            echo htmlspecialchars(
                                                $employee['designation']
                                                ?? 'Employee'
                                            );

                                            ?>

                                        </td>



                                        <!-- PERFORMANCE -->

                                        <td>

                                            <div
                                                class="performance-cell"
                                            >

                                                <div>

                                                    <strong>

                                                        <?php
                                                        echo $score;
                                                        ?>%

                                                    </strong>

                                                </div>


                                                <div
                                                    style="
                                                    width:120px;
                                                    height:8px;
                                                    background:#e2e8f0;
                                                    border-radius:10px;
                                                    overflow:hidden;
                                                    "
                                                >

                                                    <div
                                                        style="
                                                        width:<?php
                                                        echo min(
                                                            max($score, 0),
                                                            100
                                                        );
                                                        ?>%;
                                                        height:100%;
                                                        background:
                                                        <?php

                                                        if ($score >= 90) {
                                                            echo '#16a34a';
                                                        } elseif ($score >= 75) {
                                                            echo '#2563eb';
                                                        } elseif ($score >= 50) {
                                                            echo '#f59e0b';
                                                        } else {
                                                            echo '#ef4444';
                                                        }

                                                        ?>;
                                                        "
                                                    ></div>

                                                </div>

                                            </div>

                                        </td>



                                        <!-- WORKLOAD -->

                                        <td>

                                            <strong>

                                                <?php
                                                echo $workload;
                                                ?>

                                            </strong>

                                        </td>



                                        <!-- PROJECTS -->

                                        <td>

                                            <strong>

                                                <?php
                                                echo $projects;
                                                ?>

                                            </strong>

                                        </td>



                                        <!-- LEVEL -->

                                        <td>

                                            <span
                                                class="
                                                status-badge
                                                status-<?php
                                                echo htmlspecialchars(
                                                    $badge
                                                );
                                                ?>"
                                            >

                                                <?php
                                                echo htmlspecialchars(
                                                    $level
                                                );
                                                ?>

                                            </span>

                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="empty-state">

                        <div class="empty-icon">

                            <i class="bi bi-people"></i>

                        </div>

                        <h5>
                            No Employees Found
                        </h5>

                        <p>

                            There are currently no employees
                            assigned to your department.

                        </p>

                    </div>


                <?php endif; ?>


            </div>

        </div>


    </main>

</div>



<!-- =====================================================
     CHART SCRIPT
===================================================== -->

<?php if (!empty($employees)): ?>

<script>

const employeeNames =
    <?php echo json_encode($chart_names); ?>;

const performanceScores =
    <?php echo json_encode($chart_scores); ?>;


const ctx =
    document.getElementById(
        'performanceChart'
    );


if (ctx) {

    new Chart(
        ctx,
        {

            type: 'bar',

            data: {

                labels: employeeNames,

                datasets: [

                    {

                        label:
                            'Performance Score (%)',

                        data:
                            performanceScores,

                        backgroundColor:
                            '#2563eb',

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

                        display: false

                    }

                },


                scales: {

                    y: {

                        beginAtZero: true,

                        max: 100,

                        ticks: {

                            stepSize: 20

                        }

                    },


                    x: {

                        grid: {

                            display: false

                        }

                    }

                }

            }

        }
    );

}

</script>

<?php endif; ?>


</body>

</html>