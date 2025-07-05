<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: profile.php");
    exit;
}

$talent_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch existing post
$stmt = $conn->prepare("SELECT * FROM talents WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $talent_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: profile.php");
    exit;
}

$talent = $result->fetch_assoc();

// Fetch existing files
$file_stmt = $conn->prepare("SELECT * FROM talent_files WHERE talent_id = ?");
$file_stmt->bind_param("i", $talent_id);
$file_stmt->execute();
$files_result = $file_stmt->get_result();

// Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $visibility = $_POST['visibility'];

    $update = $conn->prepare("UPDATE talents SET title = ?, description = ?, visibility = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $update->bind_param("sssii", $title, $description, $visibility, $talent_id, $user_id);
    $update->execute();

    // Handle new file uploads
    $uploadDir = 'uploads/talents/';
    foreach ($_FILES['files']['name'] as $index => $name) {
        if ($_FILES['files']['error'][$index] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['files']['tmp_name'][$index];
            $unique_name = time() . "_" . basename($name);
            $targetPath = $uploadDir . $unique_name;
            move_uploaded_file($tmp_name, $targetPath);

            $insert = $conn->prepare("INSERT INTO talent_files (talent_id, file_path) VALUES (?, ?)");
            $insert->bind_param("is", $talent_id, $targetPath);
            $insert->execute();
        }
    }

    header("Location: profile.php?updated=1");
    exit;
}
?>
