<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../include/dbConfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$allowed = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
$fileType = mime_content_type($_FILES['file']['tmp_name']);
if (!in_array($fileType, $allowed)) {
    echo json_encode(['status' => false, 'message' => 'Only CSV files are supported']);
    exit;
}

$handle = fopen($_FILES['file']['tmp_name'], 'r');
if (!$handle) {
    echo json_encode(['status' => false, 'message' => 'Unable to open uploaded file']);
    exit;
}

$headers = fgetcsv($handle);
if (!$headers) {
    echo json_encode(['status' => false, 'message' => 'CSV file is empty']);
    exit;
}

$required = ['school_name', 'assigned_to', 'work_type_id', 'work_name_id', 'status'];
$headerMap = [];
foreach ($headers as $index => $header) {
    $key = trim(strtolower(str_replace(' ', '_', $header)));
    $headerMap[$key] = $index;
}

foreach ($required as $field) {
    if (!array_key_exists($field, $headerMap)) {
        echo json_encode(['status' => false, 'message' => 'Missing required CSV column: ' . $field]);
        exit;
    }
}

$inserted = 0;
$errors = [];

mysqli_begin_transaction($conn);
try {
    while (($row = fgetcsv($handle)) !== false) {
        $school_name = trim($row[$headerMap['school_name']] ?? '');
        $assigned_to = trim($row[$headerMap['assigned_to']] ?? '');
        $work_type_id = intval($row[$headerMap['work_type_id']] ?? 0);
        $work_name_id = intval($row[$headerMap['work_name_id']] ?? 0);
        $status = trim($row[$headerMap['status']] ?? 'Pending');
        $additional_notes = isset($headerMap['additional_notes']) ? trim($row[$headerMap['additional_notes']]) : '';

        if ($school_name === '' || $assigned_to === '' || $work_type_id <= 0 || $work_name_id <= 0) {
            $errors[] = 'Invalid row data at line ' . ($inserted + 2);
            continue;
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO work_master (school_name, assigned_to, work_type_id, work_name_id, status, additional_notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $created_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
        mysqli_stmt_bind_param($stmt, 'ssiiisi', $school_name, $assigned_to, $work_type_id, $work_name_id, $status, $additional_notes, $created_by);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $inserted++;
    }

    mysqli_commit($conn);
    fclose($handle);
    echo json_encode(['status' => true, 'message' => "Imported $inserted rows", 'errors' => $errors]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    fclose($handle);
    echo json_encode(['status' => false, 'message' => 'Import failed: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>