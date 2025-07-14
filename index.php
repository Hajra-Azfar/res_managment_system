<?php
session_start();
include('db_connect.php');

// Show modal only if user is NOT logged in
$showModal = !isset($_SESSION['user_email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Restaurant Management System</title>
  <style>
    :root {
      --primary-color: #3f51b5;
      --text-light: #ffffff;
      --bg-light: #f4f4f4;
      --card-bg: #ffffff;
      --shadow: rgba(0, 0, 0, 0.1);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--bg-light);
      display: flex;
    }

    .sidebar {
      width: 250px;
      background-color: var(--primary-color);
      color: var(--text-light);
      padding: 20px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      display: flex;
      flex-direction: column;
    }

    .sidebar h2 {
      font-size: 24px;
      margin-bottom: 20px;
      text-align: center;
    }

    .sidebar nav {
      display: flex;
      flex-direction: column;
      width: 100%;
    }

    .sidebar nav a {
      color: var(--text-light);
      text-decoration: none;
      font-weight: bold;
      padding: 12px;
      margin-bottom: 15px;
      text-align: center;
      border-radius: 5px;
      background-color: transparent;
      transition: 0.3s ease;
    }

    .sidebar nav a:hover {
      background-color: #303f9f;
      transform: scale(1.05);
    }

    .main-content {
      margin-left: 250px;
      padding: 20px;
      width: 100%;
    }

    .header {
      background-color: var(--primary-color);
      color: var(--text-light);
      padding: 20px;
      margin-bottom: 30px;
    }

    .header h1 { font-size: 24px; }

    main {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
    }

    .card {
      background-color: var(--card-bg);
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 8px var(--shadow);
      text-align: center;
      transition: 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px var(--shadow);
    }

    .card h2 {
      margin-bottom: 20px;
      color: var(--primary-color);
    }

    .card a {
      display: inline-block;
      background-color: var(--primary-color);
      color: white;
      text-decoration: none;
      padding: 10px 20px;
      border-radius: 6px;
      font-weight: bold;
      transition: 0.3s ease;
    }

    .card a:hover {
      background-color: #303f9f;
    }

    .modal {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 999;
    }

    .modal-content {
      background-color: white;
      padding: 40px;
      border-radius: 10px;
      text-align: center;
      width: 90%;
      max-width: 400px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    .auth-buttons {
      margin-top: 20px;
      display: flex;
      justify-content: space-around;
    }

    .auth-buttons .btn {
      padding: 10px 20px;
      background-color: var(--primary-color);
      color: white;
      text-decoration: none;
      border-radius: 5px;
      transition: 0.3s;
    }

    .auth-buttons .btn:hover {
      background-color: #303f9f;
    }

    .logout-button {
      margin-top: auto;
      padding: 12px;
      text-align: center;
      background-color: #d32f2f;
      border-radius: 5px;
      font-weight: bold;
      text-decoration: none;
      color: white;
      transition: 0.3s ease;
    }

    .logout-button:hover {
      background-color: #b71c1c;
      transform: scale(1.05);
    }
  </style>
</head>
<body>

<!-- Modal -->
<div id="authModal" class="modal">
  <div class="modal-content">
    <h2>Welcome to FOODIES</h2>
    <p>Please sign in or sign up to continue.</p>
    <div class="auth-buttons">
      <a href="auth/signin.php" class="btn">Sign In</a>
      <a href="auth/signup.php" class="btn">Sign Up</a>
    </div>
  </div>
</div>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Dashboard</h2>
  <nav>
    <?php if (!isset($_SESSION['user_email'])): ?>
      <a href="auth/signup.php">Sign Up</a>
      <a href="auth/signin.php">Sign In</a>
    <?php endif; ?>
    <a href="food/menu.php">View Menu</a>
    <a href="reservation/book.php">Make a Reservation</a>
  </nav>
  
  <?php if (isset($_SESSION['user_email'])): ?>
    <a href="auth/logout.php" class="logout-button">Logout</a>
  <?php endif; ?>
</div>

<!-- Main Content -->
<div class="main-content">
  <header class="header">
    <h1>FOODIES</h1>
  </header>

  <main>
    <div class="card">
      <h2>Explore Our Menu</h2>
      <a href="food/menu.php">View Menu</a>
    </div>

    <div class="card">
      <h2>Make a Reservation</h2>
      <a href="reservation/book.php">Book Now</a>
    </div>
  </main>
</div>

<script>
  window.onload = function () {
    const shouldShow = <?php echo $showModal ? 'true' : 'false'; ?>;
    if (shouldShow) {
      document.getElementById('authModal').style.display = 'flex';
    }
  };
</script>
</body>
</html>
