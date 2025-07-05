<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
  echo "<p style='color:red;'>Access denied.</p>";
  header("Refresh:2;url=login.php");
  exit;
}

$filter = $_GET['filter'] ?? 'all';

// Handle mark/unmark as important
if (isset($_GET['toggle'])) {
  $id = (int)$_GET['toggle'];
  $new_value = ($_GET['state'] == '1') ? 0 : 1;
  $stmt = $conn->prepare("UPDATE feedback SET is_important = ? WHERE feedback_id = ?");
  $stmt->bind_param("ii", $new_value, $id);
  $stmt->execute();
  header("Location: view_feedback.php?filter=$filter");
  exit;
}

// Load feedback
if ($filter === 'important') {
  $stmt = $conn->prepare("
    SELECT f.feedback_id, f.message, f.submitted_at AS created_at, f.is_important,
           u.username AS name, u.email
    FROM feedback f
    LEFT JOIN users u ON f.user_id = u.user_id
    WHERE f.is_important = 1
    ORDER BY f.submitted_at DESC
  ");
} else {
  $stmt = $conn->prepare("
    SELECT f.feedback_id, f.message, f.submitted_at AS created_at, f.is_important,
           u.username AS name, u.email
    FROM feedback f
    LEFT JOIN users u ON f.user_id = u.user_id
    ORDER BY f.submitted_at DESC
  ");
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Feedback - Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Rubik', sans-serif;
      background: #f0f4f8;
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
      margin-left: 20px;
    }

    .container {
      max-width: 960px;
      margin: 40px auto;
      padding: 0 20px;
    }

    h2 {
      font-size: 24px;
      color: #D32F2F;
      margin-bottom: 20px;
    }

    .filter-bar {
      margin-bottom: 20px;
    }

    .filter-bar a {
      text-decoration: none;
      margin-right: 20px;
      color: #0D47A1;
      font-weight: 600;
      border-bottom: 2px solid transparent;
    }

    .filter-bar a.active {
      border-color: #D32F2F;
    }

    .feedback-box {
      background: white;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      box-shadow: 0 0 6px rgba(0,0,0,0.05);
    }

    .feedback-box h3 {
      margin: 0;
      color: #0D47A1;
      font-size: 18px;
    }

    .feedback-box .email {
      font-size: 13px;
      color: #555;
    }

    .feedback-box .message {
      margin: 10px 0;
      white-space: pre-wrap;
    }

    .feedback-box .meta {
      font-size: 12px;
      color: #999;
      display: flex;
      justify-content: space-between;
    }

    .feedback-box .actions a {
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
    }

    .feedback-box .actions a.star {
      color: #D32F2F;
    }

    .feedback-box .actions a.unstar {
      color: #999;
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
      <a href="admin_dashboard.php">📂 Dashboard</a>
    </div>
  </div>

  <div class="container">
    <h2>📨 User Feedback</h2>

    <div class="filter-bar">
      <a href="view_feedback.php?filter=all" class="<?php echo ($filter === 'all') ? 'active' : ''; ?>">All</a>
      <a href="view_feedback.php?filter=important" class="<?php echo ($filter === 'important') ? 'active' : ''; ?>">⭐ Important Only</a>
    </div>

    <?php if ($result->num_rows === 0): ?>
      <p>No feedback found.</p>
    <?php endif; ?>

    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="feedback-box">
        <h3><?php echo htmlspecialchars($row['name'] ?? 'Anonymous'); ?></h3>
        <div class="email"><?php echo htmlspecialchars($row['email'] ?? ''); ?></div>
        <div class="message"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div>

        <div class="meta">
          <div><?php echo date("F j, Y - g:i A", strtotime($row['created_at'])); ?></div>
          <div class="actions">
            <?php if ($row['is_important']): ?>
              <a href="view_feedback.php?toggle=<?php echo $row['feedback_id']; ?>&state=1&filter=<?php echo $filter; ?>" class="unstar">☆ Unmark</a>
            <?php else: ?>
              <a href="view_feedback.php?toggle=<?php echo $row['feedback_id']; ?>&state=0&filter=<?php echo $filter; ?>" class="star">⭐ Mark Important</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

</body>
</html>
