<?php

require_once "../config/database.php";

$id = (int)$_GET['id'];

$sql = "DELETE FROM departments WHERE department_id=?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i",$id);

$stmt->execute();

header("Location:view.php");

exit();

?>