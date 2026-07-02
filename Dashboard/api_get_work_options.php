<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../include/dbConfig.php';

$types = [];
$res = mysqli_query($conn, "SELECT id, work_type_name FROM work_type_master WHERE status='Active' ORDER BY work_type_name");
while ($r = mysqli_fetch_assoc($res)) $types[] = $r;

$names = [];
$res2 = mysqli_query($conn, "SELECT id, work_type_id, work_name FROM work_name_master WHERE status='Active' ORDER BY work_name");
while ($r = mysqli_fetch_assoc($res2)) $names[] = $r;

echo json_encode(['work_types' => $types, 'work_names' => $names]);
mysqli_close($conn);
?>
