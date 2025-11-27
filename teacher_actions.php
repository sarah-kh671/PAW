<?php
session_start();
require "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$action = $_POST['action'] ?? '';

// --------------------------------------------------------------
// 1) GET COURSES
// --------------------------------------------------------------
if ($action === "get_courses") {

    $stmt = $pdo->prepare("SELECT * FROM courses");
    $stmt->execute();
    $courses = $stmt->fetchAll();

    echo json_encode($courses);
    exit;
}

// --------------------------------------------------------------
// 2) GET STUDENTS FOR SELECTED COURSE
// --------------------------------------------------------------
if ($action === "get_students_for_course") {

    $course_id = $_POST["course_id"];

    $stmt = $pdo->prepare("
        SELECT s.id, s.first_name, s.last_name
        FROM students s
        JOIN student_courses sc ON s.id = sc.student_id
        WHERE sc.course_id = ?
    ");

    $stmt->execute([$course_id]);
    $students = $stmt->fetchAll();

    echo json_encode($students);
    exit;
}

// --------------------------------------------------------------
// 3) SAVE ATTENDANCE
// --------------------------------------------------------------
if ($action === "save_attendance") {

    if (!isset($_POST["course_id"])) {
        echo json_encode(["status" => "error", "message" => "Missing course_id"]);
        exit;
    }

    $course_id = $_POST["course_id"];
    $data = json_decode($_POST["data"], true);

    if (!is_array($data)) {
        echo json_encode(["status" => "error", "message" => "Invalid data format"]);
        exit;
    }

    foreach ($data as $item) {

        $stmt = $pdo->prepare("
            INSERT INTO attendance_records (student_id, course_id, status)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $item["student_id"],
            $course_id,
            $item["present"]
        ]);
    }

    echo json_encode(["status" => "ok"]);
    exit;
}

?>

