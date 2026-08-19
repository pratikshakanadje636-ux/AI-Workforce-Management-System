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
   GET MANAGER INFORMATION
=========================== */

$sql = "
SELECT
    users.user_id,
    users.email,
    users.status,
    employees.employee_id,
    employees.full_name,
    employees.employee_code,
    employees.designation,
    employees.department_id,
    employees.profile_picture,
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

$manager_name = $manager['full_name'] ?? 'Manager';

$manager_initial = strtoupper(
    substr($manager_name, 0, 1)
);

$profile_picture = $manager['profile_picture'] ?? '';


/* ===========================
   EMPLOYEE STATISTICS
=========================== */

$total_employees = 0;

$sql = "
SELECT COUNT(*) AS total
FROM employees
WHERE department_id = ?
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param(
        "i",
        $manager['department_id']
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    $total_employees = $row['total'] ?? 0;
}


/* ===========================
   TASK STATISTICS
=========================== */

$total_tasks = 0;
$completed_tasks = 0;
$pending_tasks = 0;
$in_progress_tasks = 0;


/*
   Tasks belonging to employees
   in manager's department
*/

$sql = "
SELECT

    COUNT(*) AS total_tasks,

    SUM(
        CASE
            WHEN tasks.status = 'Completed'
            THEN 1
            ELSE 0
        END
    ) AS completed_tasks,

    SUM(
        CASE
            WHEN tasks.status = 'Pending'
            THEN 1
            ELSE 0
        END
    ) AS pending_tasks,

    SUM(
        CASE
            WHEN tasks.status = 'In Progress'
            THEN 1
            ELSE 0
        END
    ) AS in_progress_tasks

FROM tasks

INNER JOIN employees
    ON tasks.employee_id = employees.employee_id

WHERE employees.department_id = ?
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param(
        "i",
        $manager['department_id']
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $stats = $result->fetch_assoc();

    $total_tasks =
        $stats['total_tasks'] ?? 0;

    $completed_tasks =
        $stats['completed_tasks'] ?? 0;

    $pending_tasks =
        $stats['pending_tasks'] ?? 0;

    $in_progress_tasks =
        $stats['in_progress_tasks'] ?? 0;
}


/* ===========================
   TEAM PERFORMANCE
=========================== */

$team_performance = 0;

$sql = "
SELECT
    AVG(performance_score) AS average_score

FROM employees

WHERE department_id = ?
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param(
        "i",
        $manager['department_id']
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $performance =
        $result->fetch_assoc();

    $team_performance =
        $performance['average_score'] ?? 0;
}

$team_performance =
    round($team_performance);


/* ===========================
   COMPLETION RATE
=========================== */

if ($total_tasks > 0) {

    $completion_rate =
        round(
            ($completed_tasks / $total_tasks) * 100
        );

} else {

    $completion_rate = 0;

}


/* ===========================
   RECENT TASKS
=========================== */

$recent_tasks = [];

$sql = "
SELECT

    tasks.task_id,
    tasks.task_title,
    tasks.status,

    employees.full_name

FROM tasks

INNER JOIN employees
    ON tasks.employee_id = employees.employee_id

WHERE employees.department_id = ?

ORDER BY tasks.task_id DESC

LIMIT 5
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param(
        "i",
        $manager['department_id']
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $recent_tasks[] = $row;

    }
}


/* ===========================
   TEAM MEMBERS
=========================== */

$team_members = [];

$sql = "
SELECT

    employee_id,
    full_name,
    employee_code,
    designation,
    performance_score,
    workload

FROM employees

WHERE department_id = ?

ORDER BY full_name ASC

LIMIT 6
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param(
        "i",
        $manager['department_id']
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $team_members[] = $row;

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

<title>
    Manager Dashboard | AI Workforce
</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
>


<link
    rel="stylesheet"
    href="manager.css"
>


<style>

/* =========================================================
   MANAGER DASHBOARD PROFILE PICTURE
========================================================= */

.avatar img {

    width: 100%;

    height: 100%;

    border-radius: 50%;

    object-fit: cover;

    display: block;

}


/* =========================================================
   SIDEBAR LOGO OVERRIDE
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

}


.brand-icon img {

    width: 38px;

    height: 38px;

    object-fit: contain;

    display: block;

}

</style>

</head>


<body>


<?php include "../config/page_actions.php"; ?>


<!-- ===========================
     SIDEBAR
=========================== -->

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
                Manager Portal
            </small>

        </div>

    </div>


    <!-- DASHBOARD -->

    <a
        href="dashboard.php"
        class="nav-link-custom active"
    >

        <i class="bi bi-grid-1x2-fill"></i>

        <span class="nav-text">

            Dashboard

        </span>

    </a>


    <!-- TEAM -->

    <a
        href="team.php"
        class="nav-link-custom"
    >

        <i class="bi bi-people-fill"></i>

        <span class="nav-text">

            My Team

        </span>

    </a>


    <!-- TASKS -->

    <a
        href="tasks.php"
        class="nav-link-custom"
    >

        <i class="bi bi-list-task"></i>

        <span class="nav-text">

            Tasks

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


    <!-- PROJECTS -->

    <a
        href="projects.php"
        class="nav-link-custom"
    >

        <i class="bi bi-kanban-fill"></i>

        <span class="nav-text">

            Projects

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


    <div style="height: 30%;"></div>


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


<!-- ===========================
     MAIN
=========================== -->

<div class="main">


    <!-- TOPBAR -->

    <header class="topbar">


        <div class="portal-title">

            <i class="bi bi-speedometer2 text-primary"></i>

            Manager Dashboard

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


            <!-- MANAGER PROFILE AVATAR -->

            <div class="avatar">

                <?php if (!empty($profile_picture)): ?>

                    <img
                        src="../assets/images/profiles/<?php echo htmlspecialchars($profile_picture); ?>"
                        alt="Profile Picture"
                    >

                <?php else: ?>

                    <?php

                    echo htmlspecialchars(
                        $manager_initial
                    );

                    ?>

                <?php endif; ?>

            </div>


        </div>


    </header>


    <!-- CONTENT -->

    <main class="content">


        <!-- WELCOME -->

        <div class="welcome-hero">


            <h2>

                Welcome back,

                <?php

                echo htmlspecialchars(
                    $manager_name
                );

                ?>

                👋

            </h2>


            <p>

                Manage your team, monitor tasks and track overall productivity.

            </p>


            <span class="badge bg-light text-primary">

                <i class="bi bi-building"></i>

                <?php

                echo htmlspecialchars(
                    $manager['department_name']
                    ?? 'Department'
                );

                ?>

            </span>


        </div>


        <!-- ===========================
             STATISTICS
        =========================== -->

        <div class="row g-4">


            <!-- EMPLOYEES -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">


                    <div class="stat-icon blue">

                        <i class="bi bi-people-fill"></i>

                    </div>


                    <div
                        class="stat-number"
                        id="totalEmployees"
                    >
                        0
                    </div>


                    <div class="stat-label">

                        Team Members

                    </div>


                </div>

            </div>


            <!-- TOTAL TASKS -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">


                    <div class="stat-icon purple">

                        <i class="bi bi-clipboard-check"></i>

                    </div>


                    <div
                        class="stat-number"
                        id="totalTasks"
                    >
                        0
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


                    <div
                        class="stat-number"
                        id="completedTasks"
                    >
                        0
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


                    <div
                        class="stat-number"
                        id="pendingTasks"
                    >
                        0
                    </div>


                    <div class="stat-label">

                        Pending Tasks

                    </div>


                </div>

            </div>


        </div>


        <!-- ===========================
             PERFORMANCE + COMPLETION
        =========================== -->

        <div class="row g-4 mt-1">


            <!-- TEAM PERFORMANCE -->

            <div class="col-lg-6">

                <div class="dashboard-card">


                    <div class="card-heading">

                        <h5>

                            <i class="bi bi-graph-up-arrow text-primary"></i>

                            Team Performance

                        </h5>

                    </div>


                    <div class="card-body-custom">


                        <div class="d-flex justify-content-between mb-2">

                            <span class="text-muted">

                                Average Team Score

                            </span>


                            <strong>

                                <?php

                                echo $team_performance;

                                ?>%

                            </strong>

                        </div>


                        <div class="progress mb-4">

                            <div
                                class="progress-bar bg-primary"
                                style="
                                width:
                                <?php

                                echo $team_performance;

                                ?>%;
                                "
                            >

                            </div>

                        </div>


                        <div class="text-muted">

                            Monitor your team's overall productivity and performance.

                        </div>


                    </div>

                </div>

            </div>


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
                            >

                            </div>

                        </div>


                        <div class="row text-center">

                            <div class="col-4">

                                <strong class="text-success">

                                    <?php

                                    echo $completed_tasks;

                                    ?>

                                </strong>

                                <div class="small text-muted">

                                    Completed

                                </div>

                            </div>


                            <div class="col-4">

                                <strong class="text-warning">

                                    <?php

                                    echo $pending_tasks;

                                    ?>

                                </strong>

                                <div class="small text-muted">

                                    Pending

                                </div>

                            </div>


                            <div class="col-4">

                                <strong class="text-primary">

                                    <?php

                                    echo $in_progress_tasks;

                                    ?>

                                </strong>

                                <div class="small text-muted">

                                    In Progress

                                </div>

                            </div>

                        </div>


                    </div>

                </div>

            </div>


        </div>


        <!-- ===========================
             TEAM + RECENT TASKS
        =========================== -->

        <div class="row g-4 mt-1">


            <!-- TEAM MEMBERS -->

            <div class="col-lg-6">

                <div class="dashboard-card">


                    <div class="card-heading">

                        <h5>

                            <i class="bi bi-people text-primary"></i>

                            My Team

                        </h5>

                    </div>


                    <div class="card-body-custom">


                        <?php if (count($team_members) > 0): ?>


                            <?php foreach (
                                $team_members
                                as $member
                            ): ?>


                                <div class="team-member">


                                    <div class="team-avatar">

                                        <?php

                                        echo strtoupper(
                                            substr(
                                                $member['full_name'],
                                                0,
                                                1
                                            )
                                        );

                                        ?>

                                    </div>


                                    <div class="team-info">

                                        <div class="team-name">

                                            <?php

                                            echo htmlspecialchars(
                                                $member['full_name']
                                            );

                                            ?>

                                        </div>


                                        <div class="team-role">

                                            <?php

                                            echo htmlspecialchars(
                                                $member['designation']
                                                ?? 'Employee'
                                            );

                                            ?>

                                        </div>

                                    </div>


                                    <div>

                                        <span class="badge bg-light text-primary">

                                            <?php

                                            echo htmlspecialchars(
                                                $member['performance_score']
                                                ?? 0
                                            );

                                            ?>%

                                        </span>

                                    </div>


                                </div>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <div class="text-center text-muted py-4">

                                <i
                                    class="bi bi-people"
                                    style="font-size:35px;"
                                ></i>

                                <div class="mt-2">

                                    No team members found.

                                </div>

                            </div>


                        <?php endif; ?>


                    </div>

                </div>

            </div>


            <!-- RECENT TASKS -->

            <div class="col-lg-6">

                <div class="dashboard-card">


                    <div class="card-heading">

                        <h5>

                            <i class="bi bi-list-check text-primary"></i>

                            Recent Tasks

                        </h5>

                    </div>


                    <div class="card-body-custom">


                        <?php if (count($recent_tasks) > 0): ?>


                            <?php foreach (
                                $recent_tasks
                                as $task
                            ): ?>


                                <div class="task-row">


                                    <div>

                                        <div class="task-title">

                                            <?php

                                            echo htmlspecialchars(
                                                $task['task_title']
                                            );

                                            ?>

                                        </div>


                                        <div class="task-user">

                                            <?php

                                            echo htmlspecialchars(
                                                $task['full_name']
                                            );

                                            ?>

                                        </div>

                                    </div>


                                    <?php

                                    $status =
                                        $task['status'] ?? 'Pending';


                                    if (
                                        strtolower($status)
                                        == 'completed'
                                    ) {

                                        $status_class =
                                            'status-completed';

                                    }

                                    elseif (
                                        strtolower($status)
                                        == 'in progress'
                                    ) {

                                        $status_class =
                                            'status-progress';

                                    }

                                    else {

                                        $status_class =
                                            'status-pending';

                                    }

                                    ?>


                                    <span
                                        class="
                                        status-badge
                                        <?php

                                        echo $status_class;

                                        ?>
                                        "
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $status
                                        );

                                        ?>

                                    </span>


                                </div>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <div class="text-center text-muted py-4">

                                <i
                                    class="bi bi-clipboard"
                                    style="font-size:35px;"
                                ></i>

                                <div class="mt-2">

                                    No tasks found.

                                </div>

                            </div>


                        <?php endif; ?>


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


<script>

async function updateManagerDashboard() {

    try {

        const response = await fetch(
            "api/dashboard_data.php"
        );

        const data = await response.json();

        if (!data.success) {

            console.error(
                "Failed to load dashboard data"
            );

            return;

        }


        /* EMPLOYEES */

        document.getElementById(
            "totalEmployees"
        ).textContent =
            data.employees.total;


        /* TASKS */

        document.getElementById(
            "totalTasks"
        ).textContent =
            data.tasks.total;

        document.getElementById(
            "completedTasks"
        ).textContent =
            data.tasks.completed;

        document.getElementById(
            "pendingTasks"
        ).textContent =
            data.tasks.pending;

        const inProgressElement =
            document.getElementById(
                "inProgressTasks"
            );

        if (inProgressElement) {

            inProgressElement.textContent =
                data.tasks.in_progress;

        }


        /* PROJECTS */

        const totalProjects =
            document.getElementById(
                "totalProjects"
            );

        const activeProjects =
            document.getElementById(
                "activeProjects"
            );

        const completedProjects =
            document.getElementById(
                "completedProjects"
            );


        if (totalProjects) {

            totalProjects.textContent =
                data.projects.total;

        }

        if (activeProjects) {

            activeProjects.textContent =
                data.projects.active;

        }

        if (completedProjects) {

            completedProjects.textContent =
                data.projects.completed;

        }

    }

    catch (error) {

        console.error(
            "Dashboard update error:",
            error
        );

    }

}


/* LOAD IMMEDIATELY */

updateManagerDashboard();


/* UPDATE EVERY 5 SECONDS */

setInterval(
    updateManagerDashboard,
    5000
);

</script>

</body>

</html>