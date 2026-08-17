<?php

require_once "../config/database.php";

// Load Departments
$departments = $conn->query("
SELECT department_id, department_name
FROM departments
ORDER BY department_name
");

$message = "";

if(isset($_POST['submit'])){

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $designation = $_POST['designation'];
    $department_id = $_POST['department_id'];
    $salary = $_POST['salary'];

    $joining_date = date("Y-m-d");

    // Employee Role
    $role_id = 3;

    // Check Email Exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
    $check->bind_param("s",$email);
    $check->execute();
    $check->store_result();

    if($check->num_rows>0){

        $message="
        <div class='alert alert-danger'>
        Email already exists.
        </div>";

    }else{

        $password_hash=password_hash($password,PASSWORD_DEFAULT);

        // Insert User

        $user_sql="
        INSERT INTO users
        (role_id,name,email,password,status)
        VALUES
        (?,?,?,?,?)
        ";

        $status="Active";

        $user_stmt=$conn->prepare($user_sql);

        $user_stmt->bind_param(
            "issss",
            $role_id,
            $full_name,
            $email,
            $password_hash,
            $status
        );

        if($user_stmt->execute()){

            $user_id=$conn->insert_id;

            $employee_code="EMP".rand(1000,9999);

            // Insert Employee

            $employee_sql="
            INSERT INTO employees
            (
                user_id,
                employee_code,
                full_name,
                gender,
                phone,
                designation,
                department_id,
                joining_date,
                salary
            )

            VALUES
            (?,?,?,?,?,?,?,?,?)
            ";

            $employee_stmt=$conn->prepare($employee_sql);

            $employee_stmt->bind_param(

                "isssssisd",

                $user_id,
                $employee_code,
                $full_name,
                $gender,
                $phone,
                $designation,
                $department_id,
                $joining_date,
                $salary

            );

            if($employee_stmt->execute()){

                $message="
                <div class='alert alert-success'>
                Employee Added Successfully.
                </div>";

            }else{

                $message="
                <div class='alert alert-danger'>
                ".$employee_stmt->error."
                </div>";

            }

        }else{

            $message="
            <div class='alert alert-danger'>
            ".$user_stmt->error."
            </div>";

        }

    }

}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Add Employee</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f8f9fa;
}

.card{
border-radius:15px;
}

</style>

</head>

<body>
    <?php include "../config/page_actions.php"; ?>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>Add Employee</h3>

</div>

<div class="card-body">

<?php echo $message; ?>

<form method="POST">
    <div class="row">

<div class="col-md-6 mb-3">
<label class="form-label">Full Name</label>
<input type="text" name="full_name" class="form-control" required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Gender</label>

<select name="gender" class="form-control" required>

<option value="">Select Gender</option>
<option>Male</option>
<option>Female</option>
<option>Other</option>

</select>

</div>

<div class="col-md-6 mb-3">
<label class="form-label">Phone</label>
<input type="text" name="phone" class="form-control">
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Designation</label>
<input type="text" name="designation" class="form-control">
</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Department
</label>

<select
name="department_id"
class="form-control"
required>

<option value="">
Select Department
</option>

<?php while($department=$departments->fetch_assoc()){ ?>

<option
value="<?php echo $department['department_id']; ?>">

<?php echo $department['department_name']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Salary
</label>

<input
type="number"
step="0.01"
name="salary"
class="form-control">

</div>

</div>

<button
type="submit"
name="submit"
class="btn btn-primary">

Add Employee

</button>

<a
href="view.php"
class="btn btn-secondary">

View Employees

</a>

</form>
