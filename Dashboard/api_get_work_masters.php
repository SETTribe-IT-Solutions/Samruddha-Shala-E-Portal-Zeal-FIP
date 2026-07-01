<?php
header("Content-Type: application/json");

$host = "82.25.121.144";
$username = "u196817721_S_Eportal_U";
$password = "Sam_shalaEportal@2026";
$database = "u196817721_S_shalaEportal";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Build base query to fetch work records with joined names
$sql = "SELECT wm.id, wm.school_name, wm.assigned_to, wm.status, wm.created_at, wm.updated_at,
               wt.work_type_name, wn.work_name,
               (SELECT GROUP_CONCAT(stage_name ORDER BY id SEPARATOR ', ') FROM work_stages WHERE work_id = wm.id) AS stage_names,
               (SELECT LEAST(GREATEST(ROUND(IFNULL(SUM(stage_percentage),0),2),0),100) FROM work_stages WHERE work_id = wm.id) AS completed_percentage
        FROM work_master wm
        LEFT JOIN work_type_master wt ON wm.work_type_id = wt.id
        LEFT JOIN work_name_master wn ON wm.work_name_id = wn.id
        WHERE 1=1";

// Apply filters from query string
if (!empty($_GET['q'])) {
    $q = mysqli_real_escape_string($conn, $_GET['q']);
    $sql .= " AND (wn.work_name LIKE '%$q%' OR wm.school_name LIKE '%$q%' OR wt.work_type_name LIKE '%$q%')";
}

if (!empty($_GET['category'])) {
    $cat = mysqli_real_escape_string($conn, $_GET['category']);
    // category may be string name or id; try to match name
    $sql .= " AND (wt.work_type_name = '$cat' OR wm.work_type_id = '$cat')";
}

if (!empty($_GET['status'])) {
    $st = mysqli_real_escape_string($conn, $_GET['status']);
    $sql .= " AND wm.status = '$st'";
}

// Sorting
$allowedSortFields = [
    'id' => 'wm.id',
    'work_type_name' => 'wt.work_type_name',
    'work_name' => 'wn.work_name',
    'school_name' => 'wm.school_name',
    'completed_percentage' => 'completed_percentage',
    'status' => 'wm.status',
    'created_at' => 'wm.created_at',
    'updated_at' => 'wm.updated_at'
];

$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
$sort_dir = isset($_GET['sort_dir']) && strtolower($_GET['sort_dir']) === 'asc' ? 'ASC' : 'DESC';
if (!array_key_exists($sort_by, $allowedSortFields)) { $sort_by = 'created_at'; }

$sql .= " ORDER BY " . $allowedSortFields[$sort_by] . " $sort_dir";

// Pagination params
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 10;
$offset = ($page - 1) * $per_page;

// Get total count
$countSql = "SELECT COUNT(*) as cnt FROM work_master wm
             LEFT JOIN work_type_master wt ON wm.work_type_id = wt.id
             LEFT JOIN work_name_master wn ON wm.work_name_id = wn.id
             WHERE 1=1";
if (!empty($_GET['q'])) {
    $q = mysqli_real_escape_string($conn, $_GET['q']);
    $countSql .= " AND (wn.work_name LIKE '%$q%' OR wm.school_name LIKE '%$q%' OR wt.work_type_name LIKE '%$q%')";
}
if (!empty($_GET['category'])) {
    $cat = mysqli_real_escape_string($conn, $_GET['category']);
    $countSql .= " AND (wt.work_type_name = '$cat' OR wm.work_type_id = '$cat')";
}
if (!empty($_GET['status'])) {
    $st = mysqli_real_escape_string($conn, $_GET['status']);
    $countSql .= " AND wm.status = '$st'";
}

$cntRes = mysqli_query($conn, $countSql);
$total = 0;
if ($cntRes) {
    $c = mysqli_fetch_assoc($cntRes);
    $total = intval($c['cnt']);
}

// Append LIMIT
$sql .= " LIMIT $per_page OFFSET $offset";

$res = mysqli_query($conn, $sql);
$rows = [];
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        // normalize fields for frontend
        $rows[] = [
            'id' => $r['id'],
            'school_name' => $r['school_name'],
            'assigned_to' => $r['assigned_to'],
            'status' => $r['status'],
            'created_at' => $r['created_at'],
            'updated_at' => $r['updated_at'],
            'work_type_name' => $r['work_type_name'],
            'work_name' => $r['work_name'],
            'stage_name' => $r['stage_names'],
            'completed_percentage' => $r['completed_percentage']
        ];
    }
}

echo json_encode(['data' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $per_page]);

mysqli_close($conn);
?>
