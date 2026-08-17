<?php

require_once "../config/database.php";

/* =========================================================
   FETCH EMPLOYEES
========================================================= */

$sql = "
SELECT
    employees.*,
    departments.department_name,

    COUNT(tasks.task_id) AS total_tasks,

    SUM(
        CASE
            WHEN tasks.status = 'Completed' THEN 1
            ELSE 0
        END
    ) AS completed_tasks,

    SUM(
        CASE
            WHEN tasks.status = 'Pending' THEN 1
            ELSE 0
        END
    ) AS pending_tasks,

    COALESCE(
        ROUND(
            SUM(
                CASE
                    WHEN tasks.status = 'Completed' THEN 1
                    ELSE 0
                END
            )
            /
            NULLIF(COUNT(tasks.task_id), 0)
            * 100,
            0
        ),
        0
    ) AS performance

FROM employees

LEFT JOIN departments
    ON employees.department_id = departments.department_id

LEFT JOIN tasks
    ON employees.employee_id = tasks.employee_id

GROUP BY
    employees.employee_id

ORDER BY
    performance DESC,
    completed_tasks DESC
";

$result = $conn->query($sql);


/* =========================================================
   TOP PERFORMER
========================================================= */

$top = $conn->query("
    SELECT
        employees.full_name,
        departments.department_name,

        COUNT(tasks.task_id) AS total_tasks,

        SUM(
            CASE
                WHEN tasks.status = 'Completed' THEN 1
                ELSE 0
            END
        ) AS completed_tasks,

        SUM(
            CASE
                WHEN tasks.status = 'Pending' THEN 1
                ELSE 0
            END
        ) AS pending_tasks,

        COALESCE(
            ROUND(
                SUM(
                    CASE
                        WHEN tasks.status = 'Completed' THEN 1
                        ELSE 0
                    END
                )
                /
                NULLIF(COUNT(tasks.task_id), 0)
                * 100,
                0
            ),
            0
        ) AS performance

    FROM employees

    LEFT JOIN departments
        ON employees.department_id = departments.department_id

    LEFT JOIN tasks
        ON employees.employee_id = tasks.employee_id

    GROUP BY employees.employee_id

    ORDER BY
        performance DESC,
        completed_tasks DESC

    LIMIT 1
");

$top_performer = null;

if ($top && $top->num_rows > 0) {
    $top_performer = $top->fetch_assoc();
}


/* =========================================================
   SUMMARY
========================================================= */

$total_employees = 0;
$total_tasks = 0;
$total_completed = 0;
$avg_performance = 0;


/* Total employees */

$query = $conn->query("
    SELECT COUNT(*) AS total
    FROM employees
");

if ($query) {
    $total_employees = (int)$query->fetch_assoc()['total'];
}


/* Total tasks */

$query = $conn->query("
    SELECT COUNT(*) AS total
    FROM tasks
");

if ($query) {
    $total_tasks = (int)$query->fetch_assoc()['total'];
}


/* Completed tasks */

$query = $conn->query("
    SELECT COUNT(*) AS total
    FROM tasks
    WHERE status = 'Completed'
");

if ($query) {
    $total_completed = (int)$query->fetch_assoc()['total'];
}


/* Average performance */

$query = $conn->query("
    SELECT
        COALESCE(
            ROUND(AVG(performance), 0),
            0
        ) AS avg_score

    FROM
    (
        SELECT

            ROUND(

                SUM(
                    CASE
                        WHEN status = 'Completed' THEN 1
                        ELSE 0
                    END
                )

                /

                NULLIF(COUNT(task_id), 0)

                * 100,

                0

            ) AS performance

        FROM tasks

        GROUP BY employee_id

    ) x
");

if ($query) {
    $avg_performance = (int)$query->fetch_assoc()['avg_score'];
}


/* =========================================================
   SAFE OUTPUT
========================================================= */

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
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

<title>Employee Management | AI Workforce Management</title>


<!-- Bootstrap -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<!-- Font Awesome -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
>


<!-- Main Project CSS -->

<link
    rel="stylesheet"
    href="../assets/css/style.css">
<link
    rel="stylesheet" 
    href="../assets/css/dark-mode.css">



<style>

/* =========================================================
   PAGE
========================================================= */

body {
    background: #f4f7fb;
    color: #1f2937;
}


/* =========================================================
   MAIN CONTAINER
========================================================= */

.employee-page {
    padding: 35px 25px 60px;
}


/* =========================================================
   HEADER
========================================================= */

.page-header {
    background: linear-gradient(
        135deg,
        #0d6efd,
        #6610f2
    );

    color: white;

    border-radius: 20px;

    padding: 30px;

    margin-bottom: 25px;

    box-shadow:
        0 12px 30px rgba(13, 110, 253, 0.20);
}

.page-header h1 {
    font-weight: 700;
    margin-bottom: 8px;
}

.page-header p {
    margin-bottom: 0;
    opacity: 0.9;
}


/* =========================================================
   BUTTONS
========================================================= */

.btn-add-employee {
    background: white;
    color: #0d6efd;

    font-weight: 700;

    border: none;

    padding: 12px 20px;

    border-radius: 12px;

    transition: 0.2s;
}

.btn-add-employee:hover {
    background: #f0f6ff;
    color: #084298;

    transform: translateY(-2px);
}


/* =========================================================
   SUMMARY CARDS
========================================================= */

.summary-card {
    border: none;

    border-radius: 18px;

    background: white;

    box-shadow:
        0 8px 25px rgba(0, 0, 0, 0.06);

    transition: 0.25s;

    height: 100%;
}

.summary-card:hover {
    transform: translateY(-4px);

    box-shadow:
        0 12px 30px rgba(0, 0, 0, 0.10);
}

.summary-icon {
    width: 50px;
    height: 50px;

    border-radius: 14px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 22px;
}

.summary-number {
    font-size: 28px;

    font-weight: 700;

    margin-top: 12px;
}

.summary-label {
    color: #6b7280;

    font-size: 14px;

    margin: 0;
}


/* =========================================================
   AI CARD
========================================================= */

.ai-card {
    background: white;

    border: none;

    border-radius: 18px;

    box-shadow:
        0 8px 25px rgba(0, 0, 0, 0.06);

    padding: 25px;

    margin-bottom: 25px;
}

.ai-title {
    font-weight: 700;

    color: #6610f2;
}


/* =========================================================
   EMPLOYEE TABLE CARD
========================================================= */

.employee-table-card {
    background: white;

    border: none;

    border-radius: 18px;

    box-shadow:
        0 8px 25px rgba(0, 0, 0, 0.06);

    overflow: hidden;
}

.employee-table-header {
    padding: 22px 25px;

    border-bottom: 1px solid #e5e7eb;
}

.employee-table-header h3 {
    margin: 0;

    font-weight: 700;
}


/* =========================================================
   TABLE
========================================================= */

.employee-table {
    margin-bottom: 0;

    vertical-align: middle;
}

.employee-table thead th {
    background: #111827;

    color: white;

    border: none;

    padding: 15px 12px;

    font-size: 13px;

    white-space: nowrap;
}

.employee-table tbody td {
    padding: 15px 12px;

    border-color: #edf0f4;

    font-size: 14px;
}

.employee-table tbody tr {
    transition: 0.15s;
}

.employee-table tbody tr:hover {
    background: #f8fbff;
}


/* =========================================================
   EMPLOYEE NAME
========================================================= */

.employee-name {
    font-weight: 700;

    color: #111827;
}

.employee-code {
    font-size: 12px;

    color: #6b7280;
}


/* =========================================================
   PERFORMANCE
========================================================= */

.performance-wrapper {
    min-width: 150px;
}

.performance-text {
    font-size: 12px;

    font-weight: 700;

    margin-bottom: 5px;
}

.performance-bar {
    height: 8px;

    border-radius: 10px;

    background: #e5e7eb;

    overflow: hidden;
}

.performance-fill {
    height: 100%;

    border-radius: 10px;

    background: linear-gradient(
        90deg,
        #198754,
        #20c997
    );
}


/* =========================================================
   ACTION BUTTONS
========================================================= */

.action-btn {
    border-radius: 8px;

    margin-right: 3px;

    font-size: 12px;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {
    padding: 60px 20px;

    text-align: center;

    color: #6b7280;
}

.empty-state i {
    font-size: 50px;

    margin-bottom: 15px;

    color: #9ca3af;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .employee-page {
        padding: 20px 12px;
    }

    .page-header {
        padding: 22px;
    }

    .page-header .btn-add-employee {
        margin-top: 20px;
        width: 100%;
    }

}

</style>

</head>


<body>

<?php include "../config/page_actions.php"; ?>


<div class="employee-page">


<!-- =====================================================
     PAGE HEADER
====================================================== -->

<div class="page-header">

    <div
        class="d-flex justify-content-between align-items-center flex-wrap"
    >

        <div>

            <h1>
                <i class="fa-solid fa-users me-2"></i>
                Employee Management
            </h1>

            <p>
                Manage employees, monitor performance,
                and analyze workforce productivity.
            </p>

        </div>


        <!-- RESTORED ADD EMPLOYEE BUTTON -->

        <a
            href="add.php"
            class="btn btn-add-employee"
        >

            <i class="fa-solid fa-user-plus me-2"></i>

            Add Employee

        </a>

    </div>

</div>



<!-- =====================================================
     SUMMARY CARDS
====================================================== -->

<div class="row g-4 mb-4">


    <!-- TOTAL EMPLOYEES -->

    <div class="col-xl-3 col-md-6">

        <div class="summary-card">

            <div class="card-body p-4">

                <div
                    class="summary-icon"
                    style="
                        background:#e7f1ff;
                        color:#0d6efd;
                    "
                >

                    <i class="fa-solid fa-users"></i>

                </div>

                <div class="summary-number text-primary">

                    <?php echo $total_employees; ?>

                </div>

                <p class="summary-label">

                    Total Employees

                </p>

            </div>

        </div>

    </div>



    <!-- TOTAL TASKS -->

    <div class="col-xl-3 col-md-6">

        <div class="summary-card">

            <div class="card-body p-4">

                <div
                    class="summary-icon"
                    style="
                        background:#e8f8ef;
                        color:#198754;
                    "
                >

                    <i class="fa-solid fa-list-check"></i>

                </div>

                <div class="summary-number text-success">

                    <?php echo $total_tasks; ?>

                </div>

                <p class="summary-label">

                    Total Tasks

                </p>

            </div>

        </div>

    </div>



    <!-- COMPLETED TASKS -->

    <div class="col-xl-3 col-md-6">

        <div class="summary-card">

            <div class="card-body p-4">

                <div
                    class="summary-icon"
                    style="
                        background:#fff4db;
                        color:#f59e0b;
                    "
                >

                    <i class="fa-solid fa-circle-check"></i>

                </div>

                <div class="summary-number text-warning">

                    <?php echo $total_completed; ?>

                </div>

                <p class="summary-label">

                    Completed Tasks

                </p>

            </div>

        </div>

    </div>



    <!-- PERFORMANCE -->

    <div class="col-xl-3 col-md-6">

        <div class="summary-card">

            <div class="card-body p-4">

                <div
                    class="summary-icon"
                    style="
                        background:#f1eaff;
                        color:#6610f2;
                    "
                >

                    <i class="fa-solid fa-chart-line"></i>

                </div>

                <div class="summary-number" style="color:#6610f2;">

                    <?php echo $avg_performance; ?>%

                </div>

                <p class="summary-label">

                    Average Performance

                </p>

            </div>

        </div>

    </div>

</div>



<!-- =====================================================
     AI ANALYSIS
====================================================== -->

<div class="ai-card">

    <div class="d-flex align-items-center mb-4">

        <div
            class="summary-icon me-3"
            style="
                background:#f1eaff;
                color:#6610f2;
            "
        >

            <i class="fa-solid fa-robot"></i>

        </div>

        <div>

            <h4 class="ai-title mb-1">

                AI Employee Analysis

            </h4>

            <small class="text-muted">

                Workforce performance recommendation

            </small>

        </div>

    </div>


    <?php if ($top_performer) { ?>

    <div class="row g-4">


        <!-- TOP PERFORMER -->

        <div class="col-lg-3 col-md-6">

            <small class="text-muted">
                Top Performer
            </small>

            <h5 class="mt-2 mb-1">

                <i class="fa-solid fa-trophy text-warning me-2"></i>

                <?php echo e($top_performer['full_name']); ?>

            </h5>

            <small class="text-muted">

                <?php
                echo e(
                    $top_performer['department_name']
                    ?: 'Department not assigned'
                );
                ?>

            </small>

        </div>



        <!-- PERFORMANCE -->

        <div class="col-lg-3 col-md-6">

            <small class="text-muted">
                Performance
            </small>

            <div class="mt-2">

                <strong>

                    <?php
                    echo e($top_performer['performance']);
                    ?>%

                </strong>

                <div class="progress mt-2">

                    <div
                        class="progress-bar bg-success"
                        style="
                            width:
                            <?php
                            echo (int)$top_performer['performance'];
                            ?>%;
                        "
                    ></div>

                </div>

            </div>

        </div>



        <!-- TASKS -->

        <div class="col-lg-3 col-md-6">

            <small class="text-muted">
                Task Summary
            </small>

            <div class="mt-2">

                <span class="badge bg-success me-1">

                    <?php
                    echo e($top_performer['completed_tasks']);
                    ?>
                    Completed

                </span>

                <span class="badge bg-warning text-dark">

                    <?php
                    echo e($top_performer['pending_tasks']);
                    ?>
                    Pending

                </span>

            </div>

        </div>



        <!-- AI DECISION -->

        <div class="col-lg-3 col-md-6">

            <small class="text-muted">
                AI Decision
            </small>

            <div class="mt-2">

                <span class="badge bg-success p-2">

                    <i class="fa-solid fa-star me-1"></i>

                    Recommended for New Projects

                </span>

            </div>

        </div>

    </div>

    <?php } else { ?>

        <div class="alert alert-info mb-0">

            <i class="fa-solid fa-circle-info me-2"></i>

            No employee performance data is available yet.

        </div>

    <?php } ?>

</div>



<!-- =====================================================
     EMPLOYEE TABLE
====================================================== -->

<div class="employee-table-card">


    <!-- HEADER -->

    <div class="employee-table-header">

        <div
            class="d-flex justify-content-between align-items-center flex-wrap"
        >

            <div>

                <h3>

                    <i class="fa-solid fa-address-card me-2 text-primary"></i>

                    All Employees

                </h3>

                <small class="text-muted">

                    View and manage your workforce

                </small>

            </div>


            <!-- ADD EMPLOYEE AGAIN -->

            <a
                href="add.php"
                class="btn btn-primary"
            >

                <i class="fa-solid fa-user-plus me-2"></i>

                Add Employee

            </a>

        </div>

    </div>



    <!-- TABLE -->

    <div class="table-responsive">

        <table class="table employee-table">

            <thead>

                <tr>

                    <th>Rank</th>

                    <th>ID</th>

                    <th>Employee</th>

                    <th>Department</th>

                    <th>Phone</th>

                    <th>Designation</th>

                    <th>Tasks</th>

                    <th>Performance</th>

                    <th>AI Rating</th>

                    <th>Actions</th>

                </tr>

            </thead>


            <tbody>

            <?php

            $rank = 1;

            if ($result && $result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {

                    $performance =
                        (int)$row['performance'];

            ?>

                <tr>


                    <!-- RANK -->

                    <td>

                    <?php

                    if ($rank == 1) {

                        echo '<span class="badge bg-warning text-dark">
                                🥇 #1
                              </span>';

                    } elseif ($rank == 2) {

                        echo '<span class="badge bg-secondary">
                                🥈 #2
                              </span>';

                    } elseif ($rank == 3) {

                        echo '<span class="badge bg-danger">
                                🥉 #3
                              </span>';

                    } else {

                        echo '<strong>#' . $rank . '</strong>';

                    }

                    ?>

                    </td>



                    <!-- ID -->

                    <td>

                        <strong>

                            <?php
                            echo e($row['employee_id']);
                            ?>

                        </strong>

                    </td>



                    <!-- EMPLOYEE -->

                    <td>

                        <div class="employee-name">

                            <?php
                            echo e($row['full_name']);
                            ?>

                        </div>

                        <div class="employee-code">

                            <?php
                            echo e($row['employee_code']);
                            ?>

                        </div>

                    </td>



                    <!-- DEPARTMENT -->

                    <td>

                        <?php

                        echo e(
                            $row['department_name']
                            ?: 'Not Assigned'
                        );

                        ?>

                    </td>



                    <!-- PHONE -->

                    <td>

                        <?php
                        echo e($row['phone']);
                        ?>

                    </td>



                    <!-- DESIGNATION -->

                    <td>

                        <?php
                        echo e($row['designation']);
                        ?>

                    </td>



                    <!-- TASKS -->

                    <td>

                        <div class="mb-1">

                            <span class="badge bg-primary">

                                <?php
                                echo e($row['total_tasks']);
                                ?>

                                Total

                            </span>

                        </div>

                        <div>

                            <span class="badge bg-success">

                                <?php
                                echo e($row['completed_tasks']);
                                ?>

                                Done

                            </span>

                            <span class="badge bg-warning text-dark">

                                <?php
                                echo e($row['pending_tasks']);
                                ?>

                                Pending

                            </span>

                        </div>

                    </td>



                    <!-- PERFORMANCE -->

                    <td>

                        <div class="performance-wrapper">

                            <div
                                class="performance-text"
                            >

                                <?php echo $performance; ?>%

                            </div>

                            <div class="performance-bar">

                                <div
                                    class="performance-fill"
                                    style="
                                        width:
                                        <?php
                                        echo $performance;
                                        ?>%;
                                    "
                                ></div>

                            </div>

                        </div>

                    </td>



                    <!-- AI RATING -->

                    <td>

                    <?php

                    if ($performance >= 90) {

                        echo '<span class="badge bg-success">
                                🏆 Excellent
                              </span>';

                    } elseif ($performance >= 70) {

                        echo '<span class="badge bg-primary">
                                ⭐ Good
                              </span>';

                    } elseif ($performance >= 50) {

                        echo '<span class="badge bg-warning text-dark">
                                🙂 Average
                              </span>';

                    } else {

                        echo '<span class="badge bg-danger">
                                ⚠ Needs Improvement
                              </span>';

                    }

                    ?>

                    </td>



                    <!-- ACTIONS -->

                    <td>

                        <div class="d-flex flex-wrap gap-1">

                            <!-- VIEW -->

                            <a
                                href="profile.php?id=<?php
                                echo e($row['employee_id']);
                                ?>"
                                class="btn btn-info btn-sm action-btn text-white"
                                title="View Profile"
                            >

                                <i class="fa-solid fa-eye"></i>

                            </a>


                            <!-- EDIT -->

                            <a
                                href="edit.php?id=<?php
                                echo e($row['employee_id']);
                                ?>"
                                class="btn btn-warning btn-sm action-btn"
                                title="Edit Employee"
                            >

                                <i class="fa-solid fa-pen"></i>

                            </a>


                            <!-- DELETE -->

                            <a
                                href="delete.php?id=<?php
                                echo e($row['employee_id']);
                                ?>"
                                class="btn btn-danger btn-sm action-btn"
                                title="Delete Employee"
                                onclick="
                                    return confirm(
                                        'Are you sure you want to delete this employee?'
                                    );
                                "
                            >

                                <i class="fa-solid fa-trash"></i>

                            </a>

                        </div>

                    </td>

                </tr>

            <?php

                    $rank++;

                }

            } else {

            ?>

                <tr>

                    <td
                        colspan="10"
                        class="empty-state"
                    >

                        <i class="fa-solid fa-users-slash d-block"></i>

                        <h5>No Employees Found</h5>

                        <p>
                            Add your first employee to start
                            managing the workforce.
                        </p>

                        <a
                            href="add.php"
                            class="btn btn-primary"
                        >

                            <i class="fa-solid fa-user-plus me-2"></i>

                            Add Employee

                        </a>

                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>


</div>


<!-- Bootstrap JS -->

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>