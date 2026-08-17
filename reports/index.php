<?php

require_once "../config/database.php";

// Dashboard Counts

$employees = $conn->query("SELECT COUNT(*) total FROM employees")->fetch_assoc()['total'];
$departments = $conn->query("SELECT COUNT(*) total FROM departments")->fetch_assoc()['total'];
$projects = $conn->query("SELECT COUNT(*) total FROM projects")->fetch_assoc()['total'];
$tasks = $conn->query("SELECT COUNT(*) total FROM tasks")->fetch_assoc()['total'];
$pending = $conn->query("SELECT COUNT(*) total FROM tasks WHERE status='Pending'")->fetch_assoc()['total'];
$completed = $conn->query("SELECT COUNT(*) total FROM tasks WHERE status='Completed'")->fetch_assoc()['total'];


// Project Progress

$project_sql = "
SELECT
projects.project_name,
COUNT(tasks.task_id) AS total_tasks,
SUM(CASE WHEN tasks.status='Completed' THEN 1 ELSE 0 END) AS completed_tasks

FROM projects

LEFT JOIN tasks
ON projects.project_id = tasks.project_id

GROUP BY projects.project_id
";

$project_result = $conn->query($project_sql);


// Employee Performance

$employee_sql = "
SELECT

employees.full_name,

COUNT(tasks.task_id) AS total_tasks,

SUM(CASE WHEN tasks.status='Completed' THEN 1 ELSE 0 END) AS completed_tasks

FROM employees

LEFT JOIN tasks
ON employees.employee_id = tasks.employee_id

GROUP BY employees.employee_id
";

$employee_result = $conn->query($employee_sql);


// ------------------ Chart Data ------------------

// Pie Chart
$taskLabels = ['Pending', 'Completed'];
$taskData = [$pending, $completed];

// Bar Chart
$projectNames = [];
$projectTaskCounts = [];

$chart_sql = "
SELECT
projects.project_name,
COUNT(tasks.task_id) AS total_tasks

FROM projects

LEFT JOIN tasks
ON projects.project_id = tasks.project_id

GROUP BY projects.project_id
";

$chart_result = $conn->query($chart_sql);

while($chart = $chart_result->fetch_assoc()){

    $projectNames[] = $chart['project_name'];
    $projectTaskCounts[] = $chart['total_tasks'];

}
// ------------------ AI Insights ------------------

$completionRate = ($tasks > 0) ? round(($completed / $tasks) * 100) : 0;

if($completionRate >= 80){
    $performance = "Excellent";
}
elseif($completionRate >= 60){
    $performance = "Good";
}
elseif($completionRate >= 40){
    $performance = "Average";
}
else{
    $performance = "Needs Improvement";
}
?>



<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Reports Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


</head>
<style>

/* =========================================================
   REPORTS PAGE - DARK MODE FIX
========================================================= */

/* Main reports container */
body.admin-dark-mode .container {
    color: #e5e7eb !important;
}

/* Normal cards */
body.admin-dark-mode .container .card {
    background: #1e293b !important;
    color: #e5e7eb !important;
    border-color: #334155 !important;
}

/* Card body */
body.admin-dark-mode .container .card-body {
    background: #1e293b !important;
    color: #e5e7eb !important;
}

/* Headings */
body.admin-dark-mode .container h2,
body.admin-dark-mode .container h3,
body.admin-dark-mode .container h4,
body.admin-dark-mode .container h5 {
    color: #f8fafc !important;
}

/* Normal tables */
body.admin-dark-mode .container .table {
    color: #e5e7eb !important;
    --bs-table-bg: #1e293b;
    --bs-table-color: #e5e7eb;
    --bs-table-border-color: #475569;
}

/* Table rows */
body.admin-dark-mode .container .table tbody tr {
    background: #1e293b !important;
    color: #e5e7eb !important;
}

/* Table cells */
body.admin-dark-mode .container .table tbody td {
    background: #1e293b !important;
    color: #e5e7eb !important;
    border-color: #475569 !important;
}

/* Keep dark table headers */
body.admin-dark-mode .container .table thead th {
    background: #111827 !important;
    color: #ffffff !important;
    border-color: #374151 !important;
}

/* AI Insights list */
body.admin-dark-mode .container .list-group-item {
    background: #1e293b !important;
    color: #e5e7eb !important;
    border-color: #475569 !important;
}

/* Progress background */
body.admin-dark-mode .container .progress {
    background: #334155 !important;
}

/* Horizontal lines */
body.admin-dark-mode .container hr {
    border-color: #475569 !important;
    opacity: 1;
}

/* Chart cards */
body.admin-dark-mode .container .shadow {
    box-shadow: 0 4px 15px rgba(0,0,0,0.35) !important;
}

/* Text-muted */
body.admin-dark-mode .container .text-muted {
    color: #94a3b8 !important;
}

</style>
</head>

<body class="bg-light">

    <?php include "../config/page_actions.php"; ?>

<div class="container mt-5">

<h2 class="text-center mb-5">
📊 Reports Dashboard
</h2>

<div class="row">

<div class="col-md-4 mb-3">
<div class="card bg-primary text-white shadow">
<div class="card-body text-center">
<h5>Total Employees</h5>
<h2><?php echo $employees; ?></h2>
</div>
</div>
</div>

<div class="col-md-4 mb-3">
<div class="card bg-success text-white shadow">
<div class="card-body text-center">
<h5>Total Departments</h5>
<h2><?php echo $departments; ?></h2>
</div>
</div>
</div>

<div class="col-md-4 mb-3">
<div class="card bg-info text-white shadow">
<div class="card-body text-center">
<h5>Total Projects</h5>
<h2><?php echo $projects; ?></h2>
</div>
</div>
</div>

<div class="col-md-4 mb-3">
<div class="card bg-dark text-white shadow">
<div class="card-body text-center">
<h5>Total Tasks</h5>
<h2><?php echo $tasks; ?></h2>
</div>
</div>
</div>

<div class="col-md-4 mb-3">
<div class="card bg-warning shadow">
<div class="card-body text-center">
<h5>Pending Tasks</h5>
<h2><?php echo $pending; ?></h2>
</div>
</div>
</div>

<div class="col-md-4 mb-3">
<div class="card bg-danger text-white shadow">
<div class="card-body text-center">
<h5>Completed Tasks</h5>
<h2><?php echo $completed; ?></h2>
</div>
</div>
</div>

</div>

<hr class="my-5">

<h3 class="mb-3">
📁 Project Progress
</h3>

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>Project</th>
<th>Total Tasks</th>
<th>Completed</th>
<th>Progress</th>

</tr>

</thead>

<tbody>

<?php while($row = $project_result->fetch_assoc()){

$total = $row['total_tasks'];
$done = $row['completed_tasks'];

$percent = ($total>0)?round(($done/$total)*100):0;

?>

<tr>

<td><?php echo $row['project_name']; ?></td>

<td><?php echo $total; ?></td>

<td><?php echo $done; ?></td>

<td>

<div class="progress">

<div
class="progress-bar bg-success"
style="width:<?php echo $percent; ?>%;">

<?php echo $percent; ?>%

</div>

</div>

</td>

</tr>

<?php } ?>

</tbody>

</table>

<hr class="my-5">

<h3 class="mb-3">
👨‍💼 Employee Performance
</h3>

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>Employee</th>
<th>Total Tasks</th>
<th>Completed Tasks</th>

</tr>

</thead>

<tbody>

<?php while($row = $employee_result->fetch_assoc()){ ?>

<tr>

<td><?php echo $row['full_name']; ?></td>

<td><?php echo $row['total_tasks']; ?></td>

<td><?php echo $row['completed_tasks']; ?></td>

</tr>

<?php } ?>

</tbody>

</table>


<hr class="my-5">

<div class="card border-primary shadow">

    <div class="card-header bg-primary text-white">

        <h4 class="mb-0">
            🤖 AI Insights
        </h4>

    </div>

    <div class="card-body">

        <ul class="list-group">

            <li class="list-group-item">
                📊 Total Tasks :
                <strong><?php echo $tasks; ?></strong>
            </li>

            <li class="list-group-item">
                ✅ Completed Tasks :
                <strong><?php echo $completed; ?></strong>
            </li>

            <li class="list-group-item">
                ⏳ Pending Tasks :
                <strong><?php echo $pending; ?></strong>
            </li>

            <li class="list-group-item">
                📈 Completion Rate :
                <strong><?php echo $completionRate; ?>%</strong>
            </li>

            <li class="list-group-item">
                🧠 Performance :
                <strong><?php echo $performance; ?></strong>
            </li>

            <li class="list-group-item">
                💡 Recommendation :
                <?php
                if($completionRate >= 80){
            echo "Excellent progress! Continue maintaining the current productivity.";
            }
           elseif($completionRate >= 60){
    echo "Project is progressing well. Focus on completing the remaining tasks.";
              }
               elseif($completionRate >= 40){
    echo "Average progress. Consider assigning more resources to pending tasks.";
}
              else{
    echo "Project needs immediate attention. Increase task completion rate.";
}
                ?>

                <li class="list-group-item">
           🤖 AI Prediction :
          <strong>
    <?php
     if($completionRate >= 80){
    echo "Project is likely to finish on time.";
 }
 elseif($completionRate >= 50){
     echo "Project is on track but needs regular monitoring.";
 }
 else{
     echo "High risk of project delay.";
 }
 ?>
 </strong>
 </li>
              </li>

        </ul>

    </div>

</div>

<hr class="my-5">

<h3 class="mb-4">
📈 Analytics Charts
</h3>

<div class="row">

    <div class="col-md-6">

        <div class="card shadow">

            <div class="card-body">

                <h5 class="text-center">
                    Task Status
                </h5>

                <canvas id="taskChart"></canvas>

            </div>

        </div>

    </div>

    <div class="col-md-6">

        <div class="card shadow">

            <div class="card-body">

                <h5 class="text-center">
                    Tasks Per Project
                </h5>

                <canvas id="projectChart"></canvas>

            </div>

        </div>

    </div>

</div>

</div>

<script>

new Chart(document.getElementById('taskChart'),{

    type:'pie',

    data:{

        labels: <?php echo json_encode($taskLabels); ?>,

        datasets:[{

            label:'Tasks',

            data: <?php echo json_encode($taskData); ?>

        }]

    }

});


new Chart(document.getElementById('projectChart'),{

    type:'bar',

    data:{

        labels: <?php echo json_encode($projectNames); ?>,

        datasets:[{

            label:'Total Tasks',

            data: <?php echo json_encode($projectTaskCounts); ?>

        }]

    },

    options:{

        responsive:true,

        scales:{

            y:{

                beginAtZero:true

            }

        }

    }

});

</script>

</body>
</html>