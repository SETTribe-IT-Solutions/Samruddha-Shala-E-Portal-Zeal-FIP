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

<title>HM Utility Master</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="../css/sidebar.css" rel="stylesheet">
<link href="css/hm_dashboard.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="hm-dashboard-page">

<div id="wrapper">

    <!-- Sidebar -->
    <?php include '../include/sidebar.php'; ?>

    <div id="content">

        <!-- Header -->
        <div class="hm-fixed-header">
            <?php include '../include/website_header.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="container-fluid p-4">

            <div class="card shadow-lg border-0 text-center p-5">

                <i class="fa-solid fa-screwdriver-wrench fa-5x text-primary mb-4"></i>

                <h2 class="fw-bold">
                    HM Utility Master
                </h2>

                <p class="text-muted fs-5">
                    This module is currently under development.
                </p>

                <div class="progress mt-4 mb-4" style="height:12px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                         style="width:70%">
                    </div>
                </div>

                <a href="hm_dashboard.php" class="btn btn-primary">
                    <i class="fa-solid fa-arrow-left me-2"></i>
                    Back to Dashboard
                </a>

            </div>

        </div>

        <!-- Footer -->
        <div class="hm-fixed-footer">
            <?php include '../include/website_footer.php'; ?>
        </div>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        title: '🚧 Work in Progress',
        html: '<b>This feature is currently under development.</b><br>It will be available in the next update.',
        icon: 'warning',
        confirmButtonText: 'Got It',
        confirmButtonColor: '#198754',
        backdrop: true,
        allowOutsideClick: false
    }).then(() => {
        window.location.href = 'hm_dashboard.php';
    });
});
</script>

</body>
</html>