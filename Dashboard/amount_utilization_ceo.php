<?php 
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['role'] !== 'CEO') {
    header("Location: ceo_dashboard.php");
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
    
    <link rel="stylesheet" href="css/ceo_dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/amount_utilization.css?v=<?php echo time(); ?>">
</head>
<body class="ceo-dashboard-page">
    
    <div id="wrapper">
        <?php include '../include/sidebar.php'; ?>

        <div id="content">
            <div class="ceo-fixed-header">
                <?php include '../include/website_header.php'; ?>
            </div>
            
            <nav class="navbar navbar-expand-lg navbar-light p-3" style="position: relative;">
                <div class="container-fluid d-flex flex-nowrap align-items-center px-1 justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                        <!-- Mobile Sidebar Toggle -->
                        <button class="btn btn-light d-lg-none me-2 shadow-sm border-0 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 40px; height: 40px; background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%); color: white;" type="button" id="mobileSidebarToggle" aria-label="Toggle Sidebar">
                            <i class="fa-solid fa-bars fs-6"></i>
                        </button>
                        <h5 class="mb-0 fw-bold text-truncate" id="pageMainHeader" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: clamp(1.1rem, 4vw, 1.4rem); font-weight: 800 !important; line-height: 1.2;" data-i18n="navTitle">Fund Utilization Details</h5>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap language-switcher">
                        <button id="langMarBtn" class="btn btn-sm btn-primary">मराठी</button>
                        <button id="langEngBtn" class="btn btn-sm btn-outline-primary">English</button>
                    </div>
                </div>
            </nav>
            
            <div class="container-fluid p-4">
                <!-- Tier 1: KPI Summary Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card p-3 shadow-sm border-0 border-start border-primary border-4 h-100 kpi-card" style="background: rgba(255,255,255,0.97); border-radius: 16px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;" data-i18n="lblTotalSanctioned">Total Sanctioned Budget</h6>
                                    <h3 class="fw-bold mb-0 text-primary" style="font-family: 'Outfit'; font-size: clamp(1.4rem, 4vw, 1.8rem);">₹<?php echo number_format($grandSanctioned, 2); ?></h3>
                                </div>
                                <div class="bg-primary-light p-3 rounded-3 text-primary">
                                    <i class="fa-solid fa-indian-rupee-sign fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 shadow-sm border-0 border-start border-warning border-4 h-100 kpi-card" style="background: rgba(255,255,255,0.97); border-radius: 16px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;" data-i18n="lblTotalSpent">Total Fund Expenditure</h6>
                                    <h3 class="fw-bold mb-0 text-warning" style="font-family: 'Outfit'; font-size: clamp(1.4rem, 4vw, 1.8rem);">₹<?php echo number_format($grandSpent, 2); ?></h3>
                                </div>
                                <div class="bg-warning-light p-3 rounded-3 text-warning">
                                    <i class="fa-solid fa-receipt fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 shadow-sm border-0 border-start border-success border-4 h-100 kpi-card" style="background: rgba(255,255,255,0.97); border-radius: 16px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;" data-i18n="lblTotalRemaining">Total Remaining Balance</h6>
                                    <h3 class="fw-bold mb-0 text-success" style="font-family: 'Outfit'; font-size: clamp(1.4rem, 4vw, 1.8rem);">₹<?php echo number_format($grandRemaining, 2); ?></h3>
                                </div>
                                <div class="bg-success-light p-3 rounded-3 text-success">
                                    <i class="fa-solid fa-wallet fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tier 2: Form Entry -->
                <div class="row g-4 mb-4">
                    <!-- Form Entry -->
                    <div class="col-12">
                        <div class="utilization-card p-4 h-100 shadow-sm" style="border-radius: 24px; border: 1px solid rgba(232, 236, 242, 0.9); background: rgba(255,255,255,0.97);">
                            <h4 class="mb-3 theme-title fw-bold text-center border-bottom pb-2" style="font-family: 'Outfit', sans-serif;"><i class="fa-solid fa-pen-to-square me-2"></i><span data-i18n="lblFormTitle">Fund Allocation & Expenditure Tracker</span></h4>
                            
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger mb-3 py-2" role="alert"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success mb-3 py-2" role="alert"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>

                            <form method="post" id="amount-utilization-form" action="amount_utilization_db.php">
                                <div class="card p-3 mb-3 border-0 bg-light" style="border-radius: 16px;">
                                    <h6 class="mb-3 fw-bold text-dark" style="border-left: 3px solid #17a2b8; padding-left: 8px;" data-i18n="lblWorkInfo">Fund Availability & Work Info</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="form-label small mb-1" for="work_type" data-i18n="lblWorkType">Work Type <span class="text-danger">*</span></label>
                                                <select name="work_type" id="work_type" class="form-select form-select-sm w-100" required>
                                                    <option value="" data-i18n="optSelectWorkType">Select Work Type</option>
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
                                                <label class="form-label small mb-1" for="fund_source" data-i18n="lblFundingSource">Fund Source <span class="text-danger">*</span></label>
                                                <select name="fund_source" id="fund_source" class="form-select form-select-sm w-100" required>
                                                    <option value="" data-i18n="optSelectFundSource">Select Fund Source</option>
                                                    <option value="Zilla Parishad Fund" <?php echo (isset($_POST['fund_source']) && $_POST['fund_source'] === 'Zilla Parishad Fund') ? 'selected' : ''; ?>>Zilla Parishad Fund</option>
                                                    <option value="State Government Fund" <?php echo (isset($_POST['fund_source']) && $_POST['fund_source'] === 'State Government Fund') ? 'selected' : ''; ?>>State Government Fund</option>
                                                    <option value="Additional Fund" <?php echo (isset($_POST['fund_source']) && $_POST['fund_source'] === 'Additional Fund') ? 'selected' : ''; ?>>Additional Fund</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="form-label small mb-1" for="sanctioned_amount" data-i18n="lblSanctionedAmount">Sanctioned Amount <span class="text-danger">*</span></label>
                                                <input type="number" name="sanctioned_amount" id="sanctioned_amount" class="form-control form-control-sm w-100" step="0.01" min="0" value="<?php echo htmlspecialchars($_POST['sanctioned_amount'] ?? ''); ?>" placeholder="Enter Sanctioned Amount" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="form-label small mb-1" for="work_name" data-i18n="lblWorkName">Work Name <span class="text-danger">*</span></label>
                                                <select name="work_name" id="work_name" class="form-select form-select-sm w-100" required>
                                                    <option value="" data-i18n="optSelectWorkName">Select Work Name</option>
                                                    <option value="Building Maintenance" <?php echo (isset($_POST['work_name']) && $_POST['work_name'] === 'Building Maintenance') ? 'selected' : ''; ?>>Building Maintenance</option>
                                                    <option value="Electrical Work" <?php echo (isset($_POST['work_name']) && $_POST['work_name'] === 'Electrical Work') ? 'selected' : ''; ?>>Electrical Work</option>
                                                    <option value="Water Supply" <?php echo (isset($_POST['work_name']) && $_POST['work_name'] === 'Water Supply') ? 'selected' : ''; ?>>Water Supply</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mb-0">
                                                <label class="form-label small mb-1" for="share" data-i18n="lblRemarks">Remarks (Optional)</label>
                                                <input type="text" name="share" id="share" class="form-control form-control-sm w-100" value="<?php echo htmlspecialchars($_POST['share'] ?? ''); ?>" placeholder="Enter Remarks">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card p-3 border-0 bg-light" style="border-radius: 16px;">
                                    <h6 class="mb-3 fw-bold text-dark" style="border-left: 3px solid #17a2b8; padding-left: 8px;" data-i18n="lblExpenditureSplits">Expenditure Splits</h6>
                                    
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-4">
                                            <div class="p-2 text-center bg-white rounded-3 shadow-sm border-start border-3 border-info">
                                                <label class="d-block mb-1 text-muted fw-bold" style="font-size:0.65rem;" data-i18n="lblSplitTotal">TOTAL</label>
                                                <div class="fw-bold text-info" id="summary-total" style="font-size:0.95rem;">₹0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-2 text-center bg-white rounded-3 shadow-sm border-start border-3 border-warning">
                                                <label class="d-block mb-1 text-muted fw-bold" style="font-size:0.65rem;" data-i18n="lblSplitSpent">SPENT</label>
                                                <div class="fw-bold text-warning" id="summary-spent" style="font-size:0.95rem;">₹0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-2 text-center bg-white rounded-3 shadow-sm border-start border-3 border-success">
                                                <label class="d-block mb-1 text-muted fw-bold" style="font-size:0.65rem;" data-i18n="lblSplitRemain">REMAINING</label>
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
                                                    <label class="form-label small mb-1" data-i18n="lblAmountSpent">Amount Spent <span class="text-danger">*</span></label>
                                                    <input type="number" name="expense_amount[]" class="form-control form-control-sm expense-amount w-100" step="0.01" min="0" value="<?php echo $amount; ?>" placeholder="Amount Spent" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1" data-i18n="lblSplitDate">Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="expense_date[]" class="form-control form-control-sm expense-date w-100" value="<?php echo $date; ?>" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small mb-1" data-i18n="lblSplitRemarks">Remarks</label>
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
                                        <button type="submit" name="save" class="btn btn-primary btn-sm px-4" data-i18n="btnSubmit">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    </div>
                </div>

                <!-- Tier 3: Detailed Transaction Log Table -->
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card p-4 shadow-sm h-100 utilization-card" style="border-radius: 24px; border: 1px solid rgba(232, 236, 242, 0.9); background: rgba(255,255,255,0.97);">
                            <h4 class="fw-bold mb-3 theme-title border-bottom pb-2" style="font-family: 'Outfit', sans-serif;"><i class="fa-solid fa-file-invoice-dollar me-2"></i><span data-i18n="lblReportTitle">Fund Utilization Summary Report</span></h4>
                            <p class="text-muted small mb-3" data-i18n="lblReportDesc">Comprehensive tracking logs with dynamic project balances.</p>
                            
                            <div class="table-responsive" style="max-height: 340px; overflow-y: auto; border: 1px solid #eee; border-radius: 12px;">
                                <table class="table table-hover align-middle mb-0" style="font-size:0.82rem;">
                                    <thead class="sticky-top table-light" style="z-index: 5;">
                                        <tr style="border-bottom: 2px solid #dee2e6;">
                                            <th style="width: 15%; font-weight: 600; color: #2d064d; text-align: center;" data-i18n="lblSplitDate">Date</th>
                                            <th style="width: 25%; font-weight: 600; color: #2d064d; text-align: left;" data-i18n="lblWorkName">Work Name</th>
                                            <th style="width: 20%; font-weight: 600; color: #2d064d; text-align: center;" data-i18n="lblWorkType">Work Type</th>
                                            <th style="width: 20%; font-weight: 600; color: #2d064d; text-align: right;" data-i18n="lblSanctionedAmount">Sanctioned</th>
                                            <th style="width: 20%; font-weight: 600; color: #2d064d; text-align: right;" data-i18n="lblAmountSpent">Spent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($reportRows)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4" data-i18n="lblNoRecords">No records found.</td>
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
        }
        
        updateSummary();

        // Mobile sidebar toggle logic
        document.addEventListener('DOMContentLoaded', function() {
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            if (mobileSidebarToggle && sidebar) {
                mobileSidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('active');
                });
                
                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 991 && sidebar.classList.contains('active')) {
                        if (!sidebar.contains(e.target) && e.target !== mobileSidebarToggle) {
                            sidebar.classList.remove('active');
                        }
                    }
                });
            }

            document.getElementById('langMarBtn').addEventListener('click', () => {
                localStorage.setItem('ceoLang', 'mar');
                setLanguage('mar');
            });
            document.getElementById('langEngBtn').addEventListener('click', () => {
                localStorage.setItem('ceoLang', 'eng');
                setLanguage('eng');
            });

            const savedLang = localStorage.getItem('ceoLang') || 'mar';
            setLanguage(savedLang);
        });

        const langStrings = {
            navTitle: { mar: 'निधी वापर तपशील', eng: 'Fund Utilization Details' },
            lblTotalSanctioned: { mar: 'एकूण मंजूर बजेट', eng: 'Total Sanctioned Budget' },
            lblTotalSpent: { mar: 'एकूण खर्च निधी', eng: 'Total Fund Expenditure' },
            lblTotalRemaining: { mar: 'एकूण उर्वरित निधी', eng: 'Total Remaining Balance' },
            lblFormTitle: { mar: 'निधी वाटप आणि खर्च ट्रॅकर', eng: 'Fund Allocation & Expenditure Tracker' },
            lblWorkInfo: { mar: 'निधी उपलब्धता आणि कामाची माहिती', eng: 'Fund Availability & Work Info' },
            lblWorkType: { mar: 'कामाचा प्रकार', eng: 'Work Type' },
            lblFundingSource: { mar: 'निधीचा स्रोत', eng: 'Fund Source' },
            lblSanctionedAmount: { mar: 'मंजूर रक्कम (₹)', eng: 'Sanctioned Amount (₹)' },
            lblWorkName: { mar: 'कामाचे नाव', eng: 'Work Name' },
            lblRemarks: { mar: 'शेरा (प्रशासकीय)', eng: 'Remarks (Optional)' },
            lblExpenditureSplits: { mar: 'खर्चाचे विवरण (तपशीलवार)', eng: 'Expenditure Splits' },
            lblSplitTotal: { mar: 'एकूण (TOTAL)', eng: 'TOTAL' },
            lblSplitSpent: { mar: 'खर्च (SPENT)', eng: 'SPENT' },
            lblSplitRemain: { mar: 'उर्वरित (REMAINING)', eng: 'REMAINING' },
            lblAmountSpent: { mar: 'खर्च रक्कम (₹)', eng: 'Amount Spent' },
            lblSplitDate: { mar: 'दिनांक', eng: 'Date' },
            lblSplitRemarks: { mar: 'खर्च शेरा / तपशील', eng: 'Remarks' },
            btnSubmit: { mar: 'जतन करा', eng: 'Submit' },
            lblReportTitle: { mar: 'निधी वापर अहवाल रजिस्टर', eng: 'Fund Utilization Summary Report' },
            lblReportDesc: { mar: 'सर्व शाळांच्या निधी आणि खर्चाचा सविस्तर इतिहास रजिस्टर.', eng: 'Comprehensive tracking logs with dynamic project balances.' },
            lblNoRecords: { mar: 'कोणतीही नोंद सापडली नाही.', eng: 'No records found.' },
            optSelectWorkType: { mar: 'कामाचा प्रकार निवडा', eng: 'Select Work Type' },
            optSelectFundSource: { mar: 'निधीचा स्रोत निवडा', eng: 'Select Fund Source' },
            optSelectWorkName: { mar: 'कामाचे नाव निवडा', eng: 'Select Work Name' },
            
            sideDashboard: { mar: 'CEO डॅशबोर्ड', eng: 'CEO Dashboard' },
            sideCreateStages: { mar: 'टप्पे तयार करा', eng: 'Create Stages' },
            sideStagesReport: { mar: 'टप्प्यांचा अहवाल', eng: 'Stages Report' },
            sideWorkReport: { mar: 'कामाचा अहवाल', eng: 'Work Report' },
            sideFundingReport: { mar: 'निधी अहवाल', eng: 'Funding Report' },
            sideCreateUser: { mar: 'युझर तयार करा', eng: 'Create User' },
            sideFundUtil: { mar: 'निधी वापर तपशील', eng: 'Fund Utilization Details' }
        };

        function setLanguage(lang) {
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (langStrings[key] && langStrings[key][lang]) {
                    el.textContent = langStrings[key][lang];
                }
            });

            const marBtn = document.getElementById('langMarBtn');
            const engBtn = document.getElementById('langEngBtn');
            if (marBtn && engBtn) {
                marBtn.classList.toggle('btn-primary', lang === 'mar');
                marBtn.classList.toggle('btn-outline-primary', lang !== 'mar');
                engBtn.classList.toggle('btn-primary', lang === 'eng');
                engBtn.classList.toggle('btn-outline-primary', lang !== 'eng');
            }
        }
    </script>
</body>
</html>
