<?php
session_start();
require 'db.php'; // PDO
require 'vendor/autoload.php'; // PHPSpreadsheet v1.30
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Add student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $first = $_POST['first_name'] ?? '';
    $last = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($first && $last && $email && $username && $password) {
        $stmt = $pdo->prepare("INSERT INTO students (first_name,last_name,email) VALUES (?, ?, ?)");
        $stmt->execute([$first, $last, $email]);
        $student_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO users (username,password,role,student_id) VALUES (?, ?, 'student', ?)");
        $stmt->execute([$username, $password, $student_id]);
        $message = "Student added successfully!";
    } else {
        $message = "All fields are required!";
    }
}

// Delete student
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM users WHERE student_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM student_courses WHERE student_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM students WHERE id=?")->execute([$id]);
    $message = "Student deleted!";
}

// Export students
if (isset($_POST['export_excel'])) {
    $students = $pdo->query("SELECT s.id, s.first_name, s.last_name, s.email, u.username
                             FROM students s LEFT JOIN users u ON u.student_id = s.id")->fetchAll(PDO::FETCH_ASSOC);
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1','ID');
    $sheet->setCellValue('B1','First Name');
    $sheet->setCellValue('C1','Last Name');
    $sheet->setCellValue('D1','Email');
    $sheet->setCellValue('E1','Username');

    $row = 2;
    foreach($students as $s){
        $sheet->setCellValue('A'.$row,$s['id']);
        $sheet->setCellValue('B'.$row,$s['first_name']);
        $sheet->setCellValue('C'.$row,$s['last_name']);
        $sheet->setCellValue('D'.$row,$s['email']);
        $sheet->setCellValue('E'.$row,$s['username']);
        $row++;
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="students.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}

// Import students
if (isset($_POST['import_excel']) && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();
    for($i=1;$i<count($rows);$i++){
        $row = $rows[$i];
        $first = $row[0] ?? '';
        $last = $row[1] ?? '';
        $email = $row[2] ?? '';
        $username = $row[3] ?? '';
        $password = $row[4] ?? '123456';

        if($first && $last && $email && $username){
            $stmt = $pdo->prepare("INSERT INTO students (first_name,last_name,email) VALUES (?, ?, ?)");
            $stmt->execute([$first, $last, $email]);
            $student_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO users (username,password,role,student_id) VALUES (?, ?, 'student', ?)");
            $stmt->execute([$username, $password, $student_id]);
        }
    }
    $message = "Students imported successfully!";
}

// Fetch all students for display
$students = $pdo->query("SELECT s.id, s.first_name, s.last_name, s.email, u.username
                         FROM students s LEFT JOIN users u ON u.student_id = s.id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Manage Students</title>
<style>
body { font-family: Arial; background: #f4f7fb; margin: 0; padding: 20px; }
nav { background: #2563eb; padding: 12px; }
nav a { color: #fff; margin-right: 15px; text-decoration: none; font-weight: bold; }
h2,h3 { color: #2563eb; }
table { border-collapse: collapse; width: 100%; background: #fff; margin-top: 20px; }
table, th, td { border: 1px solid #000; padding: 8px; text-align: left; }
input, button { padding: 6px 10px; border-radius: 6px; margin: 2px; }
button { background-color: #2563eb; color: #fff; border: none; cursor: pointer; }
button:hover { opacity: 0.9; }
.message { margin-top: 10px; color: green; }
</style>
</head>
<body>
<nav>
<a href="admin_dashboard.php">Dashboard</a>
<a href="admin_statistics.php">Statistics</a>
<a href="logout.php">Logout</a>
</nav>

<h2>Manage Students</h2>
<?php if(isset($message)) echo "<p class='message'>$message</p>"; ?>

<h3>Add New Student</h3>
<form method="post">
<input type="text" name="first_name" placeholder="First Name" required>
<input type="text" name="last_name" placeholder="Last Name" required>
<input type="email" name="email" placeholder="Email" required><br>
<input type="text" name="username" placeholder="Username" required>
<input type="text" name="password" placeholder="Password" required>
<button type="submit" name="add_student">Add Student</button>
</form>

<h3>Import/Export Students (Excel)</h3>
<form method="post" enctype="multipart/form-data">
<input type="file" name="excel_file" accept=".xlsx,.xls" required>
<button type="submit" name="import_excel">Import Excel</button>
</form>

<form method="post" style="margin-top:10px;">
<button type="submit" name="export_excel">Export Excel</button>
</form>

<h3>All Students</h3>
<table>
<tr>
<th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Action</th>
</tr>
<?php foreach($students as $s): ?>
<tr>
<td><?php echo htmlspecialchars($s['id']); ?></td>
<td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
<td><?php echo htmlspecialchars($s['email']); ?></td>
<td><?php echo htmlspecialchars($s['username']); ?></td>
<td><a href="?delete=<?php echo $s['id']; ?>" onclick="return confirm('Delete this student?');">Delete</a></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>

