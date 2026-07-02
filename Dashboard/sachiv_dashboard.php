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
            
            <div class="row">
                <!-- Main Content (Left 9 cols) -->
                <div class="col-lg-9">
                    
                    <div class="d-flex align-items-center mb-4">
                        <button class="btn btn-light d-lg-none me-3 shadow-sm border-0 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 44px; height: 44px; background: linear-gradient(135deg, #6420a5 0%, #efbc4d 100%); color: white; border-radius: 12px;" type="button" id="menuToggle" aria-label="Toggle Sidebar">
                            <i class="fa-solid fa-bars fs-5"></i>
                        </button>
                        <h3 class="fw-bold mb-0">Dashboard</h3>
                    </div>

                    <!-- KPI Row -->
                    <div class="row g-3 mb-4">
                        <div class="col-md col-sm-6">
                            <div class="card p-3 kpi-card new-card h-100">
                                <div class="d-flex align-items-center">
                                    <div class="kpi-icon-circle bg-light-primary text-primary">
                                        <i class="fa-solid fa-school"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="kpi-label text-muted small fw-bold">Total Schools</div>
                                        <div class="kpi-value fs-4 fw-bold">2</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md col-sm-6">
                            <div class="card p-3 kpi-card new-card h-100">
                                <div class="d-flex align-items-center">
                                    <div class="kpi-icon-circle bg-light-purple text-purple">
                                        <i class="fa-solid fa-list-ul"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="kpi-label text-muted small fw-bold">Total Works</div>
                                        <div class="kpi-value fs-4 fw-bold">2</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md col-sm-6">
                            <div class="card p-3 kpi-card new-card h-100">
                                <div class="d-flex align-items-center">
                                    <div class="kpi-icon-circle bg-light-info text-info">
                                        <i class="fa-solid fa-indian-rupee-sign"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="kpi-label text-muted small fw-bold">Total Budget</div>
                                        <div class="kpi-value fs-4 fw-bold">₹20,000</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md col-sm-6">
                            <div class="card p-3 kpi-card new-card h-100">
                                <div class="d-flex align-items-center">
                                    <div class="kpi-icon-circle bg-light-warning text-warning">
                                        <i class="fa-solid fa-bell"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="kpi-label text-muted small fw-bold">Total Alerts</div>
                                        <div class="kpi-value fs-4 fw-bold">2</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md col-sm-12">
                            <div class="card p-3 kpi-card new-card h-100">
                                <div class="d-flex align-items-center">
                                    <div class="kpi-icon-circle bg-light-success text-success">
                                        <i class="fa-solid fa-check-circle"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="kpi-label text-muted small fw-bold">Total Approvals</div>
                                        <div class="kpi-value fs-4 fw-bold">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <div class="card p-4 h-100 new-card">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h6 class="fw-bold m-0">Work Completion Overview</h6>
                                    <div class="d-flex gap-3 small">
                                        <div><span class="d-inline-block rounded-circle bg-success me-1" style="width:8px;height:8px;"></span>Completed</div>
                                        <div><span class="d-inline-block rounded-circle bg-primary me-1" style="width:8px;height:8px;"></span>In Progress</div>
                                        <div><span class="d-inline-block rounded-circle bg-danger me-1" style="width:8px;height:8px;"></span>Pending</div>
                                    </div>
                                </div>
                                <div style="height: 250px;">
                                    <canvas id="barChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card p-4 h-100 new-card">
                                <h6 class="fw-bold mb-4">Overall Progress</h6>
                                <div class="position-relative d-flex justify-content-center mb-3">
                                    <div style="width: 150px; height: 150px;">
                                        <canvas id="donutChart"></canvas>
                                    </div>
                                    <div class="position-absolute top-50 start-50 translate-middle text-center" style="margin-top: 5px;">
                                        <h3 class="fw-bold m-0" style="font-size: 28px;">38%</h3>
                                        <small class="text-muted" style="font-size: 11px;">Completed</small>
                                    </div>
                                </div>
                                
                                <div class="mt-4 px-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted small fw-bold">Completed</span>
                                        <span class="text-muted small fw-bold">0%</span>
                                    </div>
                                    <div class="progress mb-3" style="height: 6px;">
                                        <div class="progress-bar bg-light" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted small fw-bold">Pending</span>
                                        <span class="text-muted small fw-bold">100%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Row -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card p-4 h-100 new-card">
                                <h6 class="fw-bold mb-4"><i class="fa-solid fa-clock-rotate-left me-2 text-muted"></i>Recent Activities</h6>
                                
                                <div class="activity-item d-flex align-items-center p-3 bg-light rounded-3 mb-2 border">
                                    <div class="activity-dot bg-success rounded-circle me-3" style="width: 10px; height: 10px;"></div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold small">Shrimati. Kavita Shinde</div>
                                        <div class="text-muted small">Logged in successfully</div>
                                    </div>
                                    <i class="fa-solid fa-check-circle text-success"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card p-4 h-100 new-card">
                                <h6 class="fw-bold mb-4"><i class="fa-solid fa-building me-2 text-muted"></i>Work Progress Status</h6>
                                
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="small fw-bold">Shrimati. Kavita Shinde <br><span class="text-muted fw-normal">(Dindori)</span></span>
                                        <span class="small fw-bold text-primary align-self-end">60%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 60%; border-radius: 4px;"></div>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="small fw-bold">Shrimati. Savita Ghatte <br><span class="text-muted fw-normal">(Peth)</span></span>
                                        <span class="small fw-bold text-primary align-self-end">28%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 28%; border-radius: 4px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                
                <!-- Right Sidebar (Right 3 cols) -->
                <div class="col-lg-3 mt-4 mt-lg-0">
                    
                    <div class="d-flex justify-content-lg-end mb-4">
                        <button class="btn btn-primary d-flex align-items-center justify-content-center" style="background-color: #2563eb; border: none; border-radius: 8px; padding: 8px 16px; height: 40px; width: auto;" title="Refresh Data" onclick="location.reload();">
                            <i class="fa-solid fa-rotate-right me-2"></i>
                            <span class="fw-bold" style="font-size: 14px;">Refresh Data</span>
                        </button>
                    </div>

                    <!-- Notifications -->
                    <div class="card p-3 mb-3 new-card">
                        <h6 class="fw-bold mb-3">Notifications (0)</h6>
                        <div class="text-center text-muted small py-4">No new notifications</div>
                    </div>
                    
                    <!-- Reminder -->
                    <div class="card p-4 mb-3 new-card">
                        <h6 class="fw-bold mb-4"><i class="fa-solid fa-bell text-warning me-2"></i>Reminder</h6>
                        <div class="small text-muted mb-2">01-07-2026, 10:30 AM</div>
                        <div class="text-primary small fw-bold mb-2">Work Completion</div>
                        <div class="fw-bold mb-3 text-dark">School Building</div>
                        <p class="small text-muted mb-4">Priority: This work has not been updated in 15 days. Please update.</p>
                        <a href="#" class="text-danger small fw-bold text-decoration-none">Savita Ghatte <i class="fa-solid fa-play ms-1" style="font-size: 10px;"></i></a>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="card p-4 new-card">
                        <h6 class="fw-bold mb-4">Quick Links</h6>
                        <div class="d-grid gap-3">
                            <a href="#" class="btn btn-light text-primary fw-bold btn-sm py-2 rounded-3" style="background-color: #eef2ff; border: 1px solid #e0e7ff;">Add New Work</a>
                            <a href="sachiv_work_master.php" class="btn btn-light text-purple fw-bold btn-sm py-2 rounded-3" style="background-color: #faf5ff; border: 1px solid #f3e8ff;">Work List</a>
                            <a href="Financial_Master.php" class="btn btn-light text-success fw-bold btn-sm py-2 rounded-3" style="background-color: #f0fdf4; border: 1px solid #dcfce7;">Reports</a>
                            <a href="#" class="btn btn-light fw-bold btn-sm py-2 rounded-3" style="background-color: #fffbeb; border: 1px solid #fef3c7; color: #d97706 !important;">Settings</a>
                        </div>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

    // Bar Chart
    const barCtx = document.getElementById('barChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: ['Completed', 'In Progress', 'Pending'],
            datasets: [{
                label: 'Works',
                data: [0, 0, 100], // Example data to match mockup 
                backgroundColor: ['#22c55e', '#3b82f6', '#ef4444'],
                borderRadius: 4,
                barPercentage: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) { return value + "%" }
                    },
                    grid: { color: '#f1f5f9' },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    border: { display: false }
                }
            }
        }
    });

    // Donut Chart
    const donutCtx = document.getElementById('donutChart').getContext('2d');
    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending'],
            datasets: [{
                data: [38, 62],
                backgroundColor: ['#3b82f6', '#f1f5f9'],
                borderWidth: 0,
                cutout: '80%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });

});
</script>