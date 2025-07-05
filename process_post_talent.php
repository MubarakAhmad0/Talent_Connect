<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']) ?? '';
    $visibility = $_POST['visibility'] === 'public' ? 'public' : 'private';

    // Insert into talents table
    $stmt = $conn->prepare("INSERT INTO talents (user_id, title, description, visibility) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $description, $visibility);
    $stmt->execute();
    $talent_id = $stmt->insert_id;
    $stmt->close();

    // Handle file uploads
    $uploadDir = 'uploads/talents/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['files']['name'] as $index => $filename) {
        if ($_FILES['files']['error'][$index] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['files']['tmp_name'][$index];
            $uniqueName = time() . '_' . basename($filename);
            $targetPath = $uploadDir . $uniqueName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $stmt = $conn->prepare("INSERT INTO talent_files (talent_id, file_path) VALUES (?, ?)");
                $stmt->bind_param("is", $talent_id, $targetPath);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    header("Location: profile.php?posted=1");
    exit;
}
?>
