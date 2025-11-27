<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit;
}

require "db.php";

$course_id = $_GET['course_id'] ?? 0;

// Get students enrolled in this course
$stmt = $pdo->prepare("SELECT s.* FROM students s
                       JOIN student_courses sc ON s.id = sc.student_id
                       WHERE sc.course_id = ?");
$stmt->execute([$course_id]);
$students = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create new session
    $date = date("Y-m-d");
    $pdo->prepare("INSERT INTO attendance_sessions(course_id, session_date, is_open)
                   VALUES(?,?,1)")
        ->execute([$course_id, $date]);

    $session_id = $pdo->lastInsertId();

    // Save attendance
    foreach ($_POST['status'] as $student_id => $present) {
        $status = ($present == "1") ? "present" : "absent";
        $pdo->prepare("INSERT INTO attendance_records(session_id, student_id, status)
                       VALUES(?,?,?)")
            ->execute([$session_id, $student_id, $status]);
    }

    echo "<p style='color:green;'>Attendance Saved!</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Mark Attendance</title>
<style>
body { font-family: Arial; background:#f0f4ff; padding:20px; }
table { border-collapse: collapse; width:100%; background:#fff; }
td, th { border:1px solid #ccc; padding:8px; }
</style>
</head>
<body>

<h1>Mark Attendance</h1>

<form method="POST">
<table>
<tr><th>Student</th><th>Present?</th></tr>

<?php foreach ($students as $stu): ?>
<tr>
    <td><?php echo $stu['first_name'] . " " . $stu['last_name']; ?></td>
    <td>
        <select name="status[<?php echo $stu['id']; ?>]">
            <option value="1">Present</option>
            <option value="0">Absent</option>
        </select>
    </td>
</tr>
<?php endforeach; ?>

</table>

<br>
<button type="submit">Save Attendance</button>
</form>

<br>
<a href="teacher_home.php">Back</a>

</body>
</html>
