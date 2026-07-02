<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../include/dbConfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = $_POST;
if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id']);
    exit;
}

$id = intval($data['id']);
$school = isset($data['school_name']) ? $data['school_name'] : null;
$assigned = isset($data['assigned_to']) ? $data['assigned_to'] : null;
$work_type_id = isset($data['work_type_id']) ? intval($data['work_type_id']) : null;
$work_name_id = isset($data['work_name_id']) ? intval($data['work_name_id']) : null;
$status = isset($data['status']) ? $data['status'] : null;
$notes = isset($data['additional_notes']) ? $data['additional_notes'] : null;

$fields = [];
$params = [];
$types = '';

if ($school !== null) { $fields[] = 'school_name = ?'; $params[] = $school; $types .= 's'; }
if ($assigned !== null) { $fields[] = 'assigned_to = ?'; $params[] = $assigned; $types .= 's'; }
if ($work_type_id !== null) { $fields[] = 'work_type_id = ?'; $params[] = $work_type_id; $types .= 'i'; }
if ($work_name_id !== null) { $fields[] = 'work_name_id = ?'; $params[] = $work_name_id; $types .= 'i'; }
if ($status !== null) { $fields[] = 'status = ?'; $params[] = $status; $types .= 's'; }
if ($notes !== null) { $fields[] = 'additional_notes = ?'; $params[] = $notes; $types .= 's'; }

if (empty($fields)) {
    echo json_encode(['status' => true, 'message' => 'Nothing to update']);
    exit;
}

$sql = 'UPDATE work_master SET ' . implode(', ', $fields) . ' WHERE id = ?';
$params[] = $id; $types .= 'i';

 $stmt = mysqli_prepare($conn, $sql);
 if (!$stmt) {
     http_response_code(500);
     echo json_encode(['error' => 'Prepare failed', 'mysql' => mysqli_error($conn)]);
     exit;
 }

 // bind params dynamically (bind_param requires references)
 $bind_names = array_merge([$types], $params);
 $refs = [];
 foreach ($bind_names as $key => $value) {
     $refs[$key] = &$bind_names[$key];
 }
 call_user_func_array([$stmt, 'bind_param'], $refs);
 $ok = mysqli_stmt_execute($stmt);

if ($ok) {
    echo json_encode(['status' => true, 'message' => 'Updated']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Update failed', 'mysql' => mysqli_error($conn)]);
}

mysqli_close($conn);
?>
