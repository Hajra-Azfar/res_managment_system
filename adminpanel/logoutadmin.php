<?php
session_start();

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to home page outside the admin folder
header("Location: ../home.php"); // Change path if your home page is somewhere else
exit();
