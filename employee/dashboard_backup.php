<?php
session_start();

require_once "../config/database.php";

/* ===========================
   EMPLOYEE LOGIN CHECK
=========================== */

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../authentication/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


/* ===========================
   GET LOGGED-IN EMPLOYEE
=========================== */

$sql = "
SELECT
    employees.employee_id,
    employees.full_name,
    employees.employee_code,
    employees.designation,
    employees.phone,
    employees.performance_score,
    employees.workload,
    departments.department_name

FROM employees

LEFT JOIN departments
    ON employees.department_id = departments.department_id

WHERE employees.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$employee_result = $stmt->get_result();

if ($employee_result->num_rows != 1) {
    die("Employee profile not found.");
}

$employee = $employee_result->fetch_assoc();

$employee_id = $employee['employee_id'];


/* ===========================
   TASK SUMMARY
=========================== */

$sql = "
SELECT

    COUNT(*) AS total_tasks,

    SUM(
        CASE
            WHEN status = 'Completed'
            THEN 1
            ELSE 0
        END
    ) AS completed_tasks,

    SUM(
        CASE
            WHEN status = 'Pending'
            THEN 1
            ELSE 0
        END
    ) AS pending_tasks,

    SUM(
        CASE
            WHEN status != 'Completed'
            THEN 1
            ELSE 0
        END
    ) AS active_tasks

FROM tasks

WHERE employee_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();

$summary = $stmt->get_result()->fetch_assoc();

$total_tasks = $summary['total_tasks'] ?? 0;
$completed_tasks = $summary['completed_tasks'] ?? 0;
$pending_tasks = $summary['pending_tasks'] ?? 0;
$active_tasks = $summary['active_tasks'] ?? 0;


/* ===========================
   TODAY'S COMPLETED TASKS
=========================== */

$sql = "
SELECT COUNT(*) AS today_completed

FROM tasks

WHERE employee_id = ?

AND status = 'Completed'

AND DATE(due_date) = CURDATE()
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();

$today_completed = $stmt->get_result()->fetch_assoc()['today_completed'] ?? 0;


/* ===========================
   GET EMPLOYEE TASKS
=========================== */

$sql = "
SELECT
    tasks.task_id,
    tasks.task_title,
    tasks.description,
    tasks.priority,
    tasks.status,
    tasks.start_date,
    tasks.due_date,
    projects.project_name

FROM tasks

LEFT JOIN projects
    ON tasks.project_id = projects.project_id

WHERE tasks.employee_id = ?

ORDER BY
    CASE
        WHEN tasks.status = 'Pending' THEN 1
        ELSE 2
    END,
    tasks.due_date ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();

$tasks = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Employee Dashboard</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
rel="stylesheet"
>

<style>

body {
    background: #f4f7fc;
}

.navbar-brand {
    font-weight: bold;
}

.dashboard-container {
    max-width: 1200px;
    margin: 40px auto;
}

.stat-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.stat-number {
    font-size: 32px;
    font-weight: bold;
}

.profile-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.task-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.06);
}

</style>

</head>


<body>
<?php include "../config/page_actions.php"; ?>

<!-- ===========================
     NAVBAR
=========================== -->

<nav class="navbar navbar-dark bg-primary">

<div class="container">

<a class="navbar-brand" href="#">
🤖 AI Workforce Management
</a>

<a
href="../authentication/logout.php"
class="btn btn-light"
>
Logout
</a>

</div>

</nav>


<!-- ===========================
     MAIN CONTAINER
=========================== -->

<div class="container dashboard-container">


<!-- WELCOME -->

<div class="mb-4">

<h2>
Welcome, <?php echo htmlspecialchars($employee['full_name']); ?> 👋
</h2>

<p class="text-muted">
Here is your personal work dashboard.
</p>

</div>


<!-- ===========================
     STAT CARDS
=========================== -->

<div class="row g-4 mb-4">


<div class="col-md-3">

<div class="card stat-card text-center p-3">

<div class="stat-number text-primary">

<?php echo $total_tasks; ?>

</div>

<p class="mb-0">
Total Tasks
</p>

</div>

</div>


<div class="col-md-3">

<div class="card stat-card text-center p-3">

<div class="stat-number text-success">

<?php echo $completed_tasks; ?>

</div>

<p class="mb-0">
Completed
</p>

</div>

</div>


<div class="col-md-3">

<div class="card stat-card text-center p-3">

<div class="stat-number text-warning">

<?php echo $pending_tasks; ?>

</div>

<p class="mb-0">
Pending
</p>

</div>

</div>


<div class="col-md-3">

<div class="card stat-card text-center p-3">

<div class="stat-number text-danger">

<?php echo $today_completed; ?>

</div>

<p class="mb-0">
Completed Today
</p>

</div>

</div>

</div>


<!-- ===========================
     PROFILE + PERFORMANCE
=========================== -->

<div class="row g-4 mb-4">


<div class="col-md-6">

<div class="card profile-card">

<div class="card-body">

<h4>
👤 My Profile
</h4>

<hr>

<p>
<strong>Name:</strong>
<?php echo htmlspecialchars($employee['full_name']); ?>
</p>

<p>
<strong>Employee Code:</strong>
<?php echo htmlspecialchars($employee['employee_code']); ?>
</p>

<p>
<strong>Designation:</strong>
<?php echo htmlspecialchars($employee['designation']); ?>
</p>

<p>
<strong>Department:</strong>
<?php echo htmlspecialchars($employee['department_name']); ?>
</p>

</div>

</div>

</div>


<div class="col-md-6">

<div class="card profile-card">

<div class="card-body">

<h4>
📊 My Performance
</h4>

<hr>

<p>
<strong>Performance Score:</strong>
<?php echo $employee['performance_score']; ?>%
</p>

<div class="progress mb-3">

<div
class="progress-bar bg-success"
style="width: <?php echo min(100, max(0, $employee['performance_score'])); ?>%;"
>

<?php echo $employee['performance_score']; ?>%

</div>

</div>

<p>
<strong>Current Workload:</strong>
<?php echo $employee['workload']; ?>
</p>

</div>

</div>

</div>

</div>


<!-- ===========================
     MY TASKS
=========================== -->

<div class="card task-card">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">
📋 My Assigned Tasks
</h4>

</div>


<div class="card-body">


<?php if ($tasks->num_rows > 0) { ?>


<div class="table-responsive">

<table class="table table-hover align-middle">

<thead>

<tr>

<th>Task</th>

<th>Project</th>

<th>Priority</th>

<th>Status</th>

<th>Start Date</th>

<th>Due Date</th>

</tr>

</thead>


<tbody>


<?php while ($task = $tasks->fetch_assoc()) { ?>


<tr>


<td>

<strong>
<?php echo htmlspecialchars($task['task_title']); ?>
</strong>

<br>

<small class="text-muted">

<?php echo htmlspecialchars($task['description']); ?>

</small>

</td>


<td>

<?php echo htmlspecialchars($task['project_name'] ?? 'No Project'); ?>

</td>


<td>


<?php

$priority_class = "secondary";

if ($task['priority'] == "High") {
    $priority_class = "danger";
}

elseif ($task['priority'] == "Medium") {
    $priority_class = "warning";
}

elseif ($task['priority'] == "Low") {
    $priority_class = "success";
}

?>


<span class="badge bg-<?php echo $priority_class; ?>">

<?php echo htmlspecialchars($task['priority']); ?>

</span>


</td>


<td>


<?php

$status_class = "warning";

if ($task['status'] == "Completed") {
    $status_class = "success";
}

elseif ($task['status'] == "In Progress") {
    $status_class = "primary";
}

?>


<span class="badge bg-<?php echo $status_class; ?>">

<?php echo htmlspecialchars($task['status']); ?>

</span>


</td>


<td>

<?php echo htmlspecialchars($task['start_date']); ?>

</td>


<td>

<?php echo htmlspecialchars($task['due_date']); ?>

</td>


</tr>


<?php } ?>


</tbody>

</table>

</div>


<?php } else { ?>


<div class="text-center p-5">

<h5>
📭 No Tasks Assigned
</h5>

<p class="text-muted">

You currently don't have any assigned tasks.

</p>

</div>


<?php } ?>


</div>

</div>


</div>


</body>

</html>