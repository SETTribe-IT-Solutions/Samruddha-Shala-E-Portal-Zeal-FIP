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
    <title>Samruddha Shala E-Portal - Update Work Progress</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- CSS -->
    <link href="../css/sidebar.css" rel="stylesheet">
    <link href="css/hm_dashboard.css?v=1.0.1" rel="stylesheet">
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
                    <a href="hm_dashboard.php" class="btn btn-sm btn-outline-secondary fw-semibold">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </nav>

        <!-- Form and List Content -->
        <div class="container-fluid p-0">

            <!-- Under Maintenance Section -->
            <div class="card p-5 text-center shadow-sm border-0 rounded-4 my-3" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
                <div class="card-body py-4">
                    <div class="mb-4">
                        <img src="../images/maintenance_boy.png?v=2" alt="Maintenance Boy" style="width: 240px; max-width: 100%; height: auto;" class="img-fluid rounded-4 shadow-sm mb-2">
                    </div>
                    <h2 class="fw-bold text-dark mb-3">
                        We're Under <span style="color: #2563eb;">Maintenance!</span>
                    </h2>
                    <p class="text-muted fs-6 mx-auto mb-4" style="max-width: 550px; line-height: 1.6;">
                        The <strong>Update Work Progress</strong> section is currently undergoing scheduled maintenance and system upgrades to enhance performance. Please check back again soon!
                    </p>
                    <div class="d-inline-flex align-items-center gap-2 px-4 py-2.5 rounded-pill mb-4 shadow-sm" style="background-color: #f0f4ff; color: #1e3a8a; font-weight: 600; font-size: 0.95rem;">
                        <i class="fa-regular fa-face-smile text-primary me-1 fs-5"></i>
                        <span>Thank you for your patience and support!</span>
                        <i class="fa-solid fa-heart text-primary ms-1"></i>
                    </div>
                    <div class="mt-2">
                        <a href="hm_dashboard.php" class="btn btn-primary px-4 py-2.5 rounded-3 fw-semibold shadow-sm">
                            <i class="fa-solid fa-house me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Hidden original elements for script compatibility -->
            <div class="d-none">
                <select id="hmSchoolSelect" class="form-select d-none" onchange="loadHMSchoolSpecificDetails(this.value)"></select>
                <div id="worksCountBadge"></div>
                <input type="text" id="hmSchoolSearch">
                <div id="hmSchoolList"></div>
                <strong id="hmSelectedSchoolName"></strong>
                <span id="hmSelectedBlockBadge"></span>
                <div id="hmTaskAlertBanner"></div>
                <span id="hmTaskAlertText"></span>
                <form id="hmUpdateForm">
                    <input type="text" id="hmWorkType">
                    <input type="text" id="hmFundingSource">
                    <span id="hmProgressValueText"></span>
                    <input type="range" id="hmProgressRange">
                    <input type="number" id="hmSpentAmount">
                    <input type="text" id="hmBudgetNotes">
                    <select id="hmBlockerSelector"></select>
                    <div id="hmBlockerDetailsContainer"></div>
                    <textarea id="hmBlockerDetails"></textarea>
                    <input type="text" id="hmGeoCoordinates">
                    <input type="hidden" id="hmGeotagInput">
                    <i id="uploadZoneIcon"></i>
                    <p id="uploadZoneText"></p>
                    <img id="photoPreview">
                    <input type="file" id="hmPhotoFile">
                    <textarea id="hmRemarks"></textarea>
                </form>
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

<!-- Database scripts -->
<script src="js/db.js"></script>

<!-- Dashboard dynamic controller -->
<script src="js/hm_update.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        html: `
            <div style="text-align: center; padding: 10px 0;">
                <img src="../images/maintenance_boy.png?v=2" alt="Maintenance Boy" style="width: 230px; margin-bottom: 20px; border-radius: 15px;">
                <h2 style="color: #1e293b; font-weight: 800; margin-bottom: 15px;">
                    We're Under <span style="color: #2563eb;">Maintenance!</span>
                </h2>
                <p style="color: #475569; font-size: 1.05rem; line-height: 1.5; margin-bottom: 25px;">
                    We're currently working on making things better.<br>
                    Please check back again soon!
                </p>
                <div style="background-color: #f0f4ff; color: #1e3a8a; padding: 12px 25px; border-radius: 50px; display: inline-block; font-weight: 600; font-size: 0.95rem;">
                    <i class="fa-regular fa-face-smile text-primary me-2"></i> 
                    Thank you for your patience and support! 
                    <i class="fa-solid fa-heart text-primary ms-1"></i>
                </div>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: '<i class="fa-solid fa-house me-2"></i>Back to Dashboard',
        confirmButtonColor: '#2563eb',
        showCloseButton: true,
        allowOutsideClick: true,
        width: '550px',
        padding: '2em',
        customClass: {
            popup: 'rounded-4 shadow-lg border-0'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'hm_dashboard.php';
        }
    });
});
</script>

</body>
</html>