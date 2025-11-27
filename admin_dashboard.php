<?php
session_start();
require 'db.php'; // PDO

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; padding: 0; }
nav { background-color: #2563eb; padding: 12px; }
nav a { color: #fff; text-decoration: none; margin-right: 15px; font-weight: bold; }
h2,h3 { color: #2563eb; margin-left: 20px; }
ul { list-style: none; padding: 0; margin-left: 20px; }
li { background: #fff; padding: 10px; margin-bottom: 5px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
a { color: #2563eb; text-decoration: none; }
a:hover { opacity: 0.9; }
</style>
</head>
<body>
<nav>
    <a href="logout.php">Logout</a>
    <a href="admin_statistics.php">Statistics</a>
    <a href="admin_students.php">Manage Students</a>
</nav>

<h2>Welcome Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
<h3>Quick Links</h3>
<ul>
    <li><a href="admin_statistics.php">View Statistics</a></li>
    <li><a href="admin_students.php">Manage Students</a></li>
</ul>

</body>
</html>

