<?php
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Restrict access to CEO only
if (!isset($_SESSION['role']) || strtoupper($_SESSION['role']) !== 'CEO') {
    header("Location: ../login.php");
    exit();
}

$host = "82.25.121.144";
$username = "u196817721_S_Eportal_U";
$password = "Sam_shalaEportal@2026";
$database = "u196817721_S_shalaEportal";

$dbConn = mysqli_connect($host, $username, $password, $database);
if (!$dbConn) {
    die("<h3 style='color:red'>❌ Database Connection Failed<br>Error: " . mysqli_connect_error() . "</h3>");
}
mysqli_set_charset($dbConn, "utf8");

// HM amount utilization summary
$summarySql = "SELECT
        COUNT(*) AS record_count,
        SUM(sanctioned_amount) AS total_sanctioned,
        SUM(amount_spent) AS total_spent,
        SUM(amount_spent > sanctioned_amount) AS over_utilized,
        SUM(amount_spent >= sanctioned_amount * 0.75 AND amount_spent <= sanctioned_amount) AS close_to_limit,
        SUM(amount_spent < sanctioned_amount * 0.75) AS on_track
    FROM amount_utilization";
$summaryResult = mysqli_query($dbConn, $summarySql);
$summary = mysqli_fetch_assoc($summaryResult) ?: [];

$total_utilization_records = (int)($summary['record_count'] ?? 0);
$total_sanctioned = (float)($summary['total_sanctioned'] ?? 0.0);
$total_spent = (float)($summary['total_spent'] ?? 0.0);
$average_utilization = $total_sanctioned > 0 ? round(($total_spent / $total_sanctioned) * 100, 2) : 0;
$over_utilized_count = (int)($summary['over_utilized'] ?? 0);
$close_to_limit_count = (int)($summary['close_to_limit'] ?? 0);
$on_track_count = (int)($summary['on_track'] ?? 0);

// Funding source breakdown
$fundingBreakdown = [];
$fundSql = "SELECT fund_source, SUM(sanctioned_amount) AS sanctioned, SUM(amount_spent) AS spent, COUNT(*) AS records
    FROM amount_utilization
    GROUP BY fund_source
    ORDER BY sanctioned DESC";
$fundResult = mysqli_query($dbConn, $fundSql);
while ($row = mysqli_fetch_assoc($fundResult)) {
    $fundingBreakdown[] = $row;
}

// Work type utilization breakdown
$workTypeBreakdown = [];
$workTypeSql = "SELECT work_type, SUM(sanctioned_amount) AS sanctioned, SUM(amount_spent) AS spent, COUNT(*) AS records
    FROM amount_utilization
    GROUP BY work_type
    ORDER BY spent DESC";
$workTypeResult = mysqli_query($dbConn, $workTypeSql);
while ($row = mysqli_fetch_assoc($workTypeResult)) {
    $workTypeBreakdown[] = $row;
}

// Taluka school coverage from talukas_school_data
$talukaCoverage = [];
$talukaSql = "SELECT taluka_name,
        COUNT(DISTINCT school_name) AS schools,
        GROUP_CONCAT(DISTINCT work_type ORDER BY work_type SEPARATOR ', ') AS work_types
    FROM talukas_school_data
    WHERE is_active = 1
    GROUP BY taluka_name
    ORDER BY schools DESC, taluka_name ASC";
$talukaResult = mysqli_query($dbConn, $talukaSql);
if (!$talukaResult) {
    die("<h3 style='color:red'>DB Error taluka query: " . mysqli_error($dbConn) . "</h3>");
}
while ($row = mysqli_fetch_assoc($talukaResult)) {
    $talukaCoverage[] = $row;
}

$talukaTotalsSql = "SELECT
        COUNT(DISTINCT school_name) AS total_schools,
        COUNT(DISTINCT taluka_name) AS total_talukas
    FROM talukas_school_data
    WHERE is_active = 1";
$talukaTotalsResult = mysqli_query($dbConn, $talukaTotalsSql);
if (!$talukaTotalsResult) {
    die("<h3 style='color:red'>DB Error taluka totals query: " . mysqli_error($dbConn) . "</h3>");
}
$talukaTotals = mysqli_fetch_assoc($talukaTotalsResult) ?: [];
$totalTalukaSchools = (int)($talukaTotals['total_schools'] ?? 0);
$totalTalukas = (int)($talukaTotals['total_talukas'] ?? 0);

// Recent activity from HM amount utilization
$recentActivityRows = [];
$recentSql = "SELECT a.work_name, a.fund_source, a.sanctioned_amount, a.amount_spent, a.expense_date,
        u.name AS hm_name
    FROM amount_utilization a
    LEFT JOIN users u ON u.id = a.hm_id
    ORDER BY a.id DESC
    LIMIT 6";
$recentSql = "SELECT a.work_name, a.fund_source, a.sanctioned_amount, a.amount_spent, a.expense_date,
        u.name AS hm_name
    FROM amount_utilization a
    LEFT JOIN users u ON u.id = a.hm_id
    ORDER BY a.id DESC
    LIMIT 6";
$recentResult = mysqli_query($dbConn, $recentSql);
if (!$recentResult) {
    die('<h3 style="color:red">DB Error in recent activity query: ' . mysqli_error($dbConn) . '</h3>');
}
while ($row = mysqli_fetch_assoc($recentResult)) {
    $recentActivityRows[] = $row;
}

// Blocker info based on highest utilization ratio
$blockerInfo = null;
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
$blockerResult = mysqli_query($dbConn, $blockerSql);
if ($blockerResult) {
    $blockerInfo = mysqli_fetch_assoc($blockerResult);
} else {
    die('<h3 style="color:red">DB Error in blocker query: ' . mysqli_error($dbConn) . '</h3>');
}

// CEO task and work master snapshot
$taskSummary = [];
$taskSql = "SELECT COUNT(*) AS total_tasks,
        SUM(status = 'Pending') AS pending_tasks,
        SUM(status = 'Completed') AS completed_tasks
    FROM ceo_create_tasks";
$taskResult = mysqli_query($dbConn, $taskSql);
$taskSummary = mysqli_fetch_assoc($taskResult) ?: [];
$totalTasks = (int)($taskSummary['total_tasks'] ?? 0);
$pendingTasks = (int)($taskSummary['pending_tasks'] ?? 0);
$completedTasks = (int)($taskSummary['completed_tasks'] ?? 0);

$workSummary = [];
$workSql = "SELECT COUNT(*) AS total_works,
        SUM(status = 'Pending') AS pending_works,
        SUM(status = 'Completed') AS completed_works
    FROM work_master";
$workResult = mysqli_query($dbConn, $workSql);
$workSummary = mysqli_fetch_assoc($workResult) ?: [];
$totalWorks = (int)($workSummary['total_works'] ?? 0);
$pendingWorks = (int)($workSummary['pending_works'] ?? 0);
$completedWorks = (int)($workSummary['completed_works'] ?? 0);

// Latest HM utilization submissions
$recentUtilizationRows = [];
$recentUtilizationSql = "SELECT a.*, u.name AS hm_name
    FROM amount_utilization a
    LEFT JOIN users u ON u.id = a.hm_id
    ORDER BY a.id DESC
    LIMIT 10";
$recentUtilizationResult = mysqli_query($dbConn, $recentUtilizationSql);
if (!$recentUtilizationResult) {
    die('<h3 style="color:red">DB Error in latest utilization query: ' . mysqli_error($dbConn) . '</h3>');
}
while ($row = mysqli_fetch_assoc($recentUtilizationResult)) {
    $recentUtilizationRows[] = $row;
}

$fundTimeline = [];
$fundTimelineSql = "SELECT DATE_FORMAT(expense_date, '%b %Y') AS month_label,
        SUM(sanctioned_amount) AS total_sanctioned,
        SUM(amount_spent) AS total_spent
    FROM amount_utilization
    WHERE expense_date IS NOT NULL
    GROUP BY YEAR(expense_date), MONTH(expense_date)
    ORDER BY YEAR(expense_date), MONTH(expense_date)
    LIMIT 6";
$fundTimelineResult = mysqli_query($dbConn, $fundTimelineSql);
if ($fundTimelineResult) {
    while ($row = mysqli_fetch_assoc($fundTimelineResult)) {
        $fundTimeline[] = $row;
    }
}

$fundDistribution = [];
$fundDistributionSql = "SELECT IFNULL(fund_source, 'Unknown') AS fund_source,
        SUM(amount_spent) AS total_spent
    FROM amount_utilization
    GROUP BY fund_source
    ORDER BY total_spent DESC
    LIMIT 6";
$fundDistributionResult = mysqli_query($dbConn, $fundDistributionSql);
if ($fundDistributionResult) {
    while ($row = mysqli_fetch_assoc($fundDistributionResult)) {
        $fundDistribution[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEO Dashboard - Samruddha Shala E-Portal</title>
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
        .chart-panel { min-height: 320px; }
        .badge-status { border-radius: 999px; text-transform: uppercase; letter-spacing: 0.04em; font-size: 0.72rem; font-weight: 700; padding: 0.55rem 0.9rem; }
        .badge-status-success { background: #dcfce7; color: #166534; }
        .badge-status-warning { background: #fef3c7; color: #92400e; }
        .badge-status-danger { background: #fee2e2; color: #991b1b; }
        .nav-summary-pill { border-radius: 999px; padding: 0.55rem 1rem; font-size: 0.82rem; font-weight: 700; }
        .chart-legend-key { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 0.65rem; }
        .chart-legend-item { display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.65rem; }
        .chart-legend-item span { color: #334155; font-size: 0.9rem; }
        .table-responsive { min-height: 1px; }
        .table thead th { background: #f8fafc; border-bottom: 0; }
        .table tbody tr:hover { background: rgba(59, 130, 246, 0.04); }
        .taluka-button {
            min-height: 142px;
            border-radius: 24px;
            border: 1px solid rgba(148, 163, 184, 0.22);
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            color: #0f172a;
        }
        .taluka-button:hover {
            transform: translateY(-2px);
            border-color: rgba(59, 130, 246, 0.4);
            background: #f8fbff;
        }
        .taluka-button-active {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: #ffffff;
        }
        .taluka-button-active .taluka-icon {
            background: rgba(255,255,255,0.18);
            color: #ffffff;
        }
        .taluka-button-inner {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        .taluka-icon {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
        }
        .taluka-button .taluka-icon {
            background: #eff6ff;
            color: #2563eb;
        }
        .taluka-label {
            font-size: 0.95rem;
            line-height: 1.3;
        }
        .taluka-section-icon {
            width: 56px;
            height: 56px;
            border-radius: 18px;
        }
        .language-switcher .btn {
            min-width: 100px;
        }
        .nav-tabs {
            border-bottom: 0;
            gap: 0.75rem;
        }
        .nav-tabs .nav-link {
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 14px;
            margin-right: 0.5rem;
            color: #334155;
            background: #f8fafc;
            padding: 0.85rem 1.15rem;
            min-height: 50px;
        }
        .nav-tabs .nav-link.active {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.15);
        }
        .tab-content .tab-pane {
            padding-top: 1rem;
        }
        .tab-card .chart-panel {
            min-height: 340px;
        }
        .tab-card .list-group-item {
            border: 0;
            padding-left: 0;
            padding-right: 0;
            background: transparent;
        }
        .tab-card .list-group-item + .list-group-item {
            margin-top: 0.65rem;
        }
        .tab-card .list-group {
            gap: 0.75rem;
        }
        @media (max-width: 991px) {
            .chart-panel { min-height: 260px; }
            .nav-tabs .nav-link { width: 100%; text-align: center; margin-right: 0; }
        }
    </style>
</head>
<body class="ceo-dashboard-page">
    <div id="wrapper">
        <?php include '../include/sidebar.php'; ?>
        <div id="content">
            <div class="ceo-fixed-header">
                <?php include '../include/website_header.php'; ?>
            </div>

            <nav class="navbar navbar-expand-lg navbar-light p-3 mb-4" style="background: rgba(255,255,255,0.92); border-radius: 22px; box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);">
                <div class="container-fluid px-2">
                    <div class="d-flex align-items-center flex-grow-1 gap-3">
                        <button class="btn btn-light d-lg-none me-2 shadow-sm border-0 d-flex justify-content-center align-items-center" style="width: 42px; height: 42px; background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%); color: white;" type="button" id="mobileSidebarToggle" aria-label="Toggle Sidebar">
                            <i class="fa-solid fa-bars fs-6"></i>
                        </button>
                        <div>
                            <h4 class="fw-bold mb-1 text-truncate" id="pageMainHeader" data-i18n="navTitle">CEO Dashboard</h4>
                            <p class="mb-0 text-muted" style="font-size: 0.95rem;" id="pageMainDescription" data-i18n="navDescription">A live taluka dashboard built from the E-Portal database.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap language-switcher">
                        <button id="langMarBtn" class="btn btn-sm btn-primary">मराठी</button>
                        <button id="langEngBtn" class="btn btn-sm btn-outline-primary">English</button>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-0">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card dashboard-card p-4 h-100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width:52px; height:52px;">
                                    <i class="fa-solid fa-school fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small mb-1" data-i18n="summarySchoolsLabel">एकूण शाळा</div>
                                    <div class="fw-bold fs-4"><?php echo number_format($totalTalukaSchools); ?></div>
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
                                    <div class="text-muted small mb-1" data-i18n="summaryBudgetLabel">एकूण बजेट</div>
                                    <div class="fw-bold fs-4">₹<?php echo number_format($total_sanctioned, 2); ?></div>
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
                                    <div class="text-muted small mb-1" data-i18n="summaryCompletedLabel">पूर्ण झालेले प्रकल्प</div>
                                    <div class="fw-bold fs-4"><?php echo number_format($completedWorks); ?></div>
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
                                    <div class="text-muted small mb-1" data-i18n="summaryPendingLabel">प्रलंबित कामे</div>
                                    <div class="fw-bold fs-4"><?php echo number_format($pendingWorks); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card dashboard-card p-4 tab-card h-100">
                            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1" data-i18n="fundTabSectionTitle">निधीचा आढावा आणि वाटप</h5>
                                    <p class="text-muted mb-0" data-i18n="fundTabSectionDescription">निधीचा आढावा व वाटप डेटाबेसमधून पाहा.</p>
                                </div>
                                <ul class="nav nav-tabs mb-0" id="fundTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="receipt-tab" data-bs-toggle="tab" data-bs-target="#receipt-tab-pane" type="button" role="tab" aria-controls="receipt-tab-pane" aria-selected="true" data-i18n="fundReceiptTab">निधीचा आढावा</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="distribution-tab" data-bs-toggle="tab" data-bs-target="#distribution-tab-pane" type="button" role="tab" aria-controls="distribution-tab-pane" aria-selected="false" data-i18n="fundDistributionTab">निधी वाटप</button>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="receipt-tab-pane" role="tabpanel" aria-labelledby="receipt-tab">
                                    <div class="row g-4 align-items-stretch">
                                        <div class="col-lg-8">
                                            <div class="chart-panel rounded-4 p-3 bg-white shadow-sm">
                                                <canvas id="fundReceiptChart"></canvas>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="p-4 bg-light rounded-4 h-100 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="text-muted small mb-2" data-i18n="fundReceiptSummaryTitle">Receipt Summary</div>
                                                    <div class="fw-semibold fs-4">₹<?php echo number_format(array_sum(array_map('floatval', array_column($fundTimeline, 'total_sanctioned'))), 2); ?></div>
                                                    <div class="text-muted mt-1" data-i18n="fundReceiptSummaryText">एकूण प्राप्त निधी</div>
                                                </div>
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <span class="text-muted" data-i18n="fundReceiptChartLabel1">Received</span>
                                                        <span class="fw-semibold">₹<?php echo number_format(array_sum(array_map('floatval', array_column($fundTimeline, 'total_sanctioned'))), 2); ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="text-muted" data-i18n="fundReceiptChartLabel2">Spent</span>
                                                        <span class="fw-semibold">₹<?php echo number_format(array_sum(array_map('floatval', array_column($fundTimeline, 'total_spent'))), 2); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="distribution-tab-pane" role="tabpanel" aria-labelledby="distribution-tab">
                                    <div class="row g-4 align-items-stretch">
                                        <div class="col-lg-6">
                                            <div class="chart-panel rounded-4 p-3 bg-white shadow-sm">
                                                <canvas id="fundDistributionChart"></canvas>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="p-4 bg-light rounded-4 h-100">
                                                <div class="text-muted small mb-3" data-i18n="fundDistributionSummaryTitle">Distribution Summary</div>
                                                <div class="list-group list-group-flush">
                                                    <?php foreach ($fundDistribution as $distribution): ?>
                                                        <div class="list-group-item d-flex justify-content-between align-items-center border rounded-3 p-3 mb-2 bg-white">
                                                            <span><?php echo htmlspecialchars($distribution['fund_source']); ?></span>
                                                            <span class="fw-semibold">₹<?php echo number_format((float)$distribution['total_spent'], 2); ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card dashboard-card p-4">
                            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="taluka-section-icon bg-primary text-white d-flex align-items-center justify-content-center">
                                        <i class="fa-solid fa-map-location-dot fs-5"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1" id="talukaSectionTitle" data-i18n="talukaTitle">तालुक्यानुसार माहिती</h5>
                                        <p class="text-muted mb-0" id="talukaSectionDescription" data-i18n="talukaDescription">सक्रिय तालुक्यांची माहिती आणि शाळा मॅपिंग डेटाबेसवरून.</p>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted small mb-1" data-i18n="talukaSummaryLabel">संपूर्ण सक्रिय तालुक्यांची संख्या</div>
                                    <div class="fw-bold fs-5"><?php echo number_format($totalTalukas); ?> <span class="text-secondary" data-i18n="talukaLabel">तालुके</span></div>
                                </div>
                            </div>
                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-6 g-3">
                                <div class="col">
                                    <button type="button" class="btn taluka-button taluka-button-active w-100 py-4" data-lang-mar="सर्व तालुके" data-lang-eng="All Talukas" id="talukaAllButton">
                                        <div class="taluka-button-inner">
                                            <div class="taluka-icon bg-primary text-white mb-3"><i class="fa-solid fa-map"></i></div>
                                            <span class="taluka-label fw-semibold" data-default="सर्व तालुके">सर्व तालुके</span>
                                        </div>
                                    </button>
                                </div>
                                <?php foreach ($talukaCoverage as $taluka): ?>
                                    <div class="col">
                                        <button type="button" class="btn taluka-button w-100 py-4" data-lang-mar="<?php echo htmlspecialchars($taluka['taluka_name'], ENT_QUOTES); ?>" data-lang-eng="<?php echo htmlspecialchars($taluka['taluka_name'], ENT_QUOTES); ?>">
                                            <div class="taluka-button-inner">
                                                <div class="taluka-icon bg-light text-primary mb-3"><i class="fa-solid fa-map"></i></div>
                                                <span class="taluka-label fw-semibold"><?php echo htmlspecialchars($taluka['taluka_name']); ?></span>
                                            </div>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-7">
                        <div class="card dashboard-card p-4 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1" id="recentTitle" data-i18n="recentTitle">रिसेंट अॅक्टिव्हिटी</h5>
                                    <p class="text-muted mb-0" id="recentDescription" data-i18n="recentDescription">ताज्या HM खर्च नोंदी आणि शाळा/तालुका माहिती.</p>
                                </div>
                                <span class="badge bg-primary text-white" id="recentBadge" data-i18n="latestBadge">नवीनतम</span>
                            </div>
                            <?php if (empty($recentActivityRows)): ?>
                                <div class="py-5 text-center text-muted" id="recentEmpty" data-i18n="noRecent">रिसेंट अॅक्टिव्हिटी उपलब्ध नाही.</div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recentActivityRows as $item): ?>
                                        <div class="list-group-item border-0 px-0 py-3">
                                            <div class="d-flex align-items-start justify-content-between gap-3">
                                                <div>
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($item['work_name'] ?: 'कार्य उपलब्ध नाही'); ?></div>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($item['fund_source'] ?: 'Funding'); ?></div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-semibold">₹<?php echo number_format((float)$item['amount_spent'], 2); ?></div>
                                                    <div class="text-muted small"><?php echo htmlspecialchars(date('d MMM, Y', strtotime($item['expense_date']))); ?></div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-2 gap-2">
                                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($item['hm_name'] ?: 'HM'); ?></span>
                                                <span class="text-muted small"><?php echo htmlspecialchars($item['fund_source'] ?: 'Funding'); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card dashboard-card p-4 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1" id="blockerTitle" data-i18n="blockerTitle">ब्लॉकर माहिती</h5>
                                    <p class="text-muted mb-0" id="blockerDescription" data-i18n="blockerDescription">उच्च उपयोग झालेल्या प्रकल्पांबद्दलची माहिती.</p>
                                </div>
                                <span class="badge bg-danger text-white" id="blockerBadge" data-i18n="highBadge">उच्च</span>
                            </div>
                            <?php if (empty($blockerInfo)): ?>
                                <div class="py-5 text-center text-muted" id="blockerEmpty" data-i18n="noBlocker">ब्लॉकर माहिती उपलब्ध नाही.</div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <div class="text-muted small mb-1" data-i18n="blockerWorkLabel">कार्य</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($blockerInfo['work_name'] ?: 'कार्य उपलब्ध नाही'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small mb-1" data-i18n="blockerSchoolLabel">शाळा</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($blockerInfo['school_name'] ?: 'N/A'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small mb-1" data-i18n="blockerTalukaLabel">तालुका</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($blockerInfo['taluka_name'] ?: 'N/A'); ?></div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-3 text-center">
                                            <div class="text-muted small" data-i18n="blockerSanctionLabel">अनुदान</div>
                                            <div class="fw-semibold">₹<?php echo number_format((float)$blockerInfo['sanctioned_amount'], 2); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-3 text-center">
                                            <div class="text-muted small" data-i18n="blockerSpentLabel">खर्च</div>
                                            <div class="fw-semibold">₹<?php echo number_format((float)$blockerInfo['amount_spent'], 2); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <span class="badge bg-warning text-dark">उपयोग %: <?php echo number_format((float)$blockerInfo['utilization_percent'], 1); ?>%</span>
                                    <span class="text-muted small">HM: <?php echo htmlspecialchars($blockerInfo['hm_name'] ?: 'N/A'); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ceo-fixed-footer">
                <?php include '../include/website_footer.php'; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const langStrings = {
            pageTitle: { mar: 'CEO डॅशबोर्ड - समृद्ध शाळा ई-पोर्टल', eng: 'CEO Dashboard - Samruddha Shala E-Portal' },
            navTitle: { mar: 'CEO डॅशबोर्ड', eng: 'CEO Dashboard' },
            navDescription: { mar: 'डेटाबेसवरून तालुकानिहाय माहिती आणि ताज्या अहवालांसह फायनल व्यू.', eng: 'Taluka-wise data and latest activity loaded from database.' },
            fundReceiptTitle: { mar: 'निधीचा आढावा', eng: 'Fund Receipt Overview' },
            fundReceiptDescription: { mar: 'ताज्या निधी प्राप्ती आणि खर्चाचा आढावा.', eng: 'Recent fund receipts and expenditure analysis.' },
            fundReceiptBadge: { mar: 'निधी आढावा', eng: 'Fund Overview' },
            fundDistributionTitle: { mar: 'निधी वाटप विवरण', eng: 'Fund Allocation Details' },
            fundDistributionDescription: { mar: 'वितरण शेअर आणि निधी स्रोतांनुसार तपशील.', eng: 'Allocation share and funding source breakdown.' },
            fundDistributionBadge: { mar: 'वाटप', eng: 'Allocation' },
            fundTabSectionTitle: { mar: 'निधीचा आढावा आणि वाटप', eng: 'Fund Overview & Distribution' },
            fundTabSectionDescription: { mar: 'निधीचा आढावा व वाटप डेटाबेसमधून पाहा.', eng: 'View receipt and distribution details from the database.' },
            fundReceiptTab: { mar: 'निधीचा आढावा', eng: 'Receipt' },
            fundDistributionTab: { mar: 'निधी वाटप', eng: 'Distribution' },
            fundReceiptSummaryTitle: { mar: 'Receipt Summary', eng: 'Receipt Summary' },
            fundReceiptSummaryText: { mar: 'एकूण प्राप्त निधी', eng: 'Total received funds' },
            fundReceiptChartLabel1: { mar: 'प्राप्त', eng: 'Received' },
            fundReceiptChartLabel2: { mar: 'खर्च', eng: 'Spent' },
            fundDistributionSummaryTitle: { mar: 'वितरण सारांश', eng: 'Distribution Summary' },
            talukaTitle: { mar: 'तालुक्यानुसार माहिती', eng: 'Taluka-wise Information' },
            talukaDescription: { mar: 'सक्रिय तालुक्यांची माहिती आणि शाळा मॅपिंग डेटाबेसवरून.', eng: 'Active taluka details and school mapping from the database.' },
            talukaSummaryLabel: { mar: 'संपूर्ण सक्रिय तालुक्यांची संख्या', eng: 'Total active talukas' },
            talukaLabel: { mar: 'तालुके', eng: 'Talukas' },
            summarySchoolsLabel: { mar: 'एकूण शाळा', eng: 'Total Schools' },
            summaryBudgetLabel: { mar: 'एकूण बजेट', eng: 'Total Budget' },
            summaryCompletedLabel: { mar: 'पूर्ण झालेले प्रकल्प', eng: 'Completed Projects' },
            summaryPendingLabel: { mar: 'प्रलंबित कामे', eng: 'Pending Works' },
            recentTitle: { mar: 'रिसेंट अॅक्टिव्हिटी', eng: 'Recent Activity' },
            recentDescription: { mar: 'ताज्या HM खर्च नोंदी आणि शाळा/तालुका माहिती.', eng: 'Latest HM expense updates and taluka info.' },
            latestBadge: { mar: 'नवीनतम', eng: 'Latest' },
            noRecent: { mar: 'रिसेंट अॅक्टिव्हिटी उपलब्ध नाही.', eng: 'No recent activity available.' },
            blockerTitle: { mar: 'ब्लॉकर माहिती', eng: 'Blocker Info' },
            blockerDescription: { mar: 'उच्च उपयोग झालेल्या प्रकल्पांबद्दलची माहिती.', eng: 'Details of the highest utilization project.' },
            highBadge: { mar: 'उच्च', eng: 'High' },
            noBlocker: { mar: 'ब्लॉकर माहिती उपलब्ध नाही.', eng: 'No blocker information available.' },
            blockerWorkLabel: { mar: 'कार्य', eng: 'Work' },
            blockerSchoolLabel: { mar: 'शाळा', eng: 'School' },
            blockerTalukaLabel: { mar: 'तालुका', eng: 'Taluka' },
            blockerSanctionLabel: { mar: 'अनुदान', eng: 'Sanction' },
            blockerSpentLabel: { mar: 'खर्च', eng: 'Spent' },
            fundReceivedLabel: { mar: 'प्राप्त निधी', eng: 'Funds Received' },
            fundSpentLabel: { mar: 'वितरित निधी', eng: 'Funds Spent' }
        };

        const fundReceiptLabels = <?php echo json_encode(array_column($fundTimeline, 'month_label')); ?>;
        const fundReceiptSanctioned = <?php echo json_encode(array_map('floatval', array_column($fundTimeline, 'total_sanctioned'))); ?>;
        const fundReceiptSpent = <?php echo json_encode(array_map('floatval', array_column($fundTimeline, 'total_spent'))); ?>;
        const fundDistributionLabels = <?php echo json_encode(array_column($fundDistribution, 'fund_source')); ?>;
        const fundDistributionValues = <?php echo json_encode(array_map('floatval', array_column($fundDistribution, 'total_spent'))); ?>;

        let fundReceiptChart = null;
        let fundDistributionChart = null;

        function createCharts() {
            if (typeof Chart === 'undefined') {
                return;
            }
            const receiptCtx = document.getElementById('fundReceiptChart');
            if (receiptCtx) {
                fundReceiptChart = new Chart(receiptCtx, {
                    type: 'bar',
                    data: {
                        labels: fundReceiptLabels,
                        datasets: [
                            {
                                label: langStrings.fundReceivedLabel.mar,
                                data: fundReceiptSanctioned,
                                backgroundColor: 'rgba(59, 130, 246, 0.85)'
                            },
                            {
                                label: langStrings.fundSpentLabel.mar,
                                data: fundReceiptSpent,
                                backgroundColor: 'rgba(16, 185, 129, 0.85)'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
            const distributionCtx = document.getElementById('fundDistributionChart');
            if (distributionCtx) {
                fundDistributionChart = new Chart(distributionCtx, {
                    type: 'doughnut',
                    data: {
                        labels: fundDistributionLabels,
                        datasets: [{
                            label: langStrings.fundDistributionBadge.mar,
                            data: fundDistributionValues,
                            backgroundColor: ['#2563eb', '#0ea5e9', '#22c55e', '#f59e0b', '#ec4899', '#8b5cf6']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
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

        function setLanguage(lang) {
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (langStrings[key] && langStrings[key][lang]) {
                    el.textContent = langStrings[key][lang];
                }
            });

            document.getElementById('langMarBtn').classList.toggle('btn-primary', lang === 'mar');
            document.getElementById('langMarBtn').classList.toggle('btn-outline-primary', lang !== 'mar');
            document.getElementById('langEngBtn').classList.toggle('btn-primary', lang === 'eng');
            document.getElementById('langEngBtn').classList.toggle('btn-outline-primary', lang !== 'eng');

            document.querySelectorAll('.taluka-button').forEach(btn => {
                const text = btn.getAttribute('data-lang-' + lang);
                const label = btn.querySelector('.taluka-label');
                if (text && label) {
                    label.textContent = text;
                }
            });

            updateChartLanguage(lang);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const sidebar = document.getElementById('sidebar');
            if (mobileSidebarToggle && sidebar) {
                mobileSidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
            document.getElementById('langMarBtn').addEventListener('click', () => setLanguage('mar'));
            document.getElementById('langEngBtn').addEventListener('click', () => setLanguage('eng'));
            setLanguage('mar');
            createCharts();
        });
    </script>
</body>
</html>
