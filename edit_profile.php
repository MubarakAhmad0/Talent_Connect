<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'] ?? '';
  $quote = $_POST['quote'] ?? '';
  $bio = $_POST['bio'] ?? '';
  $linkedin = $_POST['linkedin_url'] ?? '';
  $skills = $_POST['skills'] ?? '';
  $availability = $_POST['availability'] ?? '';
  $theme = $_POST['theme'] ?? '';
  $visibility = $_POST['visibility'] ?? '';

  // Handle resume
  $resume_path = null;
  if (!empty($_FILES['resume']['name'])) {
    $resumeFile = $_FILES['resume'];
    $ext = strtolower(pathinfo($resumeFile['name'], PATHINFO_EXTENSION));
    if ($ext === 'pdf') {
      $resume_path = "uploads/resumes/" . time() . "_" . basename($resumeFile['name']);
      move_uploaded_file($resumeFile['tmp_name'], $resume_path);
    }
  }

  // Handle photo
  $photo_path = null;
  if (!empty($_FILES['profile_photo']['name'])) {
    $photoFile = $_FILES['profile_photo'];
    $ext = strtolower(pathinfo($photoFile['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
      $photo_path = "uploads/profile_photos/" . time() . "_" . basename($photoFile['name']);
      move_uploaded_file($photoFile['tmp_name'], $photo_path);
    }
  }

  // Build query
  $query = "UPDATE profiles SET name=?, quote=?, bio=?, linkedin_url=?, skills=?, availability_status=?, profile_theme=?, visibility=?";
  $params = [$name, $quote, $bio, $linkedin, $skills, $availability, $theme, $visibility];
  $types = "ssssssss";



  if ($resume_path) {
    $query .= ", resume_path=?";
    $params[] = $resume_path;
    $types .= "s";
  }

  if ($photo_path) {
    $query .= ", profile_photo=?";
    $params[] = $photo_path;
    $types .= "s";
  }

  $query .= " WHERE user_id=?";
  $params[] = $user_id;
  $types .= "i";

  $stmt = $conn->prepare($query);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();

  $message = "Profile updated successfully!";
}

// Load current profile
$stmt = $conn->prepare("SELECT * FROM profiles WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// Create a notification
$stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
$message = "Your profile was updated.";
$link = "profile.php"; // or NULL
$stmt->bind_param("iss", $user_id, $message, $link);
$stmt->execute();

?>

<!DOCTYPE html>
<html>

<head>
  <title>Edit Full Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Rubik', sans-serif;
      background: #f7f9fc;
      margin: 0;
      color: #222;
    }

    .nav-bar {
      background: #0D47A1;
      color: white;
      padding: 15px 30px;
      display: flex;
      align-items: center;
      justify-content: space-between;
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

    h1 {
      color: #D32F2F;
      margin-bottom: 30px;
    }

    form {
      background: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: 600;
    }

    input[type="text"],
    input[type="url"],
    input[type="file"],
    select,
    textarea {
      width: 100%;
      padding: 10px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-top: 5px;
    }

    textarea {
      resize: vertical;
      min-height: 100px;
    }

    input[type="submit"] {
      background: #0D47A1;
      color: white;
      font-weight: 600;
      padding: 12px 20px;
      margin-top: 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    input[type="submit"]:hover {
      background: #002171;
    }

    .success {
      background: #d0f0d4;
      padding: 10px;
      color: #1B5E20;
      border-radius: 6px;
      margin-bottom: 20px;
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
    <h1>Edit My Profile</h1>

    <?php if ($message): ?>
      <div class="success"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <label>Full Name</label>
      <input type="text" name="name" value="<?php echo htmlspecialchars($data['name'] ?? ''); ?>" required>

      <label>Quote</label>
      <input type="text" name="quote" value="<?php echo htmlspecialchars($data['quote'] ?? ''); ?>">

      <label>Bio</label>
      <textarea name="bio"><?php echo htmlspecialchars($data['bio'] ?? ''); ?></textarea>

      <label>LinkedIn URL</label>
      <input type="url" name="linkedin_url" value="<?php echo htmlspecialchars($data['linkedin_url'] ?? ''); ?>">

      <label>Skills (comma-separated)</label>
      <input type="text" name="skills" value="<?php echo htmlspecialchars($data['skills'] ?? ''); ?>">

      <label>Upload Resume (PDF only)</label>
      <input type="file" name="resume" accept="application/pdf">

      <label>Upload Profile Photo (JPG/PNG)</label>
      <input type="file" name="profile_photo" accept="image/*">

      <label>Availability Status</label>
      <select name="availability">
        <option value="Available" <?php if ($data['availability_status'] === 'Available') echo 'selected'; ?>>Available</option>
        <option value="Not Available" <?php if ($data['availability_status'] === 'Not Available') echo 'selected'; ?>>Not Available</option>
      </select>

      <label>Profile Theme</label>
      <select name="theme">
        <option value="Default" <?php if ($data['profile_theme'] === 'Default') echo 'selected'; ?>>Default</option>
        <option value="Dark" <?php if ($data['profile_theme'] === 'Dark') echo 'selected'; ?>>Dark</option>
        <option value="Red-Blue" <?php if ($data['profile_theme'] === 'Red-Blue') echo 'selected'; ?>>Red-Blue</option>
      </select>

      <label>Profile Visibility</label>
      <select name="visibility">
        <option value="public" <?php if ($data['visibility'] === 'public') echo 'selected'; ?>>Public</option>
        <option value="private" <?php if ($data['visibility'] === 'private') echo 'selected'; ?>>Private</option>
      </select>

      <input type="submit" value="Save Full Profile">
    </form>
  </div>

  <div class="footer">
    &copy; <?php echo date('Y'); ?> MMU Talent Connect
  </div>

</body>

</html>