<?php
session_start();
if (!isset($_SESSION['admin_email'])) {
    header("Location: adminlogin.php");
    exit();
}

include '../db_connect.php';

$feedback = "";

// Handle Update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $date = $_POST['reservation_date'];
    $guests = $_POST['num_guests'];
    $requests = $_POST['special_requests'];

    $sql = "UPDATE reservations SET reservation_date = ?, num_guests = ?, special_requests = ? WHERE id = ?";
    $params = [$date, $guests, $requests, $id];
    $stmt = sqlsrv_query($conn, $sql, $params);

    $feedback = $stmt ? "✅ Reservation updated successfully." : "❌ Error updating reservation.";
}

// Handle Delete
if (isset($_POST['delete'])) {
    $id = $_POST['id'];

    $sql = "DELETE FROM reservations WHERE id = ?";
    $stmt = sqlsrv_query($conn, $sql, [$id]);

    $feedback = $stmt ? "✅ Reservation deleted." : "❌ Error deleting reservation.";
}

// Fetch reservations
$reservations = [];
$sql = "SELECT r.id, u.email, r.reservation_date, r.num_guests, r.special_requests, r.created_at 
        FROM reservations r 
        JOIN users_records u ON r.user_id = u.id 
        ORDER BY r.id ASC";
$stmt = sqlsrv_query($conn, $sql);
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $reservations[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Reservations</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f6f8;
        }
        .sidebar {
            width: 200px;
            background-color: rgb(255, 0, 0);
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
            background-color: rgb(255, 0, 0);
        }
        .main-content {
            margin-left: 220px;
            padding: 20px;
        }
        h2 {
            color: rgb(255, 0, 0);
        }
        .feedback {
            color: green;
            margin: 10px auto;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        input[type="date"], input[type="number"], textarea {
            width: 100%;
            padding: 5px;
        }
        button {
            background-color: #006400;
            color: white;
            border: none;
            padding: 6px 10px;
            cursor: pointer;
        }
        button[name="delete"] {
            background-color: rgb(255, 0, 0);
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        form {
            margin: 0;
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
    <h2>Manage Table Reservations</h2>

    <?php if ($feedback): ?>
        <div class="feedback"><?= htmlspecialchars($feedback) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User Email</th>
                <th>Date</th>
                <th>Guests</th>
                <th>Special Requests</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($reservations as $res): ?>
            <tr>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $res['id'] ?>">
                    <td><?= $res['id'] ?></td>
                    <td><?= htmlspecialchars($res['email']) ?></td>
                    <td><input type="date" name="reservation_date" value="<?= $res['reservation_date']->format('Y-m-d') ?>"></td>
                    <td><input type="number" name="num_guests" value="<?= $res['num_guests'] ?>" min="1"></td>
                    <td><textarea name="special_requests"><?= htmlspecialchars($res['special_requests']) ?></textarea></td>
                    <td><?= $res['created_at']->format('Y-m-d H:i:s') ?></td>
                    <td>
                        <button type="submit" name="update">Update</button>
                        <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this reservation?')">Delete</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
