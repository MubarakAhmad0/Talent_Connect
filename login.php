<?php
session_start();
require 'db_connect.php';

function sanitize($input) {
  return htmlspecialchars(trim($input));
}


if (isset($_SESSION['user_id'])) {
  if ($_SESSION['user_type'] === 'admin') {
    header("Location: admin_dashboard.php");
  } else {
    header("Location: profile.php");
  }
  exit;
}

$error = "";


if (!isset($_SESSION['login_attempts'])) {
  $_SESSION['login_attempts'] = 0;
}

if ($_SESSION['login_attempts'] >= 5) {
  $error = "Too many failed attempts. Please try again later.";
} elseif (isset($_POST['login'])) {
  $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
  $password = $_POST['password'];

  if (!$email || strlen($password) < 6) {
    $error = "Please enter valid credentials.";
    $_SESSION['login_attempts']++;
  } else {
    $stmt = $conn->prepare("SELECT user_id, password, username, user_type FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();
      if (password_verify($password, $user['password'])) {

        $_SESSION['login_attempts'] = 0;


        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];


        $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $update->bind_param("i", $user['user_id']);
        $update->execute();

        // Redirect
        if ($user['user_type'] === 'admin') {
          header("Location: admin_dashboard.php");
        } else {
          header("Location: profile.php");
        }
        exit;
      } else {
        $error = "Incorrect password.";
        $_SESSION['login_attempts']++;
      }
    } else {
      $error = "No account found with that email.";
      $_SESSION['login_attempts']++;
    }
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login - MMU Talent Connect</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Rubik', sans-serif;
      background: linear-gradient(to right,rgba(8, 13, 161, 0.9),rgba(168, 11, 11, 0.98));
      display: flex;
      height: 100vh;
      color: #000;
    }

    .left, .right {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
    }

    .left {
      background: white;
      flex-direction: column;
    }

    .right {
      color: white;
      text-align: center;
      flex-direction: column;
      padding: 60px;
    }

    .logo-bar {
      position: absolute;
      top: 20px;
      left: 30px;
      display: flex;
      align-items: center;
    }

    .logo-bar img {
      height: 40px;
      margin-right: 10px;
    }

    .logo-bar span {
      font-weight: bold;
      font-size: 18px;
      color: #0D47A1;
    }

    .login-box {
      width: 100%;
      max-width: 400px;
    }

    h2 {
      text-align: center;
      color: #D32F2F;
      font-weight: 600;
      margin-bottom: 25px;
    }

    label {
      font-weight: 500;
      display: block;
      margin-bottom: 5px;
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-bottom: 15px;
    }

    input[type="submit"] {
      width: 100%;
      background: #0D47A1;
      color: white;
      border: none;
      padding: 12px;
      font-weight: 600;
      border-radius: 4px;
      cursor: pointer;
    }

    input[type="submit"]:hover {
      background: #002171;
    }

    .note {
      font-size: 13px;
      text-align: center;
      margin-top: 10px;
    }

    .right h1 {
      font-size: 30px;
      margin-bottom: 10px;
    }

    .right p {
      font-size: 18px;
      font-style: italic;
      max-width: 400px;
    }
  </style>
</head>
<body>

  <div class="logo-bar">
    <img src="logo.png" alt="MMU Logo">
    <span>MMU Talent Connect</span>
  </div>

  <div class="left">
    <div class="login-box">
      <h2>🔐 Login to Continue</h2>
      <form method="post" action="login.php">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <input type="submit" name="login" value="Login">
      </form>

      <div class="note">
        Don't have an account? <a href="register.php">Register here</a>
      </div>
    </div>
  </div>

  <div class="right">
    <h1>🌟 Connect. Discover. Get Hired.</h1>
    <p>Showcase your talent, find opportunities, and build your future through MMU Talent Connect.</p>
  </div>

</body>
</html>



