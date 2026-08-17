<?php

require_once "../config/database.php";

$id = (int)$_GET['id'];

$sql = "SELECT * FROM departments WHERE department_id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();

$result = $stmt->get_result();

$department = $result->fetch_assoc();

if(isset($_POST['update'])){

    $department_name = trim($_POST['department_name']);

    $sql = "UPDATE departments
            SET department_name=?
            WHERE department_id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si",$department_name,$id);

    if($stmt->execute()){

        header("Location:view.php");
        exit();

    }

}

?>

<!DOCTYPE html>
<html>

<head>

<title>Edit Department</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <?php include "../config/page_actions.php"; ?>

<div class="container mt-5">

<h2>Edit Department</h2>

<form method="POST">

<label class="form-label">
Department Name
</label>

<input
type="text"
name="department_name"
class="form-control mb-3"
value="<?php echo $department['department_name']; ?>"
required>

<button
type="submit"
name="update"
class="btn btn-success">

Update Department

</button>

<a href="view.php" class="btn btn-secondary">

Back

</a>

</form>

</div>

</body>

</html>