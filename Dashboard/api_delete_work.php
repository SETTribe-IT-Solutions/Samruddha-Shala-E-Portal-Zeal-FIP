<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../include/dbConfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid id']);
    exit;
}

$stmt = mysqli_prepare($conn, 'DELETE FROM work_master WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
$ok = mysqli_stmt_execute($stmt);

if ($ok) echo json_encode(['status' => true, 'message' => 'Deleted']);
else { http_response_code(500); echo json_encode(['error' => 'Delete failed', 'mysql' => mysqli_error($conn)]); }

mysqli_close($conn);
?>
