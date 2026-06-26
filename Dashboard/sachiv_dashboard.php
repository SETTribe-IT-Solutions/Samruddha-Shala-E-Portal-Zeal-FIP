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
<link rel="stylesheet" href="css/sachiv_dashboard.css?v=<?php echo time(); ?>">

</head>
<body class="sachiv-dashboard-page">

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

    <div class="mb-4">
        <h3 class="fw-bold">
            <i class="fa-solid fa-user-tie text-primary"></i>
            Sachiv Dashboard
        </h3>

        <p class="text-muted">
            Welcome to Samruddha Shala E-Portal
        </p>
    </div>

    <!-- KPI CARDS -->
    <div class="row g-4">

        <div class="col-md-3">
            <div class="card dashboard-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Total Works</h6>
                        <h2>25</h2>
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
                        <h2>15</h2>
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
                        <h2>7</h2>
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
                        <h2>3</h2>
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
            <div class="card dashboard-card p-4">

                <h5>Quick Actions</h5>

                <a href="sachiv_work_master.php"
                   class="btn btn-primary w-100 mb-2">
                    <i class="fa-solid fa-list"></i>
                    Work Master
                </a>

                <a href="utility_master.php"
                   class="btn btn-success w-100">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                    Utility Master
                </a>

            </div>
        </div>

        <div class="col-md-6">
            <div class="card dashboard-card p-4">

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
                            <td>Toilet Construction</td>
                            <td>CSR Fund</td>
                            <td>
                                <span class="badge bg-warning">
                                    Pending
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td>3</td>
                            <td>Water Tank Installation</td>
                            <td>Annual Plan</td>
                            <td>
                                <span class="badge bg-primary">
                                    Ongoing
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
