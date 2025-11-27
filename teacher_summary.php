<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

require "db.php";

$course_id = $_GET['course_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT s.first_name, s.last_name,
           SUM(ar.status = 'present') AS presents,
           SUM(ar.status = 'absent') AS absents
    FROM students s
    JOIN student_courses sc ON s.id = sc.student_id
    LEFT JOIN attendance_records ar ON ar.student_id = s.id
    LEFT JOIN attendance_sessions ss ON ss.id = ar.session_id AND ss.course_id = sc.course_id
    WHERE sc.course_id = ?
    GROUP BY s.id
");
$stmt->execute([$course_id]);
$rows = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<title>Attendance Summary</title>
<style>
body { font-family: Arial; background:#f0f4ff; padding:20px; }
table { border-collapse: collapse; width:100%; background:#fff; }
td, th { border:1px solid #ccc; padding:8px; text-align:center; }
</style>
</head>
<body>

<h1>Attendance Summary</h1>

<table>
<tr>
    <th>Student</th>
    <th>Presents</th>
    <th>Absents</th>
</tr>

<?php foreach ($rows as $r): ?>
<tr>
    <td><?php echo $r['first_name']." ".$r['last_name']; ?></td>
    <td><?php echo $r['presents'] ?? 0; ?></td>
    <td><?php echo $r['absents'] ?? 0; ?></td>
</tr>
<?php endforeach; ?>

</table>

<br>
<a href="teacher_home.php">Back</a>

</body>
</html>
