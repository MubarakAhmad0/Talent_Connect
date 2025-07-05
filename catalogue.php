<?php
session_start();
require 'db_connect.php';

// Redirect if not logged in or not a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit;
}

$search_query = $_GET['search'] ?? '';
$filter_skill = $_GET['skill'] ?? '';

// Base query to fetch public student profiles
$sql = "SELECT u.user_id, u.name, p.profile_photo, p.bio, p.skills 
        FROM users u 
        JOIN profiles p ON u.user_id = p.user_id 
        WHERE u.user_type = 'student' AND p.visibility = 'public'";

$params = [];
$types = "";

// Add search condition
if (!empty($search_query)) {
    $sql .= " AND (u.name LIKE ? OR p.bio LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $types .= "ss";
}

// Add skill filter condition
if (!empty($filter_skill)) {
    $sql .= " AND FIND_IN_SET(?, REPLACE(LOWER(p.skills), ' ', ''))";
    $params[] = strtolower(trim($filter_skill));
    $types .= "s";
}


$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$talents = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all unique skills for filter dropdown
$skill_stmt = $conn->prepare("SELECT DISTINCT skill_name FROM (SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(skills, ',', numbers.n), ',', -1)) AS skill_name FROM profiles JOIN (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10) numbers ON CHAR_LENGTH(skills) - CHAR_LENGTH(REPLACE(skills, ',', '')) >= numbers.n - 1 WHERE skills IS NOT NULL AND skills != '') AS all_skills ORDER BY skill_name");
$skill_stmt->execute();
$all_skills_result = $skill_stmt->get_result();
$unique_skills = [];
while ($row = $all_skills_result->fetch_assoc()) {
    if (!empty($row['skill_name'])) {
        $unique_skills[] = $row['skill_name'];
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>E-Catalogue - MMU Talent Connect</title>
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
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h2 {
            color: #D32F2F;
            margin-bottom: 20px;
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

        .talent-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .talent-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
        }

        .talent-card .profile-pic {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #0D47A1;
            margin-bottom: 15px;
        }

        .talent-card h3 {
            margin: 0 0 10px 0;
            color: #0D47A1;
        }

        .talent-card .bio-snippet {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
            height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .talent-card .skills {
            margin-bottom: 15px;
        }

        .talent-card .skills span {
            display: inline-block;
            background: #e3f2fd;
            color: #0D47A1;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            margin: 3px;
        }

        .talent-card .view-profile-btn {
            margin-top: auto;
            background: #D32F2F;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background 0.2s ease;
        }

        .talent-card .view-profile-btn:hover {
            background: #B71C1C;
        }

        .no-results {
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
        <h2>📚 E-Catalogue of Talents</h2>

        <form class="filter-search-bar" method="GET" action="catalogue.php">
            <input type="text" name="search" placeholder="Search by name or bio..." value="<?php echo htmlspecialchars($search_query); ?>">
            <select name="skill">
                <option value="">All Categories (Skills)</option>
                <?php foreach ($unique_skills as $skill): ?>
                    <option value="<?php echo trim(htmlspecialchars($skill)); ?>" <?php echo ($filter_skill === trim($skill)) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($skill); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Search / Filter</button>
            <?php if (!empty($search_query) || !empty($filter_skill)): ?>
                <button type="button" onclick="window.location.href='catalogue.php'">Clear Filters</button>
            <?php endif; ?>
        </form>

    </div>

    <div class="talent-grid">
        <?php if (!empty($talents)): ?>
            <?php foreach ($talents as $talent): ?>
                <div class="talent-card">
                    <img class="profile-pic" src="<?php echo htmlspecialchars($talent['profile_photo'] ?: 'default-avatar.png'); ?>" alt="Profile Picture of <?php echo htmlspecialchars($talent['name']); ?>">
                    <h3><?php echo htmlspecialchars($talent['name']); ?></h3>
                    <p class="bio-snippet"><?php echo nl2br(htmlspecialchars($talent['bio'] ?: 'No bio available.')); ?></p>
                    <div class="skills">
                        <?php
                        $student_skills = explode(",", $talent['skills']);
                        foreach ($student_skills as $skill) {
                            $skill = trim($skill);
                            if (!empty($skill)) {
                                echo "<span>" . htmlspecialchars($skill) . "</span>";
                            }
                        }
                        ?>
                    </div>
                    <a href="view_profile.php?user_id=<?php echo $talent['user_id']; ?>" class="view-profile-btn">View Full Profile</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-results">No talents found matching your criteria.</p>
        <?php endif; ?>
    </div>
    </div>

</body>

</html>