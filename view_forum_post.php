<?php
session_start();
require 'db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$post_id = $_GET['post_id'] ?? null;

if (!$post_id) {
    header("Location: forum.php"); // Redirect back if no post_id is provided
    exit;
}

// Fetch the main forum post
$stmt_post = $conn->prepare("SELECT fp.*, u.name as username, p.profile_photo FROM forum_posts fp JOIN users u ON fp.user_id = u.user_id JOIN profiles p ON u.user_id = p.user_id WHERE fp.post_id = ?");
$stmt_post->bind_param("i", $post_id);
$stmt_post->execute();
$result_post = $stmt_post->get_result();
$post = $result_post->fetch_assoc();

if (!$post) {
    echo "<p style='text-align: center; margin-top: 50px;'>Post not found.</p>";
    exit;
}

// Handle new comment submission
$comment_message = '';
$comment_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_comment'])) {
    $comment_text = trim($_POST['comment_text'] ?? '');
    $current_user_id = $_SESSION['user_id'];

    if (empty($comment_text)) {
        $comment_error = "Comment cannot be empty.";
    } else {
        $stmt_comment = $conn->prepare("INSERT INTO forum_comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
        $stmt_comment->bind_param("iis", $post_id, $current_user_id, $comment_text);
        if ($stmt_comment->execute()) {
            $comment_message = "Your comment has been added.";
            // Refresh to show the new comment
            header("Location: view_forum_post.php?post_id=" . $post_id);
            exit;
        } else {
            $comment_error = "Error adding comment: " . $conn->error;
        }
    }
}

// Fetch comments for the post
$stmt_comments = $conn->prepare("SELECT fc.*, u.name as username, p.profile_photo FROM forum_comments fc JOIN users u ON fc.user_id = u.user_id JOIN profiles p ON u.user_id = p.user_id WHERE fc.post_id = ? ORDER BY fc.created_at ASC");
$stmt_comments->bind_param("i", $post_id);
$stmt_comments->execute();
$comments_result = $stmt_comments->get_result();
$comments = $comments_result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($post['title']); ?> - MMU Talent Connect Forum</title>
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

        .post-detail {
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .post-detail h2 {
            color: #D32F2F;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .post-meta {
            font-size: 0.9em;
            color: #777;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .post-meta img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #eee;
        }

        .post-meta a {
            color: #0D47A1;
            text-decoration: none;
            font-weight: 500;
        }

        .post-content {
            line-height: 1.8;
            margin-bottom: 20px;
            white-space: pre-wrap; /* Preserves whitespace and line breaks */
        }

        .resource-info {
            background: #e3f2fd;
            border-left: 4px solid #0D47A1;
            padding: 15px 20px;
            margin-top: 20px;
            font-size: 0.95em;
            border-radius: 5px;
        }

        .resource-info a {
            color: #0D47A1;
            text-decoration: none;
            font-weight: 500;
            word-break: break-all; /* Break long URLs */
        }

        .comments-section h3 {
            color: #0D47A1;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .comment {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.03);
            display: flex;
            gap: 15px;
        }

        .comment .avatar {
            flex-shrink: 0; /* Prevent avatar from shrinking */
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #0D47A1;
        }

        .comment-content {
            flex-grow: 1;
        }

        .comment-meta {
            font-size: 0.85em;
            color: #666;
            margin-bottom: 5px;
        }

        .comment-meta strong a {
            color: #0D47A1;
            text-decoration: none;
        }

        .comment-text {
            line-height: 1.6;
            margin-top: 5px;
        }

        .add-comment-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
        }

        .add-comment-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .add-comment-form textarea {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 100px;
            resize: vertical;
            font-family: 'Rubik', sans-serif;
            font-size: 1em;
        }

        .add-comment-form button {
            background: #0D47A1;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
            font-weight: 500;
            transition: background 0.2s ease;
        }

        .add-comment-form button:hover {
            background: #002171;
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
        <a href="forum.php" class="back-button">← Back to Forum</a>

        <div class="post-detail">
            <h2><?php echo htmlspecialchars($post['title']); ?></h2>
            <div class="post-meta">
                <img src="<?php echo htmlspecialchars($post['profile_photo'] ?: 'default-avatar.png'); ?>" alt="Avatar">
                Posted by <a href="view_profile.php?user_id=<?php echo $post['user_id']; ?>"><?php echo htmlspecialchars($post['username']); ?></a> on <?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?>
                <?php if (!empty($post['category'])): ?>
                    <span style="margin-left: 10px;">Category: <strong><?php echo htmlspecialchars($post['category']); ?></strong></span>
                <?php endif; ?>
            </div>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>

            <?php if ($post['is_resource']): ?>
                <div class="resource-info">
                    <p>This post shares a resource:</p>
                    <?php if (!empty($post['resource_link'])): ?>
                        <p><strong>Link:</strong> <a href="<?php echo htmlspecialchars($post['resource_link']); ?>" target="_blank"><?php echo htmlspecialchars($post['resource_link']); ?></a></p>
                    <?php endif; ?>
                    <?php if (!empty($post['attachment_path'])): ?>
                        <p><strong>Attachment:</strong> <a href="<?php echo htmlspecialchars($post['attachment_path']); ?>" download>Download File</a></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="comments-section">
            <h3>Comments (<?php echo count($comments); ?>)</h3>
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <img class="avatar" src="<?php echo htmlspecialchars($comment['profile_photo'] ?: 'default-avatar.png'); ?>" alt="Avatar">
                        <div class="comment-content">
                            <div class="comment-meta">
                                <strong><a href="view_profile.php?user_id=<?php echo $comment['user_id']; ?>"><?php echo htmlspecialchars($comment['username']); ?></a></strong> on <?php echo date('F j, Y, g:i a', strtotime($comment['created_at'])); ?>
                            </div>
                            <div class="comment-text">
                                <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php endif; ?>

            <div class="add-comment-form">
                <h4>Add a Comment</h4>
                <?php if (!empty($comment_message)): ?>
                    <div class="message success"><?php echo htmlspecialchars($comment_message); ?></div>
                <?php endif; ?>
                <?php if (!empty($comment_error)): ?>
                    <div class="message error"><?php echo htmlspecialchars($comment_error); ?></div>
                <?php endif; ?>
                <form method="post" action="view_forum_post.php?post_id=<?php echo $post_id; ?>">
                    <div class="form-group">
                        <label for="comment_text">Your Comment:</label>
                        <textarea id="comment_text" name="comment_text" required></textarea>
                    </div>
                    <button type="submit" name="new_comment">Post Comment</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>