<?php
session_start();
if (!isset($_SESSION['admin_email'])) {
    header("Location: adminlogin.php");
    exit();
}

include '../db_connect.php';

$feedback = "";
$search_term = "";

// Check if search is triggered
if (isset($_POST['search'])) {
    $search_term = $_POST['search_term'];

    // Prepare SQL query for fetching all user data (reservations, orders, food items)
    $sql = "SELECT r.id AS reservation_id, u.email, r.reservation_date, r.num_guests, r.special_requests, r.created_at AS reservation_created_at,
                   fo.id AS order_id, fo.order_time, fo.total_price, fo.payment_status,
                   fi.name AS food_item_name, oi.quantity, oi.price AS food_item_price
            FROM reservations r
            JOIN users_records u ON r.user_id = u.id
            LEFT JOIN food_orders_records fo ON u.id = fo.user_id
            LEFT JOIN order_items oi ON fo.id = oi.order_id
            LEFT JOIN food_items_records fi ON oi.food_id = fi.id
            WHERE u.email LIKE ? OR r.id LIKE ?
            ORDER BY r.id ASC, fo.id ASC";
    $params = ["%$search_term%", "%$search_term%"];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die("Error executing query: " . print_r(sqlsrv_errors(), true));
    }
    
    // Fetch results
    $user_data = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $user_data[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search User Data</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f6f8;
        }
        .sidebar {
            width: 200px;
            background-color: #8B0000;
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
            background-color: #a52a2a;
        }
        .main-content {
            margin-left: 220px;
            padding: 20px;
        }
        h2 {
            color: #8B0000;
        }
        input[type="text"] {
            width: 300px;
            padding: 8px;
        }
        button {
            background-color: #006400;
            color: white;
            border: none;
            padding: 6px 10px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
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
    <a href="logout.php">Logout</a>
</div>

<div class="main-content">
    <h2>Search User Data</h2>

    <form method="POST">
        <input type="text" name="search_term" value="<?= htmlspecialchars($search_term) ?>" placeholder="Search by email or ID">
        <button type="submit" name="search">Search</button>
    </form>

    <?php if (isset($user_data) && count($user_data) > 0): ?>
        <h3>Search Results</h3>
        <table>
            <thead>
                <tr>
                    <th>Reservation ID</th>
                    <th>User Email</th>
                    <th>Reservation Date</th>
                    <th>Guests</th>
                    <th>Special Requests</th>
                    <th>Reservation Created</th>
                    <th>Order ID</th>
                    <th>Order Time</th>
                    <th>Total Price</th>
                    <th>Payment Status</th>
                    <th>Food Item</th>
                    <th>Quantity</th>
                    <th>Food Item Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($user_data as $data): ?>
                    <tr>
                        <td><?= $data['reservation_id'] ?></td>
                        <td><?= htmlspecialchars($data['email']) ?></td>
                        <td><?= $data['reservation_date']->format('Y-m-d') ?></td>
                        <td><?= $data['num_guests'] ?></td>
                        <td><?= htmlspecialchars($data['special_requests']) ?></td>
                        <td><?= $data['reservation_created_at']->format('Y-m-d H:i:s') ?></td>
                        <td><?= $data['order_id'] ?></td>
                        <td><?= $data['order_time']->format('Y-m-d H:i:s') ?></td>
                        <td><?= $data['total_price'] ?></td>
                        <td><?= $data['payment_status'] ?></td>
                        <td><?= htmlspecialchars($data['food_item_name']) ?></td>
                        <td><?= $data['quantity'] ?></td>
                        <td><?= $data['food_item_price'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (isset($user_data)): ?>
        <p>No results found for your search.</p>
    <?php endif; ?>
</div>

</body>
</html>
