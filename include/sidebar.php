<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$sessionUsernameRaw = trim((string) ($_SESSION['username'] ?? ''));
$sessionRoleRaw = trim((string) ($_SESSION['role'] ?? ''));

if ($sessionRoleRaw === '' && isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/dbConfig.php';

    if (isset($conn) && $conn instanceof mysqli) {
        $roleStmt = $conn->prepare('SELECT username, role FROM users WHERE id = ? LIMIT 1');
        if ($roleStmt) {
            $userId = (int) $_SESSION['user_id'];
            $roleStmt->bind_param('i', $userId);
            $roleStmt->execute();
            $roleResult = $roleStmt->get_result();

            if ($roleResult && ($dbUser = $roleResult->fetch_assoc())) {
                if ($sessionUsernameRaw === '' && isset($dbUser['username'])) {
                    $sessionUsernameRaw = (string) $dbUser['username'];
                    $_SESSION['username'] = $sessionUsernameRaw;
                }

                if (isset($dbUser['role']) && trim((string) $dbUser['role']) !== '') {
                    $sessionRoleRaw = trim((string) $dbUser['role']);
                    $_SESSION['role'] = strtoupper($sessionRoleRaw);
                }
            }

            $roleStmt->close();
        }
    }
}

if ($sessionRoleRaw === '' && $sessionUsernameRaw !== '') {
    $sessionRoleRaw = $sessionUsernameRaw;
}

$sessionUsername = strtoupper($sessionUsernameRaw);
$sessionRole = strtoupper($sessionRoleRaw);
$isCeoLoggedIn = ($sessionUsername === 'CEO' || $sessionRole === 'CEO');

$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$isDashboardPath = stripos($scriptName, '/dashboard/') !== false;
$isCeoDashboardPage = stripos($scriptName, '/dashboard/ceo_dashboard.php') !== false;
$isAdminUserPage = stripos($scriptName, '/admin_user_management.php') !== false;
$basePath = $isDashboardPath ? '..' : '.';
$adminUserUrl = $basePath . '/admin_user_management.php';
$ceoUpdatesUrl = $basePath . '/CEO_updates.php';
$logoutUrl = $basePath . '/logout.php';
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

    <!-- CEO Specific Sidebar Menu -->
    <ul class="list-unstyled components">
        <p>Monitoring & Analytics</p>

        <li class="active" id="nav-ceo-overview">
            <a href="javascript:void(0)" onclick="switchTab('ceo-overview')">
                <i class="fa-solid fa-chart-pie"></i> Overview Dashboard
            </a>
        </li>

        <li id="nav-ceo-task">
            <a href="javascript:void(0)" onclick="switchTab('ceo-task')">
                <i class="fa-solid fa-file-signature"></i> Assign Task
            </a>
        </li>

        <li id="nav-ceo-physical">
            <a href="javascript:void(0)" onclick="switchTab('ceo-physical')">
                <i class="fa-solid fa-industry"></i> Physical Progress
            </a>
        </li>

        <li id="nav-ceo-funding">
            <a href="javascript:void(0)" onclick="switchTab('ceo-funding')">
                <i class="fa-solid fa-hand-holding-dollar"></i> Funding Distribution
            </a>
        </li>

        <li id="nav-ceo-alerts">
            <a href="javascript:void(0)" onclick="switchTab('ceo-alerts')">
                <i class="fa-solid fa-bell"></i> Alerts & Notifications
                <span id="alertsSidebarBadge" class="badge bg-danger ms-auto rounded-pill d-none">0</span>
            </a>
        </li>

        <p>Database & Reports</p>

        <li id="nav-ceo-monitor">
            <a href="javascript:void(0)" onclick="switchTab('ceo-monitor')">
                <i class="fa-solid fa-list-check"></i> School Project Monitor
            </a>
        </li>

        <li id="nav-ceo-updates">
            <a href="<?php echo htmlspecialchars($ceoUpdatesUrl); ?>">
                <i class="fa-solid fa-pen-to-square"></i> CEO Updates
            </a>
        </li>

        <?php if ($isCeoLoggedIn && ($isCeoDashboardPage || $isAdminUserPage)): ?>
        <li id="nav-ceo-user-management">
            <a href="<?php echo htmlspecialchars($adminUserUrl); ?>">
                <i class="fa-solid fa-users-gear"></i> Create User
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- Footer -->
    <div class="sidebar-footer">
        <p>Mr. Anil Deshmukh</p>
        <p>Head Master</p>

        <div class="logout-wrapper">
            <a href="#" class="logout-btn" onclick="confirmLogout(event)">
    <i class="fas fa-sign-out-alt"></i> Logout
</a>
        </div>

        <!-- <p>Kolhapur District Board</p>
        <p>Version 2.4 (Zeal FIP)</p> -->
    </div>
</nav>
<script>
function confirmLogout(event) {
    event.preventDefault();

    Swal.fire({
        title: 'Logout?',
        text: 'Are you sure you want to logout?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6a1b9a',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Logout',
        cancelButtonText: 'Cancel'
    }).then((result) => {

        if (result.isConfirmed) {

            Swal.fire({
                title: 'Logged Out!',
                text: 'You have been logged out successfully.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {

                window.location.href = '<?php echo htmlspecialchars($logoutUrl); ?>';

            });

        }

    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>