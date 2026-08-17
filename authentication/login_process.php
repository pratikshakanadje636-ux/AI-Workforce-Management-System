<?php

session_start();

require_once "../config/database.php";

/* ===========================
   GET LOGIN DATA
=========================== */

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

/* ===========================
   BASIC VALIDATION
=========================== */

if ($email === '' || $password === '') {

    header("Location: login.php?error=empty");
    exit();

}

/* ===========================
   FIND USER
=========================== */

$sql = "
    SELECT
        user_id,
        role_id,
        name,
        email,
        password,
        status
    FROM users
    WHERE email = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("Database error: " . $conn->error);

}

$stmt->bind_param("s", $email);

$stmt->execute();

$result = $stmt->get_result();

/* ===========================
   USER NOT FOUND
=========================== */

if ($result->num_rows !== 1) {

    header("Location: login.php?error=invalid");
    exit();

}

$user = $result->fetch_assoc();

/* ===========================
   ACCOUNT STATUS CHECK
=========================== */

if (
    isset($user['status']) &&
    strtolower($user['status']) !== 'active'
) {

    header("Location: login.php?error=inactive");
    exit();

}

/* ===========================
   PASSWORD CHECK
=========================== */

if (!password_verify($password, $user['password'])) {

    header("Location: login.php?error=invalid");
    exit();

}

/* ===========================
   CREATE SESSION
=========================== */

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['role_id'] = $user['role_id'];

$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];

/* ===========================
   ROLE-BASED REDIRECTION
=========================== */

/*
    ROLE IDs

    1 = Administrator
    2 = Manager
    3 = Employee
*/

/* ===========================
   ADMIN
=========================== */

if ($user['role_id'] == 1) {

    header("Location: ../admin/dashboard_new.php");
    exit();

}

/* ===========================
   MANAGER
=========================== */

elseif ($user['role_id'] == 2) {

    header("Location: ../manager/dashboard.php");
    exit();

}

/* ===========================
   EMPLOYEE
=========================== */

elseif ($user['role_id'] == 3) {

    header("Location: ../employee/dashboard.php");
    exit();

}

/* ===========================
   UNKNOWN ROLE
=========================== */

else {

    session_unset();
    session_destroy();

    header("Location: login.php?error=role");
    exit();

}

?>