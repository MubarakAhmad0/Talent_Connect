<?php
session_start();
require 'db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$search_query = $_GET['search'] ?? '';
$filter_category = $_GET['category'] ?? '';

// Base query to fetch forum posts
$sql = "SELECT fp.*, u.name as username 
        FROM forum_posts fp 
        JOIN users u ON fp.user_id = u.user_id 
        WHERE 1=1"; // Start with a true condition to easily append WHERE clauses

$params = [];
$types = "";

// Add search condition
if (!empty($search_query)) {
    $sql .= " AND (fp.title LIKE ? OR fp.content LIKE ? OR u.name LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $types .= "sss";
}

// Add category filter condition
if (!empty($filter_category)) {
    $sql .= " AND fp.category = ?";
    $params[] = $filter_category;
    $types .= "s";
}

$sql .= " ORDER BY fp.created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all unique categories for filter dropdown
$category_stmt = $conn->prepare("SELECT DISTINCT category FROM forum_posts ORDER BY category");
$category_stmt->execute();
$all_categories_result = $category_stmt->get_result();
$unique_categories = [];
while ($row = $all_categories_result->fetch_assoc()) {
    if (!empty($row['category'])) {
        $unique_categories[] = $row['category'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forum - MMU Talent Connect</title>
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
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h2 {
            color: #D32F2F;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-add-post {
            background: #4CAF50;
            color: white;
            padding: 7px 14px;
            text-decoration: none;
            border-radius: 3.5px;
            font-weight: 500;
            font-size: 0.7em;
            transition: background 0.2s ease;
        }

        .btn-add-post:hover {
            background: #388E3C;
        }

        .filter-search-bar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-search-bar input[type="text"],
        .filter-search-bar select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            flex: 1;
            min-width: 150px;
        }

        .filter-search-bar button {
            padding: 10px 15px;
            background: #0D47A1;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .filter-search-bar button:hover {
            background: #002171;
        }

        .forum-post {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
        }

        .forum-post h3 {
            margin-top: 0;
            color: #0D47A1;
        }

        .forum-post .meta {
            font-size: 0.9em;
            color: #777;
            margin-bottom: 15px;
        }

        .forum-post .meta a {
            color: #0D47A1;
            text-decoration: none;
        }

        .forum-post .content {
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .forum-post .read-more {
            color: #D32F2F;
            text-decoration: none;
            font-weight: 500;
        }

        .forum-post .resource-info {
            background: #e3f2fd;
            border-left: 4px solid #0D47A1;
            padding: 10px 15px;
            margin-top: 15px;
            font-size: 0.9em;
        }

        .forum-post .resource-info a {
            color: #0D47A1;
            text-decoration: none;
            font-weight: 500;
        }

        .no-posts {
            text-align: center;
            color: #777;
            margin-top: 50px;
            font-size: 1.1em;
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
        <h2>Forum <a href="add_forum_post.php" class="btn-add-post">➕ New Post</a></h2>

        <div class="filter-search-bar">
            <form method="GET" action="forum.php" style="display: flex; gap: 15px; flex-wrap: wrap; width: 100%;">
                <input type="text" name="search" placeholder="Search posts by title, content, or author..." value="<?php echo htmlspecialchars($search_query); ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($unique_categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($filter_category === $category) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Search / Filter</button>
                <?php if (!empty($search_query) || !empty($filter_category)): ?>
                    <button type="button" onclick="window.location.href='forum.php'">Clear Filters</button>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="forum-post">
                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    <div class="meta">
                        Posted by <a href="view_profile.php?user_id=<?php echo $post['user_id']; ?>"><?php echo htmlspecialchars($post['username']); ?></a> on <?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?>
                        <?php if (!empty($post['category'])): ?>
                            <span style="margin-left: 10px;">Category: <strong><?php echo htmlspecialchars($post['category']); ?></strong></span>
                        <?php endif; ?>
                    </div>
                    <div class="content">
                        <?php
                            $short_content = strlen($post['content']) > 200 ? substr($post['content'], 0, 200) . '...' : $post['content'];
                            echo nl2br(htmlspecialchars($short_content));
                        ?>
                        <?php if (strlen($post['content']) > 200): ?>
                            <a href="view_forum_post.php?post_id=<?php echo $post['post_id']; ?>" class="read-more">Read More</a>
                        <?php endif; ?>
                    </div>

                    <?php if ($post['is_resource']): ?>
                        <div class="resource-info">
                            This is a resource post.
                            <?php if (!empty($post['resource_link'])): ?>
                                <p><strong>Link:</strong> <a href="<?php echo htmlspecialchars($post['resource_link']); ?>" target="_blank"><?php echo htmlspecialchars($post['resource_link']); ?></a></p>
                            <?php endif; ?>
                            <?php if (!empty($post['attachment_path'])): ?>
                                <p><strong>Attachment:</strong> <a href="<?php echo htmlspecialchars($post['attachment_path']); ?>" download>Download File</a></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <p><a href="view_forum_post.php?post_id=<?php echo $post['post_id']; ?>">View Discussion</a></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-posts">No forum posts found. Be the first to create one!</p>
        <?php endif; ?>
    </div>

</body>
</html>
