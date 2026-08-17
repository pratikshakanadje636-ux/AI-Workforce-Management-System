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


/* ===========================
   CHECK REQUEST
=========================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}


$task_id = isset($_POST['task_id'])
    ? (int)$_POST['task_id']
    : 0;

$new_status = $_POST['status'] ?? '';

$allowed_statuses = [
    'Pending',
    'In Progress',
    'Completed'
];


/* ===========================
   VALIDATE STATUS
=========================== */

if (
    $task_id <= 0 ||
    !in_array($new_status, $allowed_statuses, true)
) {
    header("Location: dashboard.php?error=invalid");
    exit();
}


/* ===========================
   GET EMPLOYEE
=========================== */

$user_id = $_SESSION['user_id'];

$sql = "
SELECT employee_id
FROM employees
WHERE user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Employee not found.");
}

$employee = $result->fetch_assoc();

$employee_id = $employee['employee_id'];


/* ===========================
   SECURITY CHECK
   Make sure this task belongs
   to the logged-in employee.
=========================== */

$sql = "
SELECT task_id
FROM tasks
WHERE task_id = ?
AND employee_id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $task_id,
    $employee_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    header("Location: dashboard.php?error=unauthorized");
    exit();

}


/* ===========================
   UPDATE TASK
=========================== */

$sql = "
UPDATE tasks
SET status = ?
WHERE task_id = ?
AND employee_id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sii",
    $new_status,
    $task_id,
    $employee_id
);

if ($stmt->execute()) {

    header("Location: dashboard.php?success=updated");
    exit();

} else {

    header("Location: dashboard.php?error=update");
    exit();

}

?>