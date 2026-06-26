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
    <link href="css/ceo_dashboard.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="css/ceo_alerts.css?v=5" rel="stylesheet">
</head>
<body class="ceo-dashboard-page">
    <div id="wrapper">
        <?php include '../include/sidebar.php'; ?>

        <div id="content">
            <div class="ceo-fixed-header">
                <?php include '../include/website_header.php'; ?>
            </div>
            <nav class="navbar navbar-expand-lg navbar-light p-3" style="position: relative;">
                <div class="container-fluid d-flex flex-nowrap align-items-center px-1">
                    <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                        <!-- Mobile Sidebar Toggle -->
                        <button class="btn btn-light d-lg-none me-2 shadow-sm border-0 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 40px; height: 40px; background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%); color: white;" type="button" id="mobileSidebarToggle" aria-label="Toggle Sidebar">
                            <i class="fa-solid fa-bars fs-6"></i>
                        </button>
                        <h4 class="fw-bold mb-0 text-truncate" id="pageMainHeader" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: clamp(1.1rem, 4vw, 1.4rem); font-weight: 800 !important; line-height: 1.2;">Notifications</h4>
                    </div>
                    <div class="ms-2 d-flex align-items-center flex-shrink-0">
                        <div class="position-relative me-3">
                            <a href="ceo_alerts.php" class="btn btn-link text-dark p-1 text-decoration-none" id="notifBellButton" title="View Alerts & Notifications">
                                <i class="fa-regular fa-bell fs-5"></i>
                                <span id="alertsHeaderBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.65rem;">0</span>
                            </a>
                        </div>
                        <div class="d-flex align-items-center border-start ps-2">
                            <h4 class="fw-bold mb-0"><span class="role-badge badge-ceo" style="font-size: 0.85rem; padding: 4px 8px;">CEO</span></h4>
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
                    <ul class="nav nav-pills mb-4 gap-4 bg-light p-3 rounded" id="alertsNavPills" role="tablist">
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
    <script src="js/ceo.js?v=7"></script>
    <script>
        // Mobile sidebar toggle logic
        document.addEventListener('DOMContentLoaded', function() {
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            if (mobileSidebarToggle && sidebar) {
                mobileSidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('active');
                });
                
                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 991 && sidebar.classList.contains('active')) {
                        if (!sidebar.contains(e.target) && e.target !== mobileSidebarToggle) {
                            sidebar.classList.remove('active');
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
