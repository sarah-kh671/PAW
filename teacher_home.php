<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

require "db.php";

$teacher_id = $_SESSION['teacher_id'];

// Get all courses taught by this teacher
$stmt = $pdo->prepare("SELECT * FROM courses");
$stmt->execute();
$courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<title>Teacher Home</title>
<style>
body { font-family: Arial; background:#f0f4ff; padding:20px; }
.card { background:#fff; padding:15px; border-radius:10px; margin-bottom:15px; }
a.button { background:#2563eb; color:white; padding:8px 12px; border-radius:6px; text-decoration:none; }
</style>
</head>
<body>

<h1>Welcome, <?php echo $_SESSION['username']; ?> 👋</h1>
<h3>Your Courses</h3>

<?php foreach ($courses as $c): ?>

<div class="card">
    <h2><?php echo $c['course_name']; ?></h2>

    <a class="button" href="teacher_session.php?course_id=<?php echo $c['id']; ?>">
        Open Session & Mark Attendance
    </a>

    <a class="button" href="teacher_summary.php?course_id=<?php echo $c['id']; ?>">
        View Attendance Summary
    </a>
</div>

<?php endforeach; ?>

<a href="logout.php">Logout</a>

</body>
</html>
