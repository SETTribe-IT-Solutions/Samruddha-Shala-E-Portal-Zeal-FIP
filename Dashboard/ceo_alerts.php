<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samruddha Shala E-Portal - CEO Alerts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div id="wrapper">
        <nav id="sidebar">
            <div class="sidebar-header d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-0 text-white font-weight-bold"><i class="fa-solid fa-graduation-cap me-2 text-primary"></i>Samruddha Shala</h4>
                    <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 1px;">E-Portal System</small>
                </div>
                <button type="button" id="sidebarCollapse" class="btn btn-outline-light btn-sm sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                    <i class="fas fa-align-left"></i>
                </button>
            </div>
            <ul class="list-unstyled components">
                <p>CEO Modules</p>
                <li id="nav-ceo-overview">
                    <a href="ceo_dashboard.php">
                        <i class="fa-solid fa-chart-pie"></i>Overview Report
                    </a>
                </li>
                <li class="active" id="nav-ceo-alerts">
                    <a href="ceo_alerts.php">
                        <i class="fa-solid fa-bell"></i>View Alerts & Notifications
                        <span id="alertsSidebarBadge" class="badge bg-danger ms-auto rounded-pill d-none">0</span>
                    </a>
                </li>
                <li id="nav-ceo-create-work">
                    <a href="ceo_create_work.php">
                        <i class="fa-solid fa-plus-circle"></i>Create Work
                    </a>
                </li>
                <li id="nav-ceo-task">
                    <a href="ceo_assign_task.php">
                        <i class="fa-solid fa-file-signature"></i>Assign Work to HM
                    </a>
                </li>
            </ul>
        </nav>

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <div class="ms-3 d-flex align-items-center">
                        <h5 class="mb-0 font-weight-bold" id="pageMainHeader">Kolhapur District Alerts</h5>
                    </div>
                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown me-4 position-relative">
                            <button class="btn btn-link text-dark p-1 text-decoration-none" id="notifBellButton" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-regular fa-bell fs-5"></i>
                                <span id="alertsHeaderBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" id="notifBellDropdown" style="width: 320px; border-radius: 12px;">
                                <li class="dropdown-header font-weight-bold d-flex justify-content-between align-items-center border-bottom pb-2">
                                    <span>Notifications Center</span>
                                    <span class="badge bg-danger rounded-pill" id="notifBellCountText">0 Alerts</span>
                                </li>
                                <div id="notifBellList" class="my-2" style="max-height: 250px; overflow-y: auto;"></div>
                                <li class="text-center pt-2 border-top">
                                    <a class="text-decoration-none text-primary fw-bold" href="ceo_alerts.php" style="font-size: 0.8rem;">View All Critical Alerts</a>
                                </li>
                            </ul>
                        </div>
                        <div class="d-flex align-items-center border-start ps-4">
                            <div class="text-end me-3 d-none d-md-block">
                                <p class="mb-0 fw-bold fs-6">Shri. R. K. Patil, IAS</p>
                                <small class="text-muted">District CEO</small>
                            </div>
                            <span class="role-badge badge-ceo">CEO</span>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold mb-1"><i class="fa-solid fa-bell me-2 text-danger animate-pulse"></i>Alerts & Notifications Command Center</h4>
                            <p class="text-muted mb-0">Review urgent reports, missing details, delayed work stages, and active blockers for schools across Kolhapur District</p>
                        </div>
                    </div>
                    <ul class="nav nav-pills mb-4 gap-2 bg-light p-2 rounded" id="alertsNavPills" role="tablist">
                        <li class="nav-item">
                            <button class="btn btn-sm btn-outline-dark active" onclick="filterAlerts('all')">All Alerts <span class="badge bg-secondary ms-1" id="alerts-cnt-all">0</span></button>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-sm btn-outline-danger" onclick="filterAlerts('blocker')">🛑 Reported Blockers <span class="badge bg-danger ms-1" id="alerts-cnt-blocker">0</span></button>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-sm btn-outline-warning" onclick="filterAlerts('delay')">⚠️ Delayed Progress <span class="badge bg-warning ms-1" id="alerts-cnt-delay">0</span></button>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-sm btn-outline-secondary" onclick="filterAlerts('geotag')">📍 Missing Geo-Tag <span class="badge bg-secondary ms-1" id="alerts-cnt-geotag">0</span></button>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-sm btn-outline-info" onclick="filterAlerts('pending')">📥 Pending Updates <span class="badge bg-info text-dark ms-1" id="alerts-cnt-pending">0</span></button>
                        </li>
                    </ul>
                    <div id="detailedAlertsContainer" class="mb-4"></div>
                </div>
            </div>

            <footer class="border-top bg-white mt-4">
                <div class="container-fluid px-4 py-3 d-flex flex-column flex-md-row justify-content-between align-items-center text-muted small">
                    <span>© 2026 Samruddha Shala E-Portal</span>
                    <span>Kolhapur District CEO Dashboard</span>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/db.js"></script>
    <script src="js/ceo.js"></script>
</body>
</html>
