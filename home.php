<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Restaurant Management System</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif; 
      margin: 0;
      padding: 0;
      background-color: #ffffff; /* White background */
      color: #0000ff; /* Blue text */
    }

    header {
      background-color: #ffffff; 
      color: #0000ff;
      padding: 20px;
      text-align: center;
      position: relative;
      font-size: 32px;
    }

    nav {
      background-color: #0000ff;
      padding: 15px;
      text-align: center;
    }

    nav a {
      color: #ffffff;
      margin: 0 20px;
      text-decoration: none;
      font-size: 18px;
      font-weight: 600;
    }

    nav a:hover {
      text-decoration: underline;
    }

    #home {
      background-color: #f0f8ff; /* Light blueish-white */
      padding: 50px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    #home img {
      max-width: 50%;
      border-radius: 10px;
    }

    #home div {
      max-width: 45%;
      color: #0000ff;
      text-align: left; 
      font-size: 20px;
    }

    #about {
      background-color: #e6f0ff; /* Light blue */
      padding: 50px;
      color: #0000ff;
      text-align: center;
      font-size: 20px;
    }

    footer {
      background-color: #0000ff;
      color: white;
      text-align: center;
      padding: 15px;
      position: fixed;
      bottom: 0;
      width: 100%;
      font-size: 16px;
    }

    .auth-btn {
      position: absolute;
      top: 5px;
      background-color: #0000ff;
      color: white;
      padding: 5px 5px;
      border-radius: 2px;
      text-decoration: none;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .auth-btn:hover {
      background-color: #ffffff;
      color: #0000ff;
      transform: scale(1.05);
    }

    .admin-btn {
      right: 160px;
    }

    .user-btn {
      right: 20px;
    }
  </style>
</head>
<body>

  <header>
    <h1>FOODIES</h1>

    <a href="adminpanel/adminlogin.php" class="auth-btn admin-btn"> Admin</a>
    <a href="auth/signin.php" class="auth-btn user-btn"> User</a>
  </header>

  <nav>
    <a href="#home">Home</a>
  </nav>

  <section id="home">
    <div>
      <h2>Home Section</h2>
      <p>Welcome to FOODIES</p>
    </div>
  </section>


  <footer>
    &copy; 2025 Restaurant Management System. All rights reserved.
  </footer>

</body>
</html>
