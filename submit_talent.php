<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $title = $_POST['title'];
  $description = $_POST['description'] ?? '';
  $visibility = $_POST['visibility'] ?? 'private';

  // Insert talent post
  $stmt = $conn->prepare("INSERT INTO talents (user_id, title, description, visibility) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("isss", $user_id, $title, $description, $visibility);
  $stmt->execute();
  $talent_id = $stmt->insert_id;

  // Handle multiple file uploads
  $uploadDir = 'uploads/talents/';
  foreach ($_FILES['talent_files']['name'] as $index => $name) {
    if ($_FILES['talent_files']['error'][$index] === UPLOAD_ERR_OK) {
      $tmp_name = $_FILES['talent_files']['tmp_name'][$index];
      $unique_name = time() . "_" . basename($name);
      $targetPath = $uploadDir . $unique_name;
      move_uploaded_file($tmp_name, $targetPath);

      $stmt = $conn->prepare("INSERT INTO talent_files (talent_id, file_path) VALUES (?, ?)");
      $stmt->bind_param("is", $talent_id, $targetPath);
      $stmt->execute();
    }
  }

  header("Location: profile.php?posted=1");
  exit;
}
?>
