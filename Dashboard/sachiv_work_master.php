<?php
session_start();

if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['username']) ||
    ($_SESSION['role'] != 'SACHIV' && $_SESSION['role'] != 'CEO')
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

<title>Sachiv Work Master</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<!-- Custom Style Sheets -->
<link href="../css/sidebar.css" rel="stylesheet">
<link rel="stylesheet" href="css/sachiv_dashboard.css?v=<?php echo time(); ?>">

<style>
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
.animate-bounce {
    animation: bounce 2s infinite ease-in-out;
}
</style>

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
            <i class="fa-solid fa-list text-primary"></i>
            Sachiv Work Master
        </h3>

        <p class="text-muted">
            Welcome to Samruddha Shala E-Portal
        </p>
    </div>

    <!-- WORK IN PROGRESS CONTAINER -->
    <div class="card p-5 text-center shadow-lg border-0 text-white" style="border-radius: 16px; background: linear-gradient(135deg, #6a1b9a 0%, #8e44ad 35%, #b76db8 65%, #f5c542 100%);">
        <div class="mb-4" style="color: #f5c542;">
            <i class="fa-solid fa-screwdriver-wrench fa-4x animate-bounce"></i>
        </div>
        <h2 class="fw-bold mb-3">Feature Under Construction</h2>
        <p class="fs-5 mx-auto" style="max-width: 600px; color: rgba(255,255,255,0.95);">
            The <strong>Sachiv Work Master</strong> module is currently under active development. Our team is building advanced administrative tools to manage school works and configurations.
        </p>
        <div class="progress my-4 mx-auto" style="max-width: 400px; height: 10px; border-radius: 5px; background: rgba(255, 255, 255, 0.25);">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="mt-4">
            <a href="<?php echo ($role === 'CEO') ? 'ceo_dashboard.php' : 'sachiv_dashboard.php'; ?>" class="btn btn-light px-4 py-2 fw-bold text-dark shadow-sm" style="border-radius: 8px;">
                <i class="fa-solid fa-arrow-left me-2"></i> Go Back to Dashboard
            </a>
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

<!-- SweetAlert2 popup trigger -->
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
    });
});
</script>

</body>
</html>
