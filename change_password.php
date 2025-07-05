<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $current = $_POST['current_password'] ?? '';
  $new = $_POST['new_password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  if (empty($current) || empty($new) || empty($confirm)) {
    $message = "All fields are required.";
  } elseif ($new !== $confirm) {
    $message = "New passwords do not match.";
  } elseif (strlen($new) < 6) {
    $message = "Password must be at least 6 characters.";
  } else {
    // Check current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current, $hashed)) {
      $message = "Current password is incorrect.";
    } else {
      $newHash = password_hash($new, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
      $stmt->bind_param("si", $newHash, $user_id);
      $stmt->execute();
      $message = "✅ Password changed successfully.";
    }
  }
}

// Create a notification
$stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
$message = "Your profile was updated.";
$link = "profile.php"; // or NULL
$stmt->bind_param("iss", $user_id, $message, $link);
$stmt->execute();

?>

<!DOCTYPE html>
<html>

<head>
  <title>Change Password</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Rubik', sans-serif;
      background: #f4f6f9;
      margin: 0;
      color: #222;
    }

    .nav-bar {
      background: #0D47A1;
      color: white;
      padding: 15px 30px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .nav-left {
      display: flex;
      align-items: center;
    }

    .nav-left img {
      height: 40px;
      margin-right: 10px;
    }

    .nav-left span {
      font-size: 20px;
      font-weight: 600;
    }

    .nav-right a {
      color: white;
      text-decoration: none;
      font-weight: 500;
      font-size: 16px;
    }

    .nav-right a:hover {
      text-decoration: underline;
    }

    .container {
      max-width: 500px;
      margin: 60px auto;
      background: white;
      padding: 40px 30px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
    }

    h2 {
      display: flex;
      align-items: center;
      font-size: 24px;
      color: #D32F2F;
      margin-bottom: 10px;
      gap: 10px;
    }

    .msg {
      margin-top: 15px;
      font-weight: 500;
      font-size: 14px;
      border-radius: 6px;
      padding: 10px 12px;
      margin-bottom: 20px;
    }

    .msg:before {
      content: 'ℹ️ ';
    }

    .msg.success {
      background: #e8f5e9;
      color: #2e7d32;
    }

    .msg.error {
      background: #ffebee;
      color: #c62828;
    }

    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
    }

    input[type="submit"] {
      background: #0D47A1;
      color: white;
      font-weight: 600;
      border: none;
      padding: 12px 20px;
      border-radius: 6px;
      margin-top: 30px;
      cursor: pointer;
      width: 100%;
      font-size: 16px;
    }

    input[type="submit"]:hover {
      background: #002171;
    }


    .msg {
      margin-top: 15px;
      color: #D32F2F;
      font-weight: 500;
    }

    .footer {
      text-align: center;
      color: #aaa;
      font-size: 12px;
      margin-top: 60px;
      padding-bottom: 20px;
    }
  </style>
</head>

<body>

  <div class="nav-bar">
    <div class="nav-left">
      <img src="logo.png" alt="Logo">
      <span>MMU Talent Connect</span>
    </div>
    <div class="nav-right">
      <a href="profile.php">📋 Dashboard</a>
    </div>
  </div>

  <div class="container">
    <h2>🔑 Change Password</h2>

    <?php if ($message): ?>
      <div class="msg"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="post">
      <label>Current Password</label>
      <input type="password" name="current_password" required>

      <label>New Password</label>
      <input type="password" name="new_password" required>

      <label>Confirm New Password</label>
      <input type="password" name="confirm_password" required>

      <input type="submit" value="Change Password">
    </form>
  </div>

  <div class="footer">
    &copy; <?php echo date('Y'); ?> MMU Talent Connect
  </div>

  

</body>

</html>