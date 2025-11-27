<?php
require '../vendor/autoload.php';
require '../config/db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['import'])) {

    $file = $_FILES['excel']['tmp_name'];

    if (!$file) {
        echo "No file uploaded.";
        exit;
    }

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();

    $rows = $sheet->toArray();

    // Skip header row (index 0)
    for ($i = 1; $i < count($rows); $i++) {

        $first_name = $rows[$i][0];
        $last_name  = $rows[$i][1];
        $email      = $rows[$i][2];
        $username   = $rows[$i][3];
        $password   = password_hash($rows[$i][4], PASSWORD_BCRYPT);

        $stmt = $conn->prepare("
            INSERT INTO students (first_name, last_name, email, username, password)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([$first_name, $last_name, $email, $username, $password]);
    }

    echo "<h3 style='color: green'>Students imported successfully!</h3>";
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="excel" required>
    <button type="submit" name="import">Import Students</button>
</form>
