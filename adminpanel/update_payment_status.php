<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_email'])) {
    die("Access denied!");
}

include '../db_connect.php';

// Handle form submission for updating payment status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['payment_status'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['payment_status'];

    // SQL query to update payment status
    $sql = "UPDATE food_orders_records SET payment_status = ? WHERE id = ?";
    $params = [$status, $orderId];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        // Redirect to the same page with success message
        header("Location: adminorder.php?updated=true");
        exit();
    } else {
        die("Failed to update status: " . print_r(sqlsrv_errors(), true));
    }
}

// Fetch orders and their details
$sql = "SELECT o.id, u.email, o.order_time, o.total_price, o.payment_status
        FROM food_orders_records o
        JOIN users_records u ON o.user_id = u.id
        ORDER BY o.order_time DESC";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        form { display: inline; }
        select { padding: 4px; }
        input[type=submit] { padding: 4px 10px; background-color: #007BFF; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

<h2>Manage Orders</h2>

<!-- Success message if the payment status was updated -->
<?php if (isset($_GET['updated']) && $_GET['updated'] == 'true') { ?>
    <p style="color: green;">Payment status updated successfully!</p>
<?php } ?>

<table>
    <tr>
        <th>Order ID</th>
        <th>User Email</th>
        <th>Order Time</th>
        <th>Total Price</th>
        <th>Payment Status</th>
        <th>Update</th>
    </tr>

    <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo $row['order_time']->format('Y-m-d H:i'); ?></td>
            <td><?php echo $row['total_price']; ?></td>
            <td><?php echo htmlspecialchars($row['payment_status']); ?></td>
            <td>
                <form method="POST" action="">
                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                    <select name="payment_status">
                        <option value="unpaid" <?php if ($row['payment_status'] == 'unpaid') echo 'selected'; ?>>Unpaid</option>
                        <option value="paid" <?php if ($row['payment_status'] == 'paid') echo 'selected'; ?>>Paid</option>
                    </select>
                    <input type="submit" value="Update">
                </form>
            </td>
        </tr>
    <?php } ?>
</table>

</body>
</html>
