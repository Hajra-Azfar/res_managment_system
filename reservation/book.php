<?php
session_start();
include('../db_connect.php');

// Redirect if not signed in
if (!isset($_SESSION['user_email'])) {
    header("Location: ../signin.php");
    exit();
}

$email = $_SESSION['user_email'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['reservation_date'];
    $guests = $_POST['num_guests'];
    $requests = $_POST['special_requests'];

    if (empty($date) || empty($guests)) {
        $_SESSION['reservation_error'] = "⚠️ Please fill all required fields.";
    } else {
        $currentDate = date('Y-m-d');
        if ($date < $currentDate) {
            $_SESSION['reservation_error'] = "❌ Reservation date cannot be in the past.";
        } else {
            // Fetch the user_id based on the email
            $userQuery = "SELECT id FROM users_records WHERE email = ?";
            $userStmt = sqlsrv_query($conn, $userQuery, array($email));
            $userRow = sqlsrv_fetch_array($userStmt, SQLSRV_FETCH_ASSOC);
            $user_id = $userRow['id'];

            // Check if a reservation exists for today
            $checkQuery = "SELECT * FROM reservations WHERE user_id = ? AND CAST(reservation_date AS DATE) = ?";
            $checkStmt = sqlsrv_query($conn, $checkQuery, array($user_id, $currentDate));
            $existingReservation = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

            if ($existingReservation) {
                $_SESSION['reservation_error'] = "❌ You have already made a reservation today.";
            } else {
                // Insert the reservation into the database
                $sql = "INSERT INTO reservations (user_id, reservation_date, num_guests, special_requests) 
                        VALUES (?, ?, ?, ?)";
                $params = array($user_id, $date, $guests, $requests);
                $stmt = sqlsrv_query($conn, $sql, $params);

                if ($stmt) {
                    $_SESSION['reservation_success'] = "✅ Reservation booked successfully!";
                    header("Location: ../index.php");
                    exit();
                } else {
                    $_SESSION['reservation_error'] = "❌ Error: " . print_r(sqlsrv_errors(), true);
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book a Reservation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 40px;
        }
        form {
            background: white;
            max-width: 400px;
            margin: auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            margin-top: 12px;
            display: block;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            margin-top: 20px;
            background-color: #28a745;
            color: white;
            border: none;
            font-weight: bold;
        }
        .user-email {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <form method="POST">
        <h2>Book a Table</h2>

        <div class="user-email">
            Booking for: <?= htmlspecialchars($email) ?>
        </div>

        <label for="reservation_date">Date:</label>
        <input type="date" name="reservation_date" required>

        <label for="num_guests">Number of Guests:</label>
        <input type="number" name="num_guests" min="1" required>

        <label for="special_requests">Special Requests:</label>
        <textarea name="special_requests" rows="4" placeholder="Optional..."></textarea>

        <input type="submit" value="Book Now">

        <?php if (isset($_SESSION['reservation_error'])): ?>
            <p style="color:red; text-align:center; font-weight:bold;">
                <?= $_SESSION['reservation_error'] ?>
            </p>
            <?php unset($_SESSION['reservation_error']); ?>
        <?php endif; ?>
    </form>

</body>
</html>