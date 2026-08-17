<?php

require_once "../config/database.php";

if(isset($_POST['submit'])){

    $project_name = $_POST['project_name'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];


    $sql = "INSERT INTO projects
    (project_name, description, start_date, end_date, status)
    VALUES
    (?,?,?,?,?)";


    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "sssss",
        $project_name,
        $description,
        $start_date,
        $end_date,
        $status
    );


    if($stmt->execute()){

        echo "<div class='alert alert-success'>
        Project Added Successfully
        </div>";

    }
    else{

        echo "<div class='alert alert-danger'>
        Error: ".$stmt->error."
        </div>";

    }

}

?>


<!DOCTYPE html>
<html>

<head>

<title>Add Project</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

</head>


<body>
   
<?php include "../config/page_actions.php"; ?>

<div class="container mt-5">


<h2>Add Project</h2>


<form method="POST">


<div class="mb-3">
<label>Project Name</label>
<input type="text" name="project_name" class="form-control" required>
</div>


<div class="mb-3">
<label>Description</label>
<textarea name="description" class="form-control"></textarea>
</div>


<div class="mb-3">
<label>Start Date</label>
<input type="date" name="start_date" class="form-control">
</div>


<div class="mb-3">
<label>End Date</label>
<input type="date" name="end_date" class="form-control">
</div>


<div class="mb-3">
<label>Status</label>

<select name="status" class="form-control">

<option value="Planning">Planning</option>

<option value="Active">Active</option>

<option value="Completed">Completed</option>

<option value="On Hold">On Hold</option>

</select>

</div>


<button type="submit" name="submit" class="btn btn-primary">
Add Project
</button>


</form>


</div>


</body>

</html>