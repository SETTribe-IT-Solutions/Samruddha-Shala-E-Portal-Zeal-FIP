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

    <li><a href="<?php echo $dashboard_prefix; ?>ceo_dashboard.php"><i class="fa-solid fa-gauge"></i>CEO Dashboard</a></li>
    
    <li><a href="<?php echo $dashboard_prefix; ?>ceo_create_work.php"><i class="fa-solid fa-briefcase"></i> Create Task</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>ceo_task_management.php"><i class="fa-solid fa-plus"></i> Task Management </a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>update_work_master.php"><i class="fa-solid fa-pen"></i> Update Work Master</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>hm_workmaster.php"><i class="fa-solid fa-school"></i> CEO Work Master</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>sachiv_work_master.php"><i class="fa-solid fa-user-tie"></i> Sachiv Work Master</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>amount_utilization.php"><i class="fa-solid fa-indian-rupee-sign"></i> Amount Utilization</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>utility_master.php"><i class="fa-solid fa-screwdriver-wrench"></i> Utility Master</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>create_user.php"><i class="fa-solid fa-user-plus"></i> Create User</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>ceo_alerts.php"><i class="fa-solid fa-bell"></i> Alerts & Notifications</a></li>
<?php } elseif($role == 'SACHIV') { ?>

    <li><a href="<?php echo $dashboard_prefix; ?>sachiv_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>sachiv_work_master.php"><i class="fa-solid fa-list"></i> Sachiv Work Master</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>utility_master.php"><i class="fa-solid fa-screwdriver-wrench"></i> Utility Master</a></li>

<?php } elseif($role == 'HM') { ?>

    <li><a href="<?php echo $dashboard_prefix; ?>hm_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>hm_update_work_progress.php"><i class="fa-solid fa-chart-line"></i> Update Work Progress</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>hm_workmaster.php"><i class="fa-solid fa-school"></i> HM Work Master</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>hm_utilization.php"><i class="fa-solid fa-indian-rupee-sign"></i> Amount Utilization</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>hm_utilitymaster.php"><i class="fa-solid fa-screwdriver-wrench"></i> Utility Master</a></li>
    <li><a href="<?php echo $dashboard_prefix; ?>hm_notification.php"><i class="fa-solid fa-bell"></i> Notification</a></li>

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

function showWorkInProgress(event) {
    event.preventDefault();
    Swal.fire({
        title: 'Work Under Progress',
        text: 'This feature is currently under development.',
        icon: 'info',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0b63b7'
    });
}
</script>
<head>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>
