<?php
session_start();
require 'db.php'; // PDO

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get total students
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM students");
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total courses
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM courses");
$total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get attendance stats
$stmt = $pdo->query("
    SELECT 
        SUM(ar.status) AS present_count,
        COUNT(ar.id) AS total_records
    FROM attendance_records ar
");
$attendance = $stmt->fetch(PDO::FETCH_ASSOC);

$present_count = $attendance['present_count'] ?? 0;
$total_records = $attendance['total_records'] ?? 0;
$absent_count = $total_records - $present_count;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Statistics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; padding: 20px; }
nav { background-color: #2563eb; padding: 12px; }
nav a { color: #fff; text-decoration: none; margin-right: 15px; font-weight: bold; }
h2 { color: #2563eb; }
canvas { background: #fff; border-radius: 6px; padding: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-top: 20px; }
/* Medium size chart */
.chart-container { width: 500px; height: 300px; }
</style>
</head>
<body>
<nav>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="logout.php">Logout</a>
    <a href="admin_students.php">Manage Students</a>
</nav>

<h2>Statistics Overview</h2>
<p>Total Students: <?php echo $total_students; ?></p>
<p>Total Courses: <?php echo $total_courses; ?></p>

<h3>Attendance Summary</h3>
<div class="chart-container">
    <canvas id="attendanceChart"></canvas>
</div>

<script>
const ctx = document.getElementById('attendanceChart').getContext('2d');
const attendanceChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Present', 'Absent'],
        datasets: [{
            label: 'Attendance',
            data: [<?php echo $present_count; ?>, <?php echo $absent_count; ?>],
            backgroundColor: ['#2563eb', '#ff4d4f'],
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>
</body>
</html>

