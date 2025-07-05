<?php
session_start();
require 'db_connect.php';

function sanitize($input)
{
  return htmlspecialchars(trim($input));
}

$errors = [];
$success = "";

if (isset($_POST['register'])) {
  $name     = sanitize($_POST['name']);
  $username = sanitize($_POST['username']);
  $email    = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
  $password = $_POST['password'];
  $confirm  = $_POST['confirm_password'];

  if (!$email) $errors[] = "Invalid email format.";
  if ($password !== $confirm) $errors[] = "Passwords do not match.";
  if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";

  // Only proceed if no validation errors
  if (empty($errors)) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $errors[] = "Username or email already in use.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO users (username, name, email, password, user_type, created_at) VALUES (?, ?, ?, ?, 'student', NOW())");
      $stmt->bind_param("ssss", $username, $name, $email, $hash);
      if ($stmt->execute()) {
        $new_user_id = $stmt->insert_id;

        // Add profile entry with name
        $stmt2 = $conn->prepare("INSERT INTO profiles (user_id, name) VALUES (?, ?)");
        $stmt2->bind_param("is", $new_user_id, $name);
        $stmt2->execute();

        $success = "Account created! Redirecting to login...";
        header("refresh:2;url=login.php");
        exit;
      } else {
        $errors[] = "Registration failed. Please try again.";
      }
    }
  }
}

?>

<!DOCTYPE html>
<html>

<head>
  <title>Register - MMU Talent Connect</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Rubik', sans-serif;
      background: linear-gradient(to right, rgb(2, 52, 126), rgba(197, 6, 6, 0.83));
      color: #000;
    }

    .header {
      display: flex;
      align-items: center;
      padding: 15px 30px;
      background: white;
    }

    .header img {
      height: 50px;
      margin-right: 15px;
    }

    .header h1 {
      font-size: 22px;
      color: rgba(21, 0, 116, 0.83);
      margin: 0;
    }

    .main {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 50px 20px;
      min-height: calc(100vh - 80px);
    }

    .form-container {
      background: white;
      padding: 30px 40px;
      border-radius: 8px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.15);
      max-width: 480px;
      width: 100%;
    }

    h2 {
      text-align: center;
      color: #0D47A1;
      font-weight: 600;
      margin-bottom: 25px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      margin-bottom: 15px;
      border-radius: 4px;
      font-size: 14px;
    }

    input[type="submit"] {
      background: rgba(5, 5, 168, 0.8);
      color: white;
      border: none;
      padding: 12px;
      width: 100%;
      font-size: 15px;
      font-weight: 600;
      border-radius: 4px;
      cursor: pointer;
    }

    input[type="submit"]:hover {
      background: #002171;
    }

    .note {
      font-size: 12px;
      color: #666;
      margin-top: -10px;
      margin-bottom: 10px;
    }

    .footer {
      text-align: center;
      font-size: 12px;
      color: white;
      margin-top: 20px;
    }
  </style>
</head>

<body>

  <div class="header">
    <img src="logo.png" alt="MMU Logo">
    <h1>MMU Talent Connect</h1>
  </div>

  <div class="main">
    <div class="form-container">
      <h2>📝 Create Your Account</h2>
      <form method="post" action="register.php" onsubmit="return validateForm();">
        <label>Full Name</label>
        <input type="text" name="name" required>
        <div class="note">Your real name helps us personalize your profile.</div>

        <label>Username</label>
        <input type="text" name="username" required>
        <div class="note">Used to login. Must be unique.</div>

        <label>Email Address</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" id="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <input type="submit" name="register" value="Register">
      </form>
    </div>
  </div>

  <div class="footer">
    © <?php echo date('Y'); ?> Multimedia University | Talent Connect Platform
  </div>

  <script>
    function validateForm() {
      var pass = document.getElementById("password").value;
      var confirm = document.getElementById("confirm_password").value;
      if (pass !== confirm) {
        alert("Passwords do not match.");
        return false;
      }
      return true;
    }
  </script>
</body>

</html>