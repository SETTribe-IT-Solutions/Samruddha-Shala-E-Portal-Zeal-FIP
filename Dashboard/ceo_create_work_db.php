<?php
header("Content-Type: application/json");

// Database Connection
$host = "82.25.121.144";
$username = "u196817721_S_Eportal_U";
$password = "Sam_shalaEportal@2026";
$database = "u196817721_S_shalaEportal";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    echo json_encode([
        "status" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

// Get POST Data
$school_name    = mysqli_real_escape_string($conn, $_POST['school_name']);
$work_type      = mysqli_real_escape_string($conn, $_POST['work_type']);
$budget_lakhs   = mysqli_real_escape_string($conn, $_POST['budget_lakhs']);
$funding_source = mysqli_real_escape_string($conn, $_POST['funding_source']);
$task_description = mysqli_real_escape_string($conn, $_POST['task_description']);

// Default Values
$work_progress_percentage = 0;
$status = "Pending";

// Insert Query
$sql = "INSERT INTO ceo_create_tasks
(
    school_name,
    work_type,
    budget_lakhs,
    funding_source,
    task_description,
    work_progress_percentage,
    status,
    created_at,
    updated_at
)
VALUES
(
    '$school_name',
    '$work_type',
    '$budget_lakhs',
    '$funding_source',
    '$task_description',
    '$work_progress_percentage',
    '$status',
    NOW(),
    NOW()
)";

if (mysqli_query($conn, $sql)) {
    echo json_encode([
        "status" => true,
        "message" => "Task assigned successfully"
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>