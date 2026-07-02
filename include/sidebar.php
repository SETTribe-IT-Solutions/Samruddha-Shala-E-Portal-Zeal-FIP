<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? '';
$sessionUsername = $_SESSION['username'] ?? '';
$userFullName = '';
$userRole = '';

// Fetch user name and role from database
if (!empty($sessionUsername)) {
    require_once __DIR__ . '/dbConfig.php';
    $stmt = $conn->prepare('SELECT name, role FROM users WHERE username = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $sessionUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $userFullName = $row['name'];
            $userRole = $row['role'];
            $role = strtoupper($userRole); // ensure sidebar menus use the DB role
        }
        $stmt->close();
    }
}

// Fallback if not found
if (empty($userFullName)) {
    $userFullName = $sessionUsername ?: 'User';
}
if (empty($userRole)) {
    $userRole = $role ?: 'Guest';
}

// Dynamic routing prefix
$current_dir = basename(getcwd());
if ($current_dir === 'Dashboard') {
    $dashboard_prefix = "";
    $root_prefix = "../";
} else {
    $dashboard_prefix = "Dashboard/";
    $root_prefix = "";
}
?>
<!-- Sidebar Navigation -->
<nav id="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0 text-white font-weight-bold">
            <i class="fa-solid fa-graduation-cap me-2 text-primary"></i>
            Samruddha Shala
        </h4>
        <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 1px;">
            E-Portal System
        </small>
    </div>

    <!-- CEO Specific Sidebar Role -->
    <ul class="list-unstyled components">

<?php if($role == 'CEO') { ?>

    <li><a href="ceo_dashboard.php"><i class="fa-solid fa-gauge"></i><span data-i18n="sideDashboard">CEO Dashboard</span></a></li>
    <li><a href="ceo_task_management.php"><i class="fa-solid fa-circle-plus"></i><span data-i18n="sideCreateStages">Create Stages</span></a></li>
    <li><a href="ceo_create_work.php"><i class="fa-solid fa-list-check"></i><span data-i18n="sideStagesReport">Stages Report</span></a></li>
    <li><a href="work_report.php"><i class="fa-solid fa-file-lines"></i><span data-i18n="sideWorkReport">Work Report</span></a></li>
    <li><a href="funding_report.php"><i class="fa-solid fa-file-invoice-dollar"></i><span data-i18n="sideFundingReport">Funding Report</span></a></li>
    <li><a href="create_user.php"><i class="fa-solid fa-user-plus"></i><span data-i18n="sideCreateUser">Create User</span></a></li>
    <li><a href="amount_utilization_ceo.php"><i class="fa-solid fa-indian-rupee-sign"></i><span data-i18n="sideFundUtil">Fund Utilization Details</span></a></li>
<?php } elseif($role == 'SACHIV') { ?>

    <li><a href="sachiv_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
    <li><a href="#" onclick="showMaintenanceModal(event)"><i class="fa-solid fa-list"></i> Sachiv Work Master</a></li>
    <li><a href="#" onclick="showMaintenanceModal(event)"><i class="fa-solid fa-screwdriver-wrench"></i> Utility Master</a></li>

<?php } elseif($role == 'HM') { ?>

    <li><a href="hm_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
    <li><a href="update_work_progress.php"><i class="fa-solid fa-chart-line"></i> Update Work Progress</a></li>
    <li><a href="#" onclick="showMaintenanceModal(event)"><i class="fa-solid fa-school"></i> HM Work Master</a></li>
    <li><a href="hm_utilization.php"><i class="fa-solid fa-indian-rupee-sign"></i> Amount Utilization</a></li>
    <li><a href="#" onclick="showMaintenanceModal(event)"><i class="fa-solid fa-screwdriver-wrench"></i> Utility Master</a></li>
    <li><a href="notification.php"><i class="fa-solid fa-bell"></i> Notification</a></li>

<?php } ?>

</ul>

    <!-- Footer Profile Area -->
    <div class="sidebar-footer d-flex flex-column align-items-center" style="padding-bottom: 20px;">
        <div class="user-profile mb-3 d-flex flex-column align-items-center w-100">
            <div class="rounded-circle bg-white d-flex justify-content-center align-items-center mb-2 shadow-sm" style="width: 48px; height: 48px; color: #6a1b9a;">
                <i class="fa-solid fa-user-tie fs-4"></i>
            </div>
            <p class="mb-1 text-white fw-bold text-center" style="font-size: 1.05rem; text-shadow: 0 2px 4px rgba(0,0,0,0.2);"><?php echo htmlspecialchars($userFullName); ?></p>
            <span class="badge bg-white text-dark fw-bold shadow-sm" style="letter-spacing: 0.5px; font-size: 0.75rem; padding: 5px 10px; border-radius: 12px;"><?php echo htmlspecialchars($userRole); ?></span>
        </div>
        
        <div class="logout-wrapper w-100 p-0 m-0">
            <a href="#" class="logout-btn" onclick="confirmLogout(event)">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmLogout(event) {
    event.preventDefault();

    Swal.fire({
        title: 'Do you want to logout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?php echo $root_prefix; ?>logout.php';
        }
    });
}

function showWorkInProgress(event, moduleName) {
    event.preventDefault();
    const titleText = moduleName ? `${moduleName} Under Development` : 'Work Under Progress';
    const textMsg = moduleName ? `The ${moduleName} module is currently under development.` : 'This feature is currently under development.';
    Swal.fire({
        title: titleText,
        text: textMsg,
        icon: 'info',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0b63b7'
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const currentLocation = window.location.pathname.split("/").pop();
    const sidebarLinks = document.querySelectorAll("#sidebar .components li a");
    sidebarLinks.forEach(function(link) {
        const href = link.getAttribute("href");
        if(href && href !== '#' && currentLocation.includes(href)) {
            link.parentElement.classList.add("active");
        }
    });
});

function showMaintenanceModal(event) {
    event.preventDefault();
    Swal.fire({
        html: `
            <div style="text-align: center; padding: 10px 0;">
                <img src="../images/maintenance_boy.png?v=2" alt="Maintenance Boy" style="width: 250px; margin-bottom: 20px; border-radius: 15px;">
                <h2 style="color: #1e293b; font-weight: 800; margin-bottom: 15px; font-family: 'Outfit', sans-serif;">
                    We're Under <span style="color: #2563eb;">Maintenance!</span>
                </h2>
                <p style="color: #475569; font-size: 1.05rem; line-height: 1.5; margin-bottom: 30px;">
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
        showConfirmButton: false,
        showCloseButton: true,
        width: '550px',
        padding: '2em',
        background: '#fff',
        backdrop: `
            rgba(0,0,0,0.4)
            backdrop-filter: blur(8px)
            left top
            no-repeat
        `,
        customClass: {
            popup: 'rounded-4 shadow-lg border-0'
        }
    });
}
</script>
<head>
    <link rel="stylesheet" href="<?php echo $root_prefix; ?>css/style.css?v=1.0.1">
    <link rel="stylesheet" href="<?php echo $root_prefix; ?>css/sidebar.css?v=1.0.1">
</head>
