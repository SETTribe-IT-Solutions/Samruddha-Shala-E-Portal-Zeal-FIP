<?php
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

$host = "82.25.121.144";
$username = "u196817721_S_Eportal_U";
$password = "Sam_shalaEportal@2026";
$database = "u196817721_S_shalaEportal";

$dbConn = mysqli_connect($host, $username, $password, $database);
if (!$dbConn) {
    die('DB connect error');
}
mysqli_set_charset($dbConn, 'utf8');

$taluka = isset($_GET['taluka']) ? trim($_GET['taluka']) : 'ALL';
$talukaEsc = mysqli_real_escape_string($dbConn, $taluka);
$isAll = strtoupper($talukaEsc) === 'ALL' || $talukaEsc === '';

// Load summary and lists similar to API
if ($isAll) {
    $summaryWhere = "t.is_active = 1";
} else {
    $summaryWhere = "t.is_active = 1 AND t.taluka_name = '" . $talukaEsc . "'";
}

$summarySql = "SELECT COUNT(DISTINCT t.school_name) AS school_count, SUM(COALESCE(a.sanctioned_amount,0)) AS total_sanctioned, SUM(COALESCE(a.amount_spent,0)) AS total_spent
    FROM talukas_school_data t
    LEFT JOIN amount_utilization a ON a.work_name = t.work_name AND a.work_type = t.work_type
    WHERE " . $summaryWhere;
$summaryRes = mysqli_query($dbConn, $summarySql);
$summary = mysqli_fetch_assoc($summaryRes) ?: [];

$schools = [];
$schoolSql = $isAll ? "SELECT DISTINCT school_name FROM talukas_school_data WHERE is_active = 1 ORDER BY school_name" : "SELECT DISTINCT school_name FROM talukas_school_data WHERE is_active = 1 AND taluka_name = '" . $talukaEsc . "' ORDER BY school_name";
$schoolRes = mysqli_query($dbConn, $schoolSql);
while ($r = mysqli_fetch_assoc($schoolRes)) { $schools[] = $r['school_name']; }

$workSql = "SELECT t.work_name, t.work_type, COUNT(DISTINCT t.school_name) AS schools, SUM(COALESCE(a.amount_spent,0)) AS spent, SUM(COALESCE(a.sanctioned_amount,0)) AS sanctioned
    FROM talukas_school_data t
    LEFT JOIN amount_utilization a ON a.work_name = t.work_name AND a.work_type = t.work_type
    WHERE " . $summaryWhere . "
    GROUP BY t.work_name, t.work_type
    ORDER BY spent DESC
    LIMIT 200";
$workRes = mysqli_query($dbConn, $workSql);
$workSummary = [];
while ($w = mysqli_fetch_assoc($workRes)) { $workSummary[] = $w; }

// Dynamic calculation of completed and pending tasks from database
$completedTasks = 0;
$pendingTasks = 0;

$talukaBlockMap = [
    'आजरा' => ['Ajra', 'Ajara'],
    'करवीर' => ['Karvir', 'Karveer'],
    'कागल' => ['Kagal'],
    'गगनबावडा' => ['Gaganbawda', 'Gaganbavda'],
    'गडहिंग्लज' => ['Gadhinglaj'],
    'चंदगड' => ['Chandgad'],
    'पन्हाळा' => ['Panhala'],
    'भुदरगड' => ['Bhudargad'],
    'राधानगरी' => ['Radhanagari'],
    'शाहुवाडी' => ['Shahuwadi', 'Shahuwadi'],
    'शिरोळ' => ['Shirol'],
    'हातकणंगले' => ['Hatkanangle', 'Hatkanangale']
];

if ($isAll) {
    $taskSql = "SELECT SUM(status = 'Completed') AS completed, SUM(status = 'Pending') AS pending FROM ceo_create_tasks";
    $taskRes = mysqli_query($dbConn, $taskSql);
    if ($taskRes) {
        $taskRow = mysqli_fetch_assoc($taskRes);
        $completedTasks = (int)($taskRow['completed'] ?? 0);
        $pendingTasks = (int)($taskRow['pending'] ?? 0);
    }
} else {
    $patterns = isset($talukaBlockMap[$taluka]) ? $talukaBlockMap[$taluka] : [];
    if (!empty($patterns)) {
        $whereTerms = [];
        foreach ($patterns as $pat) {
            $whereTerms[] = "school_name LIKE '%" . mysqli_real_escape_string($dbConn, $pat) . "%'";
        }
        $whereClause = implode(" OR ", $whereTerms);
        $taskSql = "SELECT SUM(status = 'Completed') AS completed, SUM(status = 'Pending') AS pending FROM ceo_create_tasks WHERE " . $whereClause;
        $taskRes = mysqli_query($dbConn, $taskSql);
        if ($taskRes) {
            $taskRow = mysqli_fetch_assoc($taskRes);
            $completedTasks = (int)($taskRow['completed'] ?? 0);
            $pendingTasks = (int)($taskRow['pending'] ?? 0);
        }
    }
}

// Fetch Fund Timeline (Timeline of Received vs Spent)
$fundTimeline = [];
if ($isAll) {
    $fundTimelineSql = "SELECT DATE_FORMAT(expense_date, '%b %Y') AS month_label,
            SUM(sanctioned_amount) AS total_sanctioned,
            SUM(amount_spent) AS total_spent
        FROM amount_utilization
        WHERE expense_date IS NOT NULL
        GROUP BY YEAR(expense_date), MONTH(expense_date)
        ORDER BY YEAR(expense_date), MONTH(expense_date)
        LIMIT 6";
} else {
    $fundTimelineSql = "SELECT DATE_FORMAT(a.expense_date, '%b %Y') AS month_label,
            SUM(COALESCE(a.sanctioned_amount,0)) AS total_sanctioned,
            SUM(COALESCE(a.amount_spent,0)) AS total_spent
        FROM talukas_school_data t
        JOIN amount_utilization a ON a.work_name = t.work_name AND a.work_type = t.work_type
        WHERE a.expense_date IS NOT NULL AND t.is_active = 1 AND t.taluka_name = '" . $talukaEsc . "'
        GROUP BY YEAR(a.expense_date), MONTH(a.expense_date)
        ORDER BY YEAR(a.expense_date), MONTH(a.expense_date)
        LIMIT 6";
}
$fundTimelineRes = mysqli_query($dbConn, $fundTimelineSql);
if ($fundTimelineRes) {
    while ($row = mysqli_fetch_assoc($fundTimelineRes)) {
        $fundTimeline[] = $row;
    }
}

// Fetch Fund Distribution by Funding Source
$fundDistribution = [];
if ($isAll) {
    $fundDistributionSql = "SELECT IFNULL(fund_source, 'Unknown') AS fund_source,
            SUM(amount_spent) AS total_spent
        FROM amount_utilization
        GROUP BY fund_source
        ORDER BY total_spent DESC
        LIMIT 6";
} else {
    $fundDistributionSql = "SELECT IFNULL(a.fund_source, 'Unknown') AS fund_source,
            SUM(COALESCE(a.amount_spent,0)) AS total_spent
        FROM talukas_school_data t
        JOIN amount_utilization a ON a.work_name = t.work_name AND a.work_type = t.work_type
        WHERE t.is_active = 1 AND t.taluka_name = '" . $talukaEsc . "'
        GROUP BY a.fund_source
        ORDER BY total_spent DESC
        LIMIT 6";
}
$fundDistributionRes = mysqli_query($dbConn, $fundDistributionSql);
if ($fundDistributionRes) {
    while ($row = mysqli_fetch_assoc($fundDistributionRes)) {
        $fundDistribution[] = $row;
    }
}

// Fetch Recent Activity for Taluka
$recentActivityRows = [];
if ($isAll) {
    $recentSql = "SELECT a.work_name, a.fund_source, a.sanctioned_amount, a.amount_spent, a.expense_date,
            u.name AS hm_name, '' AS school_name
        FROM amount_utilization a
        LEFT JOIN users u ON u.id = a.hm_id
        ORDER BY a.id DESC
        LIMIT 6";
} else {
    $recentSql = "SELECT a.work_name, a.fund_source, a.sanctioned_amount, a.amount_spent, a.expense_date,
            u.name AS hm_name, t.school_name
        FROM talukas_school_data t
        JOIN amount_utilization a ON a.work_name = t.work_name AND a.work_type = t.work_type
        LEFT JOIN users u ON u.id = a.hm_id
        WHERE t.is_active = 1 AND t.taluka_name = '" . $talukaEsc . "'
        ORDER BY a.id DESC
        LIMIT 6";
}
$recentRes = mysqli_query($dbConn, $recentSql);
if ($recentRes) {
    while ($row = mysqli_fetch_assoc($recentRes)) {
        $recentActivityRows[] = $row;
    }
}

// Fetch Blocker Info for Taluka (Highest utilization ratio)
$blockerInfo = null;
if ($isAll) {
    $blockerSql = "SELECT a.work_name, a.fund_source, a.sanctioned_amount, a.amount_spent,
            MAX(t.school_name) AS school_name,
            MAX(t.taluka_name) AS taluka_name,
            ROUND(100 * a.amount_spent / NULLIF(a.sanctioned_amount, 0), 1) AS utilization_percent,
            u.name AS hm_name
        FROM amount_utilization a
        LEFT JOIN talukas_school_data t
            ON a.work_name = t.work_name
            AND a.work_type = t.work_type
        LEFT JOIN users u ON u.id = a.hm_id
        WHERE a.sanctioned_amount > 0
        GROUP BY a.id
        ORDER BY (a.amount_spent / a.sanctioned_amount) DESC
        LIMIT 1";
} else {
    $blockerSql = "SELECT a.work_name, a.fund_source, a.sanctioned_amount, a.amount_spent,
            MAX(t.school_name) AS school_name,
            MAX(t.taluka_name) AS taluka_name,
            ROUND(100 * a.amount_spent / NULLIF(a.sanctioned_amount, 0), 1) AS utilization_percent,
            u.name AS hm_name
        FROM amount_utilization a
        JOIN talukas_school_data t
            ON a.work_name = t.work_name
            AND a.work_type = t.work_type
        LEFT JOIN users u ON u.id = a.hm_id
        WHERE a.sanctioned_amount > 0 AND t.is_active = 1 AND t.taluka_name = '" . $talukaEsc . "'
        GROUP BY a.id
        ORDER BY (a.amount_spent / a.sanctioned_amount) DESC
        LIMIT 1";
}
$blockerResult = mysqli_query($dbConn, $blockerSql);
if ($blockerResult) {
    $blockerInfo = mysqli_fetch_assoc($blockerResult);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Taluka Details - Samruddha Shala E-Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <link href="css/ceo_dashboard.css?v=3" rel="stylesheet">
    <style>
        body.ceo-dashboard-page { background: #f4f7fb; font-family: 'Outfit', sans-serif; }
        .dashboard-card { border-radius: 24px; border: 1px solid rgba(226, 232, 240, 0.95); box-shadow: 0 18px 36px rgba(61, 84, 117, 0.08); background: #ffffff; }
        .dashboard-card .card-title { font-size: 0.85rem; letter-spacing: 0.12em; text-transform: uppercase; color: #64748b; }
        .dashboard-card .stat-value { font-size: clamp(1.8rem, 2.5vw, 2.5rem); font-weight: 700; }
        .dashboard-card .stat-detail { font-size: 0.92rem; color: #475569; }
        .language-switcher .btn { min-width: 100px; }
        .chart-panel { min-height: 280px; position: relative; }
    </style>
</head>
<body class="ceo-dashboard-page">
    <div id="wrapper">
        <?php include '../include/sidebar.php'; ?>
        <div id="content">
            <div class="ceo-fixed-header">
                <?php include '../include/website_header.php'; ?>
            </div>

            <!-- Header Navbar styled exactly like CEO Dashboard -->
            <nav class="navbar navbar-expand-lg navbar-light p-3 mb-4" style="background: rgba(255,255,255,0.92); border-radius: 22px; box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);">
                <div class="container-fluid px-2">
                    <div class="d-flex align-items-center flex-grow-1 gap-3">
                        <button class="btn btn-light d-lg-none me-2 shadow-sm border-0 d-flex justify-content-center align-items-center" style="width: 42px; height: 42px; background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%); color: white;" type="button" id="mobileSidebarToggle" aria-label="Toggle Sidebar">
                            <i class="fa-solid fa-bars fs-6"></i>
                        </button>
                        <div>
                            <h4 class="fw-bold mb-1 text-truncate" id="pageMainHeader">Taluka Details</h4>
                            <p class="mb-0 text-muted" style="font-size: 0.95rem;" id="pageMainDescription" data-i18n="navDescription">School and project details loaded directly from the database.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap language-switcher">
                        <button id="langMarBtn" class="btn btn-sm btn-primary">मराठी</button>
                        <button id="langEngBtn" class="btn btn-sm btn-outline-primary">English</button>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-0">
                <!-- Back Button styled exactly like Image 2 -->
                <a href="ceo_dashboard.php" class="btn border mb-4 px-3 py-2 fw-semibold rounded-3 d-inline-flex align-items-center gap-2" style="box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%); color: #ffffff !important; border: none !important;">
                    <i class="fa-solid fa-arrow-left small" style="color: #ffffff !important;"></i>
                    <span data-i18n="backToDashboard" style="color: #ffffff !important;">Back to District Dashboard</span>
                </a>

                <!-- 4 KPI Cards aligned styled exactly like CEO Dashboard -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card dashboard-card p-4 h-100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width:52px; height:52px;">
                                    <i class="fa-solid fa-school fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small mb-1" data-i18n="summarySchoolsLabel">Total Schools</div>
                                    <div class="fw-bold fs-4"><?php echo (int)($summary['school_count'] ?? count($schools)); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card dashboard-card p-4 h-100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-success text-white rounded-3 d-flex align-items-center justify-content-center" style="width:52px; height:52px;">
                                    <i class="fa-solid fa-coins fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small mb-1" data-i18n="summaryBudgetLabel">Total Budget</div>
                                    <div class="fw-bold fs-4">₹<?php echo number_format((float)($summary['total_sanctioned'] ?? 0), 2); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card dashboard-card p-4 h-100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-info text-white rounded-3 d-flex align-items-center justify-content-center" style="width:52px; height:52px;">
                                    <i class="fa-solid fa-check-circle fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small mb-1" data-i18n="summaryCompletedLabel">Completed Projects</div>
                                    <div class="fw-bold fs-4"><?php echo $completedTasks; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card dashboard-card p-4 h-100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-warning text-white rounded-3 d-flex align-items-center justify-content-center" style="width:52px; height:52px;">
                                    <i class="fa-solid fa-clock fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small mb-1" data-i18n="summaryPendingLabel">Pending Works</div>
                                    <div class="fw-bold fs-4"><?php echo $pendingTasks; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section: Side-by-Side Fund Overview & Fund Distribution -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card dashboard-card p-4 h-100">
                            <h5 class="fw-bold mb-3 text-dark border-bottom pb-2" data-i18n="fundReceiptTitle"><i class="fa-solid fa-chart-simple text-primary me-2"></i>निधीचा आढावा</h5>
                            <div class="chart-panel rounded-4 p-2 bg-white shadow-sm" style="min-height:280px;">
                                <canvas id="fundReceiptChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card dashboard-card p-4 h-100">
                            <h5 class="fw-bold mb-3 text-dark border-bottom pb-2" data-i18n="fundDistributionTitle"><i class="fa-solid fa-chart-pie text-success me-2"></i>निधी वाटप विवरण</h5>
                            <div class="chart-panel rounded-4 p-2 bg-white shadow-sm" style="min-height:280px;">
                                <canvas id="fundDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity & Blocker Info Section: Side-by-Side -->
                <div class="row g-4 mb-4">
                    <!-- Recent Activity -->
                    <div class="col-lg-7">
                        <div class="card dashboard-card p-4 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-2">
                                <h5 class="fw-bold mb-0 text-dark" data-i18n="recentTitle"><i class="fa-solid fa-arrow-trend-up text-primary me-2"></i>रिसेंट ॲक्टिव्हिटी</h5>
                                <span class="badge bg-primary text-white" data-i18n="latestBadge">नवीनतम</span>
                            </div>
                            <?php if (empty($recentActivityRows)): ?>
                                <div class="py-5 text-center text-muted" data-i18n="noRecent">रिसेंट ॲक्टिव्हिटी उपलब्ध नाही.</div>
                            <?php else: ?>
                                <div class="list-group list-group-flush" style="max-height:300px; overflow-y:auto;">
                                    <?php foreach ($recentActivityRows as $item): ?>
                                        <div class="list-group-item border-0 px-0 py-3" style="border-bottom: 1px solid rgba(226, 232, 240, 0.5) !important;">
                                            <div class="d-flex align-items-start justify-content-between gap-3">
                                                <div>
                                                    <div class="fw-semibold text-dark"><?php echo htmlspecialchars($item['work_name'] ?: 'कार्य उपलब्ध नाही'); ?></div>
                                                    <div class="text-muted small mt-1"><?php echo htmlspecialchars($item['school_name'] ?: ($isAll ? 'All District' : $taluka)); ?></div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-semibold text-success">₹<?php echo number_format((float)$item['amount_spent'], 2); ?></div>
                                                    <div class="text-muted small mt-1"><?php echo htmlspecialchars(date('d MMM, Y', strtotime($item['expense_date']))); ?></div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-2 gap-2">
                                                <span class="badge bg-light text-dark fw-bold" style="font-size:0.75rem;"><i class="fa-solid fa-user me-1 text-secondary"></i><?php echo htmlspecialchars($item['hm_name'] ?: 'HM'); ?></span>
                                                <span class="text-secondary small fw-semibold"><?php echo htmlspecialchars($item['fund_source'] ?: 'Funding'); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Blocker Info -->
                    <div class="col-lg-5">
                        <div class="card dashboard-card p-4 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-2">
                                <h5 class="fw-bold mb-0 text-dark" data-i18n="blockerTitle"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>ब्लॉकर माहिती</h5>
                                <span class="badge bg-danger text-white" data-i18n="highBadge">उच्च</span>
                            </div>
                            <?php if (empty($blockerInfo)): ?>
                                <div class="py-5 text-center text-muted" data-i18n="noBlocker">ब्लॉकर माहिती उपलब्ध नाही.</div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <div class="text-muted small mb-1" data-i18n="blockerWorkLabel">कार्य</div>
                                    <div class="fw-semibold text-dark"><?php echo htmlspecialchars($blockerInfo['work_name'] ?: 'कार्य उपलब्ध नाही'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small mb-1" data-i18n="blockerSchoolLabel">शाळा</div>
                                    <div class="fw-semibold text-dark"><?php echo htmlspecialchars($blockerInfo['school_name'] ?: 'N/A'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small mb-1" data-i18n="blockerTalukaLabel">तालुका</div>
                                    <div class="fw-semibold text-dark"><?php echo htmlspecialchars($blockerInfo['taluka_name'] ?: 'N/A'); ?></div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-3 text-center">
                                            <div class="text-muted small" data-i18n="blockerSanctionLabel">अनुदान</div>
                                            <div class="fw-semibold text-dark">₹<?php echo number_format((float)$blockerInfo['sanctioned_amount'], 2); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-3 text-center">
                                            <div class="text-muted small" data-i18n="blockerSpentLabel">खर्च</div>
                                            <div class="fw-semibold text-success">₹<?php echo number_format((float)$blockerInfo['amount_spent'], 2); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between gap-2 mt-auto pt-2">
                                    <span class="badge bg-warning text-dark fw-bold">उपयोग %: <?php echo number_format((float)$blockerInfo['utilization_percent'], 1); ?>%</span>
                                    <span class="text-muted small fw-semibold">HM: <?php echo htmlspecialchars($blockerInfo['hm_name'] ?: 'N/A'); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Grid Details Section -->
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card p-3 shadow-sm" style="border-radius:24px;">
                            <h6 class="fw-bold mb-3 p-1 text-primary" data-i18n="schoolListHeader">List of Schools</h6>
                            <ul class="list-group list-group-flush" style="max-height:420px; overflow:auto;">
                                <?php foreach ($schools as $s): ?>
                                    <li class="list-group-item text-dark fw-semibold py-2" style="font-size:0.95rem; border-color: rgba(226, 232, 240, 0.5);"><i class="fa-solid fa-graduation-cap me-2 text-secondary small"></i><?php echo htmlspecialchars($s); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card p-3 shadow-sm" style="border-radius:24px;">
                            <h6 class="fw-bold mb-3 p-1 text-primary" data-i18n="projectSummaryHeader">Project Summary</h6>
                            <div class="list-group list-group-flush" style="max-height:420px; overflow:auto;">
                                <?php foreach ($workSummary as $w): ?>
                                    <div class="list-group-item py-2 d-flex justify-content-between align-items-center" style="border-color: rgba(226, 232, 240, 0.5);">
                                        <div>
                                            <strong class="text-dark" style="font-size:0.95rem;"><?php echo htmlspecialchars($w['work_name']); ?></strong>
                                            <div class="small text-muted fw-semibold mt-1"><i class="fa-solid fa-tag me-1 text-secondary"></i><?php echo htmlspecialchars($w['work_type']); ?></div>
                                        </div>
                                        <div class="text-end fw-semibold text-dark" style="font-size:0.9rem;">
                                            <div><span data-i18n="schoolPrefix">Schools:</span> <span><?php echo (int)($w['schools'] ?? 0); ?></span></div>
                                            <div class="text-success mt-1"><span data-i18n="spentPrefix">Spent:</span> <span>₹<?php echo number_format((float)($w['spent'] ?? 0),2); ?></span></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="ceo-fixed-footer">
                <?php include '../include/website_footer.php'; ?>
            </div>
        </div>
    </div>

    <!-- Load Chart.js script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const talukaTranslations = {
            'आजरा': { mar: 'आजरा', eng: 'Aajra' },
            'करवीर': { mar: 'करवीर', eng: 'Karveer' },
            'कागल': { mar: 'कागल', eng: 'Kagal' },
            'गगनबावडा': { mar: 'गगनबावडा', eng: 'Gaganbawda' },
            'गडहिंग्लज': { mar: 'गडहिंग्लज', eng: 'Gadhinglaj' },
            'चंदगड': { mar: 'चंदगड', eng: 'Chandgad' },
            'पन्हाळा': { mar: 'पन्हाळा', eng: 'Panhala' },
            'भुदरगड': { mar: 'भुदरगड', eng: 'Bhudargad' },
            'राधानगरी': { mar: 'राधानगरी', eng: 'Radhanagari' },
            'शाहुवाडी': { mar: 'शाहुवाडी', eng: 'Shahuwadi' },
            'शिरोळ': { mar: 'शिरोळ', eng: 'Shirol' },
            'हातकणंगले': { mar: 'हातकणंगले', eng: 'Hatkanangle' },
            'ALL': { mar: 'सर्व तालुके', eng: 'All Talukas' }
        };

        const langStrings = {
            backToDashboard: { mar: 'मुख्य डॅशबोर्डवर परत जा (Back to District)', eng: 'Back to District Dashboard' },
            navDescription: { mar: 'डेटाबेसमधून थेट शाळा आणि प्रकल्पांची माहिती.', eng: 'School and project details loaded directly from the database.' },
            summarySchoolsLabel: { mar: 'एकूण शाळा', eng: 'Total Schools' },
            summaryBudgetLabel: { mar: 'एकूण बजेट', eng: 'Total Budget' },
            summaryCompletedLabel: { mar: 'पूर्ण झालेले प्रकल्प', eng: 'Completed Projects' },
            summaryPendingLabel: { mar: 'प्रलंबित कामे', eng: 'Pending Works' },
            schoolListHeader: { mar: 'शाळांची यादी', eng: 'List of Schools' },
            projectSummaryHeader: { mar: 'प्रकल्प सारांश', eng: 'Project Summary' },
            schoolPrefix: { mar: 'शाळा:', eng: 'Schools:' },
            spentPrefix: { mar: 'खर्च:', eng: 'Spent:' },
            fundReceiptTitle: { mar: 'निधीचा आढावा', eng: 'Fund Overview' },
            fundDistributionTitle: { mar: 'निधी वाटप विवरण', eng: 'Fund Allocation Details' },
            recentTitle: { mar: 'रिसेंट ॲक्टिव्हिटी', eng: 'Recent Activity' },
            latestBadge: { mar: 'नवीनतम', eng: 'Latest' },
            noRecent: { mar: 'रिसेंट ॲक्टिव्हिटी उपलब्ध नाही.', eng: 'No recent activity available.' },
            blockerTitle: { mar: 'ब्लॉकर माहिती', eng: 'Blocker Info' },
            highBadge: { mar: 'उच्च', eng: 'High' },
            noBlocker: { mar: 'ब्लॉकर माहिती उपलब्ध नाही.', eng: 'No blocker information available.' },
            blockerWorkLabel: { mar: 'कार्य', eng: 'Work' },
            blockerSchoolLabel: { mar: 'शाळा', eng: 'School' },
            blockerTalukaLabel: { mar: 'तालुका', eng: 'Taluka' },
            blockerSanctionLabel: { mar: 'अनुदान', eng: 'Sanction' },
            blockerSpentLabel: { mar: 'खर्च', eng: 'Spent' },
            fundReceivedLabel: { mar: 'प्राप्त निधी', eng: 'Funds Received' },
            fundSpentLabel: { mar: 'वितरित निधी', eng: 'Funds Spent' },
            fundDistributionBadge: { mar: 'वाटप', eng: 'Allocation' },
            sideDashboard: { mar: 'CEO डॅशबोर्ड', eng: 'CEO Dashboard' },
            sideCreateStages: { mar: 'टप्पे तयार करा', eng: 'Create Stages' },
            sideStagesReport: { mar: 'टप्प्यांचा अहवाल', eng: 'Stages Report' },
            sideWorkReport: { mar: 'कामाचा अहवाल', eng: 'Work Report' },
            sideFundingReport: { mar: 'निधी अहवाल', eng: 'Funding Report' },
            sideCreateUser: { mar: 'युझर तयार करा', eng: 'Create User' },
            sideFundUtil: { mar: 'निधी वापर तपशील', eng: 'Fund Utilization Details' }
        };

        const talukaRaw = <?php echo json_encode($taluka); ?>;

        const fundReceiptLabels = <?php echo json_encode(array_column($fundTimeline, 'month_label')); ?>;
        const fundReceiptSanctioned = <?php echo json_encode(array_map('floatval', array_column($fundTimeline, 'total_sanctioned'))); ?>;
        const fundReceiptSpent = <?php echo json_encode(array_map('floatval', array_column($fundTimeline, 'total_spent'))); ?>;
        const fundDistributionLabels = <?php echo json_encode(array_column($fundDistribution, 'fund_source')); ?>;
        const fundDistributionValues = <?php echo json_encode(array_map('floatval', array_column($fundDistribution, 'total_spent'))); ?>;

        let fundReceiptChart = null;
        let fundDistributionChart = null;

        function createCharts() {
            if (typeof Chart === 'undefined') return;

            const receiptCtx = document.getElementById('fundReceiptChart');
            if (receiptCtx) {
                const currentLang = localStorage.getItem('ceoLang') || 'mar';
                fundReceiptChart = new Chart(receiptCtx, {
                    type: 'bar',
                    data: {
                        labels: fundReceiptLabels,
                        datasets: [
                            {
                                label: langStrings.fundReceivedLabel[currentLang] || 'Received',
                                data: fundReceiptSanctioned,
                                backgroundColor: 'rgba(59, 130, 246, 0.85)'
                            },
                            {
                                label: langStrings.fundSpentLabel[currentLang] || 'Spent',
                                data: fundReceiptSpent,
                                backgroundColor: 'rgba(16, 185, 129, 0.85)'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

            const distributionCtx = document.getElementById('fundDistributionChart');
            if (distributionCtx) {
                const currentLang = localStorage.getItem('ceoLang') || 'mar';
                fundDistributionChart = new Chart(distributionCtx, {
                    type: 'doughnut',
                    data: {
                        labels: fundDistributionLabels,
                        datasets: [{
                            label: langStrings.fundDistributionBadge[currentLang] || 'Allocation',
                            data: fundDistributionValues,
                            backgroundColor: ['#2563eb', '#0ea5e9', '#22c55e', '#f59e0b', '#ec4899', '#8b5cf6']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        }

        function updateChartLanguage(lang) {
            if (fundReceiptChart) {
                fundReceiptChart.data.datasets[0].label = langStrings.fundReceivedLabel[lang];
                fundReceiptChart.data.datasets[1].label = langStrings.fundSpentLabel[lang];
                fundReceiptChart.update();
            }
            if (fundDistributionChart) {
                fundDistributionChart.data.datasets[0].label = langStrings.fundDistributionBadge[lang];
                fundDistributionChart.update();
            }
        }

        function setLanguagePage(lang) {
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (langStrings[key] && langStrings[key][lang]) {
                    el.textContent = langStrings[key][lang];
                }
            });

            // Set main title containing translated taluka name
            const translatedTaluka = talukaTranslations[talukaRaw] ? talukaTranslations[talukaRaw][lang] : talukaRaw;
            const headerEl = document.getElementById('pageMainHeader');
            if (headerEl) {
                if (lang === 'mar') {
                    headerEl.textContent = translatedTaluka + ' डॅशबोर्ड';
                } else {
                    headerEl.textContent = translatedTaluka + ' Dashboard';
                }
            }

            // Set active class on language toggle buttons
            document.getElementById('langMarBtn').classList.toggle('btn-primary', lang === 'mar');
            document.getElementById('langMarBtn').classList.toggle('btn-outline-primary', lang !== 'mar');
            document.getElementById('langEngBtn').classList.toggle('btn-primary', lang === 'eng');
            document.getElementById('langEngBtn').classList.toggle('btn-outline-primary', lang !== 'eng');

            updateChartLanguage(lang);
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Mobile sidebar toggle setup
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            if (mobileSidebarToggle && sidebar) {
                mobileSidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('active');
                });
                
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 991 && sidebar.classList.contains('active')) {
                        if (!sidebar.contains(e.target) && e.target !== mobileSidebarToggle) {
                            sidebar.classList.remove('active');
                        }
                    }
                });
            }

            document.getElementById('langMarBtn').addEventListener('click', () => {
                localStorage.setItem('ceoLang', 'mar');
                setLanguagePage('mar');
            });
            document.getElementById('langEngBtn').addEventListener('click', () => {
                localStorage.setItem('ceoLang', 'eng');
                setLanguagePage('eng');
            });

            // Initialize charts
            createCharts();

            // Read language preference from URL param, fallback to localStorage or 'mar'
            const urlParams = new URLSearchParams(window.location.search);
            const saved = urlParams.get('lang') || localStorage.getItem('ceoLang') || 'mar';
            setLanguagePage(saved);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
