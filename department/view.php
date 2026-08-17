<?php

require_once "../config/database.php";

$sql = "
SELECT
departments.*,
COUNT(employees.employee_id) AS total_employees

FROM departments

LEFT JOIN employees
ON departments.department_id = employees.department_id

GROUP BY departments.department_id

ORDER BY departments.department_id ASC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>

<title>Departments</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

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
   DEPARTMENT PAGE - DARK MODE
========================================================= */

body.admin-dark-mode {
    background: #0f172a !important;
    color: #e5e7eb !important;
}


/* PAGE CONTAINER */

body.admin-dark-mode .container {
    color: #e5e7eb;
}


/* HEADING */

body.admin-dark-mode h2 {
    color: #f8fafc !important;
}


/* TABLE */

body.admin-dark-mode table {
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


/* TABLE HOVER */

body.admin-dark-mode .table tbody tr:hover td {
    background: #334155 !important;
}


/* BUTTON AREA */

body.admin-dark-mode .btn-primary {
    color: #fff !important;
}


/* BOOTSTRAP TABLE STRIPING */

body.admin-dark-mode .table-striped > tbody > tr:nth-of-type(odd) > * {
    background-color: #1e293b !important;
    color: #e5e7eb !important;
}


/* DEPARTMENT EMPLOYEE BUTTON */

body.admin-dark-mode .btn-info {
    color: #fff !important;
}


/* DARK MODE TEXT */

body.admin-dark-mode .text-muted {
    color: #94a3b8 !important;
}

</style>

</head>

<body>
    <?php include "../config/page_actions.php"; ?>

<div class="container mt-5">

<div class="d-flex justify-content-between align-items-center mb-3">

<h2>Departments</h2>

<a href="add.php" class="btn btn-primary">
+ Add Department
</a>

</div>

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Department Name</th>
<th>Total Employees</th>
<th>Created At</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row = $result->fetch_assoc()){ ?>

<tr>

<td><?php echo $row['department_id']; ?></td>

<td><?php echo $row['department_name']; ?></td>

<td>

<a href="../employee/view.php?department_id=<?php echo $row['department_id']; ?>"
class="btn btn-info btn-sm">

<?php echo $row['total_employees']; ?> Employee(s)

</a>

</td>

<td><?php echo $row['created_at']; ?></td>

<td>

<a href="edit.php?id=<?php echo $row['department_id']; ?>"
class="btn btn-warning btn-sm">

Edit

</a>

<a href="delete.php?id=<?php echo $row['department_id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this department?');">

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