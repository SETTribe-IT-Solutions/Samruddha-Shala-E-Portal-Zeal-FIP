<?php
session_start();
require_once __DIR__ . '/include/dbConfig.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$currentUsername = $_SESSION['username'] ?? '';
$currentRole = $_SESSION['role'] ?? '';
$isAdmin = in_array($currentUsername, ['CEO'], true) || in_array($currentRole, ['CEO'], true);

if (!$isAdmin) {
    http_response_code(403);
}

$errors = [];
$success = '';
$userColumns = [];
$userRows = [];

$columnsResult = $conn->query("SHOW COLUMNS FROM users");
if ($columnsResult) {
    while ($col = $columnsResult->fetch_assoc()) {
        $userColumns[] = $col['Field'];
    }
} else {
    $errors[] = 'Unable to read users table schema.';
}

// Ensure role column exists so role can be stored and listed.
if (!in_array('role', $userColumns, true)) {
    if ($conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'HM'")) {
        $userColumns[] = 'role';
    } else {
        $errors[] = 'Unable to add role column to users table.';
    }
}

$hasUsername = in_array('username', $userColumns, true);
$hasPassword = in_array('password', $userColumns, true);
$hasRole = in_array('role', $userColumns, true);
$hasIsActive = in_array('is_active', $userColumns, true);

if ($hasRole && $hasUsername) {
    $conn->query("UPDATE users SET role = 'Sachiv' WHERE username = 'Sachiv' AND role <> 'Sachiv'");
    $conn->query("UPDATE users SET role = 'HM' WHERE username = 'HM' AND role <> 'HM'");
}

$displayColumns = $userColumns;
if ($hasRole && $hasUsername) {
    $roleIndex = array_search('role', $displayColumns, true);
    $usernameIndex = array_search('username', $displayColumns, true);
    if ($roleIndex !== false && $usernameIndex !== false && $roleIndex > $usernameIndex) {
        unset($displayColumns[$roleIndex]);
        $displayColumns = array_values($displayColumns);
        $usernameIndex = array_search('username', $displayColumns, true);
        array_splice($displayColumns, $usernameIndex, 0, ['role']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user']) && $isAdmin) {
    $newUsername = trim($_POST['username'] ?? '');
    $newPassword = trim($_POST['password'] ?? '');
    $newRole = trim($_POST['role'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (!$hasUsername || !$hasPassword) {
        $errors[] = 'The users table does not support username/password based creation.';
    }

    if ($newUsername === '') {
        $errors[] = 'Username is required.';
    }

    if ($newPassword === '') {
        $errors[] = 'Password is required.';
    }

    if ($hasRole && $newRole === '') {
        $errors[] = 'Please select a role.';
    }

    if ($hasRole && $newRole !== '' && !in_array($newRole, ['HM', 'Sachiv', 'CEO'], true)) {
        $errors[] = 'Please choose a valid role.';
    }

    if (empty($errors)) {
        $checkStmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM users WHERE username = ?');
        if ($checkStmt) {
            $checkStmt->bind_param('s', $newUsername);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $existing = $checkResult ? (int) ($checkResult->fetch_assoc()['cnt'] ?? 0) : 0;
            $checkStmt->close();

            if ($existing > 0) {
                $errors[] = 'Username already exists. Please choose another username.';
            }
        } else {
            $errors[] = 'Unable to validate username uniqueness.';
        }
    }

    if (empty($errors)) {
        if ($hasRole && $hasIsActive) {
            $insertStmt = $conn->prepare('INSERT INTO users (username, password, role, is_active) VALUES (?, ?, ?, ?)');
            if ($insertStmt) {
                $insertStmt->bind_param('sssi', $newUsername, $newPassword, $newRole, $isActive);
            }
        } elseif ($hasRole) {
            $insertStmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
            if ($insertStmt) {
                $insertStmt->bind_param('sss', $newUsername, $newPassword, $newRole);
            }
        } elseif ($hasIsActive) {
            $insertStmt = $conn->prepare('INSERT INTO users (username, password, is_active) VALUES (?, ?, ?)');
            if ($insertStmt) {
                $insertStmt->bind_param('ssi', $newUsername, $newPassword, $isActive);
            }
        } else {
            $insertStmt = $conn->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
            if ($insertStmt) {
                $insertStmt->bind_param('ss', $newUsername, $newPassword);
            }
        }

        if (!isset($insertStmt) || !$insertStmt) {
            $errors[] = 'Unable to prepare user creation query.';
        } elseif ($insertStmt->execute()) {
            $success = 'User created successfully.';
            $insertStmt->close();
        } else {
            $errors[] = 'Failed to create user: ' . $conn->error;
            $insertStmt->close();
        }
    }
}

if (in_array('id', $userColumns, true)) {
    $usersResult = $conn->query('SELECT * FROM users ORDER BY id ASC');
} else {
    $usersResult = $conn->query('SELECT * FROM users');
}

if ($usersResult) {
    while ($row = $usersResult->fetch_assoc()) {
        $userRows[] = $row;
    }
} else {
    $errors[] = 'Unable to fetch users list.';
}
?>
<?php include 'include/landing_header.php'; ?>
<?php include 'include/website_header.php'; ?>

<style>
.admin-wrapper {
    display: flex;
    min-height: calc(100vh - 120px);
}

.admin-sidebar {
    width: 270px;
    min-width: 270px;
    background: linear-gradient(180deg, var(--primary-color), #7c3aed);
    color: #fff;
    padding: 1.25rem 1rem;
    transition: margin-left 0.25s ease;
}

.admin-sidebar .sidebar-title {
    font-size: 1.05rem;
    font-weight: 700;
    margin-bottom: 0.2rem;
}

.admin-sidebar .sidebar-subtitle {
    font-size: 0.78rem;
    opacity: 0.9;
    margin-bottom: 1rem;
}

.admin-sidebar .menu-label {
    font-size: 0.74rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    opacity: 0.8;
    margin: 1rem 0 0.55rem;
}

.admin-sidebar .nav-link {
    color: rgba(255, 255, 255, 0.92);
    border-radius: 0.65rem;
    padding: 0.55rem 0.7rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.55rem;
}

.admin-sidebar .nav-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.12);
}

.admin-sidebar .nav-link.active {
    background: #fff;
    color: var(--primary-color);
    font-weight: 600;
}

.admin-sidebar .sidebar-footer {
    margin-top: 1.25rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.admin-content {
    flex: 1;
    min-width: 0;
}

.admin-page {
    background: var(--page-bg);
    min-height: 100%;
    padding: 24px 0 40px;
}

.admin-card {
    border: none;
    border-radius: 14px;
    box-shadow: 0 10px 24px rgba(47, 28, 82, 0.12);
}

.admin-card .card-header {
    background: linear-gradient(90deg, var(--primary-color), #7c3aed);
    color: #fff;
    border: none;
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
    padding: 0.95rem 1.15rem;
}

.page-title {
    color: var(--primary-color);
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.page-subtitle {
    color: #5b4b77;
    margin-bottom: 0;
}

.table thead th {
    background: #efe8ff;
    color: #3f2b67;
    font-weight: 600;
    border-bottom-width: 1px;
}

.table td {
    vertical-align: middle;
}

.status-pill {
    border-radius: 999px;
    padding: 0.22rem 0.65rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.form-label {
    font-weight: 600;
    color: #3f2b67;
}

.btn-admin {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: #fff;
}

.btn-admin:hover {
    background: #4c1475;
    border-color: #4c1475;
    color: #fff;
}

.admin-topbar {
    background: #fff;
    border-bottom: 1px solid #ede7ff;
    box-shadow: 0 2px 10px rgba(47, 28, 82, 0.06);
}

.admin-topbar .topbar-title {
    color: #3f2b67;
    font-weight: 700;
    margin-bottom: 0;
}

#sidebar.active {
    margin-left: -270px;
}

@media (max-width: 768px) {
    .admin-wrapper {
        flex-direction: column;
    }

    .admin-sidebar {
        width: 100%;
        min-width: 100%;
    }

    #sidebar.active {
        margin-left: 0;
    }

    .admin-page {
        padding: 16px 0 24px;
    }
}
</style>

<div class="admin-wrapper">
    <nav id="sidebar" class="admin-sidebar">
        <div class="sidebar-title">
            <i class="fa-solid fa-users-gear me-1"></i> Samruddha Shala
        </div>
        <div class="sidebar-subtitle">CEO Controls</div>

        <div class="menu-label">Management</div>
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link active" href="admin_user_management.php">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>User Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Dashboard/ceo_dashboard.php">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>CEO Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="CEO_updates.php">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>CEO Updates</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a class="nav-link" href="logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <div id="content" class="admin-content">
    <nav class="navbar admin-topbar py-2 px-3">
        <div class="container-fluid px-0">
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary btn-sm" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h5 class="topbar-title">CEO User Management</h5>
            </div>
        </div>
    </nav>

<div class="admin-page">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
            <div>
                <h2 class="page-title">CEO User Management</h2>
                <p class="page-subtitle">Create user accounts and manage the master users list.</p>
            </div>
        </div>

        <?php if (!$isAdmin): ?>
            <div class="alert alert-danger">Access denied. Only CEO can use this page.</div>
        <?php else: ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card admin-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Create New User</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <?php if ($hasRole): ?>
                                <div class="col-md-3">
                                    <label class="form-label" for="role">Role</label>
                                    <select id="role" name="role" class="form-select" required>
                                        <option value="" selected disabled>Select</option>
                                        <option value="HM">HM</option>
                                        <option value="Sachiv">Sachiv</option>
                                        <option value="CEO">CEO</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-4">
                                <label class="form-label" for="username">Username</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="password">Password</label>
                                <input type="text" id="password" name="password" class="form-control" required>
                            </div>
                            <?php if ($hasIsActive): ?>
                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-3">
                            <button type="submit" name="create_user" class="btn btn-admin">Create User</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Users Master Table</h5>
                    <span class="badge text-bg-light"><?php echo count($userRows); ?> users</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <?php foreach ($displayColumns as $column): ?>
                                        <th><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $column))); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($userRows)): ?>
                                    <tr>
                                        <td colspan="<?php echo max(1, count($displayColumns)); ?>" class="text-center py-4 text-muted">
                                            No users found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($userRows as $userRow): ?>
                                        <tr>
                                            <?php foreach ($displayColumns as $column): ?>
                                                <?php $cellValue = $userRow[$column] ?? ''; ?>
                                                <td>
                                                    <?php if ($column === 'password'): ?>
                                                        <span class="text-muted">********</span>
                                                    <?php elseif ($column === 'is_active'): ?>
                                                        <?php if ((string) $cellValue === '1'): ?>
                                                            <span class="status-pill status-active">Active</span>
                                                        <?php else: ?>
                                                            <span class="status-pill status-inactive">Inactive</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <?php echo htmlspecialchars((string) $cellValue); ?>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}
</script>

<?php include 'include/website_footer.php'; ?>
<?php include 'include/script.php'; ?>
