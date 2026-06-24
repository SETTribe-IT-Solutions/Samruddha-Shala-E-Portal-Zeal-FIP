<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? '';
$username = $_SESSION['username'] ?? '';
$userFullName = '';
$userRole = '';

// Fetch user name and role from database
if (!empty($username)) {
    require_once __DIR__ . '/dbConfig.php';
    $stmt = $conn->prepare('SELECT name, role FROM users WHERE username = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $userFullName = !empty($row['name']) ? $row['name'] : $username;
            $userRole = $row['role'] ?? $role;
        }
        $stmt->close();
    }
}

// Fallback if not found
if (empty($userFullName)) {
    $userFullName = $username ?: 'User';
}
if (empty($userRole)) {
    $userRole = $role ?: 'Guest';
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

    <li><a href="ceo_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
    
    <li><a href="ceo_create_work.php"><i class="fa-solid fa-briefcase"></i> Create Task</a></li>
    <li><a href="ceo_task_management.php"><i class="fa-solid fa-plus"></i> Task Management </a></li>
    <li><a href="update_work_master.php"><i class="fa-solid fa-pen"></i> Update Work Master</a></li>
    <li><a href="hm_work_master.php"><i class="fa-solid fa-school"></i> CEO Work Master</a></li>
    <li><a href="sachiv_work_master.php"><i class="fa-solid fa-user-tie"></i> Sachiv Work Master</a></li>
    <li><a href="amount_utilization.php"><i class="fa-solid fa-indian-rupee-sign"></i> Amount Utilization</a></li>
    <li><a href="utility_master.php"><i class="fa-solid fa-screwdriver-wrench"></i> Utility Master</a></li>
    <li><a href="create_user.php"><i class="fa-solid fa-user-plus"></i> Create User</a></li>
    <li><a href="ceo_alerts.php"><i class="fa-solid fa-bell"></i> Alerts & Notifications</a></li>
<?php } elseif($role == 'SACHIV') { ?>

    <li><a href="sachiv_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
    <li><a href="sachiv_work_master.php"><i class="fa-solid fa-list"></i> Sachiv Work Master</a></li>
    <li><a href="utility_master.php"><i class="fa-solid fa-screwdriver-wrench"></i> Utility Master</a></li>

<?php } elseif($role == 'HM') { ?>

<li><a href="hm_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
<li>
    <a href="hm_update_work_progress.php">
        <i class="fa-solid fa-bell"></i>HM Update Work Progress
    </a>
</li>   
<li>
    <a href="hm_workmaster.php">
        <i class="fa-solid fa-chart-line"></i>HM Work Master
    </a>
</li>
<li><a href="hm_utilization.php"><i class="fa-solid fa-indian-rupee-sign"></i> Amount Utilization</a></li>
<li>
    <a href="hm_utilitymaster.php">
        <i class="fa-solid fa-chart-line"></i>HM Utility Master
    </a>
</li>   
<li>
    <a href="hm_notification.php">
        <i class="fa-solid fa-bell"></i>HM Notification
    </a>
</li>   

<?php } ?>

</ul>

    <!-- Footer -->
 <<div class="sidebar-footer" style="text-align:center;">
    <i class="fa-solid fa-user-tie" style="font-size:32px;color:white;display:block;margin-bottom:8px;"></i>
    <p style="color:white;font-weight:600;margin:0;">HM Dashboard</p>
</div>
<div class="logout-wrapper">
    <a href="#" class="logout-btn" onclick="confirmLogout(event)">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
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
            window.location.href = '../logout.php';
        }
    });
}
</script>
<head>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>
