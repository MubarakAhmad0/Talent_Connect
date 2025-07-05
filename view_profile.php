<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$view_user_id = $_GET['user_id'] ?? null;

if (!$view_user_id) {
    header("Location: catalogue.php"); // Redirect back if no user_id is provided
    exit;
}

// Fetch user and profile data for the requested user
$stmt = $conn->prepare("SELECT u.name, u.email, p.* FROM users u JOIN profiles p ON u.user_id = p.user_id WHERE u.user_id = ? AND p.visibility = 'public'");
$stmt->bind_param("i", $view_user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile_data = $result->fetch_assoc();

if (!$profile_data) {
    echo "<p style='text-align: center; margin-top: 50px;'>Profile not found or is private.</p>";
    exit;
}

?>

<!DOCTYPE html>
<html>

<head>
    <title><?php echo htmlspecialchars($profile_data['name']); ?>'s Profile - MMU Talent Connect</title>
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

        .section label {
            font-weight: 600;
        }

        .info {
            margin: 10px 0;
        }

        .resume a {
            color: #0D47A1;
            font-weight: 500;
            text-decoration: none;
        }

        .skills span {
            display: inline-block;
            background: #e3f2fd;
            color: #0D47A1;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            margin: 5px 5px 0 0;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            background: #0D47A1;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: background 0.2s ease;
        }

        .back-button:hover {
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
            <a href="profile.php">👤 My Dashboard</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>

    <div class="container">
        <a href="catalogue.php" class="back-button">← Back to E-Catalogue</a>
        <h2>👤 <?php echo htmlspecialchars($profile_data['name']); ?>'s Profile</h2>

        <div class="section">
            <h3>About Me</h3>
            <img class="profile-pic" src="<?php echo htmlspecialchars($profile_data['profile_photo'] ?: 'default-avatar.png'); ?>" alt="Profile Picture">
            <p class="info"><strong>Name:</strong> <?php echo htmlspecialchars($profile_data['name']); ?></p>
            <p class="info"><strong>Email:</strong> <?php echo htmlspecialchars($profile_data['email']); ?></p>
            <p class="info"><strong>Quote:</strong> "<?php echo htmlspecialchars($profile_data['quote']); ?>"</p>
            <p class="info"><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($profile_data['bio'])); ?></p>
            <?php if (!empty($profile_data['linkedin_url'])): ?>
                <p class="info"><strong>LinkedIn:</strong> <a href="<?php echo htmlspecialchars($profile_data['linkedin_url']); ?>" target="_blank">View Profile</a></p>
            <?php endif; ?>
        </div>

        <div class="section">
            <h3>Resume & Skills</h3>
            <div class="resume">
                <p><strong>Resume:</strong>
                    <?php if (!empty($profile_data['resume_path'])): ?>
                        <a href="<?php echo htmlspecialchars($profile_data['resume_path']); ?>" download>📄 Download Resume</a>
                    <?php else: ?>
                        <em>No resume uploaded.</em>
                    <?php endif; ?>
                </p>
            </div>
            <div class="skills">
                <strong>Skills:</strong><br>
                <?php
                $skills = explode(",", $profile_data['skills']);
                foreach ($skills as $skill) {
                    $skill = trim($skill);
                    if (!empty($skill)) {
                        echo "<span>" . htmlspecialchars($skill) . "</span>";
                    }
                }
                ?>
            </div>
        </div>

        <div class="section">
            <h3>📁 Talent Posts</h3>
            <?php
            $talent_stmt = $conn->prepare("SELECT * FROM talents WHERE user_id = ? AND visibility = 'public' ORDER BY created_at DESC");
            $talent_stmt->bind_param("i", $view_user_id);
            $talent_stmt->execute();
            $talent_result = $talent_stmt->get_result();

            if ($talent_result->num_rows === 0): ?>
                <p><em>No public talents posted by this user.</em></p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <?php while ($talent = $talent_result->fetch_assoc()): ?>
                        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.05);">
                            <h4 style="margin: 0 0 10px;"><?php echo htmlspecialchars($talent['title']); ?></h4>
                            <small style="color: #888;">📅 <?php echo date("F j, Y, g:i a", strtotime($talent['created_at'])); ?></small>
                            <p style="margin: 10px 0;"><?php echo nl2br(htmlspecialchars($talent['description'])); ?></p>

                            <?php
                            $file_stmt = $conn->prepare("SELECT file_path FROM talent_files WHERE talent_id = ?");
                            $file_stmt->bind_param("i", $talent['id']);
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
                                                <a href="<?php echo $path; ?>" target="_blank">
                                                    <img src="<?php echo $path; ?>" alt="Image" style="max-width: 150px; max-height: 150px; border:1px solid #ccc; border-radius:6px;">
                                                </a>

                                            <?php else: ?>
                                                <a href="<?php echo $path; ?>" download style="display: inline-block; background: #e3f2fd; padding: 8px 12px; border-radius: 5px; color: #0D47A1; text-decoration: none;">
                                                    📄 <?php echo basename($path); ?>
                                                </a>
                                        <?php endif;
                                        endwhile; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>



</body>

</html>