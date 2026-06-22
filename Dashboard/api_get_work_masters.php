<?php
header("Content-Type: application/json");

$host = "82.25.121.144";
$username = "u196817721_S_Eportal_U";
$password = "Sam_shalaEportal@2026";
$database = "u196817721_S_shalaEportal";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    echo json_encode(["status" => false, "message" => "Database connection failed"]);
    exit;
}

$work_types = [];
$res_types = mysqli_query($conn, "SELECT id, work_type_name FROM work_type_master WHERE status = 'Active'");
while ($row = mysqli_fetch_assoc($res_types)) {
    $work_types[] = $row;
}

$work_names = [];
$res_names = mysqli_query($conn, "SELECT id, work_type_id, work_name FROM work_name_master WHERE status = 'Active'");
while ($row = mysqli_fetch_assoc($res_names)) {
    $work_names[] = $row;
}

echo json_encode([
    "status" => true,
    "work_types" => $work_types,
    "work_names" => $work_names
]);

mysqli_close($conn);
?>
