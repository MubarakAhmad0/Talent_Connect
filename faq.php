<?php
require 'db_connect.php';

$query = "SELECT * FROM faq WHERE visibility = 'public' ORDER BY category, created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
  <title>FAQs - MMU Talent Connect</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Rubik', sans-serif;
      background: #f7f9fb;
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
      max-width: 960px;
      margin: 40px auto;
      padding: 0 20px;
    }

    h1 {
      font-size: 28px;
      color: #D32F2F;
      margin-bottom: 30px;
      text-align: center;
    }

    .faq-category {
      font-size: 20px;
      font-weight: bold;
      color: #0D47A1;
      margin-top: 40px;
      margin-bottom: 10px;
      border-bottom: 2px solid #ddd;
      padding-bottom: 5px;
    }

    .faq-box {
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 6px rgba(0,0,0,0.05);
      margin-bottom: 15px;
    }

    .faq-box .question {
      font-weight: 600;
      margin-bottom: 8px;
      color: #D32F2F;
    }

    .faq-box .answer {
      color: #444;
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
    <h1>❓ Frequently Asked Questions</h1>

    <?php
    $grouped = [];
    while ($row = $result->fetch_assoc()) {
      $grouped[$row['category']][] = $row;
    }

    foreach ($grouped as $category => $items):
    ?>
      <div class="faq-category"><?php echo htmlspecialchars($category); ?></div>

      <?php foreach ($items as $row): ?>
        <div class="faq-box">
          <div class="question">Q: <?php echo htmlspecialchars($row['question']); ?></div>
          <div class="answer">A: <?php echo nl2br(htmlspecialchars($row['answer'])); ?></div>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div>

  <div class="footer">
    &copy; <?php echo date('Y'); ?> MMU Talent Connect
  </div>

</body>
</html>


