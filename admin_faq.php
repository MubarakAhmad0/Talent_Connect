<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
  echo "<p style='color:red;'>Admins only. Redirecting...</p>";
  header("Refresh:2;url=login.php");
  exit;
}

$message = "";
$edit = null;

if (isset($_POST['save'])) {
  $question = trim($_POST['question']);
  $answer = trim($_POST['answer']);
  $category = trim($_POST['category']);
  $visibility = $_POST['visibility'];
  $id = $_POST['faq_id'] ?? null;

  if (empty($question) || empty($answer)) {
    $message = "Question and Answer cannot be empty.";
  } else {
    if ($id) {
      $stmt = $conn->prepare("UPDATE faq SET question=?, answer=?, category=?, visibility=? WHERE faq_id=?");
      $stmt->bind_param("ssssi", $question, $answer, $category, $visibility, $id);
    } else {
      $stmt = $conn->prepare("INSERT INTO faq (question, answer, category, visibility) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $question, $answer, $category, $visibility);
    }

    if ($stmt->execute()) {
      header("Location: admin_faq.php");
      exit;
    } else {
      $message = "Database error.";
    }
  }
}

if (isset($_GET['delete'])) {
  $id = (int) $_GET['delete'];
  $stmt = $conn->prepare("DELETE FROM faq WHERE faq_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  header("Location: admin_faq.php");
  exit;
}

if (isset($_GET['edit'])) {
  $id = (int) $_GET['edit'];
  $stmt = $conn->prepare("SELECT * FROM faq WHERE faq_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $edit = $stmt->get_result()->fetch_assoc();
}

$faqs = $conn->query("SELECT * FROM faq ORDER BY category, created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
  <title>❓ Manage FAQs - MMU Talent Connect</title>
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
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }

    h2 {
      font-size: 26px;
      color: #D32F2F;
      margin-bottom: 20px;
    }

    form {
      background: white;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      margin-bottom: 40px;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: 500;
    }

    input[type="text"],
    textarea,
    select {
      width: 100%;
      padding: 10px;
      font-size: 14px;
      border-radius: 4px;
      border: 1px solid #ccc;
      margin-top: 5px;
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

    .faq-section {
      margin-top: 40px;
    }

    .faq-category {
      font-size: 20px;
      font-weight: bold;
      color: #0D47A1;
      margin-bottom: 10px;
      margin-top: 30px;
      border-bottom: 2px solid #ddd;
      padding-bottom: 5px;
    }

    .faq-box {
      background: white;
      padding: 20px;
      border-radius: 6px;
      margin-bottom: 12px;
      box-shadow: 0 0 6px rgba(0,0,0,0.05);
    }

    .faq-box .question {
      font-weight: 600;
      margin-bottom: 5px;
      color: #D32F2F;
    }

    .faq-box .answer {
      color: #444;
    }

    .faq-box .meta {
      font-size: 12px;
      color: #777;
      margin-top: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .badge {
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 11px;
      color: white;
    }

    .badge.public { background: #2E7D32; }
    .badge.private { background: #C62828; }

    .actions a {
      margin-left: 10px;
      text-decoration: none;
      color: #0D47A1;
      font-weight: 500;
    }

    .actions a:hover {
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
      <a href="admin_dashboard.php">📋 Dashboard</a>
    </div>
  </div>

  <div class="container">

    <h2>❓ Manage FAQs</h2>

    <?php if (!empty($message)): ?>
      <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="post" action="admin_faq.php">
      <input type="hidden" name="faq_id" value="<?php echo $edit['faq_id'] ?? ''; ?>">

      <label>Question</label>
      <textarea name="question" required><?php echo htmlspecialchars($edit['question'] ?? ''); ?></textarea>

      <label>Answer</label>
      <textarea name="answer" required><?php echo htmlspecialchars($edit['answer'] ?? ''); ?></textarea>

      <label>Category</label>
      <input type="text" name="category" value="<?php echo htmlspecialchars($edit['category'] ?? 'General'); ?>">

      <label>Visibility</label>
      <select name="visibility">
        <option value="public" <?php if (($edit['visibility'] ?? '') === 'public') echo 'selected'; ?>>Public</option>
        <option value="private" <?php if (($edit['visibility'] ?? '') === 'private') echo 'selected'; ?>>Private</option>
      </select>

      <input type="submit" name="save" value="<?php echo $edit ? 'Update' : 'Add'; ?>">
    </form>

    <div class="faq-section">
      <h2>📋 All FAQs</h2>

      <?php
      $grouped = [];
      while ($row = $faqs->fetch_assoc()) {
        $grouped[$row['category']][] = $row;
      }

      foreach ($grouped as $category => $items):
      ?>
        <div class="faq-category"><?php echo htmlspecialchars($category); ?></div>
        <?php foreach ($items as $row): ?>
          <div class="faq-box">
            <div class="question">Q: <?php echo htmlspecialchars($row['question']); ?></div>
            <div class="answer">A: <?php echo nl2br(htmlspecialchars($row['answer'])); ?></div>
            <div class="meta">
              <span class="badge <?php echo $row['visibility']; ?>"><?php echo ucfirst($row['visibility']); ?></span>
              <span class="actions">
                <a href="admin_faq.php?edit=<?php echo $row['faq_id']; ?>">✏️ Edit</a>
                <a href="admin_faq.php?delete=<?php echo $row['faq_id']; ?>" onclick="return confirm('Delete this FAQ?');">🗑️ Delete</a>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>

  </div>
</body>
</html>
