<?php
session_start();

if(empty($_SESSION['user_id']) || empty($_SESSION['username'])){
    header("Location: ../login.php");
    exit();
}

if($_SESSION['role'] != 'HM'){
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samruddha Shala E-Portal - Head Master Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="../css/sidebar.css" rel="stylesheet">
    <link href="css/hm_dashboard.css?v=1.0.1" rel="stylesheet">

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="hm-dashboard-page">

<div id="wrapper">

    <!-- Sidebar -->
    <?php include '../include/sidebar.php'; ?>

    <!-- Main Content -->
    <div id="content">

        <!-- Fixed Header -->
        <div class="hm-fixed-header">
            <?php include '../include/website_header.php'; ?>
        </div>

        <!-- Header Top Bar (Navbar) -->
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <button type="button" id="sidebarCollapse" class="btn btn-link text-dark me-2 d-lg-none" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                        <i class="fas fa-align-left fs-5"></i>
                    </button>
                    <h5 class="mb-0 fw-bold" id="pageMainHeader" data-en="School Progress Reporting Desk" data-mr="शाळा प्रगती अहवाल डेस्क">School Progress Reporting Desk</h5>
                </div>

                <div class="ms-auto d-flex align-items-center gap-3">
                    <!-- Language Selector -->
                    <div class="d-flex align-items-center gap-2 me-2">
                        <label for="langSelector" class="mb-0 fw-semibold small text-muted" data-en="Language" data-mr="भाषा">Language</label>
                        <select id="langSelector" class="form-select form-select-sm border-primary shadow-sm" style="width: 110px;" onchange="setHMLanguage(this.value)">
                            <option value="en">English</option>
                            <option value="mr">मराठी</option>
                        </select>
                    </div>

                    <!-- Notifications Dropdown -->
                    <div class="dropdown me-3 position-relative">
                        <button class="btn btn-link text-dark p-1 text-decoration-none" id="notifBellButton" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-bell fs-5"></i>
                            <span id="alertsHeaderBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                                0
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" id="notifBellDropdown" style="width: 320px; border-radius: 12px;">
                            <li class="dropdown-header fw-bold d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span>Notifications Center</span>
                                <span class="badge bg-danger rounded-pill" id="notifBellCountText">0 Alerts</span>
                            </li>
                            <div id="notifBellList" class="my-2" style="max-height: 250px; overflow-y: auto;">
                                <!-- Dynamic notifications go here -->
                            </div>
                            <li class="text-center pt-2 border-top">
                                <a class="text-decoration-none text-primary fw-bold" href="javascript:void(0)" onclick="switchTab('hm-history')" style="font-size: 0.8rem;">View Submissions History</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Dashboard Content -->
        <div class="container-fluid p-0">

            <!-- PAGE TITLE -->
            <div class="card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="fw-bold mb-1 text-dark">
                            <i class="fa-solid fa-school me-2 text-primary"></i>
                            <span data-en="Head Master Dashboard" data-mr="मुख्याध्यापक डॅशबोर्ड">Head Master Dashboard</span>
                        </h3>
                        <p class="text-muted mb-0" data-en="Monitor assigned school projects, track fund utilization details, and view interactive visual KPIs." data-mr="नियुक्त शाळा प्रकल्पांवर लक्ष ठेवा, निधी वापराचा तपशील ट्रॅक करा आणि परस्परसंवादी व्हिज्युअल KPIs पहा.">
                            Monitor assigned school projects, track fund utilization details, and view interactive visual KPIs.
                        </p>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        <label for="hmSchoolSelect" class="form-label fw-bold text-muted small text-uppercase mb-1" data-en="Active School Selection" data-mr="सक्रिय शाळा निवड">Active School Selection</label>
                        <select id="hmSchoolSelect" class="form-select border-primary shadow-sm" onchange="loadHMSchoolSpecificDetails(this.value)">
                            <!-- Dynamically populated by hm.js -->
                        </select>
                    </div>
                </div>
            </div>

            <!-- Sub-navigation Tabs -->
            <ul class="nav nav-pills mb-4 gap-2 bg-light p-2 rounded" id="hmDashboardTabs" role="tablist" style="width: fit-content; border: 1px solid rgba(228, 232, 239, 0.95); border-radius: 16px !important;">
                <li class="nav-item">
                    <button class="btn btn-sm active" id="nav-hm-report" onclick="switchTab('hm-report')">
                        <i class="fa-solid fa-gauge-high me-2"></i><span data-en="Interactive Desk" data-mr="परस्परसंवादी डेस्क">Interactive Desk</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="btn btn-sm" id="nav-hm-history" onclick="switchTab('hm-history')">
                        <i class="fa-solid fa-clock-rotate-left me-2"></i><span data-en="Submission History" data-mr="सबमिशन इतिहास">Submission History</span>
                    </button>
                </li>
                <li class="nav-item">
                    <a class="btn btn-sm text-decoration-none" href="hm_utilization.php">
                        <i class="fa-solid fa-indian-rupee-sign me-2"></i><span data-en="Amount Utilization" data-mr="रक्कम वापर">Amount Utilization</span>
                    </a>
                </li>
            </ul>

            <!-- KPI Cards Row -->
            <div class="row g-3 mb-4">
                <!-- Card 1: Allotted Budget -->
                <div class="col-md-3">
                    <div class="card hm-kpi-card h-100 border-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px; font-weight: 700;" data-en="Allotted Budget" data-mr="वाटप केलेला निधी">Allotted Budget</h6>
                                <h2 class="fw-bold mb-0 text-dark">₹<span id="kpiAllocatedBudget">0.00</span> L</h2>
                            </div>
                            <div class="hm-kpi-icon bg-primary-soft text-primary">
                                <i class="fa-solid fa-wallet"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-3 pt-3 border-top border-light" style="font-size: 0.8rem;">
                            <span class="text-muted" data-en="Total sanctioned budget" data-mr="एकूण मंजूर निधी">Total sanctioned budget</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <a href="hm_utilization.php" class="text-decoration-none h-100 d-block">
                        <div class="card hm-kpi-card h-100 border-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px; font-weight: 700;" data-en="Amount Utilized" data-mr="वापरलेली रक्कम">Amount Utilized</h6>
                                    <h2 class="fw-bold mb-0 text-dark">₹<span id="kpiAmountSpent">0.00</span> L</h2>
                                </div>
                                <div class="hm-kpi-icon bg-success-soft text-success">
                                    <i class="fa-solid fa-sack-dollar"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mt-3 pt-3 border-top border-light text-success fw-bold" style="font-size: 0.8rem;">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>
                                <span data-en="Open Weightage Setup" data-mr="टक्केवारी रचना पहा">Open Weightage Setup</span>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- Card 3: Completion Progress -->
                <div class="col-md-3">
                    <div class="card hm-kpi-card h-100 border-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px; font-weight: 700;" data-en="Completion Progress" data-mr="पूर्णता प्रगती">Completion Progress</h6>
                                <h2 class="fw-bold mb-1 text-dark"><span id="kpiCompletionProgress">0</span>%</h2>
                            </div>
                            <div class="hm-kpi-icon bg-info-soft text-info">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 6px; background: #e2e8f0; border-radius: 3px;">
                            <div class="progress-bar bg-info" role="progressbar" id="kpiCompletionBar" style="width: 0%; border-radius: 3px;"></div>
                        </div>
                    </div>
                </div>
                <!-- Card 4: Active Blocker -->
                <div class="col-md-3">
                    <div class="card hm-kpi-card h-100 border-0" id="kpiBlockerCard">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px; font-weight: 700;" data-en="Active Blocker" data-mr="सक्रिय अडथळा">Active Blocker</h6>
                                <h3 class="fw-bold mb-0 text-dark text-truncate" id="kpiBlockerText" style="max-width: 140px; font-size: 1.3rem;">None</h3>
                            </div>
                            <div class="hm-kpi-icon bg-warning-soft text-warning" id="kpiBlockerIcon">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-3 pt-3 border-top border-light" style="font-size: 0.8rem;">
                            <span class="text-muted text-truncate" id="kpiBlockerDetailsText" style="max-width: 220px;" data-en="No project blockers reported" data-mr="अडथळे नोंदवले नाहीत">No project blockers reported</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INTERACTIVE DESK REPORT VIEW -->
            <div id="hm-report-view" class="view-panel">
                <div class="row g-4">
                    <!-- Column 1: Progress Ring Chart -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card p-4 text-center h-100">
                            <h5 class="fw-bold mb-3 text-start text-dark">
                                <i class="fa-solid fa-circle-notch text-primary me-2"></i>Stage Progress Ring
                            </h5>
                            <div class="position-relative d-flex justify-content-center align-items-center mx-auto my-auto" style="height: 200px; width: 200px;">
                                <canvas id="hmProgressChart"></canvas>
                                <div class="position-absolute text-center">
                                    <h2 class="fw-bold mb-0 text-dark" id="chartPercentText" style="font-size: 2.1rem; line-height: 1;">0%</h2>
                                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Complete</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Budget vs Spent Bar Chart -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card p-4 h-100 d-flex flex-column">
                            <h5 class="fw-bold mb-3 text-dark">
                                <i class="fa-solid fa-chart-column text-primary me-2"></i>Budget vs Spent Allotment
                            </h5>
                            <div class="my-auto" style="height: 160px; position: relative;">
                                <canvas id="hmFundChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: Recent Alerts Feed & Update Action -->
                    <div class="col-lg-4 col-md-12">
                        <div class="card p-4 h-100 d-flex flex-column">
                            <h5 class="fw-bold mb-3 text-dark">
                                <i class="fa-solid fa-bell text-primary me-2"></i>Recent Alerts Feed
                            </h5>
                            <div id="hmAlertsFeed" class="scroll-panel flex-grow-1 mb-3" style="max-height: 200px; overflow-y: auto;">
                                <!-- Populated dynamically by JS -->
                            </div>
                            <div class="mt-auto pt-2">
                                <button type="button" class="btn btn-primary w-100 py-2.5 fw-semibold" onclick="redirectToUpdateProgress()">
                                    <i class="fa-solid fa-file-pen me-2"></i>Report School Progress
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>

            <!-- TIMELINE & HISTORY VIEW -->
            <div id="hm-history-view" class="view-panel d-none">
                <div class="card p-4">
                    <h4 class="fw-bold mb-1 text-dark">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>
                        Submission Timeline & History
                    </h4>
                    <p class="text-muted mb-4">
                        View all pending verification reports and historical milestone approvals.
                    </p>
                    <div id="hmTimelineContainer" class="timeline-container">
                        <!-- Dynamic History -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Fixed Footer -->
        <div class="hm-fixed-footer">
            <?php include '../include/website_footer.php'; ?>
        </div>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Database scripts -->
<script src="js/db.js"></script>

<!-- Dashboard dynamic controller -->
<script src="js/hm.js"></script>

</body>
</html>