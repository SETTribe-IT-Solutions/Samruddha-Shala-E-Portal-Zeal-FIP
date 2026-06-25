<?php
session_start();
if(empty($_SESSION['user_id']) || empty($_SESSION['username'])){
    header("Location: ../login.php");
    exit();
}

// Restrict access to CEO only
if (!isset($_SESSION['role']) || strtoupper($_SESSION['role']) !== 'CEO') {
    // If not CEO, redirect them to login page or prevent access
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
    <link href="../css/sidebar.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="css/ceo_dashboard.css?v=2" rel="stylesheet">
    <style>
        .ceo-dashboard-page .card {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out, border-color 0.3s ease-in-out !important;
        }
        .ceo-dashboard-page .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(255, 193, 7, 0.3), 0 5px 15px rgba(255, 193, 7, 0.2) !important;
            border-color: rgba(255, 193, 7, 0.4) !important;
        }
    </style>
    <!-- <style>
        :root {
            --ceo-sidebar-width: 250px;
            --ceo-header-height: 64px;
            --ceo-footer-height: 60px;
            --ceo-shell-bg: #f4f8fb;
        }

        body.ceo-dashboard-page {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: block;
            overflow: hidden;
            background:
                radial-gradient(circle at top left, rgba(123, 92, 255, 0.08), transparent 28%),
                linear-gradient(180deg, #eef6fb 0%, #f7fafc 100%);
        }

        body.ceo-dashboard-page #wrapper {
            min-height: 100vh;
        }

        body.ceo-dashboard-page #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--ceo-sidebar-width);
            min-width: var(--ceo-sidebar-width);
            max-width: var(--ceo-sidebar-width);
            height: 100vh;
            z-index: 1100;
            overflow: hidden;
            background: linear-gradient(180deg, #6420a5 0%, #8a45b8 54%, #efbc4d 100%);
            box-shadow: 10px 0 32px rgba(91, 35, 140, 0.22);
        }

        body.ceo-dashboard-page #sidebar .sidebar-header {
            padding: 20px 16px 16px;
        }

        body.ceo-dashboard-page #sidebar .sidebar-header h4 {
            font-size: 18px;
            line-height: 1.2;
        }

        body.ceo-dashboard-page #sidebar .sidebar-header small {
            font-size: 11px !important;
            letter-spacing: 0.8px !important;
        }

        body.ceo-dashboard-page #sidebar .components {
            padding: 10px 0 8px;
        }

        body.ceo-dashboard-page #sidebar .components li {
            margin: 4px 10px;
        }

        body.ceo-dashboard-page #sidebar .components li a {
            gap: 10px;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
        }

        body.ceo-dashboard-page #sidebar .components li a i {
            width: 18px;
            font-size: 14px;
        }

        body.ceo-dashboard-page #sidebar .sidebar-footer {
            padding: 12px 14px 8px;
        }

        body.ceo-dashboard-page #sidebar .sidebar-footer p {
            margin-bottom: 4px;
            font-size: 11px;
        }

        body.ceo-dashboard-page #sidebar .logout-wrapper {
            padding: 10px 12px 14px;
        }

        body.ceo-dashboard-page #sidebar .logout-btn {
            padding: 10px 14px;
            font-size: 13px;
            border-radius: 12px;
        }

        body.ceo-dashboard-page #content {
            margin-left: var(--ceo-sidebar-width);
            width: calc(100vw - var(--ceo-sidebar-width));
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            padding: calc(var(--ceo-header-height) + 16px) 18px calc(var(--ceo-footer-height) + 22px);
            background: transparent;
        }

        body.ceo-dashboard-page .ceo-fixed-header,
        body.ceo-dashboard-page .ceo-fixed-footer {
            position: fixed;
            left: var(--ceo-sidebar-width);
            right: 0;
            z-index: 1050;
        }

        body.ceo-dashboard-page .ceo-fixed-header {
            top: 0;
        }

        body.ceo-dashboard-page .ceo-fixed-footer {
            bottom: 0;
        }

        body.ceo-dashboard-page .ceo-fixed-header .site-banner-wrapper,
        body.ceo-dashboard-page .ceo-fixed-footer .login-page-footer {
            width: 100%;
            margin: 0;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
        }

        body.ceo-dashboard-page .ceo-fixed-header .site-banner-wrapper {
            box-shadow: 0 2px 12px rgba(17, 24, 39, 0.08);
        }

        body.ceo-dashboard-page .ceo-fixed-footer .login-page-footer {
            box-shadow: 0 -2px 12px rgba(17, 24, 39, 0.06);
        }

        body.ceo-dashboard-page .navbar {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(228, 232, 239, 0.95);
            border-radius: 22px;
            padding: 18px 24px;
            margin-bottom: 18px;
            box-shadow: 0 18px 36px rgba(54, 78, 105, 0.08);
        }

        body.ceo-dashboard-page .container-fluid.p-4 {
            padding: 0 !important;
        }

        body.ceo-dashboard-page .row {
            --bs-gutter-x: 1.15rem;
            --bs-gutter-y: 1.15rem;
        }

        body.ceo-dashboard-page .card,
        body.ceo-dashboard-page .border.rounded.p-4,
        body.ceo-dashboard-page .border.rounded.p-3 {
            border: 1px solid rgba(232, 236, 242, 0.9) !important;
            border-radius: 24px !important;
            box-shadow: 0 14px 32px rgba(54, 78, 105, 0.08);
            background: rgba(255, 255, 255, 0.97);
        }

        body.ceo-dashboard-page .card.p-4.bg-light {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%) !important;
        }

        body.ceo-dashboard-page .btn-outline-primary {
            border: none;
            color: #ffffff;
            background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%);
            border-radius: 16px;
            padding: 10px 16px;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(127, 42, 179, 0.18);
        }

        body.ceo-dashboard-page .btn-outline-primary:hover {
            color: #ffffff;
            background: linear-gradient(135deg, #6f239d 0%, #e2ad35 100%);
        }

        body.ceo-dashboard-page .table thead th {
            background: #f7f3ff;
            color: #4c2a7a;
            border-bottom: 0;
        }

        @media (max-width: 991px) {
            :root {
                --ceo-sidebar-width: 0px;
            }

            body.ceo-dashboard-page {
                overflow-y: auto;
            }

            body.ceo-dashboard-page #sidebar {
                width: 250px;
                min-width: 250px;
                max-width: 250px;
                left: -250px;
                overflow-y: auto;
            }

            body.ceo-dashboard-page #sidebar.active {
                left: 0;
            }

            body.ceo-dashboard-page #content {
                margin-left: 0;
                width: 100vw;
                padding: calc(var(--ceo-header-height) + 12px) 12px calc(var(--ceo-footer-height) + 18px);
            }

            body.ceo-dashboard-page .ceo-fixed-header,
            body.ceo-dashboard-page .ceo-fixed-footer {
                left: 0;
            }

            body.ceo-dashboard-page .navbar {
                padding: 14px 16px;
                border-radius: 18px;
            }
        }
    </style> -->
</head>
<body class="ceo-dashboard-page">

    <div id="wrapper">
        <!-- Sidebar Navigation -->
        <?php include '../include/sidebar.php'; ?>
        <!-- Page Content -->
        <div id="content">
            <div class="ceo-fixed-header">
                <?php include '../include/website_header.php'; ?>
            </div>
            <!-- Header Top Bar -->
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <div class="ms-3 d-flex align-items-center">
                        <h4 class="fw-bold mb-1"id="pageMainHeader">Kolhapur District CEO Overview</h4>
                    </div>
                    <div class="ms-auto d-flex align-items-center">
                        <!-- Notifications Dropdown -->
                        <div class="me-4 position-relative">
                            <a href="ceo_alerts.php" class="btn btn-link text-dark p-1 text-decoration-none" id="notifBellButton" title="View Alerts & Notifications">
                                <i class="fa-regular fa-bell fs-5"></i>
                                <span id="alertsHeaderBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                            </a>
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
                    <div class="row mb-4 align-items-stretch">
                        <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                            <div class="card kpi-card h-100 d-flex flex-column">
                                <div class="kpi-icon bg-primary-soft mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 16px; font-size: 1.8rem; box-shadow: 4px 6px 12px rgba(0,0,0,0.15), inset 2px 2px 6px rgba(255,255,255,0.8), inset -2px -2px 6px rgba(0,0,0,0.05); background-image: linear-gradient(145deg, rgba(255,255,255,0.4), rgba(255,255,255,0)); transform: perspective(100px) translateZ(5px);">
                                    <i class="fa-solid fa-list-check text-primary" style="filter: drop-shadow(2px 4px 4px rgba(0,0,0,0.25));"></i>
                                </div>
                                <h6 class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Works Active</h6>
                                <h2 class="fw-bold mb-0 text-dark flex-grow-1" id="kpi-total-works">0</h2>
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-light">
                                    <span class="text-success small fw-bold"><i class="fa-solid fa-arrow-up-right me-1"></i>12% vs Last Month</span>
                                    <span class="text-muted small fw-medium">Active works</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                            <div class="card kpi-card h-100 d-flex flex-column">
                                <div class="kpi-icon bg-success-soft mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 16px; font-size: 1.8rem; box-shadow: 4px 6px 12px rgba(0,0,0,0.15), inset 2px 2px 6px rgba(255,255,255,0.8), inset -2px -2px 6px rgba(0,0,0,0.05); background-image: linear-gradient(145deg, rgba(255,255,255,0.4), rgba(255,255,255,0)); transform: perspective(100px) translateZ(5px);">
                                    <i class="fa-solid fa-chart-line text-success" style="filter: drop-shadow(2px 4px 4px rgba(0,0,0,0.25));"></i>
                                </div>
                                <h6 class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Overall Progress</h6>
                                <h2 class="fw-bold mb-2 text-dark" id="kpi-overall-progress">0%</h2>
                                <div class="progress progress-bar-thin mb-1 flex-grow-1" style="height: 8px; border-radius: 4px;">
                                    <div id="kpi-progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-light">
                                    <span class="text-muted small fw-medium">Avg Completion</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                            <div class="card kpi-card h-100 d-flex flex-column">
                                <div class="kpi-icon bg-warning-soft mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 16px; font-size: 1.8rem; box-shadow: 4px 6px 12px rgba(0,0,0,0.15), inset 2px 2px 6px rgba(255,255,255,0.8), inset -2px -2px 6px rgba(0,0,0,0.05); background-image: linear-gradient(145deg, rgba(255,255,255,0.4), rgba(255,255,255,0)); transform: perspective(100px) translateZ(5px);">
                                    <i class="fa-solid fa-indian-rupee-sign text-warning" style="filter: drop-shadow(2px 4px 4px rgba(0,0,0,0.25));"></i>
                                </div>
                                <h6 class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Funding Allocation</h6>
                                <!-- Added word-break to prevent long numbers from overflowing -->
                                <h2 class="fw-bold mb-0 text-dark flex-grow-1" id="kpi-funding" style="word-break: break-word; line-height: 1.1; font-size: clamp(1.5rem, 2vw, 2rem);">₹0.0 Cr</h2>
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-light">
                                    <span class="text-muted small fw-medium">Utilized: <strong class="text-dark" id="kpi-funding-utilization">0%</strong></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                            <div class="card kpi-card h-100 d-flex flex-column" style="cursor: pointer; transition: transform 0.3s ease;" onclick="switchTab('ceo-alerts')" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
                                <div class="kpi-icon bg-danger-soft mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 16px; font-size: 1.8rem; box-shadow: 4px 6px 12px rgba(0,0,0,0.15), inset 2px 2px 6px rgba(255,255,255,0.8), inset -2px -2px 6px rgba(0,0,0,0.05); background-image: linear-gradient(145deg, rgba(255,255,255,0.4), rgba(255,255,255,0)); transform: perspective(100px) translateZ(5px);">
                                    <i class="fa-solid fa-triangle-exclamation text-danger" style="filter: drop-shadow(2px 4px 4px rgba(0,0,0,0.25));"></i>
                                </div>
                                <h6 class="text-muted mb-1 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Critical Alerts</h6>
                                <h2 class="fw-bold mb-0 text-dark flex-grow-1" id="kpi-alerts">0</h2>
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-light">
                                    <span class="text-danger small fw-bold" id="kpi-blocker-count-badge">0 Blockers Active</span>
                                    <span class="pulse-dot" id="kpi-pulse-dot" style="width: 10px; height: 10px; background-color: #dc3545; border-radius: 50%; box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); animation: pulse-red 2s infinite;"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Summary Preview Section -->
                    <div class="row mb-4 align-items-stretch">
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <div class="card p-4 h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0">Physical Progress Analytics</h5>
                                    <button type="button" class="btn btn-sm btn-outline-primary" style="pointer-events: none;">Detailed Analytics</button>
                                </div>
                                <div class="flex-grow-1" style="min-height: 300px; position: relative;">
                                    <canvas id="overviewPhysicalChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <div class="card p-4 h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0">Funding Distribution</h5>
                                    <button type="button" class="btn btn-sm btn-outline-primary" style="pointer-events: none;">Funding Dashboard</button>
                                </div>
                                <div class="flex-grow-1" style="min-height: 300px; position: relative;">
                                    <canvas id="overviewFundingChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts Overview & Recent Blockers -->
                    <div class="row mb-4 align-items-stretch">
                        <div class="col-lg-5 mb-3 mb-lg-0">
                            <div class="card p-4 h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="fw-bold mb-0">Live Alert Summary</h5>
                                    <span class="badge bg-danger rounded-pill" id="ceo-overview-alerts-pill">0 Alerts</span>
                                </div>
                                <div id="overviewAlertsContainer" class="flex-grow-1" style="max-height: 300px; overflow-y: auto;">
                                    <!-- Dynamic notifications/alerts summary -->
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7 mb-3 mb-lg-0">
                            <div class="card p-4 h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="fw-bold mb-0">High-Priority Interventions</h5>
                                    <span class="badge bg-danger rounded-pill">Action Required</span>
                                </div>
                                <div class="table-responsive flex-grow-1" style="max-height: 300px; overflow-y: auto;">
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


                <!-- ============================================== -->
                <!-- CEO VIEW: ASSIGN TASK -->
                <!-- ============================================== -->
                <div id="ceo-task-view" class="view-panel d-none">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card p-4">
                                <h4 class="fw-bold mb-1"><i class="fa-solid fa-file-signature me-2 text-primary"></i>Assign Task to School</h4>
                                <p class="text-muted mb-4">Create or update the active task for a Kolhapur school. This resets work progress to 0% and marks the task as pending CEO action.</p>

                                <form id="ceoAssignTaskForm" onsubmit="handleAssignTaskSubmit(event)">
                                    <div class="mb-3">
                                        <label for="ceoTaskSchoolSelect" class="form-label fw-semibold">Select School</label>
                                        <select id="ceoTaskSchoolSelect" class="form-select"></select>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="ceoTaskWorkType" class="form-label fw-semibold">Work Type</label>
                                            <select id="ceoTaskWorkType" class="form-select">
                                                <option value="Construction">Construction</option>
                                                <option value="Non-Construction">Non-Construction</option>
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
                                            <select id="ceoTaskFundingSource" class="form-select" required>
                                                <option value="" disabled selected>Select Funding Source</option>
                                                <option value="Annual Plan">Annual Plan</option>
                                                <option value="Minor Mineral Fund">Minor Mineral Fund</option>
                                                <option value="ZP Own Fund">ZP Own Fund</option>
                                                <option value="CSR Fund">CSR Fund</option>
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
                                <p class="text-muted mb-0">Overview of all active construction, sanitation, fencing, and water works across schools in Kolhapur District, Maharashtra, by taluka and block</p>
                            </div>
                        </div>

                        <!-- Filter options row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                    <input type="text" id="schoolSearchInput" class="form-control border-start-0" placeholder="Search by School Name or Kolhapur Taluka/Block (e.g. Karvir, Shirol)..." onkeyup="filterSchoolsTable()">
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
                                        <th>Kolhapur Taluka / Block (Location)</th>
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

            <div class="ceo-fixed-footer">
                <?php include '../include/website_footer.php'; ?>
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
    <script src="js/db.js?v=2"></script>
    <!-- CEO Application Logic -->
    <script src="js/ceo.js?v=9"></script>
</body>
</html>