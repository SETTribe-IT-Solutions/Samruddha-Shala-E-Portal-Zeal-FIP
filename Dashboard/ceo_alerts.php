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
    <title>Samruddha Shala E-Portal - CEO Alerts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <link href="css/ceo_alerts.css" rel="stylesheet">   
</head>
<body class="ceo-dashboard-page">
    <div id="wrapper">
        <?php include '../include/sidebar.php'; ?>

        <div id="content">
            <div class="ceo-fixed-header">
                <?php include '../include/website_header.php'; ?>
            </div>
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <div class="ms-3 d-flex align-items-center">
                        <h5 class="mb-0 font-weight-bold" id="pageMainHeader">Notifications </h5>
                    </div>
                    <div class="ms-auto d-flex align-items-center">
                        <div class="me-4 position-relative">
                            <a href="ceo_alerts.php" class="btn btn-link text-dark p-1 text-decoration-none" id="notifBellButton" title="View Alerts & Notifications">
                                <i class="fa-regular fa-bell fs-5"></i>
                                <span id="alertsHeaderBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                            </a>
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

            <div class="ceo-fixed-footer">
                <?php include '../include/website_footer.php'; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/db.js"></script>
    <script src="js/ceo.js?v=5"></script>
</body>
</html>
