<?php 
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['role'] !== 'CEO' && $_SESSION['role'] !== 'HM') {
    header("Location: ../login.php");
    exit();
}

require_once '../include/dbConfig.php';

// Fetch cumulative data for the chart (sanctioned & spent per work type)
$workTypeLabels = [];
$workTypeSanctioned = [];
$workTypeSpent = [];
$sql1 = "SELECT 
            work_type,
            SUM(proj_sanctioned) as total_sanctioned,
            SUM(proj_spent) as total_spent
        FROM (
            SELECT 
                work_type,
                MAX(sanctioned_amount) as proj_sanctioned,
                SUM(amount_spent) as proj_spent
            FROM amount_utilization
            GROUP BY work_name, work_type, fund_source, created_at
        ) as subquery
        GROUP BY work_type";

$res1 = $conn->query($sql1);
if ($res1) {
    while ($row = $res1->fetch_assoc()) {
        $workTypeLabels[] = $row['work_type'];
        $workTypeSanctioned[] = (float) $row['total_sanctioned'];
        $workTypeSpent[] = (float) $row['total_spent'];
    }
}

// Convert to JSON for JS consumption
$workTypeLabelsJson = json_encode($workTypeLabels);
$workTypeSanctionedJson = json_encode($workTypeSanctioned);
$workTypeSpentJson = json_encode($workTypeSpent);

// Fetch all records for the report table
$reportRows = [];
$sqlReport = "SELECT * FROM amount_utilization ORDER BY id DESC";
$resReport = $conn->query($sqlReport);
if ($resReport) {
    while ($row = $resReport->fetch_assoc()) {
        $reportRows[] = $row;
    }
}

// Compute total spent per project to calculate remaining balance dynamically
$projectSpentMap = [];
$sqlProjectSpent = "SELECT work_name, work_type, fund_source, created_at, SUM(amount_spent) as total_spent FROM amount_utilization GROUP BY work_name, work_type, fund_source, created_at";
$resProjectSpent = $conn->query($sqlProjectSpent);
if ($resProjectSpent) {
    while ($row = $resProjectSpent->fetch_assoc()) {
        $key = $row['work_name'] . '|' . $row['work_type'] . '|' . $row['fund_source'] . '|' . $row['created_at'];
        $projectSpentMap[$key] = (float) $row['total_spent'];
    }
}

// Fetch Grand Totals for KPI Summary Cards
$grandSanctioned = 0.0;
$grandSpent = 0.0;
$grandRemaining = 0.0;

$sqlGrandSanctioned = "SELECT SUM(proj_sanctioned) as grand_sanctioned FROM (
    SELECT MAX(sanctioned_amount) as proj_sanctioned 
    FROM amount_utilization 
    GROUP BY work_name, work_type, fund_source, created_at
) as subquery";
$resGrandSanctioned = $conn->query($sqlGrandSanctioned);
if ($resGrandSanctioned) {
    $row = $resGrandSanctioned->fetch_assoc();
    $grandSanctioned = (float)($row['grand_sanctioned'] ?? 0.0);
}

$sqlGrandSpent = "SELECT SUM(amount_spent) as grand_spent FROM amount_utilization";
$resGrandSpent = $conn->query($sqlGrandSpent);
if ($resGrandSpent) {
    $row = $resGrandSpent->fetch_assoc();
    $grandSpent = (float)($row['grand_spent'] ?? 0.0);
}

$grandRemaining = $grandSanctioned - $grandSpent;
if ($grandRemaining < 0) {
    $grandRemaining = 0.0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/amount_utilization.css?v=<?php echo time(); ?>">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            font-family: 'Outfit', sans-serif !important;
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
            position: fixed !important;
            left: var(--ceo-sidebar-width) !important;
            right: 0 !important;
            z-index: 1050 !important;
        }

        body.ceo-dashboard-page .ceo-fixed-header {
            top: 0 !important;
        }

        body.ceo-dashboard-page .ceo-fixed-footer {
            bottom: 0 !important;
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

        /* Specific form styles */
        .form-label, label {
            transform: none !important;
            position: static !important;
            display: block !important;
            margin-bottom: 6px !important;
            opacity: 1 !important;
            color: #212529 !important;
            font-weight: 600 !important;
        }
        .form-group {
            margin-bottom: 15px !important;
        }
        .action-btns .btn {
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Dashboard card style matching */
        .utilization-card,
        #amount-utilization-form .card {
            border: 1px solid rgba(232, 236, 242, 0.9) !important;
            border-radius: 24px !important;
            box-shadow: 0 14px 32px rgba(54, 78, 105, 0.08) !important;
            background: rgba(255, 255, 255, 0.97) !important;
        }

        /* Watermark background */
        .utilization-card {
            position: relative !important;
            overflow: hidden !important;
        }

        .utilization-card::before {
            content: "" !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            width: 380px !important;
            height: 380px !important;
            background: url('../images/demo.jpg') no-repeat center/contain !important;
            opacity: 0.035 !important;
            filter: grayscale(100%) !important;
            pointer-events: none !important;
            z-index: 0 !important;
        }

        /* Ensure card contents stay on top of the watermark */
        .utilization-card > * {
            position: relative !important;
            z-index: 1 !important;
        }
    </style>
</head>
<body class="ceo-dashboard-page">
    
    <div id="wrapper">
        <?php include '../include/sidebar.php'; ?>

        <div id="content">
            <div class="ceo-fixed-header">
                <?php include '../include/website_header.php'; ?>
            </div>
            
            <nav class="navbar navbar-expand-lg navbar-light" style="position: relative;">
                <div class="container-fluid d-flex align-items-center justify-content-between position-relative">
                    <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary btn-sm" onclick="toggleSidebar()" style="border: 1px solid #ddd; background: #f8f9fa; z-index: 10;">
                        <i class="fas fa-align-left text-dark"></i>
                    </button>
                    <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none; z-index: 5; width: auto; max-width: 70%;">
                        <h5 class="mb-0 fw-bold" id="pageMainHeader" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 900 !important; pointer-events: auto; display: inline-block;">Amount Utilization Portal</h5>
                    </div>
                    <div style="width: 32px; z-index: 10;"></div>
                </div>
            </nav>
            
            <div class="container-fluid p-4">
                <!-- Tier 1: KPI Summary Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card p-3 shadow-sm border-0 border-start border-primary border-4" style="background: rgba(255,255,255,0.97); border-radius: 16px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;">Total Sanctioned Budget</h6>
                                    <h3 class="fw-bold mb-0 text-primary" style="font-family: 'Outfit';">₹<?php echo number_format($grandSanctioned, 2); ?></h3>
                                </div>
                                <div class="bg-primary-light p-3 rounded-3 text-primary">
                                    <i class="fa-solid fa-indian-rupee-sign fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 shadow-sm border-0 border-start border-warning border-4" style="background: rgba(255,255,255,0.97); border-radius: 16px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;">Total Fund Expenditure</h6>
                                    <h3 class="fw-bold mb-0 text-warning" style="font-family: 'Outfit';">₹<?php echo number_format($grandSpent, 2); ?></h3>
                                </div>
                                <div class="bg-warning-light p-3 rounded-3 text-warning">
                                    <i class="fa-solid fa-receipt fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 shadow-sm border-0 border-start border-success border-4" style="background: rgba(255,255,255,0.97); border-radius: 16px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;">Total Remaining Balance</h6>
                                    <h3 class="fw-bold mb-0 text-success" style="font-family: 'Outfit';">₹<?php echo number_format($grandRemaining, 2); ?></h3>
                                </div>
                                <div class="bg-success-light p-3 rounded-3 text-success">
                                    <i class="fa-solid fa-wallet fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tier 2: Form Entry & Live Doughnut Preview (50/50 Split) -->
                <div class="row g-4 mb-4">
                    <!-- Left: Form Entry -->
                    <div class="col-lg-6">
                        <div class="utilization-card p-4 h-100 shadow-sm" style="border-radius: 24px; border: 1px solid rgba(232, 236, 242, 0.9); background: rgba(255,255,255,0.97);">
                            <h4 class="mb-3 text-primary fw-bold text-center border-bottom pb-2" style="font-family: 'Outfit', sans-serif;"><i class="fa-solid fa-pen-to-square me-2"></i>Fund Allocation & Expenditure Tracker</h4>
                            
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger mb-3 py-2" role="alert"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success mb-3 py-2" role="alert"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>

                            <form method="post" id="amount-utilization-form" action="amount_utilization_db.php">
                                <div class="card p-3 mb-3 border-0 bg-light" style="border-radius: 16px;">
                                    <h6 class="mb-3 fw-bold text-dark" style="border-left: 3px solid #17a2b8; padding-left: 8px;">Fund Availability & Work Info</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="form-label small mb-1" for="work_type">Work Type <span class="text-danger">*</span></label>
                                                <select name="work_type" id="work_type" class="form-select form-select-sm w-100" required>
                                                    <option value="">Select Work Type</option>
                                                    <option value="Civil Works" <?php echo (isset($_POST['work_type']) && $_POST['work_type'] === 'Civil Works') ? 'selected' : ''; ?>>Civil Works</option>
                                                    <option value="Infrastructure Development" <?php echo (isset($_POST['work_type']) && $_POST['work_type'] === 'Infrastructure Development') ? 'selected' : ''; ?>>Infrastructure Development</option>
                                                    <option value="Road Construction & Repair" <?php echo (isset($_POST['work_type']) && $_POST['work_type'] === 'Road Construction & Repair') ? 'selected' : ''; ?>>Road Construction & Repair</option>
                                                    <option value="Water Supply Scheme" <?php echo (isset($_POST['work_type']) && $_POST['work_type'] === 'Water Supply Scheme') ? 'selected' : ''; ?>>Water Supply Scheme</option>
                                                    <option value="Electrical Infrastructure" <?php echo (isset($_POST['work_type']) && $_POST['work_type'] === 'Electrical Infrastructure') ? 'selected' : ''; ?>>Electrical Infrastructure</option>
                                                    <option value="Building Maintenance" <?php echo (isset($_POST['work_type']) && $_POST['work_type'] === 'Building Maintenance') ? 'selected' : ''; ?>>Building Maintenance</option>
                                                    <option value="Sanitation & Waste Management" <?php echo (isset($_POST['work_type']) && $_POST['work_type'] === 'Sanitation & Waste Management') ? 'selected' : ''; ?>>Sanitation & Waste Management</option>
                                                    <option value="Public Health Works" <?php echo (isset($_POST['work_type']) && $_POST['work_type'] === 'Public Health Works') ? 'selected' : ''; ?>>Public Health Works</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="form-label small mb-1" for="fund_source">Fund Source <span class="text-danger">*</span></label>
                                                <select name="fund_source" id="fund_source" class="form-select form-select-sm w-100" required>
                                                    <option value="">Select Fund Source</option>
                                                    <option value="Zilla Parishad Fund" <?php echo (isset($_POST['fund_source']) && $_POST['fund_source'] === 'Zilla Parishad Fund') ? 'selected' : ''; ?>>Zilla Parishad Fund</option>
                                                    <option value="State Government Fund" <?php echo (isset($_POST['fund_source']) && $_POST['fund_source'] === 'State Government Fund') ? 'selected' : ''; ?>>State Government Fund</option>
                                                    <option value="Additional Fund" <?php echo (isset($_POST['fund_source']) && $_POST['fund_source'] === 'Additional Fund') ? 'selected' : ''; ?>>Additional Fund</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="form-label small mb-1" for="sanctioned_amount">Sanctioned Amount <span class="text-danger">*</span></label>
                                                <input type="number" name="sanctioned_amount" id="sanctioned_amount" class="form-control form-control-sm w-100" step="0.01" min="0" value="<?php echo htmlspecialchars($_POST['sanctioned_amount'] ?? ''); ?>" placeholder="Enter Sanctioned Amount" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="form-label small mb-1" for="work_name">Work Name <span class="text-danger">*</span></label>
                                                <select name="work_name" id="work_name" class="form-select form-select-sm w-100" required>
                                                    <option value="">Select Work Name</option>
                                                    <option value="Building Maintenance" <?php echo (isset($_POST['work_name']) && $_POST['work_name'] === 'Building Maintenance') ? 'selected' : ''; ?>>Building Maintenance</option>
                                                    <option value="Electrical Work" <?php echo (isset($_POST['work_name']) && $_POST['work_name'] === 'Electrical Work') ? 'selected' : ''; ?>>Electrical Work</option>
                                                    <option value="Water Supply" <?php echo (isset($_POST['work_name']) && $_POST['work_name'] === 'Water Supply') ? 'selected' : ''; ?>>Water Supply</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mb-0">
                                                <label class="form-label small mb-1" for="share">Remarks (Optional)</label>
                                                <input type="text" name="share" id="share" class="form-control form-control-sm w-100" value="<?php echo htmlspecialchars($_POST['share'] ?? ''); ?>" placeholder="Enter Remarks">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card p-3 border-0 bg-light" style="border-radius: 16px;">
                                    <h6 class="mb-3 fw-bold text-dark" style="border-left: 3px solid #17a2b8; padding-left: 8px;">Expenditure Splits</h6>
                                    
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-4">
                                            <div class="p-2 text-center bg-white rounded-3 shadow-sm border-start border-3 border-info">
                                                <label class="d-block mb-1 text-muted fw-bold" style="font-size:0.65rem;">TOTAL</label>
                                                <div class="fw-bold text-info" id="summary-total" style="font-size:0.95rem;">₹0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-2 text-center bg-white rounded-3 shadow-sm border-start border-3 border-warning">
                                                <label class="d-block mb-1 text-muted fw-bold" style="font-size:0.65rem;">SPENT</label>
                                                <div class="fw-bold text-warning" id="summary-spent" style="font-size:0.95rem;">₹0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-2 text-center bg-white rounded-3 shadow-sm border-start border-3 border-success">
                                                <label class="d-block mb-1 text-muted fw-bold" style="font-size:0.65rem;">REMAINING</label>
                                                <div class="fw-bold text-success" id="summary-remain" style="font-size:0.95rem;">₹0</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="expense-rows" style="max-height: 250px; overflow-y: auto; padding-right: 4px;">
                                        <?php
                                        $expense_amounts = $_POST['expense_amount'] ?? [''];
                                        $expense_dates = $_POST['expense_date'] ?? [''];
                                        $expense_remarks = $_POST['expense_remark'] ?? [''];
                                        $expenseCount = max(count($expense_amounts), count($expense_dates), count($expense_remarks));
                                        if ($expenseCount <= 0) { $expenseCount = 1; }
                                        
                                        for ($i = 0; $i < $expenseCount; $i++):
                                            $amount = htmlspecialchars($expense_amounts[$i] ?? '');
                                            $date = htmlspecialchars($expense_dates[$i] ?? '');
                                            $remark = htmlspecialchars($expense_remarks[$i] ?? '');
                                        ?>
                                            <div class="row g-2 align-items-end expense-row mb-2">
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1">Amount Spent <span class="text-danger">*</span></label>
                                                    <input type="number" name="expense_amount[]" class="form-control form-control-sm expense-amount w-100" step="0.01" min="0" value="<?php echo $amount; ?>" placeholder="Amount Spent" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1">Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="expense_date[]" class="form-control form-control-sm expense-date w-100" value="<?php echo $date; ?>" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small mb-1">Remarks</label>
                                                    <input type="text" name="expense_remark[]" class="form-control form-control-sm expense-remark w-100" placeholder="Remarks" value="<?php echo $remark; ?>">
                                                </div>
                                                <div class="col-md-1 action-btns d-flex gap-1 pb-1">
                                                    <button type="button" class="btn btn-success btn-xs p-1 add-expense-row" style="font-size: 0.75rem; height: 31px; width: 31px;" title="Add"><i class="fa fa-plus"></i></button>
                                                    <button type="button" class="btn btn-danger btn-xs p-1 remove-expense-row" style="font-size: 0.75rem; height: 31px; width: 31px;" <?php echo ($expenseCount === 1 ? 'disabled' : ''); ?> title="Delete"><i class="fa fa-trash"></i></button>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>

                                    <div class="d-flex justify-content-end align-items-center mt-3">
                                        <button type="submit" name="save" class="btn btn-primary btn-sm px-4">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Live Doughnut Preview -->
                    <div class="col-lg-6">
                        <div class="card p-4 shadow-sm h-100 utilization-card" style="border-radius: 24px; border: 1px solid rgba(232, 236, 242, 0.9); background: rgba(255,255,255,0.97);">
                            <h4 class="fw-bold mb-3 text-primary border-bottom pb-2" style="font-family: 'Outfit', sans-serif;"><i class="fa-solid fa-chart-pie me-2"></i>Live Fund Utilization Preview</h4>
                            <p class="text-muted small mb-4">Real-time visualization of your current form's entry (Sanctioned vs Spent).</p>
                            <div style="height: 380px; position: relative;" class="d-flex justify-content-center align-items-center">
                                <canvas id="livePreviewChart" style="max-width: 320px; max-height: 320px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tier 3: Stacked Bar Chart & Detailed Transaction Log (50/50 Split) -->
                <div class="row g-4">
                    <!-- Left: Cumulative Stacked Bar Chart -->
                    <div class="col-lg-6">
                        <div class="card p-4 shadow-sm h-100 utilization-card" style="border-radius: 24px; border: 1px solid rgba(232, 236, 242, 0.9); background: rgba(255,255,255,0.97);">
                            <h4 class="fw-bold mb-3 text-primary border-bottom pb-2" style="font-family: 'Outfit', sans-serif;"><i class="fa-solid fa-chart-bar me-2"></i>Cumulative Allocation vs Expenditure</h4>
                            <p class="text-muted small mb-4">Total budget vs. expenditure breakdown grouped across all school project categories in the district.</p>
                            <div style="height: 340px; position: relative;">
                                <canvas id="cumulativeWorkTypeChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Detailed Transaction Log Table -->
                    <div class="col-lg-6">
                        <div class="card p-4 shadow-sm h-100 utilization-card" style="border-radius: 24px; border: 1px solid rgba(232, 236, 242, 0.9); background: rgba(255,255,255,0.97);">
                            <h4 class="fw-bold mb-3 text-primary border-bottom pb-2" style="font-family: 'Outfit', sans-serif;"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Fund Utilization Summary Report</h4>
                            <p class="text-muted small mb-3">Comprehensive tracking logs with dynamic project balances.</p>
                            
                            <div class="table-responsive" style="max-height: 340px; overflow-y: auto; border: 1px solid #eee; border-radius: 12px;">
                                <table class="table table-hover align-middle mb-0" style="font-size:0.82rem;">
                                    <thead class="sticky-top table-light" style="z-index: 5;">
                                        <tr style="border-bottom: 2px solid #dee2e6;">
                                            <th style="width: 15%; font-weight: 600; color: #2d064d; text-align: center;">Date</th>
                                            <th style="width: 25%; font-weight: 600; color: #2d064d; text-align: left;">Work Name</th>
                                            <th style="width: 20%; font-weight: 600; color: #2d064d; text-align: center;">Work Type</th>
                                            <th style="width: 20%; font-weight: 600; color: #2d064d; text-align: right;">Sanctioned</th>
                                            <th style="width: 20%; font-weight: 600; color: #2d064d; text-align: right;">Spent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($reportRows)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No records found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($reportRows as $row): ?>
                                                <tr>
                                                    <td style="text-align: center; white-space: nowrap;"><?php echo htmlspecialchars(date('d-M-Y', strtotime($row['expense_date']))); ?></td>
                                                    <td class="fw-semibold text-dark text-wrap"><?php echo htmlspecialchars($row['work_name']); ?></td>
                                                    <td style="text-align: center;"><span class="badge bg-secondary" style="font-size: 0.7rem; font-weight: 500;"><?php echo htmlspecialchars($row['work_type']); ?></span></td>
                                                    <td style="text-align: right; font-weight: 600; color: #0288d1;">₹<?php echo number_format((float)$row['sanctioned_amount'], 2); ?></td>
                                                    <td style="text-align: right; font-weight: 600; color: #f57c00;">₹<?php echo number_format((float)$row['amount_spent'], 2); ?></td>
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
             
             <div class="ceo-fixed-footer">
                <?php include '../include/website_footer.php'; ?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('expense-rows').addEventListener('click', function(e){
            const addBtn = e.target.closest('.add-expense-row');
            if (addBtn) {
                e.preventDefault();
                const rowsContainer = document.getElementById('expense-rows');
                const firstRow = document.querySelector('.expense-row');
                const row = firstRow.cloneNode(true);
                
                row.querySelectorAll('input').forEach(function(i){ i.value = ''; });
                
                rowsContainer.appendChild(row);
                toggleTrashButtons();
                updateSummary();
            }

            const deleteBtn = e.target.closest('.remove-expense-row');
            if (deleteBtn) {
                e.preventDefault();
                const rows = document.querySelectorAll('#expense-rows .expense-row');
                if (rows.length > 1) {
                    deleteBtn.closest('.expense-row').remove();
                    toggleTrashButtons();
                    updateSummary();
                }
            }
        });

        function toggleTrashButtons() {
            const rows = document.querySelectorAll('#expense-rows .expense-row');
            rows.forEach(function(row) {
                const removeBtn = row.querySelector('.remove-expense-row');
                if (rows.length === 1) {
                    removeBtn.setAttribute('disabled', 'disabled');
                } else {
                    removeBtn.removeAttribute('disabled');
                }
            });
        }

        const sanctionedInput = document.getElementById('sanctioned_amount');
        sanctionedInput.addEventListener('input', updateSummary);
        document.getElementById('expense-rows').addEventListener('input', updateSummary);

        // Initialize Live Preview Doughnut Chart
        const liveChartCtx = document.getElementById('livePreviewChart').getContext('2d');
        const liveChart = new Chart(liveChartCtx, {
            type: 'doughnut',
            data: {
                labels: ['Spent', 'Remaining'],
                datasets: [{
                    data: [0, 0],
                    backgroundColor: ['rgba(245, 124, 0, 0.85)', 'rgba(56, 142, 60, 0.85)'],
                    borderColor: ['#f57c00', '#388e3c'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: 'Outfit',
                                size: 12
                            }
                        }
                    }
                }
            }
        });

        // Initialize Cumulative Expenditure Stacked Bar Chart
        const cumChartCtx = document.getElementById('cumulativeWorkTypeChart').getContext('2d');
        const cumulativeLabels = <?php echo $workTypeLabelsJson; ?>;
        const cumulativeSanctioned = <?php echo $workTypeSanctionedJson; ?>;
        const cumulativeSpent = <?php echo $workTypeSpentJson; ?>;
        const cumulativeRemaining = cumulativeSanctioned.map((val, idx) => {
            let rem = val - (cumulativeSpent[idx] || 0);
            return rem < 0 ? 0 : rem;
        });

        new Chart(cumChartCtx, {
            type: 'bar',
            data: {
                labels: cumulativeLabels.length > 0 ? cumulativeLabels : ['No Data'],
                datasets: [
                    {
                        label: 'Spent (₹)',
                        data: cumulativeSpent.length > 0 ? cumulativeSpent : [0],
                        backgroundColor: 'rgba(91, 22, 143, 0.8)', // deep purple/violet
                        borderColor: '#5b168f',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Remaining (₹)',
                        data: cumulativeRemaining.length > 0 ? cumulativeRemaining : [0],
                        backgroundColor: 'rgba(46, 125, 50, 0.8)', // professional dark green
                        borderColor: '#2e7d32',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { font: { family: 'Outfit' } }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: { font: { family: 'Outfit' } }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: { family: 'Outfit', size: 12 }
                        }
                    }
                }
            }
        });

        function updateSummary() {
            const ceo = parseFloat(sanctionedInput.value || 0);
            const rows = document.querySelectorAll('#expense-rows .expense-row');
            let totalSpent = 0;

            rows.forEach(function(r) {
                const amount = parseFloat(r.querySelector('.expense-amount').value || 0);
                if (!isNaN(amount)) {
                    totalSpent += amount;
                }
            });

            document.getElementById('summary-total').textContent = '₹ ' + (ceo % 1 === 0 ? ceo : ceo.toFixed(2));
            document.getElementById('summary-spent').textContent = '₹ ' + (totalSpent % 1 === 0 ? totalSpent : totalSpent.toFixed(2));
            
            let remaining = ceo - totalSpent;
            if (remaining < 0) remaining = 0;
            document.getElementById('summary-remain').textContent = '₹ ' + (remaining % 1 === 0 ? remaining : remaining.toFixed(2));

            // Update live preview chart
            liveChart.data.datasets[0].data = [totalSpent, remaining];
            liveChart.update();
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
        
        updateSummary();
    </script>
</body>
</html>
