<?php
session_start();

// ✅ Admin check
if (!isset($_SESSION['admin_email']) || $_SESSION['admin_email'] !== 'admin@res.com') {
    die("Access denied!");
}

include '../db_connect.php'; // Connection to DB

// ✅ Handle payment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['payment_status'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['payment_status'];

    $updateSql = "UPDATE food_orders_records SET payment_status = ? WHERE id = ?";
    $params = [$status, $orderId];
    $stmt = sqlsrv_query($conn, $updateSql, $params);

    if ($stmt) {
        header("Location: adminorder.php?updated=true");
        exit();
    } else {
        die("Failed to update status: " . print_r(sqlsrv_errors(), true));
    }
}

// ✅ Handle pagination
$pageNumber = isset($_GET['page']) ? intval($_GET['page']) : 1; // Default to page 1 if not provided

// ✅ Fetch order data with pagination (using the query from MS SQL changes)
$sql = "DECLARE @PageNumber INT = ?;
        DECLARE @RowsPerPage INT = 10;

        SELECT 
            o.id AS order_id,
            u.email AS customer_email,
            CONVERT(VARCHAR, o.order_time, 120) AS order_time,
            fi.name AS food_item,
            oi.quantity,
            oi.price AS unit_price,
            oi.quantity * oi.price AS total_price,
            o.payment_status
        FROM food_orders_records o
        JOIN users_records u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        JOIN food_items_records fi ON oi.food_id = fi.id
        ORDER BY o.id DESC, oi.id ASC
        OFFSET (@PageNumber - 1) * @RowsPerPage ROWS
        FETCH NEXT @RowsPerPage ROWS ONLY;";

$stmt = sqlsrv_query($conn, $sql, [$pageNumber]);
if ($stmt === false) {
    die("Error fetching data: " . print_r(sqlsrv_errors(), true));
}

// ✅ Group rows by order_id
$orders = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $id = $row['order_id'];
    if (!isset($orders[$id])) {
        $orders[$id] = [
            'customer_email' => $row['customer_email'],
            'order_time' => $row['order_time'],
            'payment_status' => $row['payment_status'],
            'items' => []
        ];
    }
    $orders[$id]['items'][] = [
        'food_item' => $row['food_item'],
        'quantity' => $row['quantity'],
        'item_price' => $row['unit_price'],
        'total_price' => $row['total_price']
    ];
}

// ✅ Fetch total number of orders for pagination (to calculate total pages)
$totalSql = "SELECT COUNT(DISTINCT o.id) AS total_orders
             FROM food_orders_records o";
$totalResult = sqlsrv_query($conn, $totalSql);
$totalRow = sqlsrv_fetch_array($totalResult, SQLSRV_FETCH_ASSOC);
$totalOrders = $totalRow['total_orders'];
$totalPages = ceil($totalOrders / 10); // Assuming 10 records per page
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Order Management</title>
    <style>
    body { font-family: Arial, sans-serif; }
    
    /* Sidebar Styles */
    .sidebar {
        width: 200px;
        background-color: rgb(255, 0, 0); /* Yellow color */
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
        background-color:  rgb(255, 0, 0); /* Slightly darker yellow */
    }

    /* Main content styles */
    .main-content {
        margin-left: 220px; /* Adjusting for sidebar */
        background-color:white; /* Dark black background for the main content */
        color: black; /* White text color */
        padding: 20px;
    }

    h2 {
        color:  rgb(255, 0, 0); /* Yellow color for headings */
    }

    /* Other styles */
    .order-box { border: 1px solid #ccc; margin-bottom: 20px; padding: 15px; border-radius: 8px; }
    .order-header { font-weight: bold; background-color: #f2f2f2; padding: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
    th { background-color: #eee; }
    .update-form { margin-top: 10px; }
    select, input[type="submit"] {
        padding: 6px 10px;
        margin: 5px 0;
        font-size: 14px;
    }
    .pagination a {
        margin: 0 5px;
        text-decoration: none;
        padding: 6px 12px;
        border: 1px solid #ccc;
    }
    .pagination a.active {
        background-color: #ddd;
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
    <h2>All Customer Orders</h2>

    <?php foreach ($orders as $orderId => $order): ?>
        <div class="order-box">
            <div class="order-header">
                <strong>Order ID:</strong> <?= $orderId ?> |
                <strong>User Email:</strong> <?= htmlspecialchars($order['customer_email']) ?> |
                <strong>Order Time:</strong> <?= $order['order_time'] ?>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Food Item</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['food_item']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['item_price'], 2) ?></td>
                            <td><?= number_format($item['total_price'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p><strong>Total Price:</strong> <?= number_format(array_sum(array_column($order['items'], 'total_price')), 2) ?></p>

            <form method="POST" class="update-form">
                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                <label>Payment Status: </label>
                <select name="payment_status">
                    <option value="unpaid" <?= ($order['payment_status'] === 'unpaid') ? 'selected' : '' ?>>Unpaid</option>
                    <option value="paid" <?= ($order['payment_status'] === 'paid') ? 'selected' : '' ?>>Paid</option>
                </select>
                <input type="submit" value="Update">
            </form>
        </div>
    <?php endforeach; ?>

    <!-- Pagination -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="adminorder.php?page=<?= $i ?>" class="<?= ($i == $pageNumber) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>

</body>
</html>
