<?php
session_start();
require_once 'include/dbConfig.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$hm_id = $_SESSION['user_id'];
$error = '';
$success = '';
$balance = null;

if (isset($_POST['save'])) {
    $work_type = trim($_POST['work_type'] ?? '');
    $fund_source = trim($_POST['fund_source'] ?? '');
    $work_name = trim($_POST['work_name'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $sanctioned_amount = isset($_POST['sanctioned_amount']) ? (float) $_POST['sanctioned_amount'] : 0;
    $expense_date = trim($_POST['expense_date'] ?? '');
    $expense_remark = trim($_POST['expense_remark'] ?? '');
    $stages_data = trim($_POST['stages_data'] ?? '[]');
    $stages = json_decode($stages_data, true);

    if ($sanctioned_amount <= 0) {
        $error = 'Please enter a valid CEO sanctioned amount.';
    } elseif (!is_array($stages) || empty($stages)) {
        $error = 'Please add at least one utilization stage.';
    } else {
        $total_stage_amount = 0;
        $stage_lines = [];
        foreach ($stages as $stage) {
            $name = trim($stage['name'] ?? '');
            $amount = isset($stage['amount']) ? (float) $stage['amount'] : 0;
            if ($name === '' || $amount <= 0) {
                $error = 'All stage names and amounts must be valid.';
                break;
            }
            $total_stage_amount += $amount;
            $stage_lines[] = "$name: ₹ " . number_format($amount, 2);
        }

        if ($error === '' && $total_stage_amount > $sanctioned_amount) {
            $error = 'Total utilization amount cannot exceed the CEO sanctioned amount.';
        }

        if ($error === '') {
            $amount_spent = $total_stage_amount;
            $remarks_combined = $remarks;
            if (!empty($stage_lines)) {
                $remarks_combined .= ($remarks_combined !== '' ? ' | ' : '') . 'Stage details: ' . implode('; ', $stage_lines);
            }

            $sql = "INSERT INTO amount_utilization
                    (hm_id, work_type, fund_source, sanctioned_amount, work_name, remarks, amount_spent, expense_date, expense_remark)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param(
                    'issdssdss',
                    $hm_id,
                    $work_type,
                    $fund_source,
                    $sanctioned_amount,
                    $work_name,
                    $remarks_combined,
                    $amount_spent,
                    $expense_date,
                    $expense_remark
                );

                if ($stmt->execute()) {
                    $success = 'Utilization record saved successfully.';
                    $balance = $sanctioned_amount - $amount_spent;
                } else {
                    $error = 'Unable to save record. Please try again.';
                }
                $stmt->close();
            } else {
                $error = 'Database error: ' . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amount Utilization</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/amount_utilization.css">
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
                        <h5 class="mb-0 font-weight-bold" id="pageMainHeader">Amount Utilization</h5>
                    </div>
                </div>
            </nav>
            <div class="container py-5">
                <div class="utilization-card mx-auto p-4" style="max-width: 900px;">
            <div class="row g-4">
                <div class="col-12">
                    <h1 class="mb-3">Amount Utilization</h1>
                    <p class="text-muted">Follow the steps: 1) View CEO allocation 2) Add stage-wise utilization 3) Submit expenses</p>
                </div>

                <?php if ($error): ?>
                    <div class="col-12">
                        <div class="alert alert-danger mb-0" role="alert"><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="col-12">
                        <div class="alert alert-success mb-0" role="alert"><?php echo htmlspecialchars($success); ?></div>
                    </div>
                <?php endif; ?>

                <div class="col-12">
                    <!-- Balance Summary -->
                    <div class="row g-3 mb-4" id="balanceSummary" style="<?php echo ($balance === null) ? 'display:none;' : ''; ?>">
                        <div class="col-12">
                            <div class="balance-box">
                                <label>Total Amount</label>
                                <div class="balance-value">₹ <?php echo number_format($_POST['sanctioned_amount'] ?? 0, 2); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="balance-box">
                                <label>Amount Spent</label>
                                <div class="balance-value">₹ <?php echo number_format($_POST['amount_spent'] ?? 0, 2); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="balance-box">
                                <label>Remaining Balance</label>
                                <div class="balance-value">₹ <?php echo number_format($balance, 2); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <!-- Stepper UI -->
                    <div id="util-stepper">
                        <div class="step active" data-step="1">
                            <h5>Step 1: CEO Sanctioned Amount</h5>
                            <p class="small text-muted">Enter the CEO sanctioned amount and project details.</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">CEO Sanctioned Amount</label>
                                    <input type="number" id="ceo_amount" class="form-control" step="0.01" min="0" value="<?php echo htmlspecialchars($_POST['sanctioned_amount'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fund Source</label>
                                    <input type="text" id="fund_source" class="form-control" value="<?php echo htmlspecialchars($_POST['fund_source'] ?? ''); ?>" placeholder="e.g. District Grant">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Work Type</label>
                                    <input type="text" id="work_type" class="form-control" value="<?php echo htmlspecialchars($_POST['work_type'] ?? ''); ?>" placeholder="e.g. Classroom Repair">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Work Name</label>
                                    <input type="text" id="work_name" class="form-control" value="<?php echo htmlspecialchars($_POST['work_name'] ?? ''); ?>" placeholder="e.g. Building Renovation">
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <button class="btn btn-outline-primary btn-sm" onclick="nextStep(2); return false;">Next</button>
                            </div>
                        </div>

                        <div class="step" data-step="2">
                            <h5>Step 2: Add Utilization Stages</h5>
                            <p class="small text-muted">Add each stage and amount used for that stage.</p>
                            <div id="stages-list">
                                <div class="row g-2 stage-row">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control stage-name" placeholder="Stage name (e.g., Foundation)">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="number" class="form-control stage-amount" placeholder="Amount utilized" step="0.01" min="0">
                                    </div>
                                    <div class="col-md-2 d-grid">
                                        <button class="btn btn-danger btn-sm remove-stage">Remove</button>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-secondary btn-sm" id="add-stage">Add another stage</button>
                            </div>
                            <div class="mt-3 text-end">
                                <button class="btn btn-outline-secondary btn-sm" onclick="prevStep(1); return false;">Back</button>
                                <button class="btn btn-outline-primary btn-sm" onclick="nextStep(3); return false;">Next</button>
                            </div>
                        </div>

                        <div class="step" data-step="3">
                            <h5>Step 3: Review & Submit</h5>
                            <p class="small text-muted">Confirm stage-wise utilization before saving.</p>

                            <div id="review-area" class="mb-3"></div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="balance-box text-center py-3">
                                        <label>Total Amount</label>
                                        <div class="balance-value" id="summary-total">₹ 0.00</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="balance-box text-center py-3">
                                        <label>Amount Spent</label>
                                        <div class="balance-value" id="summary-spent">₹ 0.00</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="balance-box text-center py-3">
                                        <label>Amount Remaining</label>
                                        <div class="balance-value" id="summary-remain">₹ 0.00</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Expense Date</label>
                                    <input type="date" id="expense_date" class="form-control" value="<?php echo htmlspecialchars($_POST['expense_date'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">HM Remarks</label>
                                    <input type="text" id="remarks" class="form-control" value="<?php echo htmlspecialchars($_POST['remarks'] ?? ''); ?>" placeholder="Notes for this utilization record">
                                </div>
                            </div>
                            <form method="post" id="final-submit-form">
                                <input type="hidden" name="work_type" id="form_work_type">
                                <input type="hidden" name="fund_source" id="form_fund_source">
                                <input type="hidden" name="sanctioned_amount" id="form_sanctioned_amount">
                                <input type="hidden" name="work_name" id="form_work_name">
                                <input type="hidden" name="amount_spent" id="form_amount_spent">
                                <input type="hidden" name="expense_date" id="form_expense_date">
                                <input type="hidden" name="remarks" id="form_remarks">
                                <input type="hidden" name="expense_remark" id="form_expense_remark" value="Stage utilization recorded">
                                <input type="hidden" name="stages_data" id="form_stages_data">
                                <div class="text-end mt-3">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="prevStep(2); return false;">Back</button>
                                    <button type="submit" name="save" class="btn-submit">Submit Utilization</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- .container -->
    </div> <!-- #content -->
    </div> <!-- #wrapper -->
    <?php include 'include/website_footer.php'; ?>
    <script>
        function showStep(n) {
            document.querySelectorAll('#util-stepper .step').forEach(function(s){ s.classList.remove('active'); });
            const el = document.querySelector('#util-stepper .step[data-step="'+n+'"]');
            if (el) el.classList.add('active');
        }

        function nextStep(n) {
            if (n === 2) {
                const ceo = parseFloat(document.getElementById('ceo_amount').value || 0);
                if (isNaN(ceo) || ceo <= 0) {
                    alert('Enter CEO sanctioned amount.');
                    return;
                }
                // Show balance summary and update values
                const summary = document.getElementById('balanceSummary');
                if (summary) {
                    summary.style.display = 'grid';
                    // Update balance box values from form inputs
                    const balanceBoxes = summary.querySelectorAll('.balance-value');
                    if (balanceBoxes[0]) {
                        balanceBoxes[0].textContent = '₹ ' + ceo.toFixed(2);
                    }
                }
            }
            if (n === 3) {
                if (!prepareReview()) return;
            }
            showStep(n);
        }

        function prevStep(n) {
            showStep(n);
        }

        document.getElementById('add-stage').addEventListener('click', function(e){
            e.preventDefault();
            const row = document.querySelector('.stage-row').cloneNode(true);
            row.querySelectorAll('input').forEach(function(i){ i.value = ''; });
            document.getElementById('stages-list').appendChild(row);
        });

        document.getElementById('stages-list').addEventListener('click', function(e){
            if (e.target.classList.contains('remove-stage')) {
                e.preventDefault();
                const rows = document.querySelectorAll('#stages-list .stage-row');
                if (rows.length > 1) e.target.closest('.stage-row').remove();
            }
        });

        function prepareReview() {
            const ceo = parseFloat(document.getElementById('ceo_amount').value || 0);
            const fundSource = document.getElementById('fund_source').value.trim();
            const workType = document.getElementById('work_type').value.trim();
            const workName = document.getElementById('work_name').value.trim();
            const expenseDate = document.getElementById('expense_date').value;
            const remarks = document.getElementById('remarks').value.trim();

            const rows = document.querySelectorAll('#stages-list .stage-row');
            const stages = [];
            let totalAmount = 0;
            let invalid = false;

            rows.forEach(function(r) {
                const name = r.querySelector('.stage-name').value.trim();
                const amount = parseFloat(r.querySelector('.stage-amount').value || 0);
                if (name === '' && amount === 0) return;
                if (name === '' || isNaN(amount) || amount <= 0) {
                    invalid = true;
                } else {
                    stages.push({ name: name, amount: amount });
                    totalAmount += amount;
                }
            });

            if (invalid || stages.length === 0) {
                alert('Please complete all stage names and amount values.');
                return false;
            }
            if (totalAmount > ceo) {
                alert('Total stage utilization cannot exceed CEO sanctioned amount.');
                return false;
            }

            let html = '<div class="mb-3 p-3" style="background:#f8f7ff;border-radius:12px;">';
            html += '<div class="mb-2"><strong>CEO Sanctioned Amount:</strong> ₹ ' + ceo.toFixed(2) + '</div>';
            html += '<div class="mb-2"><strong>Work Type:</strong> ' + (workType || '-') + '</div>';
            html += '<div class="mb-2"><strong>Work Name:</strong> ' + (workName || '-') + '</div>';
            html += '<div class="mb-2"><strong>Fund Source:</strong> ' + (fundSource || '-') + '</div>';
            html += '<div class="mb-2"><strong>Expense Date:</strong> ' + (expenseDate || '-') + '</div>';
            html += '<div class="mb-2"><strong>HM Remarks:</strong> ' + (remarks || '-') + '</div>';
            html += '<div class="mb-3"><strong>Stage Utilization Details</strong></div>';
            html += '<ul class="list-group">';
            stages.forEach(function(s) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">' + s.name + '<span>₹ ' + s.amount.toFixed(2) + '</span></li>';
            });
            html += '</ul>';
            html += '<div class="mt-3"><strong>Note:</strong> Total amount, spent amount and remaining amount are shown above.</div>';
            html += '</div>';
            document.getElementById('summary-total').textContent = '₹ ' + ceo.toFixed(2);
            document.getElementById('summary-spent').textContent = '₹ ' + totalAmount.toFixed(2);
            document.getElementById('summary-remain').textContent = '₹ ' + (ceo - totalAmount).toFixed(2);

            document.getElementById('review-area').innerHTML = html;
            document.getElementById('form_work_type').value = workType;
            document.getElementById('form_fund_source').value = fundSource;
            document.getElementById('form_sanctioned_amount').value = ceo;
            document.getElementById('form_work_name').value = workName;
            document.getElementById('form_amount_spent').value = totalAmount.toFixed(2);
            document.getElementById('form_expense_date').value = expenseDate;
            document.getElementById('form_remarks').value = remarks;
            document.getElementById('form_stages_data').value = JSON.stringify(stages);

            return true;
        }

        showStep(1);

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>
