<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: profile.php");
    exit;
}

$talent_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if this talent belongs to the logged-in user
$check = $conn->prepare("SELECT id FROM talents WHERE id = ? AND user_id = ?");
$check->bind_param("ii", $talent_id, $user_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 1) {
    // Delete associated files
    $stmt_files = $conn->prepare("SELECT file_path FROM talent_files WHERE talent_id = ?");
    $stmt_files->bind_param("i", $talent_id);
    $stmt_files->execute();
    $files = $stmt_files->get_result();

    while ($row = $files->fetch_assoc()) {
        if (file_exists($row['file_path'])) {
            unlink($row['file_path']);
        }
    }

    // Delete from talent_files and talents table
    $conn->query("DELETE FROM talent_files WHERE talent_id = $talent_id");
    $conn->query("DELETE FROM talents WHERE id = $talent_id");
}

header("Location: profile.php?deleted=1");
exit;
?>
