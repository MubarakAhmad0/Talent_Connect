<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
  header("Location: login.php");
  exit;
}

$stmt = $conn->prepare("
  SELECT title, message, created_at, is_pinned 
  FROM announcements 
  WHERE expires_at IS NULL OR expires_at >= CURDATE() 
  ORDER BY is_pinned DESC, created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>
  <title>📢 Announcements - MMU Talent Connect</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Rubik', sans-serif;
      background: #f0f4f8;
    }

    .nav-bar {
      background: #0D47A1;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
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
      max-width: 900px;
      margin: 40px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
    }

    .container h2 {
      text-align: center;
      color: #0D47A1;
      margin-bottom: 30px;
    }

    .announcement {
      border-left: 5px solid #0D47A1;
      padding: 20px;
      margin-bottom: 25px;
      background-color: #f9f9f9;
      border-radius: 6px;
    }

    .announcement.pinned {
      border-left-color: #D32F2F;
      background: #fff0f0;
    }

    .announcement h3 {
      margin-top: 0;
      color: #0D47A1;
    }

    .announcement .meta {
      color: #777;
      font-size: 12px;
      margin-bottom: 10px;
    }

    .announcement p {
      margin: 0;
      white-space: pre-wrap;
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
    <h2>📢 Announcements</h2>
    <?php if ($result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="announcement <?php echo $row['is_pinned'] ? 'pinned' : ''; ?>">
          <h3><?php echo $row['is_pinned'] ? '📌 ' : ''; ?><?php echo htmlspecialchars($row['title']); ?></h3>
          <div class="meta">🗓️ <?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?></div>
          <p><?php echo htmlspecialchars($row['message']); ?></p>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>🚫 No announcements available at the moment.</p>
    <?php endif; ?>
  </div>
</body>

</html>


