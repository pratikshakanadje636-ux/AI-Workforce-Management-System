<?php

session_start();

require_once "../../config/database.php";

/* ===========================
   MANAGER LOGIN CHECK
=========================== */

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['role_id'] != 2
) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);

    exit();
}

/* JSON RESPONSE */

header('Content-Type: application/json');

/* ===========================
   GET DASHBOARD DATA
=========================== */

/* TOTAL EMPLOYEES */

$total_employees = 0;

$sql = "SELECT COUNT(*) AS total FROM employees";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $total_employees = (int)($row['total'] ?? 0);
}


/* TOTAL TASKS */

$total_tasks = 0;

$sql = "SELECT COUNT(*) AS total FROM tasks";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $total_tasks = (int)($row['total'] ?? 0);
}


/* COMPLETED TASKS */

$completed_tasks = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM tasks
    WHERE status = 'Completed'
";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $completed_tasks = (int)($row['total'] ?? 0);
}


/* PENDING TASKS */

$pending_tasks = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM tasks
    WHERE status = 'Pending'
";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $pending_tasks = (int)($row['total'] ?? 0);
}


/* IN PROGRESS TASKS */

$in_progress_tasks = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM tasks
    WHERE status = 'In Progress'
";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $in_progress_tasks = (int)($row['total'] ?? 0);
}


/* TOTAL PROJECTS */

$total_projects = 0;

$sql = "SELECT COUNT(*) AS total FROM projects";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $total_projects = (int)($row['total'] ?? 0);
}


/* ACTIVE PROJECTS */

$active_projects = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM projects
    WHERE status = 'Active'
";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $active_projects = (int)($row['total'] ?? 0);
}


/* COMPLETED PROJECTS */

$completed_projects = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM projects
    WHERE status = 'Completed'
";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $completed_projects = (int)($row['total'] ?? 0);
}


/* ===========================
   SEND JSON
=========================== */

echo json_encode([
    "success" => true,

    "employees" => [
        "total" => $total_employees
    ],

    "tasks" => [
        "total" => $total_tasks,
        "completed" => $completed_tasks,
        "pending" => $pending_tasks,
        "in_progress" => $in_progress_tasks
    ],

    "projects" => [
        "total" => $total_projects,
        "active" => $active_projects,
        "completed" => $completed_projects
    ]
]);