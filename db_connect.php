<?php
$serverName = "DESKTOP-538SBG5"; 
$connectionInfo = array(
    "Database" => "resmanagprojupd", 
    "UID" => "",              
    "PWD" => "",             
    "Encrypt" => "no",                        
    "TrustServerCertificate" => true          
);

// Connect to SQL Server
$conn = sqlsrv_connect($serverName, $connectionInfo);

// Check connection
if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}
?>
