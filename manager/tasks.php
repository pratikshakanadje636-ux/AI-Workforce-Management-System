```php
<?php

session_start();

require_once "../config/database.php";

/* =========================================================
   MANAGER LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role_id']) ||
    (int) $_SESSION['role_id'] !== 2
) {
    header("Location: ../authentication/login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];


/* =========================================================
   GET MANAGER INFORMATION
========================================================= */

$sql = "
    SELECT
        users.user_id,
        users.email,
        users.status AS account_status,

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

    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    die("Manager profile not found.");
}

$manager = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   MANAGER DATA
========================================================= */

$manager_employee_id = (int) ($manager['employee_id'] ?? 0);

$manager_name = trim(
    $manager['full_name'] ?? ''
);

if ($manager_name === '') {
    $manager_name = 'Manager';
}

$manager_initial = strtoupper(
    substr($manager_name, 0, 1)
);

$profile_picture = trim(
    $manager['profile_picture'] ?? ''
);

$department_id = (int) (
    $manager['department_id'] ?? 0
);

$department_name = trim(
    $manager['department_name'] ?? 'Department'
);

if ($department_name === '') {
    $department_name = 'Department';
}


/* =========================================================
   DEPARTMENT VALIDATION
========================================================= */

if ($department_id <= 0) {

    die("
        <div style='
            font-family: Arial, sans-serif;
            padding: 40px;
            text-align: center;
        '>
            <h2>Department Not Assigned</h2>
            <p>
                Your manager account has not been assigned
                to a department yet.
            </p>
        </div>
    ");
}


/* =========================================================
   TASK STATISTICS
   Manager's own employee record is excluded.
========================================================= */

$total_tasks = 0;
$pending_tasks = 0;
$progress_tasks = 0;
$completed_tasks = 0;

$sql = "
    SELECT

        COUNT(*) AS total_tasks,

        COALESCE(
            SUM(
                CASE
                    WHEN tasks.status = 'Pending'
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS pending_tasks,

        COALESCE(
            SUM(
                CASE
                    WHEN tasks.status = 'In Progress'
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS progress_tasks,

        COALESCE(
            SUM(
                CASE
                    WHEN tasks.status = 'Completed'
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS completed_tasks

    FROM tasks

    INNER JOIN employees
        ON tasks.employee_id = employees.employee_id

    WHERE employees.department_id = ?
    AND employees.employee_id != ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param(
    "ii",
    $department_id,
    $manager_employee_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    $total_tasks = (int) (
        $row['total_tasks'] ?? 0
    );

    $pending_tasks = (int) (
        $row['pending_tasks'] ?? 0
    );

    $progress_tasks = (int) (
        $row['progress_tasks'] ?? 0
    );

    $completed_tasks = (int) (
        $row['completed_tasks'] ?? 0
    );
}

$stmt->close();


/* =========================================================
   COMPLETION PERCENTAGE
========================================================= */

$completion_percentage = 0;

if ($total_tasks > 0) {

    $completion_percentage = (int) round(
        ($completed_tasks / $total_tasks) * 100
    );
}


/* =========================================================
   GET DEPARTMENT TASKS
   Manager's own tasks are excluded.
========================================================= */

$tasks = [];

$sql = "
    SELECT

        tasks.task_id,
        tasks.project_id,
        tasks.employee_id,
        tasks.task_title,
        tasks.description,
        tasks.priority,
        tasks.status,
        tasks.start_date,
        tasks.due_date,
        tasks.completed_date,
        tasks.created_at,

        employees.full_name,
        employees.employee_code,
        employees.designation,

        projects.project_name

    FROM tasks

    INNER JOIN employees
        ON tasks.employee_id = employees.employee_id

    LEFT JOIN projects
        ON tasks.project_id = projects.project_id

    WHERE employees.department_id = ?
    AND employees.employee_id != ?

    ORDER BY

        CASE
            WHEN tasks.status = 'Pending'
                THEN 1

            WHEN tasks.status = 'In Progress'
                THEN 2

            WHEN tasks.status = 'Completed'
                THEN 3

            ELSE 4
        END,

        CASE
            WHEN tasks.priority = 'High'
                THEN 1

            WHEN tasks.priority = 'Medium'
                THEN 2

            WHEN tasks.priority = 'Low'
                THEN 3

            ELSE 4
        END,

        CASE
            WHEN tasks.due_date IS NULL
                THEN 1
            ELSE 0
        END,

        tasks.due_date ASC,
        tasks.task_id DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param(
    "ii",
    $department_id,
    $manager_employee_id
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $tasks[] = $row;
}

$stmt->close();


/* =========================================================
   HELPER FUNCTIONS
========================================================= */

function taskStatusClass($status)
{
    switch ($status) {

        case "Completed":
            return "status-completed";

        case "In Progress":
            return "status-progress";

        case "Pending":
            return "status-pending";

        default:
            return "status-pending";
    }
}


function priorityClass($priority)
{
    switch ($priority) {

        case "High":
            return "priority-high";

        case "Low":
            return "priority-low";

        case "Medium":
            return "priority-medium";

        default:
            return "priority-medium";
    }
}


function isTaskOverdue($due_date, $status)
{
    if (
        empty($due_date) ||
        $status === "Completed"
    ) {
        return false;
    }

    return strtotime($due_date) < strtotime(date("Y-m-d"));
}


function formatTaskDate($date)
{
    if (empty($date)) {
        return "Not set";
    }

    $timestamp = strtotime($date);

    if (!$timestamp) {
        return "Not set";
    }

    return date("d M Y", $timestamp);
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

    <title>Manager Tasks | AI Workforce</title>


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >


    <!-- =====================================================
         EXISTING MANAGER THEME
         DO NOT REMOVE
    ====================================================== -->

    <link
        rel="stylesheet"
        href="manager.css"
    >


    <!-- =====================================================
         TASK PAGE ONLY STYLES
    ====================================================== -->

    <style>

        .task-toolbar {
            background: transparent;
            border-radius: 0;
            padding: 0;
            margin-top: 25px;
            margin-bottom: 20px;
            box-shadow: none;
        }


        .task-search,
        .task-filter {
            min-height: 42px;
            border-radius: 10px;
        }


        .task-search:focus,
        .task-filter:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.10);
        }


        .completion-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 22px;
            margin-top: 25px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
        }


        .completion-title {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 8px;
        }


        .completion-number {
            font-size: 28px;
            font-weight: 700;
            color: #2563eb;
        }


        .task-item.overdue {
            border-left: 4px solid #dc2626;
        }


        .overdue-label {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 8px;
            border-radius: 6px;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 11px;
            font-weight: 600;
        }


        .task-description {
            margin-top: 8px;
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
        }


        .task-date-row {
            display: flex;
            gap: 18px;
            margin-top: 10px;
            flex-wrap: wrap;
        }


        .task-date-item {
            font-size: 12px;
            color: #64748b;
        }


        .task-date-item strong {
            color: #334155;
        }


        .no-results {
            display: none;
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }


        .department-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            padding: 5px 10px;
            border-radius: 20px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 600;
        }


        .task-summary {
            font-size: 12px;
            color: #64748b;
        }


        /* =====================================================
           MANAGER PROFILE PICTURE
        ====================================================== */

        .avatar img {

            width: 100%;

            height: 100%;

            border-radius: 50%;

            object-fit: cover;

            display: block;

        }


        /* =====================================================
           SIDEBAR LOGO
        ====================================================== */

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


        @media (max-width: 768px) {

            .task-item {
                overflow-x: auto;
            }

            .completion-card {
                padding: 18px;
            }

            .task-toolbar {
                padding: 15px;
            }

        }

    </style>

</head>


<body>

<?php include "../config/page_actions.php"; ?>


<!-- =====================================================
     SIDEBAR
====================================================== -->

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
        class="nav-link-custom active"
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
        class="nav-link-custom"
    >

        <i class="bi bi-person-fill"></i>

        <span class="nav-text">
            My Profile
        </span>

    </a>


    <div style="height:30%;"></div>


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
====================================================== -->

<div class="main">


    <!-- =================================================
         TOPBAR
    ================================================== -->

    <header class="topbar">


        <div class="portal-title">

            <i class="bi bi-list-task text-primary"></i>

            Manager Tasks

        </div>


        <div class="user-area">


            <div class="text-end d-none d-sm-block">

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $manager_name,
                        ENT_QUOTES,
                        'UTF-8'
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

                <?php if (!empty($profile_picture)): ?>

                    <img
                        src="../assets/images/profiles/<?php echo htmlspecialchars($profile_picture, ENT_QUOTES, 'UTF-8'); ?>"
                        alt="Profile Picture"
                    >

                <?php else: ?>

                    <?php
                    echo htmlspecialchars(
                        $manager_initial,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>

                <?php endif; ?>

            </div>


        </div>


    </header>


    <!-- =================================================
         CONTENT
    ================================================== -->

    <main class="content">


        <!-- =================================================
             PAGE HEADER
        ================================================== -->

        <div class="page-header">


            <h2>
                Task Management 📋
            </h2>


            <p>
                Monitor tasks assigned to employees
                in your department.
            </p>


            <div class="department-label">

                <i class="bi bi-building"></i>

                <?php
                echo htmlspecialchars(
                    $department_name,
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>

            </div>


        </div>


        <!-- =================================================
             STATISTICS
        ================================================== -->

        <div class="row g-4">


            <!-- TOTAL -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon blue">

                        <i class="bi bi-list-check"></i>

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


            <!-- PENDING -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon orange">

                        <i class="bi bi-clock-history"></i>

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
                        echo $progress_tasks;
                        ?>

                    </div>


                    <div class="stat-label">

                        In Progress

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


        </div>


        <!-- =================================================
             COMPLETION SUMMARY
        ================================================== -->

        <div class="completion-card">


            <div class="row align-items-center">


                <div class="col-md-8">


                    <div class="completion-title">

                        Department Task Completion

                    </div>


                    <div
                        class="progress"
                        style="height:10px;"
                    >

                        <div
                            class="progress-bar bg-success"
                            role="progressbar"
                            style="
                                width:
                                <?php
                                echo $completion_percentage;
                                ?>%;
                            "
                            aria-valuenow="<?php echo $completion_percentage; ?>"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ></div>

                    </div>


                    <div class="small text-muted mt-2">

                        <?php
                        echo $completed_tasks;
                        ?>

                        of

                        <?php
                        echo $total_tasks;
                        ?>

                        tasks completed

                    </div>


                </div>


                <div
                    class="col-md-4 text-md-end mt-3 mt-md-0"
                >

                    <div class="completion-number">

                        <?php
                        echo $completion_percentage;
                        ?>%

                    </div>


                    <div class="small text-muted">

                        Completion Rate

                    </div>

                </div>


            </div>

        </div>


        <!-- =================================================
             SEARCH AND FILTER
        ================================================== -->

        <div class="task-toolbar">


            <div class="row g-3 align-items-center">


                <!-- SEARCH -->

                <div class="col-lg-6">

                    <div class="input-group">

                        <span class="input-group-text">

                            <i class="bi bi-search"></i>

                        </span>


                        <input
                            type="text"
                            id="taskSearch"
                            class="form-control task-search"
                            placeholder="Search task, employee or project..."
                            autocomplete="off"
                        >

                    </div>

                </div>


                <!-- STATUS -->

                <div class="col-lg-3">

                    <select
                        id="statusFilter"
                        class="form-select task-filter"
                    >

                        <option value="all">
                            All Status
                        </option>

                        <option value="Pending">
                            Pending
                        </option>

                        <option value="In Progress">
                            In Progress
                        </option>

                        <option value="Completed">
                            Completed
                        </option>

                    </select>

                </div>


                <!-- PRIORITY -->

                <div class="col-lg-3">

                    <select
                        id="priorityFilter"
                        class="form-select task-filter"
                    >

                        <option value="all">
                            All Priorities
                        </option>

                        <option value="High">
                            High
                        </option>

                        <option value="Medium">
                            Medium
                        </option>

                        <option value="Low">
                            Low
                        </option>

                    </select>

                </div>


            </div>


        </div>


        <!-- =================================================
             TASK CARD
        ================================================== -->

        <div class="task-card">


            <div class="card-heading">


                <h5>

                    <i class="bi bi-kanban text-primary"></i>

                    Department Tasks

                </h5>


                <span
                    class="badge bg-primary"
                    id="visibleTaskCount"
                >

                    <?php
                    echo count($tasks);
                    ?>

                    Tasks

                </span>


            </div>


            <div class="card-body-custom">


                <?php if (count($tasks) > 0): ?>


                    <?php foreach ($tasks as $task): ?>


                        <?php

                        $status =
                            $task['status']
                            ?? 'Pending';

                        $priority =
                            $task['priority']
                            ?? 'Medium';

                        $employee_name =
                            trim(
                                $task['full_name']
                                ?? 'Unknown Employee'
                            );

                        if ($employee_name === '') {

                            $employee_name =
                                'Unknown Employee';

                        }

                        $employee_code =
                            $task['employee_code']
                            ?? '';

                        $designation =
                            $task['designation']
                            ?? '';

                        $project_name =
                            trim(
                                $task['project_name']
                                ?? 'No Project'
                            );

                        if ($project_name === '') {

                            $project_name =
                                'No Project';

                        }

                        $task_title =
                            trim(
                                $task['task_title']
                                ?? 'Untitled Task'
                            );

                        if ($task_title === '') {

                            $task_title =
                                'Untitled Task';

                        }

                        $overdue =
                            isTaskOverdue(
                                $task['due_date']
                                ?? null,
                                $status
                            );

                        ?>


                        <!-- =================================================
                             TASK ITEM
                        ================================================== -->

                        <div
                            class="
                                task-item
                                <?php
                                echo $overdue
                                    ? 'overdue'
                                    : '';
                                ?>
                            "

                            data-title="<?php
                                echo htmlspecialchars(
                                    strtolower(
                                        $task_title
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>"

                            data-employee="<?php
                                echo htmlspecialchars(
                                    strtolower(
                                        $employee_name
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>"

                            data-code="<?php
                                echo htmlspecialchars(
                                    strtolower(
                                        $employee_code
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>"

                            data-designation="<?php
                                echo htmlspecialchars(
                                    strtolower(
                                        $designation
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>"

                            data-project="<?php
                                echo htmlspecialchars(
                                    strtolower(
                                        $project_name
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>"

                            data-status="<?php
                                echo htmlspecialchars(
                                    $status,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>"

                            data-priority="<?php
                                echo htmlspecialchars(
                                    $priority,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                        >


                            <div class="task-main">


                                <!-- TASK ICON -->

                                <div class="task-icon">

                                    <?php
                                    if (
                                        $status === "Completed"
                                    ):
                                    ?>

                                        <i
                                            class="bi bi-check-circle-fill"
                                        ></i>

                                    <?php
                                    elseif (
                                        $status === "In Progress"
                                    ):
                                    ?>

                                        <i
                                            class="bi bi-arrow-repeat"
                                        ></i>

                                    <?php
                                    else:
                                    ?>

                                        <i
                                            class="bi bi-check2-square"
                                        ></i>

                                    <?php endif; ?>

                                </div>


                                <!-- TASK INFORMATION -->

                                <div class="task-info">


                                    <div class="task-title">

                                        <?php
                                        echo htmlspecialchars(
                                            $task_title,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>

                                    </div>


                                    <div class="task-project">

                                        <i class="bi bi-kanban"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $project_name,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>

                                    </div>


                                    <?php
                                    if (
                                        !empty(
                                            $task['description']
                                        )
                                    ):
                                    ?>

                                        <div
                                            class="task-description"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $task['description'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>

                                        </div>

                                    <?php endif; ?>


                                    <!-- DATES -->

                                    <div
                                        class="task-date-row"
                                    >


                                        <?php
                                        if (
                                            !empty(
                                                $task['start_date']
                                            )
                                        ):
                                        ?>

                                            <div
                                                class="task-date-item"
                                            >

                                                <i
                                                    class="bi bi-calendar-event"
                                                ></i>

                                                Start:

                                                <strong>

                                                    <?php
                                                    echo formatTaskDate(
                                                        $task['start_date']
                                                    );
                                                    ?>

                                                </strong>

                                            </div>

                                        <?php endif; ?>


                                        <?php
                                        if (
                                            !empty(
                                                $task['due_date']
                                            )
                                        ):
                                        ?>

                                            <div
                                                class="task-date-item"
                                            >

                                                <i
                                                    class="bi bi-calendar-x"
                                                ></i>

                                                Due:

                                                <strong>

                                                    <?php
                                                    echo formatTaskDate(
                                                        $task['due_date']
                                                    );
                                                    ?>

                                                </strong>

                                            </div>

                                        <?php endif; ?>


                                        <?php
                                        if (
                                            !empty(
                                                $task['completed_date']
                                            )
                                        ):
                                        ?>

                                            <div
                                                class="task-date-item"
                                            >

                                                <i
                                                    class="bi bi-check-circle"
                                                ></i>

                                                Completed:

                                                <strong>

                                                    <?php
                                                    echo formatTaskDate(
                                                        $task['completed_date']
                                                    );
                                                    ?>

                                                </strong>

                                            </div>

                                        <?php endif; ?>


                                    </div>


                                    <!-- OVERDUE -->

                                    <?php if ($overdue): ?>

                                        <span
                                            class="overdue-label"
                                        >

                                            <i
                                                class="bi bi-exclamation-triangle"
                                            ></i>

                                            Overdue

                                        </span>

                                    <?php endif; ?>


                                </div>


                                <!-- EMPLOYEE -->

                                <div class="employee-box">


                                    <div class="employee-name">

                                        <i
                                            class="bi bi-person text-primary"
                                        ></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $employee_name,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>

                                    </div>


                                    <div class="employee-code">

                                        <?php

                                        if (
                                            $employee_code !== ''
                                        ) {

                                            echo htmlspecialchars(
                                                $employee_code,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );

                                        } else {

                                            echo 'Employee';

                                        }

                                        ?>

                                    </div>


                                    <?php
                                    if (
                                        $designation !== ''
                                    ):
                                    ?>

                                        <div
                                            class="employee-code"
                                            style="margin-top:3px;"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $designation,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>

                                        </div>

                                    <?php endif; ?>


                                </div>


                                <!-- PRIORITY -->

                                <div>

                                    <span
                                        class="
                                            priority-badge
                                            <?php
                                            echo priorityClass(
                                                $priority
                                            );
                                            ?>
                                        "
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $priority,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>

                                    </span>

                                </div>


                                <!-- STATUS -->

                                <div>

                                    <span
                                        class="
                                            status-badge
                                            <?php
                                            echo taskStatusClass(
                                                $status
                                            );
                                            ?>
                                        "
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $status,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>

                                    </span>

                                </div>


                                <!-- DUE DATE -->

                                <div class="date-box">


                                    <?php
                                    if (
                                        !empty(
                                            $task['due_date']
                                        )
                                    ):
                                    ?>

                                        <div>
                                            Due
                                        </div>


                                        <strong>

                                            <?php
                                            echo formatTaskDate(
                                                $task['due_date']
                                            );
                                            ?>

                                        </strong>


                                    <?php else: ?>


                                        <div>
                                            No due date
                                        </div>


                                    <?php endif; ?>


                                </div>


                            </div>


                        </div>


                    <?php endforeach; ?>


                    <!-- NO SEARCH RESULTS -->

                    <div
                        id="noSearchResults"
                        class="no-results"
                    >

                        <i
                            class="bi bi-search"
                            style="font-size:35px;"
                        ></i>


                        <h5 class="mt-3">
                            No Matching Tasks
                        </h5>


                        <p>
                            Try changing your search
                            or filters.
                        </p>


                    </div>


                <?php else: ?>


                    <!-- EMPTY STATE -->

                    <div class="empty-state">


                        <div class="empty-icon">

                            <i
                                class="bi bi-clipboard-x"
                            ></i>

                        </div>


                        <h5>
                            No Tasks Found
                        </h5>


                        <p>

                            There are currently no tasks
                            assigned to employees in your
                            department.

                        </p>


                    </div>


                <?php endif; ?>


            </div>


        </div>


        <!-- =================================================
             FOOTER
        ================================================== -->

        <div class="text-center mt-4">

            <small class="text-muted">

                ©

                <?php
                echo date("Y");
                ?>

                AI Workforce Management System

            </small>

        </div>


    </main>


</div>


<!-- =====================================================
     JAVASCRIPT
====================================================== -->

<script>

/* =========================================================
   TASK SEARCH + FILTER
========================================================= */

const taskSearch =
    document.getElementById("taskSearch");

const statusFilter =
    document.getElementById("statusFilter");

const priorityFilter =
    document.getElementById("priorityFilter");

const visibleTaskCount =
    document.getElementById("visibleTaskCount");

const noSearchResults =
    document.getElementById("noSearchResults");


function filterTasks() {

    const searchText =
        taskSearch
            ? taskSearch.value
                .trim()
                .toLowerCase()
            : "";


    const selectedStatus =
        statusFilter
            ? statusFilter.value
            : "all";


    const selectedPriority =
        priorityFilter
            ? priorityFilter.value
            : "all";


    const taskItems =
        document.querySelectorAll(
            ".task-item"
        );


    let visibleCount = 0;


    taskItems.forEach(task => {

        const title =
            task.dataset.title || "";

        const employee =
            task.dataset.employee || "";

        const employeeCode =
            task.dataset.code || "";

        const designation =
            task.dataset.designation || "";

        const project =
            task.dataset.project || "";

        const status =
            task.dataset.status || "";

        const priority =
            task.dataset.priority || "";


        const matchesSearch =
            searchText === "" ||

            title.includes(searchText) ||

            employee.includes(searchText) ||

            employeeCode.includes(searchText) ||

            designation.includes(searchText) ||

            project.includes(searchText);


        const matchesStatus =
            selectedStatus === "all" ||
            status === selectedStatus;


        const matchesPriority =
            selectedPriority === "all" ||
            priority === selectedPriority;


        const visible =
            matchesSearch &&
            matchesStatus &&
            matchesPriority;


        if (visible) {

            task.style.display = "";

            visibleCount++;

        } else {

            task.style.display = "none";

        }

    });


    if (visibleTaskCount) {

        visibleTaskCount.textContent =
            visibleCount + " Tasks";

    }


    if (noSearchResults) {

        if (
            taskItems.length > 0 &&
            visibleCount === 0
        ) {

            noSearchResults.style.display =
                "block";

        } else {

            noSearchResults.style.display =
                "none";

        }

    }

}


/* =========================================================
   EVENT LISTENERS
========================================================= */

if (taskSearch) {

    taskSearch.addEventListener(
        "input",
        filterTasks
    );

}


if (statusFilter) {

    statusFilter.addEventListener(
        "change",
        filterTasks
    );

}


if (priorityFilter) {

    priorityFilter.addEventListener(
        "change",
        filterTasks
    );

}

</script>


</body>

</html>
```
