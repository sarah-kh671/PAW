<?php
// CONFIG.PHP — MySQLi VERSION (FOR DASHBOARDS)

$host = "localhost";
$user = "root";
$pass = "";
$db = "attendance_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
