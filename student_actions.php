<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';
header('Content-Type: application/json');

if(!isset($_SESSION['student_id'])){
    echo json_encode(['status'=>'error','message'=>'Not logged in']);
    exit();
}

if(isset($_POST['action']) && $_POST['action']=='submit_justification'){
    $record_id = $_POST['record_id'] ?? null;
    if(!$record_id){
        echo json_encode(['status'=>'error','message'=>'Missing record ID']);
        exit();
    }

    if(!isset($_FILES['file']) || $_FILES['file']['error']!=0){
        echo json_encode(['status'=>'error','message'=>'No file uploaded']);
        exit();
    }

    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $filename = 'just_'.$record_id.'_'.time().'.'.$ext;
    $target = 'uploads/'.$filename;

    if(!move_uploaded_file($_FILES['file']['tmp_name'], $target)){
        echo json_encode(['status'=>'error','message'=>'Failed to save file']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE attendance_records SET justification=? WHERE id=?");
    $ok = $stmt->execute([$filename, $record_id]);

    if($ok) echo json_encode(['status'=>'success','filename'=>$filename]);
    else echo json_encode(['status'=>'error','message'=>'Database update failed']);
    exit();
}

echo json_encode(['status'=>'error','message'=>'Invalid action']);
exit();
?>
