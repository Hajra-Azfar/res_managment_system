<?php

$serverName = "DESKTOP-538SBG5"; // Replace this with your actual MS SQL Server name or IP address
$connectionInfo = array(
    "Database" => "restrauntmanagmentsystem", // Database name
    "UID" => "", // Your SQL Server username
    "PWD" => "", // Your SQL Server password
    "Encrypt" => "no" // Disable encryption
);

// Establish the connection
$conn = sqlsrv_connect($serverName, $connectionInfo);

// Check if the connection is successful
if( !$conn ) {
    // If the connection fails, display errors
    die( print_r(sqlsrv_errors(), true));
} else {
    // If the connection is successful
    echo "Connection established successfully.";
}

?>
