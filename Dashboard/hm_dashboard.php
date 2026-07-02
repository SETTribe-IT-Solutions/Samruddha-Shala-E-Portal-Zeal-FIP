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
    <link href="css/hm_dashboard.css?v=2.0.2" rel="stylesheet">

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
        
        <div class="hm-main-content">
            <!-- Header Top Bar (Navbar) -->
            <nav class="navbar navbar-expand-lg navbar-light p-3 flex-shrink-0">
                <div class="container-fluid d-flex flex-nowrap align-items-center px-1">
                    <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                        <!-- Mobile Sidebar Toggle -->
                        <button class="btn btn-light d-lg-none me-2 shadow-sm border-0 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 40px; height: 40px; background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%); color: white;" type="button" id="sidebarCollapse" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
                            <i class="fa-solid fa-bars fs-6"></i>
                        </button>
                        <h4 class="fw-bold mb-0 text-dark">Dashboard</h4>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-0 flex-grow-1" style="overflow: hidden;">
                <!-- KPI Cards Row -->
            <div class="row g-4 mb-4">
                <!-- Card 1 -->
                <div class="col-md-3">
                    <div class="hm-card kpi-card">
                        <div class="kpi-icon-box kpi-icon-blue">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div class="kpi-details">
                            <h6>Total Works</h6>
                            <h2>1</h2>
                        </div>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="col-md-3">
                    <div class="hm-card kpi-card">
                        <div class="kpi-icon-box kpi-icon-green">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="kpi-details">
                            <h6>Completed Works</h6>
                            <h2>0</h2>
                        </div>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="col-md-3">
                    <div class="hm-card kpi-card">
                        <div class="kpi-icon-box kpi-icon-orange">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="kpi-details">
                            <h6>In Progress Works</h6>
                            <h2>1</h2>
                        </div>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="col-md-3">
                    <div class="hm-card kpi-card">
                        <div class="kpi-icon-box kpi-icon-purple">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="kpi-details">
                            <h6>Pending Works</h6>
                            <h2>0</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-5">
                    <div class="hm-card">
                        <h5 class="hm-card-title">Completion Progress</h5>
                        <div class="chart-container d-flex justify-content-center align-items-center">
                            <div style="width: 220px; position: relative;">
                                <canvas id="donutChart"></canvas>
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                                    <h2 class="fw-bold mb-0" style="font-size: 32px;">50%</h2>
                                    <small class="text-muted">Completed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="hm-card">
                        <h5 class="hm-card-title"><i class="fa-solid fa-chart-simple text-primary me-2"></i> Funds Overview (Fund)</h5>
                        <div class="d-flex justify-content-center mb-3 gap-4" style="font-size: 13px;">
                            <div><span style="display:inline-block; width:12px; height:12px; background:#3b82f6; border-radius:50%; margin-right:6px;"></span>Total Sanctioned (Total Sanctioned)</div>
                            <div><span style="display:inline-block; width:12px; height:12px; background:#22c55e; border-radius:50%; margin-right:6px;"></span>Total Remaining (Total Remaining)</div>
                        </div>
                        <div class="chart-container">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lists Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-7">
                    <div class="hm-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="hm-card-title mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Recent Updates</h5>
                            <a href="#" class="view-all-link">View All <i class="fa-solid fa-arrow-right ms-1"></i></a>
                        </div>
                        <div class="update-list">
                            <!-- Update Item 1 -->
                            <div class="update-item">
                                <img src="../images/demo.jpg" alt="Update" class="update-img">
                                <div class="update-info">
                                    <h6>New Entry</h6>
                                    <p>Type: testing 2 (25%)<br><i>testing2</i></p>
                                </div>
                                <div class="update-meta">
                                    <span class="date mb-2">6/30/2026</span>
                                    <a href="#" class="view-link">View <i class="fa-solid fa-arrow-right"></i></a>
                                </div>
                            </div>
                            <!-- Update Item 2 -->
                            <div class="update-item">
                                <img src="../images/demo.jpg" alt="Update" class="update-img">
                                <div class="update-info">
                                    <h6>New Entry</h6>
                                    <p>Type: testing 1 (25%)<br><i>testing</i></p>
                                </div>
                                <div class="update-meta">
                                    <span class="date mb-2">6/30/2026</span>
                                    <a href="#" class="view-link">View <i class="fa-solid fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="hm-card">
                        <h5 class="hm-card-title"><i class="fa-solid fa-bell text-primary me-2"></i> Notifications</h5>
                        <div class="notif-list mt-3">
                            <div class="notif-item">
                                <i class="fa-solid fa-bell notif-icon"></i>
                                <div class="notif-info">
                                    <h6>New Entry</h6>
                                    <p>10:30 AM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div> <!-- End container-fluid -->
        </div> <!-- End hm-main-content -->

        <div class="hm-fixed-footer">
            <?php include '../include/website_footer.php'; ?>
        </div>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
    }

    // Initialize Charts to match design
    document.addEventListener("DOMContentLoaded", function() {
        
        // Donut Chart
        const ctxDonut = document.getElementById('donutChart');
        if (ctxDonut) {
            new Chart(ctxDonut.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Pending'],
                    datasets: [{
                        data: [50, 50],
                        backgroundColor: ['#6366f1', '#e2e8f0'],
                        borderWidth: 0,
                        cutout: '75%',
                        borderRadius: 50
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
        }

        // Bar Chart
        const ctxBar = document.getElementById('barChart');
        if (ctxBar) {
            new Chart(ctxBar.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['School Fund (School Fund)'],
                    datasets: [
                        {
                            label: 'Total Sanctioned',
                            data: [20000],
                            backgroundColor: '#3b82f6',
                            borderRadius: 4,
                            barPercentage: 0.3,
                            categoryPercentage: 0.5
                        },
                        {
                            label: 'Total Remaining',
                            data: [14500],
                            backgroundColor: '#22c55e',
                            borderRadius: 4,
                            barPercentage: 0.3,
                            categoryPercentage: 0.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false } // Custom legend in HTML
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 20000,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                },
                                stepSize: 2000,
                                font: { size: 11, color: '#64748b' }
                            },
                            grid: {
                                color: '#e2e8f0',
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, color: '#64748b' } }
                        }
                    }
                }
            });
        }
    });
</script>

</body>
</html>