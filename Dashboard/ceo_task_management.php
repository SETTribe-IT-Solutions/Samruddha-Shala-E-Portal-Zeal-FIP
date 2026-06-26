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
    <link rel="stylesheet" href="css/ceo_task_management.css">
    
    
</head>
<body class="ceo-dashboard-page">
    <div id="wrapper">
        <!-- Sidebar Navigation -->
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
                        <h4 class="fw-bold mb-0 text-truncate" id="pageMainHeader" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: clamp(1.1rem, 4vw, 1.4rem); font-weight: 800 !important; line-height: 1.2;">Task Management</h4>
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
    <script src="js/create_work.js?v=3"></script>
    <script>
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

