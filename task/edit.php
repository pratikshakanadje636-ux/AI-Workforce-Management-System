<?php

require_once "../config/database.php";

$id = $_GET['id'];

$sql = "SELECT * FROM tasks WHERE task_id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();

$result = $stmt->get_result();

$task = $result->fetch_assoc();



if(isset($_POST['update'])){


    $task_title = $_POST['task_title'];
    $description = $_POST['description'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $start_date = $_POST['start_date'];
    $due_date = $_POST['due_date'];


    $sql = "UPDATE tasks SET
    task_title=?,
    description=?,
    priority=?,
    status=?,
    start_date=?,
    due_date=?

    WHERE task_id=?";


    $stmt = $conn->prepare($sql);


    $stmt->bind_param(
        "ssssssi",
        $task_title,
        $description,
        $priority,
        $status,
        $start_date,
        $due_date,
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

<title>Edit Task</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

</head>


<body>

<?php include "../config/page_actions.php"; ?>
<div class="container mt-5">


<h2>Edit Task</h2>



<form method="POST">


<div class="mb-3">

<label>Task Title</label>

<input type="text"
name="task_title"
class="form-control"
value="<?php echo $task['task_title']; ?>">

</div>



<div class="mb-3">

<label>Description</label>

<textarea name="description"
class="form-control"><?php echo $task['description']; ?></textarea>

</div>



<div class="mb-3">

<label>Priority</label>

<select name="priority" class="form-control">

<option <?php if($task['priority']=="Low") echo "selected"; ?>>
Low
</option>

<option <?php if($task['priority']=="Medium") echo "selected"; ?>>
Medium
</option>

<option <?php if($task['priority']=="High") echo "selected"; ?>>
High
</option>

</select>

</div>




<div class="mb-3">

<label>Status</label>

<select name="status" class="form-control">


<option <?php if($task['status']=="Pending") echo "selected"; ?>>
Pending
</option>


<option <?php if($task['status']=="In Progress") echo "selected"; ?>>
In Progress
</option>


<option <?php if($task['status']=="Completed") echo "selected"; ?>>
Completed
</option>


</select>


</div>




<div class="mb-3">

<label>Start Date</label>

<input type="date"
name="start_date"
class="form-control"
value="<?php echo $task['start_date']; ?>">

</div>



<div class="mb-3">

<label>Due Date</label>

<input type="date"
name="due_date"
class="form-control"
value="<?php echo $task['due_date']; ?>">

</div>



<button name="update" class="btn btn-success">

Update Task

</button>


</form>


</div>


</body>

</html>