<?php
header("Content-Type: application/json");

session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    echo json_encode(["status" => false, "message" => "Unauthorized"]);
    exit();
}

$host = "82.25.121.144";
$username = "u196817721_S_Eportal_U";
$password = "Sam_shalaEportal@2026";
$database = "u196817721_S_shalaEportal";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    echo json_encode(["status" => false, "message" => "Database connection failed"]);
    exit;
}

// Ensure correct encoding for Marathi characters
mysqli_set_charset($conn, "utf8mb4");

$query = "SELECT DISTINCT taluka_name, school_name, work_name, work_type FROM talukas_school_data WHERE school_name IS NOT NULL AND school_name != '' ORDER BY taluka_name, school_name";
$res = mysqli_query($conn, $query);

$data = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = [
            "taluka_name" => $row['taluka_name'],
            "school_name" => $row['school_name'],
            "work_name" => $row['work_name'],
            "work_type" => $row['work_type']
        ];
    }
    echo json_encode(["status" => true, "data" => $data]);
} else {
    echo json_encode(["status" => false, "message" => mysqli_error($conn)]);
}

mysqli_close($conn);
?>
