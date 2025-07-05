<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
  echo "<p style='color:red;'>Access denied.</p>";
  header("Refresh:2;url=login.php");
  exit;
}

$message = "";
$editData = null;

if (isset($_POST['save'])) {
  $title = trim($_POST['title']);
  $msg = trim($_POST['message']);
  $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
  $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
  $edit_id = $_POST['edit_id'] ?? null;

  if (empty($title) || empty($msg)) {
    $message = "Title and message are required.";
  } else {
    if ($edit_id) {
      $stmt = $conn->prepare("UPDATE announcements SET title=?, message=?, expires_at=?, is_pinned=? WHERE id=?");
      $stmt->bind_param("sssii", $title, $msg, $expires_at, $is_pinned, $edit_id);
    } else {
      $stmt = $conn->prepare("INSERT INTO announcements (title, message, expires_at, is_pinned) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("sssi", $title, $msg, $expires_at, $is_pinned);
    }

    if ($stmt->execute()) {
      header("Location: announcement.php");
      exit;
    } else {
      $message = "Failed to save announcement.";
    }
  }
}

if (isset($_GET['delete'])) {
  $id = (int) $_GET['delete'];
  $stmt = $conn->prepare("DELETE FROM announcements WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  header("Location: announcement.php");
  exit;
}

if (isset($_GET['edit'])) {
  $id = (int) $_GET['edit'];
  $stmt = $conn->prepare("SELECT * FROM announcements WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $editData = $stmt->get_result()->fetch_assoc();
}

// Get all (exclude expired)
$result = $conn->query("SELECT * FROM announcements 
  WHERE expires_at IS NULL OR expires_at >= CURDATE()
  ORDER BY is_pinned DESC, created_at DESC");
?>

<!DOCTYPE html>
<html>

<head>
  <title>Manage Announcements - MMU Talent Connect</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Rubik', sans-serif;
      background: #f5f5f5;
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
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }

    h2 {
      color: #D32F2F;
      font-size: 24px;
      margin-bottom: 20px;
    }

    form {
      background: white;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
      margin-bottom: 40px;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: 500;
    }

    input[type="text"],
    textarea,
    input[type="date"] {
      width: 100%;
      padding: 10px;
      font-size: 14px;
      border-radius: 4px;
      border: 1px solid #ccc;
      margin-top: 5px;
    }

    input[type="checkbox"] {
      margin-right: 5px;
    }

    input[type="submit"] {
      background: #0D47A1;
      color: white;
      border: none;
      padding: 12px 20px;
      font-weight: 600;
      border-radius: 4px;
      margin-top: 20px;
      cursor: pointer;
    }

    input[type="submit"]:hover {
      background: #002171;
    }

    .announcement-card {
      background: white;
      border-left: 6px solid #0D47A1;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
      position: relative;
    }

    .announcement-card.pinned {
      border-left-color: #D32F2F;
      background: #fff8f8;
    }

    .announcement-card .title {
      font-size: 18px;
      color: #0D47A1;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .announcement-card .meta {
      font-size: 12px;
      color: #777;
      margin-bottom: 10px;
    }

    .announcement-card .message {
      font-size: 14px;
      white-space: pre-wrap;
    }

    .announcement-card .badges {
      margin-top: 10px;
    }

    .badge {
      display: inline-block;
      font-size: 12px;
      background: #e91e63;
      color: white;
      padding: 3px 7px;
      border-radius: 12px;
      margin-right: 8px;
    }

    .badge.pinned {
      background: #0D47A1;
    }

    .badge.soon {
      background: #FFA000;
    }

    .actions {
      margin-top: 15px;
    }

    .actions a {
      margin-right: 12px;
      text-decoration: none;
      color: #D32F2F;
      font-weight: 500;
      font-size: 14px;
    }

    .actions a:hover {
      text-decoration: underline;
    }

    .no-announcements {
      text-align: center;
      color: #888;
      font-size: 1.1em;
      margin-top: 40px;
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
      <a href="admin_dashboard.php">📋 Admin Dashboard</a>
    </div>
  </div>

  <div class="container">

    <h2>📢 Create / Edit Announcement</h2>

    <?php if (!empty($message)): ?>
      <p style="color:red;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="post" action="announcement.php">
      <input type="hidden" name="edit_id" value="<?php echo $editData['id'] ?? ''; ?>">

      <div style="margin-bottom: 20px;">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($editData['title'] ?? ''); ?>">
      </div>

      <div style="margin-bottom: 20px;">
        <label for="message">Message</label>
        <textarea id="message" name="message" rows="5" required><?php echo htmlspecialchars($editData['message'] ?? ''); ?></textarea>
      </div>

      <div style="margin-bottom: 20px;">
        <label for="expires_at">Expiration Date <small>(optional)</small></label>
        <input type="date" id="expires_at" name="expires_at" value="<?php echo $editData['expires_at'] ?? ''; ?>">
      </div>

      <div style="margin-bottom: 25px;">
        <label>
          <input type="checkbox" name="is_pinned" <?php if (!empty($editData['is_pinned'])) echo "checked"; ?>>
          📌 Pin this announcement
        </label>
      </div>

      <input type="submit" name="save" value="<?php echo $editData ? 'Update' : 'Add'; ?>">
    </form>

    <h2>📋 Current Announcements</h2>

    <?php if ($result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <?php
        $isPinned = !empty($row['is_pinned']);
        $isExpiringSoon = !empty($row['expires_at']) && strtotime($row['expires_at']) <= strtotime('+3 days');
        ?>
        <div class="announcement-card <?php echo $isPinned ? 'pinned' : ''; ?>">
          <div class="title"><?php echo $isPinned ? '📌 ' : ''; ?><?php echo htmlspecialchars($row['title']); ?></div>
          <div class="meta">
            Posted on <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
            | Expires: <?php echo $row['expires_at'] ?? '-'; ?>
          </div>
          <div class="message"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div>
          <div class="badges">
            <?php if ($isPinned): ?><span class="badge pinned">Pinned</span><?php endif; ?>
            <?php if ($isExpiringSoon): ?><span class="badge soon">Expiring Soon</span><?php endif; ?>
          </div>
          <div class="actions">
            <a href="announcement.php?edit=<?php echo $row['id']; ?>">✏️ Edit</a>
            <a href="announcement.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this announcement?');">🗑️ Delete</a>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="no-announcements">No announcements found.</p>
    <?php endif; ?>

  </div>
</body>

</html>