<?php
session_start();
$_SESSION['logged_out'] = true; // Set this flag before destroying session
session_unset();
session_destroy();
header("Location: ../home.php");
exit;
?>
