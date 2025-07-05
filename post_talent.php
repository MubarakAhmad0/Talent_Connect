<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Post a Talent - MMU Talent Connect</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Rubik', sans-serif;
      background: #f5f8fb;
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
      max-width: 800px;
      margin: 40px auto;
      padding: 0 20px;
    }

    h2 {
      color: #D32F2F;
      margin-bottom: 25px;
    }

    form {
      background: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: 600;
    }

    input[type="text"],
    textarea,
    select {
      width: 100%;
      padding: 10px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-top: 5px;
    }

    input[type="file"] {
      margin-top: 10px;
    }

    button {
      margin-top: 20px;
      padding: 12px 20px;
      background: #0D47A1;
      color: white;
      font-weight: 600;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    button:hover {
      background: #002171;
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
    <h2>🎨 Share Your Talent</h2>

    <form action="submit_talent.php" method="POST" enctype="multipart/form-data">
      <label>Title</label>
      <input type="text" name="title" required>

      <label>Description</label>
      <textarea name="description" rows="4" required></textarea>

      <label>Visibility</label>
      <select name="visibility">
        <option value="public">Public</option>
        <option value="private">Private</option>
      </select>

      <label>Upload Files (images, pdfs, etc.)</label>
      <input type="file" name="talent_files[]" multiple required>

      <button type="submit">📤 Post Talent</button>
    </form>
  </div>

</body>
</html>

