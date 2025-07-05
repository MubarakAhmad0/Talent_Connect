<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $feedback = trim($_POST['feedback'] ?? '');

  if (strlen($feedback) < 5) {
    $message = "Feedback must be at least 5 characters.";
  } else {
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $feedback);
    $stmt->execute();
    $message = "✅ Thank you! Your feedback has been submitted.";
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Submit Feedback</title>
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
      max-width: 540px;
      margin: 60px auto;
      background: white;
      padding: 35px 28px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }

    h2 {
      font-size: 22px;
      color: #D32F2F;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    textarea {
      width: 100%;
      min-height: 120px;
      padding: 12px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-top: 10px;
      margin-bottom: 20px;
      resize: vertical;
      box-sizing: border-box;
    }

    input[type="submit"] {
      width: 100%;
      background: #0D47A1;
      color: white;
      font-weight: 600;
      font-size: 15px;
      padding: 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.2s ease-in-out;
    }

    input[type="submit"]:hover {
      background: #08306b;
    }


    .msg {
      font-weight: 500;
      font-size: 14px;
      border-radius: 6px;
      padding: 10px 12px;
      margin-top: 15px;
      margin-bottom: 20px;
    }

    .msg.success {
      background: #e8f5e9;
      color: #2e7d32;
    }

    .msg.error {
      background: #ffebee;
      color: #c62828;
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
    <h2>💬 Submit Feedback</h2>

    <?php if ($message): ?>
      <div class="msg <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <form method="post">
      <textarea name="feedback" placeholder="Write your feedback here..." required></textarea>
      <input type="submit" value="Send Feedback">
    </form>
  </div>

  <div class="footer">
    &copy; <?php echo date('Y'); ?> MMU Talent Connect
  </div>

</body>

</html>