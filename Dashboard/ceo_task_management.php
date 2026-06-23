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
    <title>Samruddha Shala E-Portal - CEO Task Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <style>
        :root {
            --ceo-sidebar-width: 250px;
            --ceo-header-height: 64px;
            --ceo-footer-height: 60px;
            --ceo-shell-bg: #f4f8fb;
        }

        body.ceo-dashboard-page {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: block;
            overflow: hidden;
            background:
                radial-gradient(circle at top left, rgba(123, 92, 255, 0.08), transparent 28%),
                linear-gradient(180deg, #eef6fb 0%, #f7fafc 100%);
        }

        body.ceo-dashboard-page #wrapper {
            min-height: 100vh;
        }

        body.ceo-dashboard-page #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--ceo-sidebar-width);
            min-width: var(--ceo-sidebar-width);
            max-width: var(--ceo-sidebar-width);
            height: 100vh;
            z-index: 1100;
            overflow: hidden;
            background: linear-gradient(180deg, #6420a5 0%, #8a45b8 54%, #efbc4d 100%);
            box-shadow: 10px 0 32px rgba(91, 35, 140, 0.22);
        }

        body.ceo-dashboard-page #sidebar .sidebar-header {
            padding: 20px 16px 16px;
        }

        body.ceo-dashboard-page #sidebar .sidebar-header h4 {
            font-size: 18px;
            line-height: 1.2;
        }

        body.ceo-dashboard-page #sidebar .sidebar-header small {
            font-size: 11px !important;
            letter-spacing: 0.8px !important;
        }

        body.ceo-dashboard-page #sidebar .components {
            padding: 10px 0 8px;
        }

        body.ceo-dashboard-page #sidebar .components li {
            margin: 4px 10px;
        }

        body.ceo-dashboard-page #sidebar .components li a {
            gap: 10px;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
        }

        body.ceo-dashboard-page #sidebar .components li a i {
            width: 18px;
            font-size: 14px;
        }

        body.ceo-dashboard-page #sidebar .sidebar-footer {
            padding: 12px 14px 8px;
        }

        body.ceo-dashboard-page #sidebar .sidebar-footer p {
            margin-bottom: 4px;
            font-size: 11px;
        }

        body.ceo-dashboard-page #sidebar .logout-wrapper {
            padding: 10px 12px 14px;
        }

        body.ceo-dashboard-page #sidebar .logout-btn {
            padding: 10px 14px;
            font-size: 13px;
            border-radius: 12px;
        }

        body.ceo-dashboard-page #content {
            margin-left: var(--ceo-sidebar-width);
            width: calc(100vw - var(--ceo-sidebar-width));
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            padding: calc(var(--ceo-header-height) + 16px) 18px calc(var(--ceo-footer-height) + 22px);
            background: transparent;
        }

        body.ceo-dashboard-page .ceo-fixed-header,
        body.ceo-dashboard-page .ceo-fixed-footer {
            position: fixed;
            left: var(--ceo-sidebar-width);
            right: 0;
            z-index: 1050;
        }

        body.ceo-dashboard-page .ceo-fixed-header {
            top: 0;
        }

        body.ceo-dashboard-page .ceo-fixed-footer {
            bottom: 0;
        }

        body.ceo-dashboard-page .ceo-fixed-header .site-banner-wrapper,
        body.ceo-dashboard-page .ceo-fixed-footer .login-page-footer {
            width: 100%;
            margin: 0;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
        }

        body.ceo-dashboard-page .ceo-fixed-header .site-banner-wrapper {
            box-shadow: 0 2px 12px rgba(17, 24, 39, 0.08);
        }

        body.ceo-dashboard-page .ceo-fixed-footer .login-page-footer {
            box-shadow: 0 -2px 12px rgba(17, 24, 39, 0.06);
        }

        body.ceo-dashboard-page .navbar {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(228, 232, 239, 0.95);
            border-radius: 22px;
            padding: 18px 24px;
            margin-bottom: 18px;
            box-shadow: 0 18px 36px rgba(54, 78, 105, 0.08);
        }

        body.ceo-dashboard-page .container-fluid.p-4 {
            padding: 0 !important;
        }

        body.ceo-dashboard-page .row {
            --bs-gutter-x: 1.15rem;
            --bs-gutter-y: 1.15rem;
        }

        body.ceo-dashboard-page .card,
        body.ceo-dashboard-page .border.rounded.p-4,
        body.ceo-dashboard-page .border.rounded.p-3 {
            border: 1px solid rgba(232, 236, 242, 0.9) !important;
            border-radius: 24px !important;
            box-shadow: 0 14px 32px rgba(54, 78, 105, 0.08);
            background: rgba(255, 255, 255, 0.97);
        }

        body.ceo-dashboard-page .card.p-4.bg-light {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%) !important;
        }

        body.ceo-dashboard-page .btn-outline-primary {
            border: none;
            color: #ffffff;
            background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%);
            border-radius: 16px;
            padding: 10px 16px;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(127, 42, 179, 0.18);
        }

        body.ceo-dashboard-page .btn-outline-primary:hover {
            color: #ffffff;
            background: linear-gradient(135deg, #6f239d 0%, #e2ad35 100%);
        }

        body.ceo-dashboard-page .table thead th {
            background: #f7f3ff;
            color: #4c2a7a;
            border-bottom: 0;
        }

        @media (max-width: 991px) {
            :root {
                --ceo-sidebar-width: 0px;
            }

            body.ceo-dashboard-page {
                overflow-y: auto;
            }

            body.ceo-dashboard-page #sidebar {
                width: 250px;
                min-width: 250px;
                max-width: 250px;
                left: -250px;
                overflow-y: auto;
            }

            body.ceo-dashboard-page #sidebar.active {
                left: 0;
            }

            body.ceo-dashboard-page #content {
                margin-left: 0;
                width: 100vw;
                padding: calc(var(--ceo-header-height) + 12px) 12px calc(var(--ceo-footer-height) + 18px);
            }

            body.ceo-dashboard-page .ceo-fixed-header,
            body.ceo-dashboard-page .ceo-fixed-footer {
                left: 0;
            }

            body.ceo-dashboard-page .navbar {
                padding: 14px 16px;
                border-radius: 18px;
            }
        }

        /* Form Layout Overrides to prevent overlap and bugs from global styles */
        #createWorkForm .form-label {
            position: static;
            transform: none;
            pointer-events: auto;
            margin-bottom: 0.5rem;
        }
        #createWorkForm .form-select {
            border: 1px solid #ced4da !important;
            appearance: auto !important;
            -webkit-appearance: auto !important;
            padding: 0.375rem 2.25rem 0.375rem 0.75rem !important;
            background-color: #fff !important;
        }
        #createWorkForm .form-control {
            border: 1px solid #ced4da !important;
            background-color: #fff !important;
        }
        #stagesTable .form-control {
            position: relative;
        }
    </style>
</head>
<body class="ceo-dashboard-page">
    <div id="wrapper">
        <!-- Sidebar Navigation -->
        <?php include '../include/sidebar.php'; ?>

        <div id="content">
            <div class="ceo-fixed-header">
                <?php include '../include/website_header.php'; ?>
            </div>
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <div class="ms-3 d-flex align-items-center">
                        <h5 class="mb-0 font-weight-bold" id="pageMainHeader">Task Management</h5>
                    </div>
                    <div class="ms-auto d-flex align-items-center">
                        <div class="me-4 position-relative">
                            <a href="ceo_alerts.php" class="btn btn-link text-dark p-1 text-decoration-none" id="notifBellButton" title="View Alerts & Notifications">
                                <i class="fa-regular fa-bell fs-5"></i>
                                <span id="alertsHeaderBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                            </a>
                        </div>
                        <div class="d-flex align-items-center border-start ps-4">
                            <div class="text-end me-3 d-none d-md-block">
                                <p class="mb-0 fw-bold fs-6">Shri. R. K. Patil, IAS</p>
                                <small class="text-muted">District CEO</small>
                            </div>
                            <span class="role-badge badge-ceo">CEO</span>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                <div id="ceo-task-view" class="view-panel">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card p-4">
                                <h4 class="fw-bold mb-1"><i class="fa-solid fa-plus-circle me-2 text-primary"></i> Manage Task</h4>
                                <p class="text-muted mb-4">Define a new work and configure its workflow stages and progress percentages.</p>
                                <form id="createWorkForm">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="workTypeSelect" class="form-label fw-semibold">Work Type <span class="text-danger">*</span></label>
                                            <select id="workTypeSelect" name="work_type_id" class="form-select" required>
                                                <option value="" disabled selected>Loading...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="workNameSelect" class="form-label fw-semibold">Work Name <span class="text-danger">*</span></label>
                                            <select id="workNameSelect" name="work_name_id" class="form-select" required>
                                                <option value="" disabled selected>Select Work Type first</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold mb-0">Stage Details <span class="text-danger">*</span></label>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="addStageBtn">
                                                <i class="fa-solid fa-plus me-1"></i> Add Stage
                                            </button>
                                        </div>
                                        <div class="table-responsive border rounded">
                                            <table class="table table-hover align-middle mb-0" id="stagesTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 50%;">Stage Name</th>
                                                        <th style="width: 30%;">Percentage (%)</th>
                                                        <th style="width: 20%;" class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="stagesTableBody">
                                                    <!-- Dynamic rows -->
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <td class="text-end fw-bold">Total Percentage:</td>
                                                        <td class="fw-bold fs-5 text-primary" id="totalPercentageDisplay">0%</td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="additionalNotes" class="form-label fw-semibold">Additional Information / Notes</label>
                                        <textarea id="additionalNotes" class="form-control" rows="4" placeholder="Work description, administrative remarks, instructions..."></textarea>
                                    </div>
                                    
                                    <div id="formError" class="alert alert-danger d-none mb-3"></div>

                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" id="submitWorkBtn">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Create Work
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ceo-fixed-footer">
                <?php include '../include/website_footer.php'; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/db.js"></script>
    <script src="js/ceo.js?v=5"></script>
    <script src="js/create_work.js"></script>
</body>
</html>

