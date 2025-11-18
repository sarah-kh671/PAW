<?php
// db connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "attendance_db";

$conn = new mysqli($host, $user, $pass, $db);
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

if($_SERVER['POST']){
    $studentId = $_POST['studentId'];
    $session = $_POST['session'];
    $present = isset($_POST['present']) ? 1 : 0;
    $participate = isset($_POST['participate']) ? 1 : 0;

    // insert or update
    $stmt = $conn->prepare("INSERT INTO attendance (student_id, session, present, participate)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE present=?, participate=?");
    $stmt->bind_param("iiiiii", $studentId, $session, $present, $participate, $present, $participate);
    $stmt->execute();
    $stmt->close();
}
?>
