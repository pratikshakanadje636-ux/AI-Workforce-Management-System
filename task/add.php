<?php

require_once "../config/database.php";

// =============================
// GET ALL PROJECTS
// =============================

$projects = $conn->query("
    SELECT project_id, project_name
    FROM projects
    ORDER BY project_name
");


// =============================
// GET ALL EMPLOYEES
// =============================

$employees = $conn->query("
    SELECT employee_id, full_name
    FROM employees
    ORDER BY full_name
");


// =============================
// AI EMPLOYEE RECOMMENDATION
// =============================

$ai_sql = "

SELECT

    e.employee_id,
    e.full_name,
    e.designation,
    d.department_name,

    COUNT(t.task_id) AS total_tasks,

    SUM(
        CASE
            WHEN t.status='Completed'
            THEN 1
            ELSE 0
        END
    ) AS completed_tasks,

    SUM(
        CASE
            WHEN t.status='Pending'
            THEN 1
            ELSE 0
        END
    ) AS pending_tasks,

    COALESCE(
        ROUND(
            SUM(
                CASE
                    WHEN t.status='Completed'
                    THEN 1
                    ELSE 0
                END
            )
            /
            NULLIF(COUNT(t.task_id),0)
            * 100
        ,0)
    ,0) AS performance,

    (

        COALESCE(
            ROUND(
                SUM(
                    CASE
                        WHEN t.status='Completed'
                        THEN 1
                        ELSE 0
                    END
                )
                /
                NULLIF(COUNT(t.task_id),0)
                * 100
            ,0)
        ,0)

        +

        (
            10 -
            SUM(
                CASE
                    WHEN t.status='Pending'
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

GROUP BY e.employee_id

HAVING pending_tasks < 5

ORDER BY ai_score DESC

LIMIT 1

";

$ai_result = $conn->query($ai_sql);

$ai_employee = null;

if ($ai_result && $ai_result->num_rows > 0) {

    $ai_employee = $ai_result->fetch_assoc();

}


// =============================
// SAVE TASK
// =============================

if (isset($_POST['save'])) {

    $project_id  = $_POST['project_id'];
    $employee_id = $_POST['employee_id'];
    $task_title  = $_POST['task_title'];
    $description = $_POST['description'];
    $priority    = $_POST['priority'];
    $status      = $_POST['status'];
    $start_date  = $_POST['start_date'];
    $due_date    = $_POST['due_date'];


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
            ".$stmt->error."
        </div>
        ";

    }

}

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <title>Add Task</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <style>

    /* Horizontal Add Task Form */
    form[method="POST"] {
        width: 90%;
        max-width: 1100px;
        margin: 30px auto;
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.10);

        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px 25px;
    }

    /* Make each form field fit its column */
    form[method="POST"] .mb-3 {
        margin-bottom: 0 !important;
    }

    form[method="POST"] .form-control,
    form[method="POST"] .form-select {
        width: 100%;
    }

    /* Description should use full width */
    form[method="POST"] .mb-3:nth-of-type(3) {
        grid-column: 1 / 3;
    }

    /* Buttons */
    form[method="POST"] button,
    form[method="POST"] a {
        width: fit-content;
    }

    /* Keep buttons together */
    form[method="POST"] button {
        grid-column: 1;
    }

    form[method="POST"] a {
        grid-column: 2;
        justify-self: start;
    }

</style>

</head>


<body class="bg-light">
    <?php include "../config/page_actions.php"; ?>

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">

            <h3>Add Task</h3>

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

<?php if($ai_employee){ ?>

<div class="alert alert-primary shadow">

<h4>
    🤖 AI Employee Recommendation
</h4>

<hr>

<p>
    <b>Name:</b>
    <?php echo $ai_employee['full_name']; ?>
</p>

<p>
    <b>Designation:</b>
    <?php echo $ai_employee['designation']; ?>
</p>

<p>
    <b>Department:</b>
    <?php echo $ai_employee['department_name']; ?>
</p>

<p>
    <b>Completed Tasks:</b>
    <?php echo $ai_employee['completed_tasks']; ?>
</p>

<p>
    <b>Pending Tasks:</b>
    <?php echo $ai_employee['pending_tasks']; ?>
</p>

<p>
    <b>Performance:</b>
    <?php echo $ai_employee['performance']; ?>%
</p>

<p>
    <b>AI Score:</b>
    <?php echo $ai_employee['ai_score']; ?>
</p>

<p>
    <b>Current Workload:</b>
    <?php echo $ai_employee['pending_tasks']; ?> Pending Tasks
</p>

<div class="progress mb-3">

    <div class="progress mb-3">

    <div
        class="progress-bar bg-success"
        style="width: <?php echo $ai_employee['performance']; ?>%;"
    >
        <?php echo $ai_employee['performance']; ?>%
    </div>

</div>

</div>

<div class="alert alert-primary mt-3">

    <h5>🧠 AI Skill Analysis</h5>

    <ul class="mb-0">

        <li>✔ Suitable Designation</li>

        <li>✔ High Performance</li>

        <li>✔ Low Pending Tasks</li>

        <li>✔ Department Match</li>

        <li>✔ Best Overall Candidate</li>

    </ul>

</div>

<div class="card border-success mt-3">

    <div class="card-body">

        <h5 class="text-success">
            AI Recommendation Result
        </h5>

        <p class="mb-0">
            This employee is automatically selected because
            the AI detected high productivity with low workload.
        </p>

    </div>

</div>
<div class="card mt-3 border-primary">

<div class="card-header bg-primary text-white">

<h5 class="mt-3">
    AI Recommendation Score
</h5>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<th>Performance</th>

<td><?php echo $ai_employee['performance']; ?>%</td>

</tr>

<tr>

<th>Pending Tasks</th>

<td><?php echo $ai_employee['pending_tasks']; ?></td>

</tr>

<tr>

<th>Department</th>

<td><?php echo $ai_employee['department_name']; ?></td>

</tr>

<tr>

<th>Designation</th>

<td><?php echo $ai_employee['designation']; ?></td>

</tr>

<tr>

<th>Final AI Score</th>

<td>

<b class="text-success">

<?php echo $ai_employee['ai_score']; ?>

</b>

</td>

</tr>

</table>

</div>

</div>

</div>

</div>


</div>

</div>



</div>

<?php } ?>

<form method="POST">

<div class="mb-3">

<label class="form-label">
Project
</label>

<select
name="project_id"
class="form-select"
required>

<option value="">
Select Project
</option>

<?php while($p=$projects->fetch_assoc()){ ?>

<option value="<?php echo $p['project_id']; ?>">

<?php echo $p['project_name']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label class="form-label">

Employee

</label>

<select
name="employee_id"
class="form-select"
required>

<option value="">
Select Employee
</option>

<?php while($e=$employees->fetch_assoc()){ ?>

<option

value="<?php echo $e['employee_id']; ?>"

<?php
if(
$ai_employee &&
$e['employee_id']==$ai_employee['employee_id']
){
echo "selected";
}
?>

>

<?php echo $e['full_name']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label class="form-label">

Task Title

</label>

<input
type="text"
name="task_title"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">

Description

</label>

<textarea
name="description"
class="form-control"
rows="3"></textarea>

</div>

<div class="mb-3">

<label class="form-label">

Priority

</label>

<select
name="priority"
class="form-select">

<option>Low</option>

<option selected>Medium</option>

<option>High</option>

</select>

</div>

<div class="mb-3">

<label class="form-label">

Status

</label>

<select
name="status"
class="form-select">

<option selected>Pending</option>

<option>In Progress</option>

<option>Completed</option>

</select>

</div>

<div class="mb-3">

<label class="form-label">

Start Date

</label>

<input
type="date"
name="start_date"
class="form-control">

</div>

<div class="mb-3">

<label class="form-label">

Due Date

</label>

<input
type="date"
name="due_date"
class="form-control">

</div>

<button
type="submit"
name="save"
class="btn btn-primary">

Save Task

</button>

<a
href="view.php"
class="btn btn-secondary">


View Tasks

</a>

</form>

</div>

</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<style>
/* =========================================================
   ADD TASK - OUTER FORM CONTAINER
========================================================= */

body.admin-dark-mode .container {
    background: #1e293b !important;
    color: #e5e7eb !important;
    border-color: #334155 !important;
}

/* If the form is inside a card */
body.admin-dark-mode .container .card {
    background: #1e293b !important;
    color: #e5e7eb !important;
    border-color: #334155 !important;
}

/* Form labels */
body.admin-dark-mode .container label {
    color: #cbd5e1 !important;
}

/* Headings */
body.admin-dark-mode .container h1,
body.admin-dark-mode .container h2,
body.admin-dark-mode .container h3,
body.admin-dark-mode .container h4,
body.admin-dark-mode .container h5,
body.admin-dark-mode .container h6 {
    color: #f8fafc !important;
}

/* =========================================================
   ADD TASK FORM - DARK MODE
========================================================= */

body.admin-dark-mode form[method="POST"] {
    background: #1e293b !important;
    color: #e5e7eb !important;
    border: 1px solid #334155 !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.30) !important;
}

/* Form labels */
body.admin-dark-mode form[method="POST"] .form-label {
    color: #cbd5e1 !important;
}

/* Text inputs */
body.admin-dark-mode form[method="POST"] .form-control,
body.admin-dark-mode form[method="POST"] .form-select {
    background-color: #1e293b !important;
    color: #f8fafc !important;
    border-color: #475569 !important;
}

/* Placeholder text */
body.admin-dark-mode form[method="POST"] .form-control::placeholder {
    color: #94a3b8 !important;
}

/* Dropdown options */
body.admin-dark-mode form[method="POST"] .form-select option {
    background: #1e293b !important;
    color: #f8fafc !important;
}

/* Date input */
body.admin-dark-mode form[method="POST"] input[type="date"] {
    color-scheme: dark;
}
</style>
</body>

</html>