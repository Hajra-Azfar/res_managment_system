<?php
session_start();

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to home page
header("Location: /home.php");  // Replace '/index.php' with your homepage URL
exit;
?>
