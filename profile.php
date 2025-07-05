<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
  $uploadDir = "uploads/profile_photos/";
  $filename = basename($_FILES["profile_photo"]["name"]);
  $targetPath = $uploadDir . time() . "_" . $filename;
  $fileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

  if (in_array($fileType, ['jpg', 'jpeg', 'png']) && $_FILES["profile_photo"]["size"] < 2 * 1024 * 1024) {
    if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $targetPath)) {
      $stmt = $conn->prepare("UPDATE profiles SET profile_photo=? WHERE user_id=?");
      $stmt->bind_param("si", $targetPath, $user_id);
      $stmt->execute();
    }
  }
}

// Get profile info
$stmt = $conn->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
  // Create an empty profile if none exists
  $stmt = $conn->prepare("INSERT INTO profiles (user_id) VALUES (?)");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();

  // Reload the new profile
  $stmt = $conn->prepare("SELECT p.*, u.name FROM profiles p JOIN users u ON p.user_id = u.user_id WHERE p.user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_assoc();
}

?>

<!DOCTYPE html>
<html>

<head>
  <title>My Dashboard - MMU Talent Connect</title>
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
      margin-left: 20px;
      font-weight: 500;
    }

    .container {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
    }

    h2 {
      color: #D32F2F;
      margin-bottom: 20px;
    }

    .section {
      background: white;
      padding: 25px;
      border-radius: 8px;
      margin-bottom: 30px;
      box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
    }

    .profile-pic {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #0D47A1;
    }

    .info {
      margin: 10px 0;
    }

    .resume a,
    .talent-files a {
      color: #0D47A1;
      text-decoration: none;
      font-weight: 500;
    }

    .skills span,
    .talent-visibility {
      display: inline-block;
      background: #e3f2fd;
      color: #0D47A1;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 13px;
      margin: 5px 5px 0 0;
    }

    .btn-edit {
      float: right;
      font-size: 14px;
      background: #0D47A1;
      color: white;
      padding: 6px 12px;
      border-radius: 4px;
      text-decoration: none;
    }

    .btn-edit:hover {
      background: #002171;
    }

    .quick-access {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin: 15px 0 25px 0;
    }

    .quick-access a {
      background: #0D47A1;
      color: white;
      padding: 10px 16px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 500;
    }

    form input,
    form textarea,
    form select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
    }

    .talent-card {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
    }

    .talent-card h4 {
      margin: 0 0 8px 0;
      color: #0D47A1;
    }

    .talent-card p {
      margin: 5px 0;
    }

    .talent-files a {
      display: inline-block;
      margin-right: 10px;
      font-size: 13px;
    }

    .talent-actions a {
      margin-right: 10px;
      color: #D32F2F;
      font-size: 14px;
      text-decoration: none;
    }

    .talent-actions a:hover {
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
      <a href="logout.php">🚪 Logout</a>
    </div>
  </div>

  <div class="container">

    <h2>👤 My Dashboard</h2>

    <div class="quick-access">
      <a href="edit_profile.php">✏️ Edit Profile</a>
      <a href="change_password.php">🔑 Change Password</a>
      <a href="feedback.php">💬 Submit Feedback</a>
      <a href="user_manual.php">📘 User Manual</a>
      <a href="catalogue.php">📚 E-Catalogue</a>
      <a href="forum.php">🗣️ Forums</a>
      <a href="faq.php">👀 FAQ</a>
      <a href="view_announcements.php">📢 Announcements</a>
    </div>

    <!-- Profile Info -->
    <div class="section">
      <a href="edit_profile.php#photo" class="btn-edit">✏️ Edit</a>
      <h3>About Me</h3>
      <img class="profile-pic" src="<?php echo $data['profile_photo'] ?: 'default-avatar.png'; ?>" alt="Profile Picture">
      <p class="info"><strong>Name:</strong> <?php echo htmlspecialchars($data['name']); ?></p>
      <p class="info"><strong>Quote:</strong> "<?php echo htmlspecialchars($data['quote']); ?>"</p>
      <p class="info"><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($data['bio'])); ?></p>
    </div>

    <!-- Resume and Skills -->
    <div class="section">
      <h3>Resume & Skills</h3>
      <p><strong>Resume:</strong>
        <?php if (!empty($data['resume_path'])): ?>
          <a href="<?php echo htmlspecialchars($data['resume_path']); ?>" download>📄 Download</a>
        <?php else: ?>
          <em>No resume uploaded.</em>
        <?php endif; ?>
      </p>
      <div class="skills">
        <strong>Skills:</strong><br>
        <?php
        $skills = explode(",", $data['skills']);
        foreach ($skills as $skill) {
          echo "<span>" . htmlspecialchars(trim($skill)) . "</span>";
        }
        ?>
      </div>
    </div>

    <div class="section">
      <h3 style="margin-bottom: 20px;">🎨 Post a New Talent</h3>
      <form action="process_post_talent.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
        <div>
          <label for="title">Title</label>
          <input type="text" id="title" name="title" required style="padding:10px; border:1px solid #ccc; border-radius:6px;">
        </div>

        <div>
          <label for="description">Description</label>
          <textarea id="description" name="description" rows="4" required style="padding:10px; border:1px solid #ccc; border-radius:6px;"></textarea>
        </div>

        <div>
          <label for="visibility">Visibility</label>
          <select id="visibility" name="visibility" style="padding:10px; border:1px solid #ccc; border-radius:6px;">
            <option value="public">Public</option>
            <option value="private">Private</option>
          </select>
        </div>

        <div>
          <label for="files">Upload Files</label>
          <input type="file" id="files" name="files[]" multiple style="padding:8px; border:1px solid #ccc; border-radius:6px; background:#fafafa;">
        </div>

        <button type="submit" style="
      padding: 12px 20px;
      background: #0D47A1;
      color: white;
      font-weight: 500;
      font-size: 15px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.2s ease;">
          📤 Post Talent
        </button>
      </form>
    </div>

    <div class="section">
      <h3>📁 My Talent Posts</h3>
      <?php
      $user_id = $_SESSION['user_id'];
      $stmt = $conn->prepare("SELECT * FROM talents WHERE user_id = ? ORDER BY created_at DESC");
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows === 0): ?>
        <p><em>You haven't posted any talents yet.</em></p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 20px;">
          <?php while ($row = $result->fetch_assoc()): ?>
            <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.05);">
              <h4 style="margin: 0 0 10px;"><?php echo htmlspecialchars($row['title']); ?></h4>
              <small style="color: #888;">📅 <?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?></small>
              <p style="margin: 10px 0;"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
              <p><strong>Visibility:</strong> <?php echo ucfirst($row['visibility']); ?></p>

              <?php
              $file_stmt = $conn->prepare("SELECT file_path FROM talent_files WHERE talent_id = ?");
              $file_stmt->bind_param("i", $row['id']);
              $file_stmt->execute();
              $files_result = $file_stmt->get_result();
              if ($files_result->num_rows > 0):
              ?>
                <div style="margin-top: 15px;">
                  <strong>Attachments:</strong><br>
                  <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 10px;">
                    <?php while ($file = $files_result->fetch_assoc()):
                      $path = htmlspecialchars($file['file_path']);
                      $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                      if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                        <img src="<?php echo $path; ?>" alt="Uploaded Image" style="max-width: 150px; max-height: 150px; border:1px solid #ccc; border-radius:6px;">
                      <?php else: ?>
                        <a href="<?php echo $path; ?>" download style="display: inline-block; background: #e3f2fd; padding: 8px 12px; border-radius: 5px; color: #0D47A1; text-decoration: none;">
                          📄 <?php echo basename($path); ?>
                        </a>
                    <?php endif;
                    endwhile; ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- Edit/Delete buttons -->
              <div style="margin-top: 15px;">
                <a href="edit_talent.php?id=<?php echo $row['id']; ?>" style="color: #0D47A1; margin-right: 15px;">✏️ Edit</a>
                <a href="delete_talent.php?id=<?php echo $row['id']; ?>" style="color: #D32F2F;" onclick="return confirm('Are you sure you want to delete this talent?');">🗑️ Delete</a>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </div>



</body>

</html>