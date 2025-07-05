<?php
session_start();
require 'db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $is_resource = isset($_POST['is_resource']) ? 1 : 0;
    $resource_link = trim($_POST['resource_link'] ?? '');
    $attachment_path = NULL;

    // Handle file upload for attachments
    if ($is_resource && isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/forum_attachments/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = basename($_FILES["attachment"]["name"]);
        $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $uniqueFilename = time() . "_" . uniqid() . "." . $fileExtension; // Prevent overwriting
        $targetPath = $uploadDir . $uniqueFilename;

        // Basic file type and size validation
        $allowedTypes = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'zip'];
        if (in_array($fileExtension, $allowedTypes) && $_FILES["attachment"]["size"] < 5 * 1024 * 1024) { // 5MB limit
            if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $targetPath)) {
                $attachment_path = $targetPath;
            } else {
                $error = "Failed to upload attachment.";
            }
        } else {
            $error = "Invalid file type or size. Allowed: PDF, DOC, DOCX, TXT, Images, ZIP (max 5MB).";
        }
    }

    if (empty($title) || empty($content)) {
        $error = "Title and content cannot be empty.";
    }

    if ($is_resource && empty($resource_link) && empty($attachment_path)) {
        $error = "Resource posts require either a link or an attachment.";
    }

    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO forum_posts (user_id, title, content, category, is_resource, resource_link, attachment_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiss", $user_id, $title, $content, $category, $is_resource, $resource_link, $attachment_path);

        if ($stmt->execute()) {
            $message = "Your post has been created successfully!";
            header("Location: forum.php"); // Redirect back to forum after successful post
            exit;
        } else {
            $error = "Error creating post: " . $conn->error;
        }
    }
}

// Fetch existing categories for the dropdown
$stmt_categories = $conn->prepare("SELECT DISTINCT category FROM forum_posts ORDER BY category");
$stmt_categories->execute();
$result_categories = $stmt_categories->get_result();
$existing_categories = [];
while ($row = $result_categories->fetch_assoc()) {
    if (!empty($row['category'])) {
        $existing_categories[] = $row['category'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Post - MMU Talent Connect</title>
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
            max-width: 700px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h2 {
            color: #D32F2F;
            margin-bottom: 20px;
        }

        .form-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            width: calc(100% - 22px); /* Account for padding and border */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Rubik', sans-serif;
            font-size: 1em;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.2); /* Make checkbox slightly larger */
        }

        .resource-options {
            border: 1px dashed #ccc;
            padding: 15px;
            margin-top: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .resource-options input[type="text"],
        .resource-options input[type="file"] {
            width: calc(100% - 22px);
            margin-top: 10px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .button-group button {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .button-group .submit-btn {
            background: #0D47A1;
            color: white;
        }

        .button-group .submit-btn:hover {
            background: #002171;
        }

        .button-group .cancel-btn {
            background: #ccc;
            color: #555;
        }

        .button-group .cancel-btn:hover {
            background: #bbb;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isResourceCheckbox = document.getElementById('is_resource');
            const resourceOptions = document.getElementById('resource_options');

            function toggleResourceOptions() {
                if (isResourceCheckbox.checked) {
                    resourceOptions.style.display = 'block';
                } else {
                    resourceOptions.style.display = 'none';
                    // Clear fields when toggled off to avoid submitting unwanted data
                    document.getElementById('resource_link').value = '';
                    if (document.getElementById('attachment')) {
                        document.getElementById('attachment').value = '';
                    }
                }
            }

            isResourceCheckbox.addEventListener('change', toggleResourceOptions);

            // Initial call to set visibility based on page load (e.g., if reloaded with error)
            toggleResourceOptions();
        });
    </script>
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
        <h2>Create New Forum Post</h2>
        

        <?php if (!empty($message)): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-section">
            <form method="post" action="add_forum_post.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Post Title:</label>
                    <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="content">Content:</label>
                    <textarea id="content" name="content" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="category">Category:</label>
                    <input list="categories" id="category" name="category" value="<?php echo htmlspecialchars($_POST['category'] ?? 'General'); ?>" placeholder="e.g., Tech, Art, Events">
                    <datalist id="categories">
                        <?php foreach ($existing_categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="is_resource" name="is_resource" <?php echo isset($_POST['is_resource']) ? 'checked' : ''; ?>>
                    <label for="is_resource">This post is for sharing a resource</label>
                </div>

                <div id="resource_options" class="resource-options" style="display: none;">
                    <p>Enter a link or upload a file:</p>
                    <div class="form-group">
                        <label for="resource_link">Resource Link (URL):</label>
                        <input type="text" id="resource_link" name="resource_link" placeholder="e.g., https://example.com/useful-doc" value="<?php echo htmlspecialchars($_POST['resource_link'] ?? ''); ?>">
                    </div>
                    <p style="text-align: center; margin: 10px 0;">- OR -</p>
                    <div class="form-group">
                        <label for="attachment">Upload File (Max 5MB):</label>
                        <input type="file" id="attachment" name="attachment">
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="submit-btn">Create Post</button>
                    <button type="button" class="cancel-btn" onclick="window.location.href='forum.php';">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>