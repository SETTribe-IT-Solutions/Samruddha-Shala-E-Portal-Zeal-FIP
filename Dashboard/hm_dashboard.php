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
    <link href="css/hm_dashboard.css" rel="stylesheet">

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/hm.js"></script>
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
                    <h5 class="mb-0 fw-bold" id="pageMainHeader">School Progress Reporting Desk</h5>
                </div>

                <div class="ms-auto d-flex align-items-center">
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
        <div class="container-fluid p-4">

            <!-- PAGE TITLE -->
            <div class="card p-4 mb-4">
                <h3 class="fw-bold mb-1">
                    <i class="fa-solid fa-school me-2 text-primary"></i>
                    Head Master Dashboard
                </h3>
                <p class="text-muted mb-0">
                    Submit progress reports, monitor project activities and manage school updates.
                </p>
            </div>

            <!-- Sub-navigation Tabs -->
            <ul class="nav nav-pills mb-4 gap-2 bg-light p-2 rounded" id="hmDashboardTabs" role="tablist" style="width: fit-content; border: 1px solid rgba(228, 232, 239, 0.95); border-radius: 16px !important;">
                <li class="nav-item">
                    <button class="btn btn-sm btn-outline-primary active" id="nav-hm-report" onclick="switchTab('hm-report')">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Submit Progress Update
                    </button>
                </li>
                <li class="nav-item">
                    <button class="btn btn-sm btn-outline-primary" id="nav-hm-history" onclick="switchTab('hm-history')">
                        <i class="fa-solid fa-clock-rotate-left me-2"></i>Timeline & History
                    </button>
                </li>
            </ul>

            <!-- HM REPORT VIEW -->
<div id="hm-report-view" class="view-panel">

    <!-- DASHBOARD CARDS -->
    <div class="row g-4 mb-4">

        <div class="col-lg-3 col-md-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fa-solid fa-school fa-3x text-primary mb-3"></i>
                    <h2>12</h2>
                    <p class="mb-0">Assigned Works</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fa-solid fa-chart-line fa-3x text-success mb-3"></i>
                    <h2>68%</h2>
                    <p class="mb-0">Work Progress</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fa-solid fa-indian-rupee-sign fa-3x text-warning mb-3"></i>
                    <h2>₹18.5L</h2>
                    <p class="mb-0">Fund Utilized</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fa-solid fa-bell fa-3x text-danger mb-3"></i>
                    <h2>4</h2>
                    <p class="mb-0">Notifications</p>
                </div>
            </div>
        </div>

    </div>

    <!-- CHARTS -->
    <div class="row g-4 mb-4">

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Work Progress Chart</h5>
                </div>
                <div class="card-body">
                    <canvas id="progressChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Fund Utilization Chart</h5>
                </div>
                <div class="card-body">
                    <canvas id="fundChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- RECENT WORK STATUS -->
    <div class="card shadow-sm mb-4">

        <div class="card-header bg-white">
            <h5 class="mb-0">Recent Work Status</h5>
        </div>

        <div class="card-body">

            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Work Name</th>
                        <th>Stage</th>
                        <th>Progress</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Classroom Construction</td>
                        <td>Foundation</td>
                        <td>40%</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                    </tr>

                    <tr>
                        <td>Toilet Repair</td>
                        <td>Finishing</td>
                        <td>90%</td>
                        <td><span class="badge bg-success">Completed</span></td>
                    </tr>

                    <tr>
                        <td>Boundary Wall</td>
                        <td>Structure</td>
                        <td>65%</td>
                        <td><span class="badge bg-info">In Progress</span></td>
                    </tr>
                </tbody>

            </table>

        </div>

    </div>

    <!-- QUICK ACTIONS -->
    <div class="row g-4">

        <div class="col
        </div>

    </div>

</div>

            <!-- HISTORY VIEW -->
            <div id="hm-history-view" class="view-panel d-none">

                <div class="card p-4">

                    <h4 class="fw-bold mb-1">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>
                        Submission Timeline & History
                    </h4>

                    <p class="text-muted mb-4">
                        View past updates and approval status.
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

<!-- Database -->
<script src="js/db.js"></script>

<!-- HM Logic -->
<script src="js/hm.js"></script>
<script>
new Chart(document.getElementById('progressChart'), {
    type: 'bar',
    data: {
        labels: ['Foundation', 'Structure', 'Finishing'],
        datasets: [{
            label: 'Completion %',
            data: [80, 60, 35]
        }]
    }
});

new Chart(document.getElementById('fundChart'), {
    type: 'doughnut',
    data: {
        labels: ['Utilized', 'Remaining'],
        datasets: [{
            data: [18.5, 6.5]
        }]
    }
});
</script>

</body>
</html>