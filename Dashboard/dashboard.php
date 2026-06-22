<?php
require_once __DIR__ . '/../include/auth.php';
requireLogin();

$user = $_SESSION['user'];

if ($user['role'] === 'CEO') {
    header('Location: ceo_dashboard.php');
    exit;
} elseif ($user['role'] === 'HM') {
    header('Location: hm_dashboard.php');
    exit;
} elseif ($user['role'] === 'Sachiv') {
    header('Location: sachiv_dashboard.php');
    exit;
}
?>
<?php require_once __DIR__ . '/../include/landing_header.php'; ?>
<?php require_once __DIR__ . '/../include/navbar.php'; ?>

<div class="container py-5">
    <div class="card dashboard-card mb-4">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <p class="text-muted mb-1">Logged in as</p>
                <h3 class="fw-bold mb-1">
                    <?php echo htmlspecialchars($user['name']); ?>
                </h3>
                <p class="mb-0 text-primary fw-semibold">
                    Role: <?php echo htmlspecialchars($user['role']); ?>
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="../login.php" class="btn btn-outline-primary btn-sm">Separate Login</a>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </div>

    <div class="alert alert-warning mb-4">
        <strong>Welcome!</strong> You are signed in as <span class="fw-semibold"><?php echo htmlspecialchars($user['role']); ?></span> and can access the dashboard features assigned to your role.
    </div>

    <div class="row g-4">
        <?php if ($user['role'] === 'CEO' || $user['role'] === 'Sachiv'): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card dashboard-card h-100 p-3">
                    <h5 class="fw-bold">School Overview</h5>
                    <p class="text-muted mb-0">View overall school performance and key indicators.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($user['role'] === 'CEO' || $user['role'] === 'HM'): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card dashboard-card h-100 p-3">
                    <h5 class="fw-bold">Student Monitoring</h5>
                    <p class="text-muted mb-0">Track attendance, performance and student records.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($user['role'] === 'CEO' || $user['role'] === 'Sachiv' || $user['role'] === 'HM'): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card dashboard-card h-100 p-3">
                    <h5 class="fw-bold">Reports & Notices</h5>
                    <p class="text-muted mb-0">Manage announcements, circulars and reports.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../include/landing_footer.php'; ?>
<?php require_once __DIR__ . '/../include/script.php'; ?>
