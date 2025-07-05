<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Fetch notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Mark all as read
$conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$user_id");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Notifications</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Rubik', sans-serif;
      background: #f4f6f9;
      color: #222;
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
      max-width: 700px;
      margin: 40px auto;
      background: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }

    h2 {
      color: #0D47A1;
      margin-bottom: 25px;
    }

    .notif {
      padding: 15px;
      border-left: 5px solid #0D47A1;
      background: #e3f2fd;
      margin-bottom: 15px;
      border-radius: 4px;
    }

    .notif.read {
      background: #f1f1f1;
      border-left-color: #aaa;
    }

    .notif time {
      font-size: 12px;
      color: #555;
      display: block;
      margin-top: 6px;
    }

    a {
      text-decoration: none;
      color: #0D47A1;
      font-weight: 500;
    }

    a:hover {
      text-decoration: underline;
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
    <h2>🔔 Notifications</h2>

    <?php if ($result->num_rows === 0): ?>
      <p>No notifications yet.</p>
    <?php else: ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="notif <?php echo $row['is_read'] ? 'read' : ''; ?>">
          <?php if ($row['link']): ?>
            <a href="<?php echo htmlspecialchars($row['link']); ?>"><?php echo htmlspecialchars($row['message']); ?></a>
          <?php else: ?>
            <?php echo htmlspecialchars($row['message']); ?>
          <?php endif; ?>
          <time><?php echo date("M d, Y h:i A", strtotime($row['created_at'])); ?></time>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>

</body>
</html>

