<?php
session_start();
require __DIR__ . '/../db.php';
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

// STOP ALL OUTPUT
ob_start();
ob_clean();

// PERMISSION CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit;
}

// FETCH STUDENTS
$stmt = $pdo->query("SELECT first_name, last_name, email, username FROM students");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// CREATE FILE
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// HEADERS
$sheet->setCellValue('A1', 'First Name');
$sheet->setCellValue('B1', 'Last Name');
$sheet->setCellValue('C1', 'Email');
$sheet->setCellValue('D1', 'Username');
$sheet->setCellValue('E1', 'Password');

// FILL DATA
$row = 2;
foreach ($students as $s) {
    $sheet->setCellValue("A{$row}", $s['first_name']);
    $sheet->setCellValue("B{$row}", $s['last_name']);
    $sheet->setCellValue("C{$row}", $s['email']);
    $sheet->setCellValue("D{$row}", $s['username']);
    $sheet->setCellValue("E{$row}", '[HIDDEN]');
    $row++;
}

// FLUSH BUFFER AGAIN
if (ob_get_length()) ob_end_clean();

// HEADERS FOR XLSX DOWNLOAD
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="students.xlsx"');
header('Cache-Control: max-age=0');

// WRITE FILE
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');
exit;



