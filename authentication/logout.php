<?php

session_start();

/* Destroy all session data */
$_SESSION = array();

/* Destroy the session */
session_destroy();

/* Go back to Home Page */
header("Location: ../index.php");
exit();

?>