<?php 
// Links the backend validation and logic rules cleanly
require_once 'amount_utilization.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/amount_utilization.css">
    
    <style>
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
    </style>
</head>
<body style="background: #f4f2fb; font-family: 'Poppins', sans-serif;">
    <?php include 'include/website_header.php'; ?>
    
    <div id="wrapper">
        <?php include 'include/sidebar.php'; ?>

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-2">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary btn-sm" onclick="toggleSidebar()">
                        <i class="fas fa-align-left"></i>
                    </button>
                    <div class="ms-3 d-flex align-items-center">
                        <h5 class="mb-0 font-weight-bold" id="pageMainHeader">Fund Details</h5>
                    </div>
                </div>
            </nav>
            
            <div class="container py-5">
                <div class="utilization-card mx-auto p-4" style="max-width: 900px;">
                    <div class="row g-4">
                        <div class="col-12">
                            <h1 class="mb-3 text-center border-bottom pb-2">Fund Details</h1>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="col-12">
                                <div class="alert alert-danger mb-0" role="alert"><?php echo htmlspecialchars($error); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="col-12">
                                <div class="alert alert-success mb-0" role="alert"><?php echo htmlspecialchars($success); ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="col-12">
                            <form method="post" id="amount-utilization-form" action="amount_utilization_db.php">
                                <div class="card p-4 mb-4">
                                    <h5 class="mb-4" style="border-left: 4px solid #17a2b8; padding-left: 10px;">Fund Availability & Work Information</h5>
                                    <div class="row g-3">
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label" for="work_type">Work Type <span class="text-danger">*</span></label>
                                                <select name="work_type" id="work_type" class="form-select w-100" required>
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
                                            <div class="form-group">
                                                <label class="form-label" for="fund_source">Fund Source <span class="text-danger">*</span></label>
                                                <select name="fund_source" id="fund_source" class="form-select w-100" required>
                                                    <option value="">Select Fund Source</option>
                                                    <option value="Zilla Parishad Fund" <?php echo (isset($_POST['fund_source']) && $_POST['fund_source'] === 'Zilla Parishad Fund') ? 'selected' : ''; ?>>Zilla Parishad Fund</option>
                                                    <option value="State Government Fund" <?php echo (isset($_POST['fund_source']) && $_POST['fund_source'] === 'State Government Fund') ? 'selected' : ''; ?>>State Government Fund</option>
                                                    <option value="Additional Fund" <?php echo (isset($_POST['fund_source']) && $_POST['fund_source'] === 'Additional Fund') ? 'selected' : ''; ?>>Additional Fund</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label" for="sanctioned_amount">Sanctioned Amount <span class="text-danger">*</span></label>
                                                <input type="number" name="sanctioned_amount" id="sanctioned_amount" class="form-control w-100" step="0.01" min="0" value="<?php echo htmlspecialchars($_POST['sanctioned_amount'] ?? ''); ?>" placeholder="Enter Sanctioned Amount" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label" for="work_name">Work Name <span class="text-danger">*</span></label>
                                                <select name="work_name" id="work_name" class="form-select w-100" required>
                                                    <option value="">Select Work Name</option>
                                                    <option value="Building Maintenance" <?php echo (isset($_POST['work_name']) && $_POST['work_name'] === 'Building Maintenance') ? 'selected' : ''; ?>>Building Maintenance</option>
                                                    <option value="Electrical Work" <?php echo (isset($_POST['work_name']) && $_POST['work_name'] === 'Electrical Work') ? 'selected' : ''; ?>>Electrical Work</option>
                                                    <option value="Water Supply" <?php echo (isset($_POST['work_name']) && $_POST['work_name'] === 'Water Supply') ? 'selected' : ''; ?>>Water Supply</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="form-label" for="share">Remarks (Optional)</label>
                                                <input type="text" name="share" id="share" class="form-control w-100" value="<?php echo htmlspecialchars($_POST['share'] ?? ''); ?>" placeholder="Enter Remarks">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="card p-4">
                                    <h5 class="mb-3" style="border-left: 4px solid #17a2b8; padding-left: 10px;">Expenditure Details</h5>
                                    
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <div class="balance-box p-3 text-center bg-light rounded-3" style="border-left: 4px solid #0288d1;">
                                                <label class="d-block mb-2 text-muted small fw-bold">TOTAL AMOUNT</label>
                                                <div class="balance-value fs-4 fw-bold" id="summary-total" style="color: #0288d1;">₹ 0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="balance-box p-3 text-center bg-light rounded-3" style="border-left: 4px solid #f57c00;">
                                                <label class="d-block mb-2 text-muted small fw-bold">AMOUNT SPENT</label>
                                                <div class="balance-value fs-4 fw-bold" id="summary-spent" style="color: #f57c00;">₹ 0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="balance-box p-3 text-center bg-light rounded-3" style="border-left: 4px solid #388e3c;">
                                                <label class="d-block mb-2 text-muted small fw-bold">REMAINING AMOUNT</label>
                                                <div class="balance-value fs-4 fw-bold" id="summary-remain" style="color: #388e3c;">₹ 0</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="expense-rows">
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
                                            <div class="row g-2 align-items-end expense-row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Amount Spent <span class="text-danger">*</span></label>
                                                    <input type="number" name="expense_amount[]" class="form-control expense-amount w-100" step="0.01" min="0" value="<?php echo $amount; ?>" placeholder="Amount Spent" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="expense_date[]" class="form-control expense-date w-100" value="<?php echo $date; ?>" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Remarks</label>
                                                    <input type="text" name="expense_remark[]" class="form-control expense-remark w-100" placeholder="Remarks" value="<?php echo $remark; ?>">
                                                </div>
                                                <div class="col-md-2 action-btns d-flex gap-1">
                                                    <button type="button" class="btn btn-success btn-sm w-50 add-expense-row" title="Add Entry"><i class="fa fa-plus"></i></button>
                                                    <button type="button" class="btn btn-danger btn-sm w-50 remove-expense-row" <?php echo ($expenseCount === 1 ? 'disabled' : ''); ?> title="Delete"><i class="fa fa-trash"></i></button>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>

                                    <div class="d-flex justify-content-end align-items-center mt-4">
                                        <button type="submit" name="save" class="btn btn-primary btn-sm px-3">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'include/website_footer.php'; ?>

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
            document.getElementById('summary-remain').textContent = '₹ ' + ((ceo - totalSpent) % 1 === 0 ? (ceo - totalSpent) : (ceo - totalSpent).toFixed(2));
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
        
        updateSummary();
    </script>
</body>
</html>