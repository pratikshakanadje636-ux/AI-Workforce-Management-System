<?php

require_once "../config/database.php";

if(isset($_POST['save'])){

    $department_name = trim($_POST['department_name']);

    $sql = "INSERT INTO departments(department_name)
            VALUES(?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("s",$department_name);

    if($stmt->execute()){

        header("Location:view.php");
        exit();

    }

}

?>

<!DOCTYPE html>
<html>

<head>

<title>Add Department</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<?php include "../config/page_actions.php"; ?>
<div class="container mt-5">

<h2>Add Department</h2>

<form method="POST">

<label class="form-label">
Department Name
</label>

<input
type="text"
name="department_name"
class="form-control mb-3"
required>

<button
type="submit"
name="save"
class="btn btn-success">

Save Department

</button>

<a href="view.php"
class="btn btn-secondary">

Back

</a>

</form>

</div>

</body>

</html>