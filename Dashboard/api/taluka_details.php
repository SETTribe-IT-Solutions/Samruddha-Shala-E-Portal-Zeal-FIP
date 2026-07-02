<?php
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['username']) || !isset($_SESSION['role']) || strtoupper($_SESSION['role']) !== 'CEO') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$host = "82.25.121.144";
$username = "u196817721_S_Eportal_U";
$password = "Sam_shalaEportal@2026";
$database = "u196817721_S_shalaEportal";

$dbConn = mysqli_connect($host, $username, $password, $database);
if (!$dbConn) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'DB connection failed']);
    exit();
}
mysqli_set_charset($dbConn, 'utf8');

$taluka = isset($_POST['taluka']) ? trim($_POST['taluka']) : '';
$talukaEsc = mysqli_real_escape_string($dbConn, $taluka);

$isAll = strtoupper($talukaEsc) === 'ALL' || $talukaEsc === '';

// Schools list
if ($isAll) {
    $schoolSql = "SELECT DISTINCT school_name FROM talukas_school_data WHERE is_active = 1 ORDER BY school_name LIMIT 1000";
    $summaryWhere = "t.is_active = 1";
    $workWhere = "t.is_active = 1";
} else {
    $schoolSql = "SELECT DISTINCT school_name FROM talukas_school_data WHERE is_active = 1 AND taluka_name = '" . $talukaEsc . "' ORDER BY school_name";
    $summaryWhere = "t.is_active = 1 AND t.taluka_name = '" . $talukaEsc . "'";
    $workWhere = "t.is_active = 1 AND t.taluka_name = '" . $talukaEsc . "'";
}

$schools = [];
$res = mysqli_query($dbConn, $schoolSql);
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        if (!empty($r['school_name'])) $schools[] = $r['school_name'];
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'DB error fetching schools: ' . mysqli_error($dbConn)]);
    exit();
}

$summarySql = "SELECT COUNT(DISTINCT t.school_name) AS school_count, SUM(COALESCE(a.sanctioned_amount,0)) AS total_sanctioned, SUM(COALESCE(a.amount_spent,0)) AS total_spent
    FROM talukas_school_data t
    LEFT JOIN amount_utilization a ON a.work_name = t.work_name AND a.work_type = t.work_type
    WHERE " . $summaryWhere;

$summaryRes = mysqli_query($dbConn, $summarySql);
if (!$summaryRes) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'DB error summary: ' . mysqli_error($dbConn)]);
    exit();
}
$summary = mysqli_fetch_assoc($summaryRes) ?: [];

$workSql = "SELECT t.work_name, t.work_type, COUNT(DISTINCT t.school_name) AS schools, SUM(COALESCE(a.amount_spent,0)) AS spent, SUM(COALESCE(a.sanctioned_amount,0)) AS sanctioned
    FROM talukas_school_data t
    LEFT JOIN amount_utilization a ON a.work_name = t.work_name AND a.work_type = t.work_type
    WHERE " . $workWhere . "
    GROUP BY t.work_name, t.work_type
    ORDER BY spent DESC
    LIMIT 50";

$workRes = mysqli_query($dbConn, $workSql);
$workSummary = [];
if ($workRes) {
    while ($w = mysqli_fetch_assoc($workRes)) {
        $workSummary[] = $w;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'DB error work summary: ' . mysqli_error($dbConn)]);
    exit();
}

$result = [
    'taluka' => $talukaEsc,
    'school_count' => (int)($summary['school_count'] ?? count($schools)),
    'total_sanctioned' => (float)($summary['total_sanctioned'] ?? 0),
    'total_spent' => (float)($summary['total_spent'] ?? 0),
    'schools' => $schools,
    'work_summary' => $workSummary
];

header('Content-Type: application/json');
echo json_encode($result);
exit();

?>
