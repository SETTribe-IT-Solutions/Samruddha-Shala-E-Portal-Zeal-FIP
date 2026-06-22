<?php
session_start();
if(empty($_SESSION['user_id']) || empty($_SESSION['username'])){
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samruddha Shala E-Portal - CEO Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom Style Sheet -->
    <link href="css/style.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <div id="wrapper">
        <!-- Sidebar Navigation -->
<?php include '../include/sidebar.php'; ?>
<?php include '../include/header.php'; ?>

        <!-- Page Content -->
        <div id="content">
            <!-- Header Top Bar -->
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary btn-sm" onclick="toggleSidebar()">
                        <i class="fas fa-align-left"></i>
                    </button>

                    <div class="ms-3 d-flex align-items-center">
                        <h5 class="mb-0 font-weight-bold" id="pageMainHeader">CEO Monitoring Dashboard</h5>
                    </div>

                    <div class="ms-auto d-flex align-items-center">
                        <!-- Notifications Dropdown -->
                        <div class="dropdown me-4 position-relative">
                            <button class="btn btn-link text-dark p-1 text-decoration-none" id="notifBellButton" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-regular fa-bell fs-5"></i>
                                <span id="alertsHeaderBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                                    0
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" id="notifBellDropdown" style="width: 320px; border-radius: 12px;">
                                <li class="dropdown-header font-weight-bold d-flex justify-content-between align-items-center border-bottom pb-2">
                                    <span>Notifications Center</span>
                                    <span class="badge bg-danger rounded-pill" id="notifBellCountText">0 Alerts</span>
                                </li>
                                <div id="notifBellList" class="my-2" style="max-height: 250px; overflow-y: auto;">
                                    <!-- Dynamic notifications go here -->
                                </div>
                                <li class="text-center pt-2 border-top">
                                    <a class="text-decoration-none text-primary fw-bold" href="javascript:void(0)" onclick="switchTab('ceo-alerts')" style="font-size: 0.8rem;">View All Critical Alerts</a>
                                </li>
                            </ul>
                        </div>

                        <!-- Active User Indicator -->
                        
                    </div>
                </div>
            </nav>

            <!-- Main Content Container -->
            <div class="container-fluid p-4">

                <!-- ============================================== -->
                <!-- CEO VIEW: OVERVIEW DASHBOARD -->
                <!-- ============================================== -->
                <div id="ceo-overview-view" class="view-panel">
                    <!-- Statistics Summary Row -->
                    <div class="row">
                        <div class="col-md-6 col-lg-3">
                            <div class="card kpi-card">
                                <div class="kpi-icon bg-primary-soft">
                                    <i class="fa-solid fa-list-check"></i>
                                </div>
                                <h6 class="text-muted mb-1">Total Works Active</h6>
                                <h2 class="fw-bold mb-2" id="kpi-total-works">0</h2>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="text-success small fw-semibold"><i class="fa-solid fa-arrow-up-right me-1"></i>12% vs Last Month</span>
                                    <span class="text-muted small">Active works</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card kpi-card">
                                <div class="kpi-icon bg-success-soft">
                                    <i class="fa-solid fa-chart-line"></i>
                                </div>
                                <h6 class="text-muted mb-1">Overall Progress</h6>
                                <h2 class="fw-bold mb-2" id="kpi-overall-progress">0%</h2>
                                <div class="progress progress-bar-thin mb-1">
                                    <div id="kpi-progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="text-muted small">Avg Completion</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card kpi-card">
                                <div class="kpi-icon bg-warning-soft">
                                    <i class="fa-solid fa-indian-rupee-sign"></i>
                                </div>
                                <h6 class="text-muted mb-1">Funding Allocation</h6>
                                <h2 class="fw-bold mb-2" id="kpi-funding">₹0.0 Cr</h2>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="text-muted small">Utilized: <strong id="kpi-funding-utilization">0%</strong></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card kpi-card" style="cursor: pointer;" onclick="switchTab('ceo-alerts')">
                                <div class="kpi-icon bg-danger-soft">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <h6 class="text-muted mb-1">Critical Alerts</h6>
                                <h2 class="fw-bold mb-2" id="kpi-alerts">0</h2>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="text-danger small fw-semibold" id="kpi-blocker-count-badge">0 Blockers Active</span>
                                    <span class="pulse-dot" id="kpi-pulse-dot"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Summary Preview Section -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0">Physical Progress Analytics</h5>
                                    <button class="btn btn-sm btn-outline-primary" onclick="switchTab('ceo-physical')">Detailed Analytics</button>
                                </div>
                                <div style="height: 300px; position: relative;">
                                    <canvas id="overviewPhysicalChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0">Funding Distribution</h5>
                                    <button class="btn btn-sm btn-outline-primary" onclick="switchTab('ceo-funding')">Funding Dashboard</button>
                                </div>
                                <div style="height: 300px; position: relative;">
                                    <canvas id="overviewFundingChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts Overview & Recent Blockers -->
                    <div class="row">
                        <div class="col-lg-5">
                            <div class="card p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="fw-bold mb-0">Live Alert Summary</h5>
                                    <span class="badge bg-danger rounded-pill" id="ceo-overview-alerts-pill">0 Alerts</span>
                                </div>
                                <div id="overviewAlertsContainer" style="max-height: 300px; overflow-y: auto;">
                                    <!-- Dynamic notifications/alerts summary -->
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="card p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="fw-bold mb-0">High-Priority Interventions</h5>
                                    <span class="text-muted small">Action Required</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead>
                                            <tr>
                                                <th>School</th>
                                                <th>Work Type</th>
                                                <th>Progress</th>
                                                <th>Blocker Type</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="overviewHighPriorityTable">
                                            <!-- Dynamic top delayed/blocked school list -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- CEO VIEW: ASSIGN TASK -->
                <!-- ============================================== -->
                <div id="ceo-task-view" class="view-panel d-none">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="card p-4">
                                <h4 class="fw-bold mb-1"><i class="fa-solid fa-file-signature me-2 text-primary"></i>Assign Task to School</h4>
                                <p class="text-muted mb-4">Create or update the active task for a Kolhapur school. This resets work progress to 0% and marks the task as pending HM action.</p>

                                <form id="ceoAssignTaskForm" onsubmit="handleAssignTaskSubmit(event)">
                                    <div class="mb-3">
                                        <label for="ceoTaskSchoolSelect" class="form-label fw-semibold">Select School</label>
                                        <select id="ceoTaskSchoolSelect" class="form-select"></select>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="ceoTaskWorkType" class="form-label fw-semibold">Work Type</label>
                                            <select id="ceoTaskWorkType" class="form-select">
                                                <option>Classrooms</option>
                                                <option>Toilets</option>
                                                <option>Fencing</option>
                                                <option>Water Facilities</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ceoTaskBudget" class="form-label fw-semibold">Budget (Lakhs)</label>
                                            <input type="number" min="0" step="0.1" id="ceoTaskBudget" class="form-control" placeholder="e.g. 4.0">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="ceoTaskFundingSource" class="form-label fw-semibold">Funding Source</label>
                                            <select id="ceoTaskFundingSource" class="form-select">
                                                <option>Annual Plan</option>
                                                <option>Minor Mineral Fund</option>
                                                <option>ZP Own Fund</option>
                                                <option>CSR Fund</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ceoTaskDescription" class="form-label fw-semibold">Task Description</label>
                                        <textarea id="ceoTaskDescription" class="form-control" rows="4" placeholder="Describe the task details, expected outcomes, and priority..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold"><i class="fa-solid fa-paper-plane me-2"></i>Assign Task</button>
                                </form>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="card p-4 bg-light border-0">
                                <h5 class="fw-bold mb-3">Active CEO Task Queue</h5>
                                <div id="ceoTaskSummary"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- CEO VIEW: PHYSICAL PROGRESS ANALYTICS -->
                <!-- ============================================== -->
                <div id="ceo-physical-view" class="view-panel d-none">
                    <div class="card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="fw-bold mb-1"><i class="fa-solid fa-industry me-2 text-primary"></i>Physical Progress Analytics</h4>
                                <p class="text-muted mb-0">Deep dive tracking of target infrastructure development across classrooms, toilets, fencing, and water facilities</p>
                            </div>
                        </div>

                        <!-- Progress Metrics Grid for four key pillars -->
                        <div class="row mb-4">
                            <div class="col-md-6 col-lg-3">
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 text-muted">Classrooms</h6>
                                        <span class="badge bg-primary rounded-pill" id="phys-cnt-classrooms">0 Works</span>
                                    </div>
                                    <h3 class="fw-bold mb-2" id="phys-avg-classrooms">0%</h3>
                                    <div class="progress progress-bar-thin">
                                        <div id="phys-bar-classrooms" class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 text-muted">Toilets</h6>
                                        <span class="badge bg-success rounded-pill" id="phys-cnt-toilets">0 Works</span>
                                    </div>
                                    <h3 class="fw-bold mb-2" id="phys-avg-toilets">0%</h3>
                                    <div class="progress progress-bar-thin">
                                        <div id="phys-bar-toilets" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 text-muted">Fencing</h6>
                                        <span class="badge bg-warning rounded-pill" id="phys-cnt-fencing">0 Works</span>
                                    </div>
                                    <h3 class="fw-bold mb-2" id="phys-avg-fencing">0%</h3>
                                    <div class="progress progress-bar-thin">
                                        <div id="phys-bar-fencing" class="progress-bar bg-warning" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 text-muted">Water Facilities</h6>
                                        <span class="badge bg-info rounded-pill" id="phys-cnt-water">0 Works</span>
                                    </div>
                                    <h3 class="fw-bold mb-2" id="phys-avg-water">0%</h3>
                                    <div class="progress progress-bar-thin">
                                        <div id="phys-bar-water" class="progress-bar bg-info" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Heavy Chart Analysis -->
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="border rounded p-4 mb-4" style="height: 380px; position: relative;">
                                    <h6 class="fw-bold mb-3">Completion Stages Breakdown by Work Type</h6>
                                    <canvas id="detailedPhysicalChart"></canvas>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="border rounded p-4 mb-4">
                                    <h6 class="fw-bold mb-3">Key Infrastructure Insights</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-3 d-flex align-items-start">
                                            <i class="fa-solid fa-circle-check text-success mt-1 me-3"></i>
                                            <div>
                                                <strong>Water Facilities</strong> have the highest average progress. Most borewells are fully operational.
                                            </div>
                                        </li>
                                        <li class="mb-3 d-flex align-items-start">
                                            <i class="fa-solid fa-circle-exclamation text-warning mt-1 me-3"></i>
                                            <div>
                                                <strong>Fencing Boundary Walls</strong> are delayed due to land boundary clarifications at ZP School Indapur.
                                            </div>
                                        </li>
                                        <li class="mb-0 d-flex align-items-start">
                                            <i class="fa-solid fa-triangle-exclamation text-danger mt-1 me-3"></i>
                                            <div>
                                                <strong>Classrooms</strong> extension construction requires close supervision due to labour shortage in rural pockets.
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- CEO VIEW: FUNDING DISTRIBUTION -->
                <!-- ============================================== -->
                <div id="ceo-funding-view" class="view-panel d-none">
                    <div class="card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="fw-bold mb-1"><i class="fa-solid fa-hand-holding-dollar me-2 text-primary"></i>Funding Distribution Dashboard</h4>
                                <p class="text-muted mb-0">Track funding allocations and spending progress from critical development sources: Annual Plan, Minor Mineral Fund, ZP Own Fund, and CSR</p>
                            </div>
                        </div>

                        <!-- Funding Summary Metric Badges -->
                        <div class="row mb-4" id="fundingSourceGlowRow">
                            <!-- Dynamic Funding source cards with calculations of Allocations vs Utilizations -->
                        </div>

                        <!-- Chart + Breakdown analysis -->
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="border rounded p-4 mb-4" style="height: 380px; position: relative;">
                                    <h6 class="fw-bold mb-3">Overall Fund Source Allocation %</h6>
                                    <canvas id="detailedFundingChart"></canvas>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="border rounded p-4 mb-4">
                                    <h6 class="fw-bold mb-3">Financial Distribution Logs</h6>
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Source</th>
                                                    <th>Allocated</th>
                                                    <th>Spent</th>
                                                    <th>Utilization %</th>
                                                </tr>
                                            </thead>
                                            <tbody id="detailedFundingTable">
                                                <!-- Dynamic calculations rendered here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- CEO VIEW: ALERTS & NOTIFICATIONS -->
                <!-- ============================================== -->
                <div id="ceo-alerts-view" class="view-panel d-none">
                    <div class="card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="fw-bold mb-1"><i class="fa-solid fa-bell me-2 text-danger animate-pulse"></i>Alerts & Notifications Command Center</h4>
                                <p class="text-muted mb-0">Review urgent reports, missing details, delayed work stages, and active blockers requiring intervention</p>
                            </div>
                        </div>

                        <!-- Alerts Category Tabs -->
                        <ul class="nav nav-pills mb-4 gap-2 bg-light p-2 rounded" id="alertsNavPills" role="tablist">
                            <li class="nav-item">
                                <button class="btn btn-sm btn-outline-dark active" onclick="filterAlerts('all')">
                                    All Alerts <span class="badge bg-secondary ms-1" id="alerts-cnt-all">0</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="btn btn-sm btn-outline-danger" onclick="filterAlerts('blocker')">
                                    🛑 Reported Blockers <span class="badge bg-danger ms-1" id="alerts-cnt-blocker">0</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="btn btn-sm btn-outline-warning" onclick="filterAlerts('delay')">
                                    ⚠️ Delayed Progress <span class="badge bg-warning ms-1" id="alerts-cnt-delay">0</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="btn btn-sm btn-outline-secondary" onclick="filterAlerts('geotag')">
                                    📍 Missing Geo-Tag <span class="badge bg-secondary ms-1" id="alerts-cnt-geotag">0</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="btn btn-sm btn-outline-info" onclick="filterAlerts('pending')">
                                    📥 Pending Updates <span class="badge bg-info text-dark ms-1" id="alerts-cnt-pending">0</span>
                                </button>
                            </li>
                        </ul>

                        <!-- Alerts Content Feed -->
                        <div id="detailedAlertsContainer" class="mb-4">
                            <!-- Dynamically loaded list of alert items with filters -->
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- CEO VIEW: SCHOOL MONITOR TABLE -->
                <!-- ============================================== -->
                <div id="ceo-monitor-view" class="view-panel d-none">
                    <div class="card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="fw-bold mb-1"><i class="fa-solid fa-list-check me-2 text-primary"></i>District-Wide School Project Monitor</h4>
                                <p class="text-muted mb-0">Overview of all active construction, sanitation, fencing, and water works across Kolhapur district schools</p>
                            </div>
                        </div>

                        <!-- Filter options row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                    <input type="text" id="schoolSearchInput" class="form-control border-start-0" placeholder="Search by School Name or Block..." onkeyup="filterSchoolsTable()">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select id="filterWorkType" class="form-select" onchange="filterSchoolsTable()">
                                    <option value="">All Work Types</option>
                                    <option value="Classrooms">Classrooms</option>
                                    <option value="Toilets">Toilets</option>
                                    <option value="Fencing">Fencing</option>
                                    <option value="Water Facilities">Water Facilities</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="filterFundingSource" class="form-select" onchange="filterSchoolsTable()">
                                    <option value="">All Funding Sources</option>
                                    <option value="Annual Plan">Annual Plan</option>
                                    <option value="Minor Mineral Fund">Minor Mineral Fund</option>
                                    <option value="ZP Own Fund">ZP Own Fund</option>
                                    <option value="CSR Fund">CSR Fund</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="filterAlertStatus" class="form-select" onchange="filterSchoolsTable()">
                                    <option value="">All Alert Statuses</option>
                                    <option value="Blocker">Reported Blocker</option>
                                    <option value="Delay">Delayed Progress</option>
                                    <option value="MissingGeotag">Missing Geo-Tag</option>
                                    <option value="Clean">No Alerts</option>
                                </select>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-outline-secondary w-100" onclick="exportToCSV()"><i class="fa-solid fa-download me-2"></i>Export CSV</button>
                            </div>
                        </div>

                        <!-- Data table -->
                        <div class="table-responsive shadow-sm">
                            <table class="table table-hover align-middle bg-white mb-0" id="schoolProjectsTable">
                                <thead>
                                    <tr>
                                        <th>School Name</th>
                                        <th>Block / Taluka</th>
                                        <th>Work Type</th>
                                        <th>Funding Source</th>
                                        <th>Progress Stage</th>
                                        <th>Geo-tag</th>
                                        <th>Alert Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="schoolProjectsTableBody">
                                    <!-- Dynamic rows loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Details View Modal -->
    <div class="modal fade" id="projectDetailsModal" tabindex="-1" aria-labelledby="projectDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: var(--card-border-radius);">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="projectDetailsModalLabel">School Work Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="projectModalBody">
                    <!-- Dynamic details go here -->
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Shared Database Layer -->
    <script src="js/db.js"></script>
    <!-- CEO Application Logic -->
    <script src="js/ceo.js"></script>
</body>
</html>