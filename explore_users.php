<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch public profiles
$query = "
SELECT u.username, p.quote, p.skills, p.availability_status, p.profile_photo
FROM users u
JOIN profiles p ON u.user_id = p.user_id
WHERE p.visibility = 'public' AND u.user_type != 'admin'
ORDER BY u.username ASC
";


$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Explore Users</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Rubik', sans-serif;
      background: #f7f9fc;
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

    h1 {
      color: #D32F2F;
      margin-bottom: 25px;
    }

    #searchInput {
      width: 100%;
      padding: 12px;
      font-size: 15px;
      margin-bottom: 30px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
    }

    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: transform 0.2s ease;
    }

    .card:hover {
      transform: translateY(-4px);
    }

    .card img {
      width: 90px;
      height: 90px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #0D47A1;
      margin-bottom: 15px;
    }

    .card h2 {
      margin: 0;
      font-size: 18px;
      color: #0D47A1;
      text-align: center;
    }

    .card p {
      margin: 6px 0;
      font-size: 14px;
      text-align: center;
      color: #555;
    }

    .no-results {
      text-align: center;
      color: #777;
      margin-top: 60px;
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
      <a href="admin_dashboard.php">📋 Admin Dashboard</a>
    </div>
  </div>

  <div class="container">
    <h1>👥 Explore Public Users</h1>
    <input type="text" id="searchInput" placeholder="Search by name, skill, or status...">

    <?php if ($result->num_rows > 0): ?>
      <div class="grid" id="userGrid">
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="card">
            <img src="<?php echo htmlspecialchars($row['profile_photo'] ?: 'default.jpg'); ?>" alt="Profile Photo">
            <h2><?php echo htmlspecialchars($row['username']); ?></h2>
            <p><strong>Quote:</strong> <?php echo htmlspecialchars($row['quote']); ?></p>
            <p><strong>Skills:</strong> <?php echo htmlspecialchars($row['skills']); ?></p>
            <p><strong>Availability:</strong> <?php echo htmlspecialchars($row['availability_status']); ?></p>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <p class="no-results">No public users found.</p>
    <?php endif; ?>
  </div>

  <script>
    document.getElementById('searchInput').addEventListener('input', function () {
      const query = this.value.toLowerCase();
      const cards = document.querySelectorAll('.card');

      cards.forEach(card => {
        const text = card.innerText.toLowerCase();
        card.style.display = text.includes(query) ? 'flex' : 'none';
      });
    });
  </script>

</body>
</html>
