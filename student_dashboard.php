<?php
session_start();
require 'db.php'; // PDO

// Check login
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student info
$stmt = $pdo->prepare("SELECT first_name, last_name FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "Student not found.";
    exit();
}

$full_name = $student['first_name'] . " " . $student['last_name'];

// Get student courses
$stmt2 = $pdo->prepare("
    SELECT c.id, c.course_name, t.full_name AS teacher_name
    FROM student_courses sc
    JOIN courses c ON sc.course_id = c.id
    LEFT JOIN teachers t ON c.teacher_id = t.id
    WHERE sc.student_id = ?
");
$stmt2->execute([$student_id]);
$courses = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #eef3ff;
    margin: 0;
    padding: 0;
}

/* NAVBAR */
nav {
    background: #2563eb;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
nav h1 {
    color: #fff;
    margin: 0;
    font-size: 20px;
}
nav a {
    color: #fff;
    text-decoration: none;
    padding: 8px 15px;
    border-radius: 6px;
    background: #1d4ed8;
    font-weight: bold;
}
nav a:hover { filter: brightness(0.9); }

/* PAGE CONTAINER */
.container {
    max-width: 900px;
    margin: 40px auto;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 0 10px #0002;
}

/* COURSE CARDS */
.course-card {
    background: #f9faff;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.course-title {
    font-size: 18px;
    color: #2563eb;
    margin: 0;
    font-weight: bold;
}
.teacher {
    color: #555;
    margin: 5px 0 10px;
}
.btn {
    display: inline-block;
    padding: 8px 15px;
    background: #2563eb;
    color: white;
    text-decoration: none;
    border-radius: 6px;
}
.btn:hover { filter: brightness(0.9); }
</style>
</head>

<body>

<nav>
    <h1>Student Dashboard</h1>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($full_name); ?> 👋</h2>
    <p>Your Student ID: <strong><?php echo htmlspecialchars($student_id); ?></strong></p>

    <h3 style="margin-top:30px;">My Courses</h3>

    <?php if ($courses): ?>
        <?php foreach ($courses as $course): ?>
            <div class="course-card">
                <p class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></p>
                <p class="teacher">Teacher: <?php echo htmlspecialchars($course['teacher_name']); ?></p>
                <a class="btn" href="student_attendance.php?course_id=<?php echo $course['id']; ?>">View Attendance</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No courses found.</p>
    <?php endif; ?>

</div>

</body>
</html>
