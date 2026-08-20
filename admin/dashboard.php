<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}

require_once "../config/database.php";

/* ===========================
   Dashboard Counts
=========================== */

$employees = 0;
$projects = 0;
$total_tasks = 0;
$pending_tasks = 0;
$completed_tasks = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM employees");
if($result){
    $employees = $result->fetch_assoc()['total'];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM projects");
if($result){
    $projects = $result->fetch_assoc()['total'];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM tasks");
if($result){
    $total_tasks = $result->fetch_assoc()['total'];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE status='Pending'");
if($result){
    $pending_tasks = $result->fetch_assoc()['total'];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE status='Completed'");
if($result){
    $completed_tasks = $result->fetch_assoc()['total'];
}

/* ===========================
   Recent Tasks
=========================== */

$recent_tasks_query = "
SELECT
tasks.task_title,
employees.full_name,
tasks.status

FROM tasks

JOIN employees
ON tasks.employee_id = employees.employee_id

ORDER BY tasks.task_id DESC

LIMIT 5
";

$recent_tasks = $conn->query($recent_tasks_query);

/* ===========================
   Recent Projects
=========================== */

$recent_projects_query = "
SELECT
project_name,
status,
start_date

FROM projects

ORDER BY project_id DESC

LIMIT 5
";

$recent_projects = $conn->query($recent_projects_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect"
href="https://fonts.gstatic.com"
crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
rel="stylesheet">

<title>Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


</head>

<body>

<?php include 'navbar.php'; ?>

<div class="d-flex">

<?php include 'sidebar.php'; ?>

<div class="container-fluid p-4">

<div class="mb-4">

<h2 class="dashboard-title">
👋 Welcome Back, Administrator
</h2>

<p class="text-muted">
AI Driven Workforce Management Dashboard
</p>

</div>

<div class="row">

<div class="col-lg-3 col-md-6 mb-4">

<a href="../employee/view.php"
class="text-decoration-none">

<div class="card stat-card bg-blue">

<div class="card-body text-center">

<h5>Total Employees</h5>

<h2><?php echo $employees; ?></h2>

</div>

</div>

</a>

</div>

<div class="col-lg-3 col-md-6 mb-4">

<a href="../project/view.php"
class="text-decoration-none">

<div class="card stat-card bg-green">

<div class="card-body text-center">

<h5>Total Projects</h5>

<h2><?php echo $projects; ?></h2>

</div>

</div>

</a>

</div>

<div class="col-lg-3 col-md-6 mb-4">

<a href="../task/view.php"
class="text-decoration-none">

<div class="card stat-card bg-purple">

<div class="card-body text-center">

<h5>Total Tasks</h5>

<h2><?php echo $total_tasks; ?></h2>

</div>

</div>

</a>

</div>

<div class="col-lg-3 col-md-6 mb-4">

<a href="../task/view.php?status=Pending"
class="text-decoration-none">

<div class="card stat-card bg-orange">

<div class="card-body text-center">

<h5>Pending Tasks</h5>

<h2><?php echo $pending_tasks; ?></h2>

</div>

</div>

</a>

</div>

<div class="col-lg-3 col-md-6 mb-4">

<a href="../task/view.php?status=Completed"
class="text-decoration-none">

<div class="card stat-card bg-red">

<div class="card-body text-center">

<h5>Completed Tasks</h5>

<h2><?php echo $completed_tasks; ?></h2>

</div>

</div>

</a>

</div>

</div>

<hr class="my-4">

<h3 class="mb-3">
⚡ Quick Actions
</h3>

<div class="row">

<div class="col-md-3 mb-3">

<a href="../employee/add.php"
class="btn btn-primary w-100 p-3">

➕ Add Employee

</a>

</div>

<div class="col-md-3 mb-3">

<a href="../project/add.php"
class="btn btn-success w-100 p-3">

📁 Add Project

</a>

</div>

<div class="col-md-3 mb-3">

<a href="../task/add.php"
class="btn btn-warning w-100 p-3">

✅ Assign Task

</a>

</div>

<div class="col-md-3 mb-3">

<a href="../reports/index.php"
class="btn btn-dark w-100 p-3">

📊 View Reports

</a>

</div>

</div>

<hr class="my-4">

<div class="card shadow mb-4">

<div class="card-header bg-dark text-white">

<h4>📅 Recent Tasks</h4>

</div>

<div class="card-body">

<table class="table table-hover">

<thead>

<tr>

<th>Task</th>
<th>Employee</th>
<th>Status</th>

</tr>

</thead>

<tbody>

<?php while($task = $recent_tasks->fetch_assoc()){ ?>

<tr>

<td><?php echo $task['task_title']; ?></td>

<td><?php echo $task['full_name']; ?></td>

<td>
<?php
if($task['status']=="Completed"){
    echo "<span class='badge bg-success'>Completed</span>";
}else{
    echo "<span class='badge bg-warning text-dark'>Pending</span>";
}
?>
</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>
<hr class="my-4">

<div class="card shadow mb-4">

    <div class="card-header bg-success text-white">

        <h4>📁 Recent Projects</h4>

    </div>

    <div class="card-body">

        <table class="table table-striped table-hover">

            <thead class="table-light">

                <tr>

                    <th>Project</th>
                    <th>Status</th>
                    <th>Start Date</th>

                </tr>

            </thead>

            <tbody>

            <?php while($project = $recent_projects->fetch_assoc()){ ?>

                <tr>

                    <td><?php echo $project['project_name']; ?></td>

                    <td>

                        <?php

                        if($project['status']=="Completed"){

                            echo "<span class='badge bg-success'>Completed</span>";

                        }
                        elseif($project['status']=="In Progress"){

                            echo "<span class='badge bg-warning text-dark'>In Progress</span>";

                        }
                        else{

                            echo "<span class='badge bg-secondary'>Pending</span>";

                        }

                        ?>

                    </td>

                    <td><?php echo $project['start_date']; ?></td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

<hr class="my-4">

<div class="alert alert-info shadow">

    <h4 class="mb-3">
        📊 Dashboard Summary
    </h4>

    <div class="row text-center">

        <div class="col-md-3">

            <h5>Total Employees</h5>

            <h3 class="text-primary">
                <?php echo $employees; ?>
            </h3>

        </div>

        <div class="col-md-3">

            <h5>Total Projects</h5>

            <h3 class="text-success">
                <?php echo $projects; ?>
            </h3>

        </div>

        <div class="col-md-3">

            <h5>Completed Tasks</h5>

            <h3 class="text-danger">
                <?php echo $completed_tasks; ?>
            </h3>

        </div>

        <div class="col-md-3">

            <h5>Pending Tasks</h5>

            <h3 class="text-warning">
                <?php echo $pending_tasks; ?>
            </h3>

        </div>

    </div>

</div>

</div>

</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>