<?php
session_start();
require 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$course_id = $_GET['course_id'] ?? null;

if (!$course_id) {
    echo "Course not selected.";
    exit();
}

// Course info
$stmt = $pdo->prepare("SELECT course_name FROM courses WHERE id=?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();
if (!$course) {
    echo "Course not found.";
    exit();
}

// Attendance records
$stmt2 = $pdo->prepare("
    SELECT id, student_id, course_id, status, created_at, justification
    FROM attendance_records 
    WHERE student_id=? AND course_id=?
    ORDER BY created_at DESC
");
$stmt2->execute([$student_id, $course_id]);
$records = $stmt2->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Attendance - <?php echo htmlspecialchars($course['course_name']); ?></title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f3f6fc;
    margin: 0; padding: 0;
}
nav {
    background: #2563eb;
    color: #fff;
    padding: 14px;
}
nav a {
    color: #fff;
    text-decoration: none;
    font-weight: bold;
}
.container {
    max-width: 900px;
    margin: 30px auto;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
h2 {
    margin-top: 0;
    color: #2563eb;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th {
    background: #2563eb;
    color: #fff;
    padding: 10px;
}
td {
    padding: 10px;
    border-bottom: 1px solid #e5e7eb;
}
.status-present {
    color: #16a34a;
    font-weight: bold;
}
.status-absent {
    color: #dc2626;
    font-weight: bold;
}
button {
    background: #2563eb;
    color: #fff;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
}
button:hover {
    opacity: 0.9;
}
.just-file {
    margin-top: 5px;
}
.back-btn {
    margin-top: 20px;
    display: inline-block;
}
</style>
</head>

<body>

<nav>
    <a href="student_dashboard.php">← Back to Dashboard</a>
</nav>

<div class="container">

    <h2>Attendance for <?php echo htmlspecialchars($course['course_name']); ?></h2>

    <table>
        <tr>
            <th>Date</th>
            <th>Status</th>
            <th>Justification</th>
            <th>Action</th>
        </tr>

        <?php foreach ($records as $rec): ?>
        <tr>
            <td><?php echo htmlspecialchars($rec['created_at']); ?></td>

            <td>
                <?php if ($rec['status']): ?>
                    <span class="status-present">Present</span>
                <?php else: ?>
                    <span class="status-absent">Absent</span>
                <?php endif; ?>
            </td>

            <td id="just-<?php echo $rec['id']; ?>">
                <?php if ($rec['justification']): ?>
                    <a href="uploads/<?php echo htmlspecialchars($rec['justification']); ?>" target="_blank">
                        View Justification
                    </a>
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>

            <td>
                <?php if (!$rec['status']): ?>
                    <form id="form-<?php echo $rec['id']; ?>" enctype="multipart/form-data">
                        <input type="file" name="file" id="file-<?php echo $rec['id']; ?>"
                               accept="image/*,application/pdf" class="just-file">
                        <br>
                        <button type="button" onclick="submitJustification(<?php echo $rec['id']; ?>)">
                            Upload
                        </button>
                    </form>
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>

</div>

<script>
function submitJustification(record_id) {
    let fileInput = document.getElementById('file-' + record_id);
    if (fileInput.files.length === 0) {
        alert("Please select a file first.");
        return;
    }

    let formData = new FormData();
    formData.append("action", "submit_justification");
    formData.append("record_id", record_id);
    formData.append("file", fileInput.files[0]);

    $.ajax({
        url: "student_actions.php",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: (res) => {
            try {
                let data = JSON.parse(res);
                if (data.status === "success") {
                    $("#just-" + record_id).html(
                        `<a href="uploads/${data.filename}" target="_blank">View Justification</a>`
                    );
                    fileInput.value = "";
                    alert("Justification uploaded successfully!");
                } else {
                    alert("Error: " + data.message);
                }
            } catch (e) {
                alert("Unexpected response: " + res);
            }
        },
        error: () => {
            alert("AJAX error.");
        }
    });
}
</script>

</body>
</html>








