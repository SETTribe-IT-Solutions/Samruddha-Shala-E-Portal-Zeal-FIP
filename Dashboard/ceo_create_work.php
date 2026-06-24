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
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <div class="ms-3 d-flex align-items-center">
                        <h5 class="mb-0 font-weight-bold" id="pageMainHeader">Create Work</h5>
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
                                <h4 class="fw-bold mb-1"><i class="fa-solid fa-file-signature me-2 text-primary"></i>Create Task for HM</h4>
                                <p class="text-muted mb-4">Create or update the active task for a school in Kolhapur District, Maharashtra. This resets work progress to 0% and marks the task as pending HM action.</p>
                                
                                <form id="ceoAssignTaskForm" action="ceo_create_work_db.php" onsubmit="handleAssignTaskSubmit(event)">
                                    <div class="mb-3">
                                        <label for="ceoTaskSchoolSelect" class="form-label fw-semibold">Select School</label>
                                        <select id="ceoTaskSchoolSelect" name="school_name" class="form-select" required></select>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="ceoTaskWorkType" class="form-label fw-semibold">Work Type</label>
                                            <select id="ceoTaskWorkType" name="work_type" class="form-select" required>
                                                <option value="Construction">Construction</option>
                                                <option value="Non-Construction">Non-Construction</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ceoTaskBudget" class="form-label fw-semibold">Budget (Lakhs)</label>
                                            <input type="number" min="0" step="0.1" id="ceoTaskBudget" class="form-control" placeholder="e.g. 4.0">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="ceoTaskFundingSource" class="form-label fw-semibold">Funding Source</label>
                                            <select id="ceoTaskFundingSource" name="funding_source" class="form-select" required>
                                                <option value="" disabled selected>Select Funding Source</option>
                                                <option value="Annual Plan">Annual Plan</option>
                                                <option value="Minor Mineral Fund">Minor Mineral Fund</option>
                                                <option value="ZP Own Fund">ZP Own Fund</option>
                                                <option value="CSR Fund">CSR Fund</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ceoTaskDescription" class="form-label fw-semibold">Task Description</label>
                                        <textarea id="ceoTaskDescription" class="form-control" rows="4" placeholder="Describe the task details, expected outcomes, and priority..." required></textarea>
                                    </div>
                                    <div class="d-flex gap-3 mt-4">
                                        <button type="reset" class="btn btn-ceo-gold flex-fill py-2 fw-semibold">
                                            <i class="fa-solid fa-rotate-right me-2"></i>Reset
                                        </button>
                                        <button type="submit" class="btn btn-ceo-purple flex-fill py-2 fw-semibold">
                                            <i class="fa-solid fa-paper-plane me-2"></i>Assign Task
                                        </button>
                                    </div>
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
</body>
</html>

