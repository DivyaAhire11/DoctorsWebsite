<?php
session_start();
session_destroy();
session_start();

$_SESSION['toast'] = "Logged out Successfully!";
$_SESSION['toast_type'] = "success";

header("Location: ../../index.php");
exit();
?>
