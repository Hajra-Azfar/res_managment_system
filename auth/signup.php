<?php
session_start();
include('../db_connect.php');

$message = '';
$messageType = '';
$signupSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $message = "Please fill all fields.";
        $messageType = "error";
    } else {
        $sqlCheck = "SELECT id FROM users_records WHERE username = ? OR email = ?";
        $paramsCheck = array($username, $email);
        $stmtCheck = sqlsrv_query($conn, $sqlCheck, $paramsCheck);

        if ($stmtCheck === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        if (sqlsrv_fetch_array($stmtCheck)) {
            $message = "Username or email already exists.";
            $messageType = "error";
        } else {
            $sql = "INSERT INTO users_records (username, email, password) VALUES (?, ?, ?)";
            $params = array($username, $email, $password);
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            } else {
                $_SESSION['user_email'] = $email;
                $_SESSION['signup_redirect'] = true;
                header("Location: ../index.php");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Sign Up</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0; padding: 0;
    }

    header {
      background-color: #3f51b5;
      color: white;
      padding: 20px 0;
      text-align: center;
    }

    main {
      max-width: 400px;
      margin: 40px auto;
      padding: 30px;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    form label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    form input[type="text"],
    form input[type="email"],
    form input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    form input[type="submit"] {
      background-color: #3f51b5;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      width: 100%;
      font-size: 16px;
    }

    form input[type="submit"]:hover {
      background-color: #303f9f;
    }

    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
      font-weight: bold;
    }

    .alert.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .alert.success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
  </style>
</head>
<body>
<header>
  <h1>Create Your Account</h1>
</header>

<main>
  <?php if (!empty($message)): ?>
    <div class="alert <?= $messageType ?>">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="signup.php">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <input type="submit" value="Sign Up">
  </form>
</main>
</body>
</html>
