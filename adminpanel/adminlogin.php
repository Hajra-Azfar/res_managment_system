<?php
session_start();
include('../db_connect.php'); // adjust path as needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "⚠️ Please fill in all fields.";
    } else {
        $sql = "SELECT * FROM users_records WHERE email = ? AND role = 'admin'";
        $params = array($email);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt && ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
            // ⚠️ Plain text comparison (not secure, only for testing)
            if ($password === $row['password']) {
                $_SESSION['admin_email'] = $row['email'];
                $_SESSION['admin_name'] = $row['username'];
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = "❌ Incorrect password.";
            }
        } else {
            $error = "❌ Admin not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        body { font-family: Arial, sans-serif; background:white; padding: 40px; }
        form { background:  rgb(255, 0, 0); max-width: 400px; margin: auto; padding: 25px; border-radius: 8px; box-shadow:  rgb(255, 0, 0); }
        input { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; }
        input[type="submit"] { background-color: rgb(255, 0, 0); /* Yellow sidebar */
            ; color: white; font-weight: bold; border: none; }
        h2 { text-align: center; }
        .error { color: red; text-align: center; font-weight: bold; }
    </style>
</head>
<body>

<form method="POST">
    <h2>Admin Login</h2>
    <input type="email" name="email" placeholder="Admin Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="submit" value="Login">

    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
</form>

</body>
</html>
