<?php
session_start();
include('../db_connect.php');

// Redirect if not signed in
if (!isset($_SESSION['user_email'])) {
    header("Location: ../signin.php");
    exit();
}

$email = $_SESSION['user_email'];

$items = [];
$message = "";

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!empty($_POST['item_ids']) && !empty($_POST['quantities'])) {
        $itemIds = $_POST['item_ids'];
        $quantities = $_POST['quantities'];

        $totalBill = 0;
        $orderItems = [];

        for ($i = 0; $i < count($itemIds); $i++) {
            $foodId = intval($itemIds[$i]);
            $qty = intval($quantities[$i]);

            if ($qty < 1) continue;

            $stmt = sqlsrv_query($conn, "SELECT id, name, price FROM food_items_records WHERE id = ?", [$foodId]);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $price = floatval($row['price']); // ensure it's numeric
                $name = $row['name'];
                $total = $price * $qty;
                $totalBill += $total;

                $orderItems[] = [
                    'food_id' => $foodId,
                    'name' => $name,
                    'quantity' => $qty,
                    'price' => $price,
                    'total' => $total
                ];
            }
        }

        if (!empty($orderItems)) {
            // Get user_id from users_records based on email
            $getUser = sqlsrv_query($conn, "SELECT id FROM users_records WHERE email = ?", array($email));
            $user = sqlsrv_fetch_array($getUser, SQLSRV_FETCH_ASSOC);
            $user_id = $user['id'];

            // Insert order with user_id
            $orderStmt = sqlsrv_query($conn, "INSERT INTO food_orders_records (user_id, total_price) OUTPUT INSERTED.id VALUES (?, ?)", [$user_id, $totalBill]);

            if ($orderStmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            if ($orderRow = sqlsrv_fetch_array($orderStmt, SQLSRV_FETCH_ASSOC)) {
                $orderId = $orderRow['id'];
                $_SESSION['last_order_id'] = $orderId;

                foreach ($orderItems as $item) {
                    // Insert order items for the placed order
                    $orderItemStmt = sqlsrv_query($conn, "INSERT INTO order_items (order_id, food_id, quantity, price) VALUES (?, ?, ?, ?)", [
                        $orderId, $item['food_id'], $item['quantity'], $item['price']
                    ]);

                    if ($orderItemStmt === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }
                }

                $message = "✅ Order placed successfully! Total Bill: Rs. " . number_format($totalBill, 2);
            } else {
                $message = "❌ Failed to create order.";
            }
        } else {
            $message = "⚠️ No valid items to order.";
        }
    } else {
        $message = "⚠️ No items selected.";
    }
}

// Fetch all food items
$sql = "SELECT * FROM food_items_records";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Menu & Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }
        .item {
            background: #fff;
            padding: 15px;
            margin: 10px auto;
            border-radius: 8px;
            max-width: 600px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 5px #ccc;
        }
        .item input[type='number'] {
            width: 60px;
        }
        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .success { color: green; }
        .error { color: red; }
        table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 90%;
            max-width: 600px;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background-color: #eee;
        }
    </style>
</head>
<body>

<h2>Welcome, <?= htmlspecialchars($email) ?></h2>

<?php if (!empty($message)): ?>
    <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<form method="POST">
    <?php foreach ($items as $item): ?>
        <div class="item">
            <div>
                <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                Rs. <?= number_format(floatval($item['price']), 2) ?>
            </div>
            <div>
                <input type="hidden" name="item_ids[]" value="<?= $item['id'] ?>">
                Quantity: <input type="number" name="quantities[]" min="0" value="0" required>
            </div>
        </div>
    <?php endforeach; ?>

    <div style="text-align:center; margin-top: 20px;">
        <input type="submit" name="place_order" value="Place Order" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 6px;">
    </div>
</form>

<?php
// Display most recent order
if (isset($_SESSION['last_order_id'])) {
    $orderId = $_SESSION['last_order_id'];
    $stmt = sqlsrv_query($conn, "SELECT fi.name, oi.quantity, oi.price, (oi.quantity * oi.price) AS total
                                 FROM order_items oi
                                 JOIN food_items_records fi ON fi.id = oi.food_id
                                 WHERE oi.order_id = ?", [$orderId]);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    echo "<h3 style='text-align:center;'>Your Recent Order</h3>";
    echo "<table>
            <tr><th>Item</th><th>Quantity</th><th>Rate</th><th>Total</th></tr>";
    $grandTotal = 0;
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rowTotal = floatval($row['total']);
        echo "<tr>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . intval($row['quantity']) . "</td>
                <td>Rs. " . number_format(floatval($row['price']), 2) . "</td>
                <td>Rs. " . number_format($rowTotal, 2) . "</td>
              </tr>";
        $grandTotal += $rowTotal;
    }
    echo "<tr>
            <td colspan='3'><strong>Grand Total</strong></td>
            <td><strong>Rs. " . number_format($grandTotal, 2) . "</strong></td>
          </tr>";
    echo "</table>";
}
?>

</body>
</html>
