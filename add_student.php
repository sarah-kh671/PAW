<?php
// ================= DATABASE CONNECTION =================
$host = "localhost";
$user = "root"; // default WAMP username
$pass = "";     // default WAMP password
$db   = "attendance_db";

$conn = new mysqli($host, $user, $pass, $db);
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// ================= FORM PROCESSING =================
$message = "";
$errors = [];

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $studentId = $_POST['studentId'];
    $lastName  = $_POST['lastName'];
    $firstName = $_POST['firstName'];
    $email     = $_POST['email'];

    // validation
    if(!preg_match("/^[0-9]+$/", $studentId)) $errors[] = "ID must be numbers only.";
    if(!preg_match("/^[A-Za-z]+$/", $lastName)) $errors[] = "Last name must be letters only.";
    if(!preg_match("/^[A-Za-z]+$/", $firstName)) $errors[] = "First name must be letters only.";
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";

    if(empty($errors)){
        $stmt = $conn->prepare("INSERT INTO student (id, last_name, first_name, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $studentId, $lastName, $firstName, $email);
        if($stmt->execute()){
            $message = "Student added successfully!";
        } else {
            $errors[] = "Database error: ".$stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Student — Attendance System</title>
<style>
body{font-family:Arial,sans-serif;margin:20px;background:#f9f9f9;}
form{background:#fff;padding:15px;border-radius:8px;width:400px;}
label{display:block;margin-top:10px;}
input[type=text], input[type=email]{width:100%;padding:8px;margin-top:4px;border-radius:4px;border:1px solid #ccc;}
button{margin-top:12px;padding:10px 15px;background:#0077cc;color:#fff;border:none;border-radius:4px;cursor:pointer;}
.error{color:red;margin-top:4px;font-size:0.9em;}
.success{color:green;margin-top:4px;font-weight:bold;}
</style>
</head>
<body>

<h2>Add Student</h2>

<?php
if(!empty($message)) echo "<p class='success'>$message</p>";
if(!empty($errors)) foreach($errors as $err) echo "<p class='error'>$err</p>";
?>

<form method="POST" action="add_student.php">
    <label>Student ID</label>
    <input type="text" name="studentId" required>

    <label>Last Name</label>
    <input type="text" name="lastName" required>

    <label>First Name</label>
    <input type="text" name="firstName" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <button type="submit">Submit</button>
</form>

</body>
</html>

