<?php

require_once "../config/database.php";

$id = $_GET['id'];

$sql = "SELECT * FROM employees WHERE employee_id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

$employee = $result->fetch_assoc();

if(isset($_POST['update'])){

    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $designation = $_POST['designation'];

    $sql = "UPDATE employees
            SET
            full_name=?,
            phone=?,
            designation=?
            WHERE employee_id=?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "sssi",
        $full_name,
        $phone,
        $designation,
        $id
    );

    if($stmt->execute()){

        header("Location:view.php");
        exit();

    }

}

?>

<!DOCTYPE html>
<html>

<head>

<title>Edit Employee</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
<?php include "../config/page_actions.php"; ?>
<div class="container mt-5">

<h2>Edit Employee</h2>

<form method="POST">

<label class="form-label">Full Name</label>

<input
type="text"
name="full_name"
value="<?php echo $employee['full_name']; ?>"
class="form-control mb-3"
required>


<label class="form-label">Phone</label>

<input
type="text"
name="phone"
value="<?php echo $employee['phone']; ?>"
class="form-control mb-3">


<label class="form-label">Designation</label>

<input
type="text"
name="designation"
value="<?php echo $employee['designation']; ?>"
class="form-control mb-3">


<button
name="update"
class="btn btn-success">

Update Employee

</button>

<a href="view.php" class="btn btn-secondary">

Back

</a>

</form>

</div>

</body>

</html>