<?php
session_start();

if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['username']) ||
    $_SESSION['role'] != 'SACHIV'
){
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];
$role     = $_SESSION['role'];

// Fetch counts from database
$host = "82.25.121.144";
$db_user = "u196817721_S_Eportal_U";
$db_pass = "Sam_shalaEportal@2026";
$db_name = "u196817721_S_shalaEportal";
$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

$total_works = 0;
$completed_works = 0;
$pending_works = 0;
$alerts_count = 0;

if ($conn) {
    $res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM work_master");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $total_works = $row['cnt'];
    }
    
    $res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM work_master WHERE status = 'Completed'");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $completed_works = $row['cnt'];
    }
    
    $res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM work_master WHERE status = 'Pending'");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $pending_works = $row['cnt'];
    }
    
    $res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM work_master WHERE status IN ('Delayed', 'Blocked')");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $alerts_count = $row['cnt'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Sachiv Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<!-- Custom Style Sheets -->
<link href="../css/sidebar.css" rel="stylesheet">
<link rel="stylesheet" href="css/sachiv_dashboard.css?v=2">

</head>
<body class="sachiv-dashboard-page">

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div id="wrapper">
    <!-- SIDEBAR -->
    <?php include '../include/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <!-- HEADER -->
        <div class="sachiv-fixed-header">
            <?php include '../include/website_header.php'; ?>
        </div>

        <!-- CONTENT AREA -->
        <div class="content-area">

    <div class="mb-4 d-flex align-items-center bg-white p-3 shadow-sm border" style="border-radius: 20px;">
        <!-- Mobile Sidebar Toggle -->
        <button class="btn btn-light d-lg-none me-3 shadow-sm border-0 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, #6420a5 0%, #efbc4d 100%); color: white; border-radius: 12px;" type="button" id="menuToggle" aria-label="Toggle Sidebar">
            <i class="fa-solid fa-bars fs-5"></i>
        </button>
        
        <div>
            <h3 class="fw-bold mb-1">
                <i class="fa-solid fa-user-tie text-primary d-none d-md-inline-block me-2"></i>Sachiv Dashboard
            </h3>
            <p class="text-muted mb-0">
                Welcome to Samruddha Shala E-Portal
            </p>
        </div>
    </div>

    <!-- KPI CARDS -->
    <div class="row g-4">

        <div class="col-md-3">
            <div class="card dashboard-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Total Works</h6>
                        <h2><?php echo htmlspecialchars($total_works); ?></h2>
                    </div>

                    <div class="icon-box bg-purple">
                        <i class="fas fa-briefcase"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card dashboard-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Completed</h6>
                        <h2><?php echo htmlspecialchars($completed_works); ?></h2>
                    </div>

                    <div class="icon-box bg-green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card dashboard-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Pending</h6>
                        <h2><?php echo htmlspecialchars($pending_works); ?></h2>
                    </div>

                    <div class="icon-box bg-orange">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card dashboard-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Alerts</h6>
                        <h2><?php echo htmlspecialchars($alerts_count); ?></h2>
                    </div>

                    <div class="icon-box bg-red">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- QUICK ACTIONS -->
    <div class="row mt-4">

        <div class="col-md-6">
            <div class="card dashboard-card p-4 h-100">

                <h5>Quick Actions</h5>

                <a href="sachiv_work_master.php"
                   class="btn btn-sidebar-gradient w-100 mb-2">
                    <i class="fa-solid fa-list"></i>
                    Work Master
                </a>
                
                <a href="Financial_Master.php"
                   class="btn btn-sidebar-gradient w-100 mb-2">
                    <i class="fa-solid fa-coins"></i>
                    Financial Master
                </a>

            </div>
        </div>

        <div class="col-md-6">
            <div class="card dashboard-card p-4 h-100">

                <h5>Recent Notifications</h5>

                <ul class="list-group">
                    <li class="list-group-item">New Work Assigned</li>
                    <li class="list-group-item">Utility Report Updated</li>
                    <li class="list-group-item">Progress Report Submitted</li>
                </ul>

            </div>
        </div>

    </div>

    <!-- RECENT WORKS -->
    <div class="card table-card mt-4">

        <div class="card-header bg-white">
            <h5 class="mb-0">Recent Works</h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead class="table-light">
                        <tr>
                            <th>Sr No</th>
                            <th>Work Name</th>
                            <th>Fund Source</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td>1</td>
                            <td>Classroom Repair</td>
                            <td>ZP Fund</td>
                            <td>
                                <span class="badge bg-success">
                                    Completed
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td>2</td>
                            <td>Water Tank Installation</td>
                            <td>Annual Plan</td>
                            <td>
                                <span class="badge bg-orange text-white">
                                    Ongoing
                                </span>
                            </td>
                            
                        </tr>

                        <tr>
                            <td>3</td>
                            <td>Toilet Construction</td>
                            <td>CSR Fund</td>
                            <td>
                                <span class="badge bg-danger">
                                    Pending
                                </span>
                            </td>
                            
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

        </div>

        <!-- FOOTER -->
        <div class="sachiv-fixed-footer">
            <?php include '../include/website_footer.php'; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const menuBtn = document.getElementById("menuToggle");
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebarOverlay");

    menuBtn.addEventListener("click", function () {
        sidebar.classList.toggle("active");
        overlay.classList.toggle("active");
    });

    overlay.addEventListener("click", function () {
        sidebar.classList.remove("active");
        overlay.classList.remove("active");
    });

});
</script>