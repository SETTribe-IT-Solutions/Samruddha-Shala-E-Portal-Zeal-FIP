<?php
session_start();
require_once __DIR__ . '/../include/dbConfig.php';

if (!isset($_SESSION['user_id'])) {
	header('Location: ../login.php');
	exit();
}

$currentUsername = $_SESSION['username'] ?? '';
$currentRole = $_SESSION['role'] ?? '';
$isAdmin = in_array($currentUsername, ['CEO', 'Admin'], true) || in_array($currentRole, ['CEO', 'Admin'], true);

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

if (!in_array('role', $userColumns, true)) {
	if ($conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'HM'")) {
		$userColumns[] = 'role';
	} else {
		$errors[] = 'Unable to add role column to users table.';
	}
}

if (!in_array('name', $userColumns, true)) {
	if ($conn->query("ALTER TABLE users ADD COLUMN name VARCHAR(100) NOT NULL DEFAULT ''")) {
		$userColumns[] = 'name';
	} else {
		$errors[] = 'Unable to add name column to users table.';
	}
}

if (!in_array('school_name', $userColumns, true)) {
	if ($conn->query("ALTER TABLE users ADD COLUMN school_name VARCHAR(150) NULL")) {
		$userColumns[] = 'school_name';
	} else {
		$errors[] = 'Unable to add school_name column to users table.';
	}
}

$hasUsername = in_array('username', $userColumns, true);
$hasPassword = in_array('password', $userColumns, true);
$hasRole = in_array('role', $userColumns, true);
$hasName = in_array('name', $userColumns, true);
$hasSchoolName = in_array('school_name', $userColumns, true);
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
	$newName = trim($_POST['name'] ?? '');
	$newSchoolName = trim($_POST['school_name'] ?? '');
	$isActive = isset($_POST['is_active']) ? 1 : 0;

	if (!$hasUsername || !$hasPassword) {
		$errors[] = 'The users table does not support username/password based creation.';
	}

	if ($newUsername === '') {
		$errors[] = 'Username is required.';
	} elseif (preg_match('/^\d/', $newUsername)) {
		$errors[] = 'Username should not start with a number.';
	}

	if ($newPassword === '') {
		$errors[] = 'Password is required.';
	}

	if ($hasName && $newName === '') {
		$errors[] = 'Full name is required.';
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
		// Use dynamic query builder to support varying columns safely
		$cols = ['username', 'password'];
		$vals = [$newUsername, $newPassword];
		$types = 'ss';

		if ($hasRole) {
			$cols[] = 'role';
			$vals[] = $newRole;
			$types .= 's';
		}
		if ($hasName) {
			$cols[] = 'name';
			$vals[] = $newName;
			$types .= 's';
		}
		if ($hasSchoolName) {
			$cols[] = 'school_name';
			$vals[] = ($newSchoolName !== '') ? $newSchoolName : null;
			$types .= 's';
		}
		if ($hasIsActive) {
			$cols[] = 'is_active';
			$vals[] = $isActive;
			$types .= 'i';
		}

		$colsStr = implode(', ', array_map(function($c) { return "`$c`"; }, $cols));
		$placeholders = implode(', ', array_fill(0, count($cols), '?'));
		
		$insertStmt = $conn->prepare("INSERT INTO users ($colsStr) VALUES ($placeholders)");
		if ($insertStmt) {
			$insertStmt->bind_param($types, ...$vals);
		}

		if (!isset($insertStmt) || !$insertStmt) {
			$errors[] = 'Unable to prepare user creation query.';
		} elseif ($insertStmt->execute()) {
			$success = 'User created successfully.';
			$insertStmt->close();
		} else {
			$errors[] = 'Failed to create user: ' . $conn->error;
			if (isset($insertStmt) && $insertStmt) {
				$insertStmt->close();
			}
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
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Samruddha Shala E-Portal - Create User</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<link href="../css/sidebar.css" rel="stylesheet">
	<style>
		:root {
			--shell-sidebar-width: 250px;
			--shell-header-height: 64px;
			--shell-footer-height: 60px;
		}

		body.create-user-shell {
			margin: 0;
			padding: 0;
			min-height: 100vh;
			display: block;
			overflow: hidden;
			font-family: 'Outfit', sans-serif;
			background:
				radial-gradient(circle at top left, rgba(123, 92, 255, 0.08), transparent 28%),
				linear-gradient(180deg, #eef6fb 0%, #f7fafc 100%);
		}

		body.create-user-shell #wrapper {
			min-height: 100vh;
		}

		body.create-user-shell #sidebar {
			position: fixed;
			top: 0;
			left: 0;
			width: var(--shell-sidebar-width);
			min-width: var(--shell-sidebar-width);
			max-width: var(--shell-sidebar-width);
			height: 100vh;
			z-index: 1100;
			overflow: hidden;
			background: linear-gradient(180deg, #6420a5 0%, #8a45b8 54%, #efbc4d 100%);
			box-shadow: 10px 0 32px rgba(91, 35, 140, 0.22);
		}

		body.create-user-shell #sidebar .sidebar-header {
			padding: 20px 16px 16px;
		}

		body.create-user-shell #sidebar .sidebar-header h4 {
			font-size: 18px;
			line-height: 1.2;
		}

		body.create-user-shell #sidebar .sidebar-header small {
			font-size: 11px !important;
			letter-spacing: 0.8px !important;
		}

		body.create-user-shell #sidebar .components {
			padding: 10px 0 8px;
		}

		body.create-user-shell #sidebar .components li {
			margin: 4px 10px;
		}

		body.create-user-shell #sidebar .components li a {
			gap: 10px;
			padding: 10px 14px;
			border-radius: 12px;
			font-size: 14px;
			font-weight: 600;
		}

		body.create-user-shell #sidebar .components li a i {
			width: 18px;
			font-size: 14px;
		}

		body.create-user-shell #sidebar .sidebar-footer {
			padding: 12px 14px 8px;
		}

		body.create-user-shell #sidebar .sidebar-footer p {
			margin-bottom: 4px;
			font-size: 11px;
		}

		body.create-user-shell #sidebar .logout-wrapper {
			padding: 10px 12px 14px;
		}

		body.create-user-shell #sidebar .logout-btn {
			padding: 10px 14px;
			font-size: 13px;
			border-radius: 12px;
		}

		body.create-user-shell #content {
			margin-left: var(--shell-sidebar-width);
			width: calc(100vw - var(--shell-sidebar-width));
			height: 100vh;
			overflow-y: auto;
			overflow-x: hidden;
			padding: calc(var(--shell-header-height) + 16px) 18px calc(var(--shell-footer-height) + 22px);
			background: transparent;
		}

		body.create-user-shell .shell-fixed-header,
		body.create-user-shell .shell-fixed-footer {
			position: fixed;
			left: var(--shell-sidebar-width);
			right: 0;
			z-index: 1050;
		}

		body.create-user-shell .shell-fixed-header {
			top: 0;
		}

		body.create-user-shell .shell-fixed-footer {
			bottom: 0;
		}

		body.create-user-shell .shell-fixed-header .site-banner-wrapper,
		body.create-user-shell .shell-fixed-footer .login-page-footer {
			width: 100%;
			margin: 0;
			background: rgba(255, 255, 255, 0.96);
			backdrop-filter: blur(12px);
		}

		body.create-user-shell .shell-fixed-header .site-banner-wrapper {
			box-shadow: 0 2px 12px rgba(17, 24, 39, 0.08);
		}

		body.create-user-shell .shell-fixed-footer .login-page-footer {
			box-shadow: 0 -2px 12px rgba(17, 24, 39, 0.06);
		}

		.create-user-topbar {
			background: rgba(255, 255, 255, 0.9);
			border: 1px solid rgba(228, 232, 239, 0.95);
			border-radius: 22px;
			padding: 18px 24px;
			margin-bottom: 18px;
			box-shadow: 0 18px 36px rgba(54, 78, 105, 0.08);
		}

		.create-user-topbar .topbar-title {
			color: #3f2b67;
			font-weight: 800;
			margin-bottom: 0;
			font-size: 1.1rem;
		}

		.create-user-page {
			padding: 0;
		}

		.create-user-page .container-fluid {
			padding: 0;
		}

		.page-title {
			color: #3f2b67;
			font-size: 2rem;
			font-weight: 800;
			margin-bottom: 0.35rem;
		}

		.page-subtitle {
			color: #5f6b7a;
			margin-bottom: 0;
			font-size: 1rem;
		}

		.admin-card {
			border: 1px solid rgba(232, 236, 242, 0.9);
			border-radius: 24px;
			box-shadow: 0 14px 32px rgba(54, 78, 105, 0.08);
			background: rgba(255, 255, 255, 0.97);
			overflow: hidden;
		}

		.create-user-card {
			border-radius: 16px;
		}

		.admin-card .card-header {
			background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%);
			color: #fff;
			border: none;
			padding: 1rem 1.2rem;
		}

		.create-user-card .card-header {
			padding: 0.7rem 1rem;
		}

		.create-user-card .card-header h5 {
			font-size: 0.95rem;
			font-weight: 700;
		}

		.create-user-card .card-body {
			padding: 0.9rem 0.9rem 1rem;
		}

		.form-label {
			font-weight: 700;
			color: #3f2b67;
		}

		.create-user-form .form-label {
			position: static !important;
			transform: none !important;
			display: block !important;
			opacity: 1 !important;
			background: transparent !important;
			pointer-events: auto !important;
			line-height: 1.2;
			font-size: 0.92rem;
			margin-bottom: 0.45rem;
		}

		.create-user-form .form-control,
		.create-user-form .form-select {
			height: 40px;
			border-radius: 8px;
			border: 1px solid #8f9bad;
			box-shadow: none;
			color: #243042;
		}

		.create-user-form .form-select {
			appearance: none;
			-webkit-appearance: none;
			-moz-appearance: none;
			padding-top: 0;
			padding-bottom: 0;
			padding-left: 0.75rem;
			padding-right: 2.5rem;
			background-color: #ffffff;
			background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.8' d='m4 6 4 4 4-4'/%3E%3C/svg%3E");
			background-repeat: no-repeat;
			background-position: right 0.8rem center;
			background-size: 14px 14px;
		}

		.create-user-form .form-select:required:invalid {
			color: #9aa3b2;
		}

		.create-user-form .form-select option {
			color: #243042;
		}

		.create-user-form .form-select option[value=""] {
			color: #9aa3b2;
		}

		.create-user-form .form-control::placeholder {
			color: #9aa3b2;
			opacity: 1;
		}

		.create-user-form .form-control:focus,
		.create-user-form .form-select:focus {
			border-color: #7c3aed;
			box-shadow: 0 0 0 0.15rem rgba(124, 58, 237, 0.12);
		}

		.create-user-form .form-check {
			display: flex;
			align-items: center;
			gap: 0.45rem;
			min-height: 40px;
			margin-bottom: 0;
		}

		.create-user-form .form-check-input {
			margin-top: 0;
		}

		.create-user-form .form-check-label {
			font-weight: 500;
			color: #263041;
		}

		.create-user-form .create-user-actions {
			margin-top: 0.8rem;
		}

		.create-user-form .btn-admin {
			padding: 0.45rem 0.95rem;
			border-radius: 8px;
			font-size: 0.95rem;
			font-weight: 600;
			box-shadow: none;
		}

		.btn-admin {
			background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%);
			border: none;
			color: #fff;
			border-radius: 14px;
			padding: 10px 18px;
			font-weight: 600;
			box-shadow: 0 10px 20px rgba(127, 42, 179, 0.18);
		}

		.btn-admin:hover {
			background: linear-gradient(135deg, #6f239d 0%, #e2ad35 100%);
			color: #fff;
		}

		.table thead th {
			background: #f7f3ff;
			color: #4c2a7a;
			font-weight: 600;
			border-bottom-width: 0;
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

		@media (max-width: 991px) {
			body.create-user-shell {
				overflow-y: auto;
			}

			body.create-user-shell #sidebar {
				width: 250px;
				min-width: 250px;
				max-width: 250px;
				left: -250px;
				overflow-y: auto;
			}

			body.create-user-shell #sidebar.active {
				left: 0;
			}

			body.create-user-shell #content {
				margin-left: 0;
				width: 100vw;
				padding: calc(var(--shell-header-height) + 12px) 12px calc(var(--shell-footer-height) + 18px);
			}

			body.create-user-shell .shell-fixed-header,
			body.create-user-shell .shell-fixed-footer {
				left: 0;
			}

			.page-title {
				font-size: 1.65rem;
			}

			.create-user-topbar {
				padding: 14px 16px;
				border-radius: 18px;
			}
		}
	</style>
</head>
<body class="create-user-shell">
	<div id="wrapper">
		<?php include '../include/sidebar.php'; ?>

		<div id="content">
			<div class="shell-fixed-header">
				<?php include '../include/website_header.php'; ?>
			</div>

			<nav class="navbar create-user-topbar">
				<div class="container-fluid px-0">
					<div class="d-flex align-items-center gap-2">
						<button type="button" id="sidebarCollapse" class="btn btn-outline-secondary btn-sm" onclick="toggleSidebar()">
							<i class="fa-solid fa-bars"></i>
						</button>
						<h5 class="topbar-title">Admin User Management</h5>
					</div>
				</div>
			</nav>

			<div class="create-user-page">
				<div class="container-fluid">
					<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
						<div>
							<h2 class="page-title">Admin User Management</h2>
							<p class="page-subtitle">Create user accounts and manage the master users list.</p>
						</div>
					</div>

					<?php if (!$isAdmin): ?>
						<div class="alert alert-warning">Limited access mode: you can view existing users. Creating users requires administrator access.</div>
					<?php endif; ?>

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

					<div class="card admin-card create-user-card mb-4">
						<div class="card-header">
							<h5 class="mb-0">Create New User</h5>
						</div>
						<div class="card-body">
							<form method="POST" class="create-user-form">
								<div class="row g-3">
									<?php if ($hasSchoolName): ?>
										<div class="col-md-3">
											<label class="form-label" for="school_name">School Name</label>
											<select id="school_name" name="school_name" class="form-select">
												<option value="" selected>None / N/A</option>
												<option value="ZP School Panhala">ZP School Panhala</option>
												<option value="ZP School Karvir">ZP School Karvir</option>
												<option value="ZP School Shahuwadi">ZP School Shahuwadi</option>
												<option value="ZP School Radhanagari">ZP School Radhanagari</option>
												<option value="ZP School Kagal">ZP School Kagal</option>
												<option value="ZP School Bhudargad">ZP School Bhudargad</option>
												<option value="ZP School Ajara">ZP School Ajara</option>
												<option value="ZP School Gadhinglaj">ZP School Gadhinglaj</option>
												<option value="ZP School Chandgad">ZP School Chandgad</option>
												<option value="ZP School Hatkanangale">ZP School Hatkanangale</option>
												<option value="ZP School Shirol">ZP School Shirol</option>
												<option value="ZP School Gaganbawda">ZP School Gaganbawda</option>
											</select>
										</div>
									<?php endif; ?>
									<?php if ($hasRole): ?>
										<div class="col-md-2">
											<label class="form-label" for="role">Role</label>
											<select id="role" name="role" class="form-select" required>
												<option value="" selected hidden>Select</option>
												<option value="HM">HM</option>
												<option value="Sachiv">Sachiv</option>
												<option value="CEO">CEO</option>
											</select>
										</div>
									<?php endif; ?>
									<?php if ($hasName): ?>
										<div class="col-md-2">
											<label class="form-label" for="name">Full Name</label>
											<input type="text" id="name" name="name" class="form-control" placeholder="Enter full name" required>
										</div>
									<?php endif; ?>
									<div class="col-md-2">
										<label class="form-label" for="username">Username</label>
										<input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required>
									</div>
									<div class="col-md-2">
										<label class="form-label" for="password">Password</label>
										<input type="text" id="password" name="password" class="form-control" placeholder="Enter password" required>
									</div>
									<?php if ($hasIsActive): ?>
										<div class="col-md-1 d-flex align-items-end justify-content-md-end">
											<div class="form-check mb-2">
												<input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
												<label class="form-check-label" for="is_active">Active</label>
											</div>
										</div>
									<?php endif; ?>
								</div>
								<div class="create-user-actions">
									<button type="submit" name="create_user" class="btn btn-admin" <?php echo $isAdmin ? '' : 'disabled'; ?>>Create User</button>
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
				</div>
			</div>

			<div class="shell-fixed-footer">
				<?php include '../include/website_footer.php'; ?>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script>
	function toggleSidebar() {
		const sidebar = document.getElementById('sidebar');
		if (sidebar) {
			sidebar.classList.toggle('active');
		}
	}

	// Client-side validation and Popup errors/success alerts
	document.addEventListener('DOMContentLoaded', function() {
		// Server-side errors popup
		<?php if (!empty($errors)): ?>
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				title: 'Validation Error',
				html: '<ul class="text-start mb-0"><?php foreach ($errors as $error): ?><li><?php echo addslashes(htmlspecialchars($error)); ?></li><?php endforeach; ?></ul>',
				icon: 'error',
				confirmButtonText: 'OK',
				confirmButtonColor: '#7f2ab3'
			});
		}
		<?php endif; ?>

		// Server-side success popup
		<?php if (!empty($success)): ?>
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				title: 'Success',
				text: '<?php echo addslashes(htmlspecialchars($success)); ?>',
				icon: 'success',
				confirmButtonText: 'OK',
				confirmButtonColor: '#7f2ab3'
			});
		}
		<?php endif; ?>

		// Form submit validation
		const form = document.querySelector('.create-user-form');
		if (form) {
			form.addEventListener('submit', function(event) {
				const roleEl = document.getElementById('role');
				const nameEl = document.getElementById('name');
				const usernameEl = document.getElementById('username');
				const passwordEl = document.getElementById('password');
				
				let errs = [];
				
				if (roleEl && !roleEl.value) {
					errs.push("Please select a role.");
				}
				if (nameEl && !nameEl.value.trim()) {
					errs.push("Full name is required.");
				}
				if (usernameEl) {
					const username = usernameEl.value.trim();
					if (!username) {
						errs.push("Username is required.");
					} else if (/^\d/.test(username)) {
						errs.push("Username should not start with a number.");
					}
				}
				if (passwordEl && !passwordEl.value.trim()) {
					errs.push("Password is required.");
				}
				
				if (errs.length > 0) {
					event.preventDefault();
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							title: 'Validation Error',
							html: '<ul class="text-start mb-0">' + errs.map(e => '<li>' + e + '</li>').join('') + '</ul>',
							icon: 'error',
							confirmButtonText: 'OK',
							confirmButtonColor: '#7f2ab3'
						});
					} else {
						alert(errs.join('\n'));
					}
				}
			});
		}
	});
	</script>
</body>
</html>
