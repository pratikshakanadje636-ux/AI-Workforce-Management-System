<?php

require_once "../config/database.php";

$where = "";

$page_title = "All Tasks";

// Employee Filter
if(isset($_GET['employee_id'])){

    $employee_id = (int)$_GET['employee_id'];

    $where = "WHERE tasks.employee_id = $employee_id";

    $page_title = "Employee Tasks";
}

// Project Filter
elseif(isset($_GET['project_id'])){

    $project_id = (int)$_GET['project_id'];

    $where = "WHERE tasks.project_id = $project_id";

    $page_title = "Project Tasks";
}

// Status Filter
elseif(isset($_GET['status'])){

    $status = $conn->real_escape_string($_GET['status']);

    $where = "WHERE tasks.status = '$status'";

    $page_title = $status . " Tasks";
}


$sql = "
SELECT

tasks.*,
projects.project_name,
employees.full_name

FROM tasks

JOIN projects
ON tasks.project_id = projects.project_id

JOIN employees
ON tasks.employee_id = employees.employee_id

$where

ORDER BY tasks.task_id DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>

<title><?php echo $page_title; ?></title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
>

<link
    rel="stylesheet"
    href="../assets/css/style.css"
>

<link
    rel="stylesheet"
    href="../assets/css/dark-mode.css"
>
<style>

/* =========================================================
   TASK PAGE - ADMIN DARK MODE
========================================================= */

body.admin-dark-mode {
    background: #0f172a !important;
    color: #e5e7eb !important;
}


/* CONTAINER */

body.admin-dark-mode .container {
    color: #e5e7eb;
}


/* PAGE TITLE */

body.admin-dark-mode h2 {
    color: #f8fafc !important;
}


/* TABLE */

body.admin-dark-mode .table {
    background: #1e293b !important;
    color: #e5e7eb !important;
}


/* TABLE HEADER */

body.admin-dark-mode .table-dark th {
    background: #020617 !important;
    color: #f8fafc !important;
    border-color: #334155 !important;
}


/* TABLE BODY */

body.admin-dark-mode .table tbody {
    background: #1e293b !important;
}

body.admin-dark-mode .table tbody tr {
    background: #1e293b !important;
    color: #e5e7eb !important;
}

body.admin-dark-mode .table tbody td {
    background: #1e293b !important;
    color: #e5e7eb !important;
    border-color: #334155 !important;
}


/* TABLE STRIPES */

body.admin-dark-mode .table-striped > tbody > tr:nth-of-type(odd) > * {
    background-color: #1e293b !important;
    color: #e5e7eb !important;
}


/* TABLE HOVER */

body.admin-dark-mode .table tbody tr:hover td {
    background: #334155 !important;
    color: #ffffff !important;
}


/* BUTTONS */

body.admin-dark-mode .btn-secondary {
    color: #ffffff !important;
}

body.admin-dark-mode .btn-primary {
    color: #ffffff !important;
}

body.admin-dark-mode .btn-warning {
    color: #111827 !important;
}

body.admin-dark-mode .btn-danger {
    color: #ffffff !important;
}


/* STATUS BADGES */

body.admin-dark-mode .badge.bg-warning {
    color: #111827 !important;
}

<style>

/* =========================================================
   ADD TASK - DARK MODE FIX
========================================================= */

body.admin-dark-mode {
    background: #0f172a !important;
    color: #e5e7eb !important;
}

/* Main form/card */
body.admin-dark-mode .container,
body.admin-dark-mode .card,
body.admin-dark-mode .form-container,
body.admin-dark-mode form {
    color: #e5e7eb;
}

/* White form cards */
body.admin-dark-mode .card {
    background: #1e293b !important;
    border-color: #334155 !important;
}

/* Labels */
body.admin-dark-mode label,
body.admin-dark-mode .form-label {
    color: #e5e7eb !important;
}

/* Inputs */
body.admin-dark-mode input,
body.admin-dark-mode select,
body.admin-dark-mode textarea {
    background: #0f172a !important;
    color: #f8fafc !important;
    border-color: #475569 !important;
}

/* Placeholder */
body.admin-dark-mode input::placeholder,
body.admin-dark-mode textarea::placeholder {
    color: #94a3b8 !important;
}

/* Select options */
body.admin-dark-mode select option {
    background: #0f172a;
    color: #f8fafc;
}

/* Input focus */
body.admin-dark-mode input:focus,
body.admin-dark-mode select:focus,
body.admin-dark-mode textarea:focus {
    background: #0f172a !important;
    color: #ffffff !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
}

/* Small/help text */
body.admin-dark-mode .text-muted {
    color: #94a3b8 !important;
}

/* Headings */
body.admin-dark-mode h1,
body.admin-dark-mode h2,
body.admin-dark-mode h3,
body.admin-dark-mode h4,
body.admin-dark-mode h5,
body.admin-dark-mode h6 {
    color: #f8fafc !important;
}

</style>
</style>

</head>

<body>

<?php include "../config/page_actions.php"; ?>

<div class="container mt-5">

<div class="d-flex justify-content-between align-items-center mb-3">

<h2><?php echo $page_title; ?></h2>

<div>

<a href="view.php" class="btn btn-secondary">
All Tasks
</a>

<a href="add.php" class="btn btn-primary">
+ Add Task
</a>

</div>

</div>

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Project</th>
<th>Employee</th>
<th>Task</th>
<th>Priority</th>
<th>Status</th>
<th>Due Date</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row = $result->fetch_assoc()){ ?>

<tr>

<td><?php echo $row['task_id']; ?></td>

<td><?php echo $row['project_name']; ?></td>

<td><?php echo $row['full_name']; ?></td>

<td><?php echo $row['task_title']; ?></td>

<td><?php echo $row['priority']; ?></td>

<td>

<?php

if($row['status']=="Completed"){

    echo "<span class='badge bg-success'>Completed</span>";

}
elseif($row['status']=="Pending"){

    echo "<span class='badge bg-warning text-dark'>Pending</span>";

}
else{

    echo "<span class='badge bg-secondary'>".$row['status']."</span>";

}

?>

</td>

<td><?php echo $row['due_date']; ?></td>

<td>

<a href="edit.php?id=<?php echo $row['task_id']; ?>"
class="btn btn-warning btn-sm">
Edit
</a>

<a href="delete.php?id=<?php echo $row['task_id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this task?');">
Delete
</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>

</html>