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
    <title>Samruddha Shala E-Portal - CEO Assign Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <link rel="stylesheet" href="css/ceo_create_work.css">
   
</head>
<body class="ceo-dashboard-page">
    <div id="wrapper">
        <?php include '../include/sidebar.php'; ?>

        <div id="content">
            <div class="ceo-fixed-header">
                <?php include '../include/website_header.php'; ?>
            </div>
            <nav class="navbar navbar-expand-lg navbar-light p-3" style="position: relative;">
                <div class="container-fluid d-flex flex-nowrap align-items-center px-1">
                    <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                        <!-- Mobile Sidebar Toggle -->
                        <button class="btn btn-light d-lg-none me-2 shadow-sm border-0 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 40px; height: 40px; background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%); color: white;" type="button" id="mobileSidebarToggle" aria-label="Toggle Sidebar">
                            <i class="fa-solid fa-bars fs-6"></i>
                        </button>
                        <h4 class="fw-bold mb-0 text-truncate" id="pageMainHeader" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: clamp(1.1rem, 4vw, 1.4rem); font-weight: 800 !important; line-height: 1.2;">Create Task</h4>
                    </div>
                    <div class="ms-2 d-flex align-items-center flex-shrink-0">
                        <div class="position-relative me-3">
                            <a href="ceo_alerts.php" class="btn btn-link text-dark p-1 text-decoration-none" id="notifBellButton" title="View Alerts & Notifications">
                                <i class="fa-regular fa-bell fs-5"></i>
                                <span id="alertsHeaderBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.65rem;">0</span>
                            </a>
                        </div>
                        <div class="d-flex align-items-center border-start ps-2">
                            <h4 class="fw-bold mb-0"><span class="role-badge badge-ceo" style="font-size: 0.85rem; padding: 4px 8px;">CEO</span></h4>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                <div id="ceo-task-view" class="view-panel">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="fw-bold mb-0 text-dark">Create Work / Project</h4>
                            </div>
                                
                            <form id="ceoAssignTaskForm" action="ceo_create_work_db.php" onsubmit="handleAssignTaskSubmit(event)">
                                <div class="row">
                                    <!-- Left Column: Work Details -->
                                    <div class="col-lg-6 mb-4">
                                        <div class="card p-4 h-100 shadow-sm border-0">
                                            <h5 class="fw-bold mb-4" style="color: #2d064d;">Work Details</h5>
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="ceoTaskWorkName" class="form-label fw-semibold small">Work Name <span class="text-danger">*</span></label>
                                                    <input type="text" id="ceoTaskWorkName" name="work_name" class="form-control form-control-sm" placeholder="Enter work name" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="ceoTaskWorkType" class="form-label fw-semibold small">Work Type <span class="text-danger">*</span></label>
                                                    <select id="ceoTaskWorkType" name="work_type" class="form-select form-select-sm" required>
                                                        <option value="" disabled selected>Select Work Type</option>
                                                        <option value="Civilian">Civilian</option>
                                                        <option value="Non Civilian">Non Civilian</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="ceoTaskSchoolSelect" class="form-label fw-semibold small">School Name <span class="text-danger">*</span></label>
                                                    <select id="ceoTaskSchoolSelect" name="school_name" class="form-select form-select-sm" required></select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="ceoTaskBudget" class="form-label fw-semibold small">Sanction Amount (₹) <span class="text-danger">*</span></label>
                                                    <input type="text" inputmode="numeric" pattern="[0-9]+" id="ceoTaskBudget" class="form-control form-control-sm" placeholder="Enter amount" onkeypress="return event.charCode >= 48 && event.charCode <= 57" required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="ceoTaskDescription" class="form-label fw-semibold small">Description</label>
                                                <textarea id="ceoTaskDescription" class="form-control form-control-sm" rows="5" placeholder="Enter work description"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Right Column: Work Stages & Weightage -->
                                    <div class="col-lg-6 mb-4">
                                        <div class="card p-4 h-100 d-flex flex-column shadow-sm border-0">
                                            <h5 class="fw-bold mb-1" style="color: #2d064d;">Work Stages & Weightage</h5>
                                            <p class="text-muted small mb-4">Total weightage must be equal to 100%</p>
                                            
                                            <div class="table-responsive flex-grow-1" style="max-height: 250px; overflow-y: auto;">
                                                <table class="table table-bordered align-middle text-center" id="stagesTable">
                                                    <thead class="table-light sticky-top">
                                                        <tr>
                                                            <th style="width: 50%; font-size: 0.85rem;">Stage</th>
                                                            <th style="width: 30%; font-size: 0.85rem;">Weightage (%)</th>
                                                            <th style="width: 20%; font-size: 0.85rem;">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="stagesTbody">
                                                        <tr class="stage-row">
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="stage-index me-2 text-muted fw-bold">1.</span>
                                                                    <input type="text" class="form-control form-control-sm stage-name" name="stage_name[]" placeholder="e.g. Planning & Approval" required>
                                                                </div>
                                                            </td>
                                                            <td><input type="number" class="form-control form-control-sm stage-weight text-center mx-auto" style="width: 80px;" name="stage_weight[]" min="1" max="100" value="" placeholder="0" required oninput="calculateTotalWeight()"></td>
                                                            <td>
                                                                <div class="d-flex justify-content-center gap-1">
                                                                    <button type="button" class="btn btn-success btn-sm p-1 text-white" style="height: 31px; width: 31px; display: flex; align-items: center; justify-content: center; box-shadow: none;" onclick="addStageRow()" title="Add"><i class="fa fa-plus"></i></button>
                                                                    <button type="button" class="btn btn-danger btn-sm p-1 text-white btn-delete-stage" style="height: 31px; width: 31px; display: flex; align-items: center; justify-content: center; box-shadow: none;" onclick="deleteStageRow(this)" disabled title="Delete"><i class="fa fa-trash"></i></button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="fw-bold bg-light">
                                                            <td class="text-start ps-3">Total</td>
                                                            <td id="totalWeightCell" class="text-danger fw-bold">0%</td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                            
                                            <div class="alert alert-primary d-flex align-items-start p-3 mt-3 mb-4" role="alert" style="background-color: #eef2ff; border-color: #eef2ff; color: #2d064d;">
                                                <i class="fa-solid fa-circle-info fs-5 me-3 mt-1 text-primary"></i>
                                                <div>
                                                    <strong class="d-block mb-1">Note</strong>
                                                    <span class="small text-muted">Stage percentage can be edited. Total must always be 100%.</span>
                                                </div>
                                            </div>

                                            <div class="d-flex gap-3 mt-auto justify-content-end">
                                                <button type="reset" class="btn btn-light border px-4 fw-semibold" onclick="setTimeout(resetStages, 10)">Reset</button>
                                                <button type="submit" class="btn btn-primary px-4 fw-semibold" style="background-color: #1a42b8; border-color: #1a42b8;">Save & Next</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
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
    <script src="js/ceo.js?v=11"></script>
    <script>
        // Stage Row Logic
        function addStageRow() {
            const tbody = document.getElementById('stagesTbody');
            const rowCount = tbody.querySelectorAll('tr').length;
            
            const tr = document.createElement('tr');
            tr.className = 'stage-row';
            tr.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        <span class="stage-index me-2 text-muted fw-bold">${rowCount + 1}.</span>
                        <input type="text" class="form-control form-control-sm stage-name" name="stage_name[]" placeholder="e.g. Next Stage" required>
                    </div>
                </td>
                <td><input type="number" class="form-control form-control-sm stage-weight text-center mx-auto" style="width: 80px;" name="stage_weight[]" min="1" max="100" value="0" required oninput="calculateTotalWeight()"></td>
                <td>
                    <div class="d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-success btn-sm p-1 text-white" style="height: 31px; width: 31px; display: flex; align-items: center; justify-content: center; box-shadow: none;" onclick="addStageRow()" title="Add"><i class="fa fa-plus"></i></button>
                        <button type="button" class="btn btn-danger btn-sm p-1 text-white btn-delete-stage" style="height: 31px; width: 31px; display: flex; align-items: center; justify-content: center; box-shadow: none;" onclick="deleteStageRow(this)" title="Delete"><i class="fa fa-trash"></i></button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
            updateStageIndices();
            calculateTotalWeight();
        }

        function deleteStageRow(btn) {
            const row = btn.closest('tr');
            row.remove();
            updateStageIndices();
            calculateTotalWeight();
        }

        function updateStageIndices() {
            const rows = document.querySelectorAll('#stagesTbody .stage-row');
            rows.forEach((row, index) => {
                row.querySelector('.stage-index').textContent = (index + 1) + '.';
                // Disable delete button if only one row left
                const deleteBtn = row.querySelector('.btn-delete-stage');
                if (rows.length === 1) {
                    deleteBtn.disabled = true;
                } else {
                    deleteBtn.disabled = false;
                }
            });
        }

        function calculateTotalWeight() {
            const weights = document.querySelectorAll('.stage-weight');
            let total = 0;
            weights.forEach(w => {
                total += parseInt(w.value) || 0;
            });
            
            const totalCell = document.getElementById('totalWeightCell');
            totalCell.textContent = total + '%';
            
            if (total === 100) {
                totalCell.className = 'text-success fw-bold';
            } else {
                totalCell.className = 'text-danger fw-bold';
            }
            return total;
        }

        function resetStages() {
            const tbody = document.getElementById('stagesTbody');
            tbody.innerHTML = `
                <tr class="stage-row">
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="stage-index me-2 text-muted fw-bold">1.</span>
                            <input type="text" class="form-control form-control-sm stage-name" name="stage_name[]" placeholder="e.g. Planning & Approval" required>
                        </div>
                    </td>
                    <td><input type="number" class="form-control form-control-sm stage-weight text-center mx-auto" style="width: 80px;" name="stage_weight[]" min="1" max="100" value="" placeholder="0" required oninput="calculateTotalWeight()"></td>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-success btn-sm p-1 text-white" style="height: 31px; width: 31px; display: flex; align-items: center; justify-content: center; box-shadow: none;" onclick="addStageRow()" title="Add"><i class="fa fa-plus"></i></button>
                            <button type="button" class="btn btn-danger btn-sm p-1 text-white btn-delete-stage" style="height: 31px; width: 31px; display: flex; align-items: center; justify-content: center; box-shadow: none;" onclick="deleteStageRow(this)" disabled title="Delete"><i class="fa fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;
            calculateTotalWeight();
        }

        // Intercept form submit to validate weightage
        document.getElementById('ceoAssignTaskForm').addEventListener('submit', function(e) {
            const total = calculateTotalWeight();
            if (total !== 100) {
                e.preventDefault();
                e.stopPropagation();
                alert('Total stage weightage must be exactly 100%. Currently it is ' + total + '%.');
            }
        });

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
        });
    </script>
</body>
</html>

