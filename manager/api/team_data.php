<?php

session_start();

require_once "../../config/database.php";

header("Content-Type: application/json");


/* ===========================
   MANAGER LOGIN CHECK
=========================== */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role_id']) ||
    $_SESSION['role_id'] != 2
) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];


/* ===========================
   GET MANAGER INFORMATION
=========================== */

$sql = "
SELECT
    employees.employee_id,
    employees.department_id,
    departments.department_name

FROM users

LEFT JOIN employees
    ON users.user_id = employees.user_id

LEFT JOIN departments
    ON employees.department_id = departments.department_id

WHERE users.user_id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $conn->error
    ]);
    exit();
}

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();


/* ===========================
   MANAGER PROFILE CHECK
=========================== */

if ($result->num_rows != 1) {
    echo json_encode([
        "success" => false,
        "message" => "Manager profile not found"
    ]);
    exit();
}

$manager = $result->fetch_assoc();

$department_id = $manager['department_id'];
$manager_employee_id = $manager['employee_id'];


/* ===========================
   CHECK MANAGER DEPARTMENT
=========================== */

if (empty($department_id)) {
    echo json_encode([
        "success" => false,
        "message" => "Manager department is not assigned"
    ]);
    exit();
}


/* ===========================
   GET TEAM MEMBERS
=========================== */

$sql = "
SELECT
    employee_id,
    employee_code,
    full_name,
    gender,
    phone,
    designation,
    joining_date,
    performance_score,
    workload,
    completed_projects

FROM employees

WHERE department_id = ?
AND employee_id != ?

ORDER BY full_name ASC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $conn->error
    ]);
    exit();
}

$stmt->bind_param(
    "ii",
    $department_id,
    $manager_employee_id
);

$stmt->execute();

$result = $stmt->get_result();


/* ===========================
   EMPLOYEE DATA
=========================== */

$employees = [];

while ($row = $result->fetch_assoc()) {

    $employees[] = [
        "employee_id" => (int)$row['employee_id'],
        "employee_code" => $row['employee_code'],
        "full_name" => $row['full_name'],
        "gender" => $row['gender'],
        "phone" => $row['phone'],
        "designation" => $row['designation'],
        "joining_date" => $row['joining_date'],
        "performance_score" => (int)($row['performance_score'] ?? 0),
        "workload" => (int)($row['workload'] ?? 0),
        "completed_projects" => (int)($row['completed_projects'] ?? 0)
    ];
}


/* ===========================
   FINAL RESPONSE
=========================== */

echo json_encode([
    "success" => true,
    "department_id" => (int)$department_id,
    "department_name" => $manager['department_name'],
    "total" => count($employees),
    "employees" => $employees
]);

?>