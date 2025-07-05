<?php
session_start();
require 'db_connect.php';

// Access control
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
  echo "<p style='color:red;'>Access denied. Admins only.</p>";
  header("Refresh:2;url=login.php");
  exit;
}

session_regenerate_id(true);
?>

<!DOCTYPE html>
<html>

<head>
  <title>Admin Dashboard - MMU Talent Connect</title>
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
      font-size: 14px;
      margin-left: 20px;
    }

    .container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .dashboard-title {
      font-size: 28px;
      color: #0D47A1;
      margin-bottom: 20px;
      text-align: center;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
    }

    .tile {
      background: white;
      padding: 30px 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
      text-align: center;
      transition: 0.3s;
      cursor: pointer;
      border-top: 6px solid #D32F2F;
    }

    .tile:hover {
      transform: translateY(-4px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    }

    .tile h3 {
      margin: 0 0 10px;
      font-size: 20px;
      color: #0D47A1;
    }

    .tile p {
      font-size: 14px;
      color: #555;
    }

    .footer {
      margin-top: 40px;
      font-size: 12px;
      color: #888;
      text-align: center;
    }

    a.tile-link {
      text-decoration: none;
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
      <a href="logout.php">🚪 Logout</a>
    </div>
  </div>

  <div class="container">
    <h1 class="dashboard-title">🛠️ Admin Dashboard</h1>

    <div class="grid">

      <a href="notifications.php" class="tile-link">
        <div class="tile">
          <h3>🔔 Notifications</h3>
          <p>View alerts and updates</p>
        </div>
      </a>

      <a href="explore_users.php" class="tile-link">
        <div class="tile">
          <h3>👥 Explore Users</h3>
          <p>Search and review student profiles</p>
        </div>
      </a>


      <a href="announcement.php" class="tile-link">
        <div class="tile">
          <h3>📢 Announcements</h3>
          <p>Create and manage platform updates</p>
        </div>
      </a>

      <a href="view_feedback.php" class="tile-link">
        <div class="tile">
          <h3>📨 Feedback</h3>
          <p>View suggestions and issues from users</p>
        </div>
      </a>

      <a href="admin_faq.php" class="tile-link">
        <div class="tile">
          <h3>❓ FAQs</h3>
          <p>Edit common questions shown to students</p>
        </div>
      </a>

    </div>

    <div class="footer">
      © <?php echo date('Y'); ?> MMU Talent Connect – Admin Access
    </div>
  </div>

</body>

</html>