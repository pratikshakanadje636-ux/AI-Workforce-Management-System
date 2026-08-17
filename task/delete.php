<?php

require_once "../config/database.php";

$id = $_GET['id'];

$sql = "DELETE FROM tasks WHERE task_id=?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i",$id);


if($stmt->execute()){

    header("Location:view.php");
    exit();

}else{

    echo "Error deleting task.";

}

?>