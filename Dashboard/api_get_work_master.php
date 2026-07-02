<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../include/dbConfig.php';

if (empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id']);
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT wm.*, wt.work_type_name, wn.work_name
        FROM work_master wm
        LEFT JOIN work_type_master wt ON wm.work_type_id = wt.id
        LEFT JOIN work_name_master wn ON wm.work_name_id = wn.id
        WHERE wm.id = ? LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'Record not found']);
    exit;
}

// fetch stages
$stages = [];
$res2 = mysqli_query($conn, "SELECT id, stage_name, stage_percentage, created_at FROM work_stages WHERE work_id = " . $id . " ORDER BY id");
while ($s = mysqli_fetch_assoc($res2)) $stages[] = $s;

$row['stages'] = $stages;

echo json_encode($row);
mysqli_close($conn);
?>
