<?php
// auth.php — robust login (PDO)
// Overwrite the current auth.php with this file.

session_start();
require "db.php"; // must load $pdo (your db.php)

header("Content-Type: application/json; charset=utf-8");

// Simple helper
function out($arr){ echo json_encode($arr, JSON_UNESCAPED_UNICODE); exit; }

if (!isset($_POST['username'], $_POST['password'], $_POST['role'])) {
    out(['status'=>'error','message'=>'Missing fields']);
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);
$role     = trim($_POST['role']);

try {
    // Find user by username AND role and password
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        out(['status'=>'error','message'=>'Invalid username or password']);
    }

    // Note: passwords stored in plain text in your DB for now.
    if ($user['password'] !== $password || $user['role'] !== $role) {
        out(['status'=>'error','message'=>'Invalid username/password/role']);
    }

    // Set sessions
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // If users table already has student_id/teacher_id fields, use them
    if (array_key_exists('student_id', $user) && !empty($user['student_id'])) {
        $_SESSION['student_id'] = $user['student_id'];
    }
    if (array_key_exists('teacher_id', $user) && !empty($user['teacher_id'])) {
        $_SESSION['teacher_id'] = $user['teacher_id'];
    }

    // As a fallback: try to find a matching students row by username / email / id
    if ($_SESSION['role'] === 'student' && empty($_SESSION['student_id'])) {
        $try = $pdo->prepare("SELECT id FROM students WHERE id = ? OR CONCAT(first_name,' ',last_name)=? OR email = ? LIMIT 1");
        $try->execute([$username, $username, $username]);
        $found = $try->fetch(PDO::FETCH_ASSOC);
        if ($found && !empty($found['id'])) {
            $_SESSION['student_id'] = $found['id'];
        }
    }

    // Fallback for teacher
    if ($_SESSION['role'] === 'teacher' && empty($_SESSION['teacher_id'])) {
        $try = $pdo->prepare("SELECT id FROM teachers WHERE id = ? OR full_name = ? OR email = ? LIMIT 1");
        $try->execute([$username, $username, $username]);
        $found = $try->fetch(PDO::FETCH_ASSOC);
        if ($found && !empty($found['id'])) {
            $_SESSION['teacher_id'] = $found['id'];
        }
    }

    // If student role but still no student_id, return an informative error
    if ($_SESSION['role'] === 'student' && empty($_SESSION['student_id'])) {
        out(['status'=>'error','message'=>'Login succeeded but student record not linked. Run mapping SQL (instructions).']);
    }

    // Success
    out(['status'=>'success','role'=>$_SESSION['role']]);

} catch (Exception $e) {
    out(['status'=>'error','message'=>'Server error: '.$e->getMessage()]);
}





