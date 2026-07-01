<?php
header("Content-Type: application/json");

session_start();
// Default user ID if session not set for the CEO/Admin.
$created_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; 

$host = "82.25.121.144";
$username = "u196817721_S_Eportal_U";
$password = "Sam_shalaEportal@2026";
$database = "u196817721_S_shalaEportal";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    echo json_encode(["status" => false, "message" => "Database connection failed"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(["status" => false, "message" => "Invalid JSON payload"]);
    exit;
}

$school_name = trim($input['school_name'] ?? '');
$assigned_to = trim($input['assigned_to'] ?? '');
$work_type_id = intval($input['work_type_id'] ?? 0);
$work_name_id = intval($input['work_name_id'] ?? 0);
$additional_notes = trim($input['additional_notes'] ?? '');
$stages = $input['stages'] ?? [];

// Validation
if (empty($school_name)) {
    echo json_encode(["status" => false, "message" => "School Name is required."]);
    exit;
}
if (empty($assigned_to)) {
    echo json_encode(["status" => false, "message" => "Assigned To is required."]);
    exit;
}
if (!in_array($assigned_to, ['Headmaster', 'Sachiv'], true)) {
    echo json_encode(["status" => false, "message" => "Invalid Assigned To selection."]);
    exit;
}
if ($work_type_id <= 0) {
    echo json_encode(["status" => false, "message" => "Work Type is required."]);
    exit;
}
if ($work_name_id <= 0) {
    echo json_encode(["status" => false, "message" => "Work Name is required."]);
    exit;
}
if (empty($stages) || !is_array($stages)) {
    echo json_encode(["status" => false, "message" => "At least one stage is required."]);
    exit;
}

$total_percentage = 0;
foreach ($stages as $stage) {
    if (empty(trim($stage['name']))) {
        echo json_encode(["status" => false, "message" => "Stage Name is required for all stages."]);
        exit;
    }
    if (!is_numeric($stage['percentage']) || $stage['percentage'] <= 0) {
        echo json_encode(["status" => false, "message" => "Percentage must be a valid positive number for all stages."]);
        exit;
    }
    $total_percentage += floatval($stage['percentage']);
}

if (abs($total_percentage - 100) > 0.01) {
    echo json_encode(["status" => false, "message" => "Total stage percentage must equal exactly 100%. (Currently $total_percentage%)"]);
    exit;
}

// Check if work_type and work_name exist
$res = mysqli_query($conn, "SELECT id FROM work_type_master WHERE id = $work_type_id");
if (mysqli_num_rows($res) === 0) {
    echo json_encode(["status" => false, "message" => "Selected Work Type does not exist."]);
    exit;
}
$res = mysqli_query($conn, "SELECT id FROM work_name_master WHERE id = $work_name_id AND work_type_id = $work_type_id");
if (mysqli_num_rows($res) === 0) {
    echo json_encode(["status" => false, "message" => "Selected Work Name does not exist for this Work Type."]);
    exit;
}

// Database Transaction
mysqli_begin_transaction($conn);
try {
    $stmt = mysqli_prepare($conn, "INSERT INTO work_master (school_name, assigned_to, work_type_id, work_name_id, additional_notes, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssiisi", $school_name, $assigned_to, $work_type_id, $work_name_id, $additional_notes, $created_by);
    mysqli_stmt_execute($stmt);
    $work_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    $stmt_stage = mysqli_prepare($conn, "INSERT INTO work_stages (work_id, stage_name, stage_percentage) VALUES (?, ?, ?)");
    foreach ($stages as $stage) {
        $name = trim($stage['name']);
        $pct = floatval($stage['percentage']);
        mysqli_stmt_bind_param($stmt_stage, "isd", $work_id, $name, $pct);
        mysqli_stmt_execute($stmt_stage);
    }
    mysqli_stmt_close($stmt_stage);

    mysqli_commit($conn);
    echo json_encode(["status" => true, "message" => "Work created successfully."]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(["status" => false, "message" => "Failed to save work. Error: " . $e->getMessage()]);
}

mysqli_close($conn);
?>
