<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized user']);
    exit;
}

$host = "82.25.121.144";
$db_user = "u196817721_S_Eportal_U";
$db_pass = "Sam_shalaEportal@2026";
$db_name = "u196817721_S_shalaEportal";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST;
}

$type = $conn->real_escape_string($input['type'] ?? '');
$work_name = $conn->real_escape_string($input['work_name'] ?? '');
$taluka_name = $conn->real_escape_string($input['taluka_name'] ?? '');
$school_name = $conn->real_escape_string($input['school_name'] ?? '');
$duration_days = (int)($input['duration_days'] ?? 0);
$work_id = (int)($input['work_id'] ?? 0);
$message = $conn->real_escape_string($input['message'] ?? '');
$sender_id = (int)$_SESSION['user_id'];
$status = 'Pending';
$created_date = date('Y-m-d H:i:s.u');

$sql = "INSERT INTO reminder_blocker (
            created_date, duration_days, message, school_name, sender_id, 
            status, taluka_name, type, updated_date, work_id, work_name
        ) VALUES (
            '$created_date', $duration_days, '$message', '$school_name', $sender_id, 
            '$status', '$taluka_name', '$type', '$created_date', $work_id, '$work_name'
        )";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $conn->error]);
}

$conn->close();
?>
