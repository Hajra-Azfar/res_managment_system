<?php 
session_start();
include('../db_connect.php'); 

$loginSuccess = false; 
$loginMessage = ""; 


if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $email = $_POST['email'] ?? ''; 
    $password = $_POST['password'] ?? ''; 

    if (empty($email) || empty($password)) { 
        $loginMessage = "Please fill all fields."; 
    } else { 
        $sql = "SELECT id, username, email, password FROM users_records WHERE email = ?"; 
        $params = array($email); 
        $stmt = sqlsrv_query($conn, $sql, $params); 

        if ($stmt === false) { 
            die(print_r(sqlsrv_errors(), true)); 
        } 

        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC); 

        if ($user) { 
            if ($password === $user['password']) { 
                // ✅ Fix: store email in the correct session key
                $_SESSION['user_email'] = $user['email']; 
                $_SESSION['username'] = $user['username']; 
                
                $loginSuccess = true; 
                $loginMessage = "Login successful! Welcome, " . htmlspecialchars($user['username']) . "."; 
            } else { 
                $loginMessage = "Invalid password."; 
            } 
        } else { 
            $loginMessage = "No user found with that email."; 
        } 
    } 
} 
?> 

<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <title>Sign In</title> 
    <link rel="stylesheet" href="../css/style.css"> 
    <style> 
        body { font-family: Arial, sans-serif; background: #f4f6f8; display: flex; flex-direction: column; align-items: center; } 
        header { background-color: #3f51b5; color: white; width: 100%; padding: 20px 0; text-align: center; } 
        main { margin-top: 40px; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); width: 300px; } 
        form { display: flex; flex-direction: column; } 
        label { margin-top: 10px; } 
        input[type="email"], input[type="password"] { padding: 8px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; } 
        input[type="submit"] { margin-top: 20px; padding: 10px; background-color: #3f51b5; color: white; border: none; border-radius: 5px; cursor: pointer; } 
        input[type="submit"]:hover { background-color: #303f9f; } 
        .message { margin-top: 10px; color: red; text-align: center; } 
    </style> 
</head> 
<body> 
    <header> 
        <h1>Sign In</h1> 
    </header> 
    <main> 
        <form method="POST" action="signin.php" autocomplete="off"> 
            <label for="email">Email:</label> 
            <input type="email" id="email" name="email" required autocomplete="off"> 

            <label for="password">Password:</label> 
            <input type="password" id="password" name="password" required autocomplete="off"> 

            <input type="submit" value="Sign In"> 
        </form> 

        <?php if (!$loginSuccess && !empty($loginMessage)) : ?> 
            <div class="message"><?php echo $loginMessage; ?></div> 
        <?php endif; ?> 
    </main> 

    <?php if ($loginSuccess) : ?> 
        <script> 
            alert("<?php echo $loginMessage; ?>"); 
            window.location.href = '../index.php';  // ✅ Redirect to main page
        </script> 
    <?php endif; ?> 
</body> 
</html>
