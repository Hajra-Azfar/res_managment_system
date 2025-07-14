<?php
session_start();
if (!isset($_SESSION['admin_email'])) {
    header("Location: adminlogin.php");
    exit();
}
include '../db_connect.php';

// Fetch all food items
$items = [];
$sql = "SELECT * FROM food_items_records ORDER BY id ASC";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $items[] = $row;
    }
} else {
    die("Error fetching food items: " . print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Food Items</title>
    <style>
        body {
            font-family: Arial;
            margin: 0;
            background: white; /* Black background */
            color: white; /* White text */
        }
        .sidebar {
            width: 200px;
            background-color:red; /* Yellow sidebar */
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: rgb(255, 0, 0); /* Dark red hover effect */
        }
        .main-content {
            margin-left: 220px;
            padding: 20px;
        }
        h2 {
            color:rgb(255, 0, 0); /* Yellow color for the heading */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white; /* Black background for table */
            color: black; /* White text for table */
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3 style="text-align: center;">Admin Panel</h3>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="adminorder.php">Order Management</a>
    <a href="update_payment_status.php">Payment</a>
    <a href="adminreservation.php">Reservations</a>
    <a href="logoutadmin.php">Logout</a>
</div>

<div class="main-content">
    <h2>Current Food Items (Live from Database)</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Food Name</th>
            <th>Price ($)</th>
        </tr>
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['id']); ?></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo number_format($item['price'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3">No food items found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
