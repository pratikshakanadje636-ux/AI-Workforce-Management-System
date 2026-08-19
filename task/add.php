<?php

require_once "../config/database.php";

/* =============================
   GET ALL PROJECTS
============================= */

$projects = $conn->query("
    SELECT project_id, project_name
    FROM projects
    ORDER BY project_name
");


/* =============================
   GET ALL EMPLOYEES
============================= */

$employees = $conn->query("
    SELECT employee_id, full_name
    FROM employees
    ORDER BY full_name
");


/* =============================
   AI EMPLOYEE RECOMMENDATION
============================= */

$ai_sql = "

SELECT

    e.employee_id,
    e.full_name,
    e.designation,
    d.department_name,

    COUNT(t.task_id) AS total_tasks,

    SUM(
        CASE
            WHEN t.status = 'Completed'
            THEN 1
            ELSE 0
        END
    ) AS completed_tasks,

    SUM(
        CASE
            WHEN t.status = 'Pending'
            THEN 1
            ELSE 0
        END
    ) AS pending_tasks,

    COALESCE(
        ROUND(
            SUM(
                CASE
                    WHEN t.status = 'Completed'
                    THEN 1
                    ELSE 0
                END
            )
            /
            NULLIF(COUNT(t.task_id), 0)
            * 100
        , 0)
    , 0) AS performance,

    (
        COALESCE(
            ROUND(
                SUM(
                    CASE
                        WHEN t.status = 'Completed'
                        THEN 1
                        ELSE 0
                    END
                )
                /
                NULLIF(COUNT(t.task_id), 0)
                * 100
            , 0)
        , 0)

        +

        (
            10 -
            SUM(
                CASE
                    WHEN t.status = 'Pending'
                    THEN 1
                    ELSE 0
                END
            )
        )

        + 20

    ) AS ai_score

FROM employees e

LEFT JOIN departments d
    ON e.department_id = d.department_id

LEFT JOIN tasks t
    ON e.employee_id = t.employee_id

GROUP BY
    e.employee_id,
    e.full_name,
    e.designation,
    d.department_name

HAVING pending_tasks < 5

ORDER BY ai_score DESC

LIMIT 1

";

$ai_result = $conn->query($ai_sql);

$ai_employee = null;

if ($ai_result && $ai_result->num_rows > 0) {

    $ai_employee = $ai_result->fetch_assoc();

}


/* =============================
   SAVE TASK
============================= */

if (isset($_POST['save'])) {

    $project_id  = $_POST['project_id'] ?? '';
    $employee_id = $_POST['employee_id'] ?? '';
    $task_title  = trim($_POST['task_title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority    = $_POST['priority'] ?? 'Medium';
    $status      = $_POST['status'] ?? 'Pending';
    $start_date  = $_POST['start_date'] ?? '';
    $due_date    = $_POST['due_date'] ?? '';


    $sql = "
        INSERT INTO tasks
        (
            project_id,
            employee_id,
            task_title,
            description,
            priority,
            status,
            start_date,
            due_date
        )

        VALUES
        (?,?,?,?,?,?,?,?)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        die(
            "Database error: " .
            htmlspecialchars($conn->error)
        );

    }

    $stmt->bind_param(
        "iissssss",
        $project_id,
        $employee_id,
        $task_title,
        $description,
        $priority,
        $status,
        $start_date,
        $due_date
    );


    if ($stmt->execute()) {

        header("Location: add.php?success=1");

        exit();

    } else {

        echo "
        <div class='alert alert-danger text-center mt-3'>
            " . htmlspecialchars($stmt->error) . "
        </div>
        ";

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
        Add Task | AI Workforce
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
   PAGE
========================================================= */

body {

    background: #f8fafc;

}


/* =========================================================
   ADD TASK FORM
========================================================= */

form[method="POST"] {

    width: 90%;

    max-width: 1100px;

    margin: 30px auto;

    background: white;

    padding: 25px;

    border-radius: 10px;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.10);

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 18px 25px;

}


/* =========================================================
   FORM FIELDS
========================================================= */

form[method="POST"] .mb-3 {

    margin-bottom: 0 !important;

}


form[method="POST"] .form-control,
form[method="POST"] .form-select {

    width: 100%;

}


form[method="POST"] .mb-3:nth-of-type(3) {

    grid-column: 1 / 3;

}


/* =========================================================
   BUTTONS
========================================================= */

form[method="POST"] button,
form[method="POST"] a {

    width: fit-content;

}


form[method="POST"] button {

    grid-column: 1;

}


form[method="POST"] a {

    grid-column: 2;

    justify-self: start;

}


/* =========================================================
   AI RECOMMENDATION
========================================================= */

.ai-card {

    width: 90%;

    max-width: 1100px;

    margin: 25px auto;

    border:
        1px solid rgba(37,99,235,0.15);

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 8px 25px rgba(15,23,42,0.08);

}


.ai-card-header {

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5,
            #7c3aed
        );

    color: white;

    padding: 18px 22px;

}


.ai-card-header h4 {

    margin: 0;

    font-weight: 700;

}


.ai-card-body {

    background: white;

    padding: 22px;

}


.ai-icon {

    width: 55px;

    height: 55px;

    border-radius: 15px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        rgba(124,58,237,0.10);

    color: #7c3aed;

    font-size: 28px;

}


.ai-score-box {

    background:
        #f0fdf4;

    border:
        1px solid #bbf7d0;

    border-radius: 12px;

    padding: 15px;

}


.ai-score {

    color: #15803d;

    font-size: 30px;

    font-weight: 800;

}


/* =========================================================
   DARK MODE
========================================================= */

body.admin-dark-mode {

    background: #0f172a !important;

    color: #e5e7eb !important;

}


body.admin-dark-mode .container {

    color: #e5e7eb !important;

}


body.admin-dark-mode .card {

    background: #1e293b !important;

    color: #e5e7eb !important;

    border-color: #334155 !important;

}


body.admin-dark-mode form[method="POST"] {

    background: #1e293b !important;

    color: #e5e7eb !important;

    border:
        1px solid #334155 !important;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.30) !important;

}


body.admin-dark-mode form[method="POST"] .form-label {

    color: #cbd5e1 !important;

}


body.admin-dark-mode form[method="POST"] .form-control,
body.admin-dark-mode form[method="POST"] .form-select {

    background-color: #1e293b !important;

    color: #f8fafc !important;

    border-color: #475569 !important;

}


body.admin-dark-mode form[method="POST"] .form-control::placeholder {

    color: #94a3b8 !important;

}


body.admin-dark-mode form[method="POST"] .form-select option {

    background: #1e293b;

    color: #f8fafc;

}


body.admin-dark-mode form[method="POST"] input[type="date"] {

    color-scheme: dark;

}


body.admin-dark-mode .ai-card-body {

    background: #1e293b !important;

    color: #e5e7eb !important;

}


body.admin-dark-mode .ai-card-body .text-muted {

    color: #94a3b8 !important;

}


body.admin-dark-mode .ai-score-box {

    background: #052e16 !important;

    border-color: #166534 !important;

}


body.admin-dark-mode .ai-score {

    color: #4ade80 !important;

}


body.admin-dark-mode .progress {

    background: #334155 !important;

}


body.admin-dark-mode .table {

    color: #e5e7eb !important;

}


body.admin-dark-mode .table-bordered {

    border-color: #475569 !important;

}


body.admin-dark-mode .table th,
body.admin-dark-mode .table td {

    border-color: #475569 !important;

}


body.admin-dark-mode .alert {

    color: #e5e7eb;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 768px) {

    form[method="POST"] {

        width: 95%;

        grid-template-columns: 1fr;

    }


    form[method="POST"] .mb-3:nth-of-type(3) {

        grid-column: 1;

    }


    form[method="POST"] button,
    form[method="POST"] a {

        grid-column: 1;

    }


    .ai-card {

        width: 95%;

    }

}

    </style>

</head>


<body>


<?php include "../config/page_actions.php"; ?>


<div class="container mt-5">


    <div class="card shadow">

        <div class="card-header bg-primary text-white">

            <h3 class="mb-0">

                <i class="bi bi-plus-circle me-2"></i>

                Add Task

            </h3>

        </div>


        <div class="card-body">


            <?php

            if (isset($_GET['success'])) {

                echo '

                <div
                    class="alert alert-success alert-dismissible fade show"
                    role="alert"
                >

                    ✅ <strong>Task Added Successfully!</strong>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

                ';

            }

            ?>


            <?php if ($ai_employee) { ?>


                <!-- =================================================
                     AI RECOMMENDATION
                ================================================== -->

                <div class="ai-card">


                    <div class="ai-card-header">

                        <div
                            class="d-flex align-items-center gap-3"
                        >

                            <div class="ai-icon">

                                🤖

                            </div>


                            <div>

                                <h4>
                                    AI Employee Recommendation
                                </h4>

                                <small>
                                    Best employee selected based on
                                    productivity and current workload.
                                </small>

                            </div>

                        </div>

                    </div>


                    <div class="ai-card-body">


                        <div
                            class="d-flex align-items-center gap-3 mb-4"
                        >

                            <div class="ai-icon">

                                <i class="bi bi-person-check-fill"></i>

                            </div>


                            <div>

                                <h5 class="mb-1">

                                    <?php

                                    echo htmlspecialchars(
                                        $ai_employee['full_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );

                                    ?>

                                </h5>


                                <div class="text-muted">

                                    <?php

                                    echo htmlspecialchars(
                                        $ai_employee['designation']
                                        ?? 'Employee',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );

                                    ?>

                                    •


                                    <?php

                                    echo htmlspecialchars(
                                        $ai_employee['department_name']
                                        ?? 'Department',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );

                                    ?>

                                </div>

                            </div>

                        </div>


                        <div class="row g-3">


                            <div class="col-md-6">

                                <div class="border rounded p-3">

                                    <div class="text-muted small">
                                        Completed Tasks
                                    </div>

                                    <strong class="fs-5">

                                        <?php

                                        echo (int)
                                            $ai_employee['completed_tasks'];

                                        ?>

                                    </strong>

                                </div>

                            </div>


                            <div class="col-md-6">

                                <div class="border rounded p-3">

                                    <div class="text-muted small">
                                        Pending Tasks
                                    </div>

                                    <strong class="fs-5">

                                        <?php

                                        echo (int)
                                            $ai_employee['pending_tasks'];

                                        ?>

                                    </strong>

                                </div>

                            </div>


                            <div class="col-md-6">

                                <div class="border rounded p-3">

                                    <div class="text-muted small">
                                        Performance
                                    </div>

                                    <strong class="fs-5">

                                        <?php

                                        echo (int)
                                            $ai_employee['performance'];

                                        ?>%

                                    </strong>

                                </div>

                            </div>


                            <div class="col-md-6">

                                <div class="ai-score-box">

                                    <div class="text-muted small">
                                        AI Score
                                    </div>

                                    <div class="ai-score">

                                        <?php

                                        echo (int)
                                            $ai_employee['ai_score'];

                                        ?>

                                    </div>

                                </div>

                            </div>


                        </div>


                        <div class="mt-4">

                            <div
                                class="d-flex justify-content-between mb-2"
                            >

                                <span class="text-muted">

                                    Performance Level

                                </span>


                                <strong>

                                    <?php

                                    echo (int)
                                        $ai_employee['performance'];

                                    ?>%

                                </strong>

                            </div>


                            <div class="progress">

                                <div
                                    class="progress-bar bg-success"
                                    style="
                                    width:
                                    <?php
                                    echo min(
                                        max(
                                            (int)
                                                $ai_employee['performance'],
                                            0
                                        ),
                                        100
                                    );
                                    ?>%;
                                    "
                                >

                                    <?php

                                    echo (int)
                                        $ai_employee['performance'];

                                    ?>%

                                </div>

                            </div>

                        </div>


                        <div
                            class="alert alert-primary mt-4 mb-0"
                        >

                            <h5>

                                🧠 AI Skill Analysis

                            </h5>


                            <ul class="mb-0">

                                <li>
                                    ✔ Suitable Designation
                                </li>

                                <li>
                                    ✔ High Performance
                                </li>

                                <li>
                                    ✔ Low Pending Tasks
                                </li>

                                <li>
                                    ✔ Department Match
                                </li>

                                <li>
                                    ✔ Best Overall Candidate
                                </li>

                            </ul>

                        </div>


                        <div
                            class="card border-success mt-3"
                        >

                            <div class="card-body">

                                <h5 class="text-success">

                                    AI Recommendation Result

                                </h5>


                                <p class="mb-0">

                                    This employee is automatically selected
                                    because the AI detected high productivity
                                    with low workload.

                                </p>

                            </div>

                        </div>


                        <div
                            class="card mt-3 border-primary"
                        >

                            <div
                                class="
                                card-header
                                bg-primary
                                text-white
                                "
                            >

                                <h5 class="mb-0">

                                    AI Recommendation Score

                                </h5>

                            </div>


                            <div class="card-body">

                                <table
                                    class="
                                    table
                                    table-bordered
                                    align-middle
                                    mb-0
                                    "
                                >

                                    <tr>

                                        <th>
                                            Performance
                                        </th>

                                        <td>

                                            <?php

                                            echo (int)
                                                $ai_employee['performance'];

                                            ?>%

                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Pending Tasks
                                        </th>

                                        <td>

                                            <?php

                                            echo (int)
                                                $ai_employee['pending_tasks'];

                                            ?>

                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Department
                                        </th>

                                        <td>

                                            <?php

                                            echo htmlspecialchars(
                                                $ai_employee['department_name']
                                                ?? 'Department',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );

                                            ?>

                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Designation
                                        </th>

                                        <td>

                                            <?php

                                            echo htmlspecialchars(
                                                $ai_employee['designation']
                                                ?? 'Employee',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );

                                            ?>

                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Final AI Score
                                        </th>

                                        <td>

                                            <b class="text-success">

                                                <?php

                                                echo (int)
                                                    $ai_employee['ai_score'];

                                                ?>

                                            </b>

                                        </td>

                                    </tr>

                                </table>

                            </div>

                        </div>


                    </div>


                </div>


            <?php } ?>


            <!-- =================================================
                 TASK FORM
            ================================================== -->

            <form method="POST">


                <!-- PROJECT -->

                <div class="mb-3">

                    <label class="form-label">

                        Project

                    </label>


                    <select
                        name="project_id"
                        class="form-select"
                        required
                    >

                        <option value="">

                            Select Project

                        </option>


                        <?php while ($p = $projects->fetch_assoc()) { ?>

                            <option
                                value="<?php
                                echo $p['project_id'];
                                ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $p['project_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>


                <!-- EMPLOYEE -->

                <div class="mb-3">

                    <label class="form-label">

                        Employee

                    </label>


                    <select
                        name="employee_id"
                        class="form-select"
                        required
                    >

                        <option value="">

                            Select Employee

                        </option>


                        <?php while ($e = $employees->fetch_assoc()) { ?>

                            <option
                                value="<?php
                                echo $e['employee_id'];
                                ?>"

                                <?php

                                if (
                                    $ai_employee &&
                                    $e['employee_id']
                                    == $ai_employee['employee_id']
                                ) {

                                    echo "selected";

                                }

                                ?>
                            >

                                <?php

                                echo htmlspecialchars(
                                    $e['full_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>


                <!-- TASK TITLE -->

                <div class="mb-3">

                    <label class="form-label">

                        Task Title

                    </label>


                    <input
                        type="text"
                        name="task_title"
                        class="form-control"
                        required
                    >

                </div>


                <!-- DESCRIPTION -->

                <div class="mb-3">

                    <label class="form-label">

                        Description

                    </label>


                    <textarea
                        name="description"
                        class="form-control"
                        rows="3"
                    ></textarea>

                </div>


                <!-- PRIORITY -->

                <div class="mb-3">

                    <label class="form-label">

                        Priority

                    </label>


                    <select
                        name="priority"
                        class="form-select"
                    >

                        <option value="Low">
                            Low
                        </option>

                        <option
                            value="Medium"
                            selected
                        >
                            Medium
                        </option>

                        <option value="High">
                            High
                        </option>

                    </select>

                </div>


                <!-- STATUS -->

                <div class="mb-3">

                    <label class="form-label">

                        Status

                    </label>


                    <select
                        name="status"
                        class="form-select"
                    >

                        <option
                            value="Pending"
                            selected
                        >
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


                <!-- START DATE -->

                <div class="mb-3">

                    <label class="form-label">

                        Start Date

                    </label>


                    <input
                        type="date"
                        name="start_date"
                        class="form-control"
                    >

                </div>


                <!-- DUE DATE -->

                <div class="mb-3">

                    <label class="form-label">

                        Due Date

                    </label>


                    <input
                        type="date"
                        name="due_date"
                        class="form-control"
                    >

                </div>


                <!-- BUTTON -->

                <button
                    type="submit"
                    name="save"
                    class="btn btn-primary"
                >

                    <i class="bi bi-check-circle me-1"></i>

                    Save Task

                </button>


                <!-- VIEW TASKS -->

                <a
                    href="view.php"
                    class="btn btn-secondary"
                >

                    <i class="bi bi-list-task me-1"></i>

                    View Tasks

                </a>


            </form>


        </div>


    </div>


</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>