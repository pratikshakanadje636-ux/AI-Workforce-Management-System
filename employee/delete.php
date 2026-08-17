<?php

require_once "../config/database.php";

$id = $_GET['id'];

$sql = "DELETE FROM employees WHERE employee_id = $id";

if($conn->query($sql)){

    header("Location: view.php");

}
else{

    echo "Error deleting employee";

}

?>