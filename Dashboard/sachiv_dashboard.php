<?php
require_once '../include/auth.php';
requireRole(['SACHIV']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samruddha Shala E-Portal - Secretary Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom Style Sheet -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

    <div id="wrapper">
        <!-- Sidebar Navigation -->
       <?php include '../include/sidebar.php'; ?>

        <!-- Page Content -->
        <div id="content">
            <!-- Header Top Bar -->
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary btn-sm" onclick="toggleSidebar()">
                        <i class="fas fa-align-left"></i>
                    </button>

                    <div class="ms-3 d-flex align-items-center">
                        <h5 class="mb-0 font-weight-bold" id="pageMainHeader">Secretary Review Dashboard</h5>
                    </div>

                    <div class="ms-auto d-flex align-items-center">
                        <!-- Active User Indicator -->
                        
                    </div>
                </div>
            </nav>

            <!-- Main Content Container -->
            <div class="container-fluid p-4">

                <!-- ============================================== -->
                <!-- SACHIV VIEW: VERIFICATION QUEUE -->
                <!-- ============================================== -->
                <div id="sachiv-queue-view" class="view-panel">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <h4 class="fw-bold mb-1"><i class="fa-solid fa-clipboard-check me-2 text-success"></i>Verification Queue</h4>
                                        <p class="text-muted mb-0">Review school progress updates, photo uploads, remarks, and confirm changes to the dynamic register</p>
                                    </div>
                                    <span class="badge bg-success rounded-pill px-3 py-2" id="sachiv-pending-queue-count">0 Pending Verification</span>
                                </div>

                                <div id="sachivQueueContainer">
                                    <!-- Dynamic approval cards loaded here -->
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card p-4 bg-light border-0 mb-3">
                                <h5 class="fw-bold mb-3">Verification Guidelines</h5>
                                <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
                                    <li class="mb-3 d-flex">
                                        <span class="badge bg-primary rounded-circle me-3" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">1</span>
                                        <div>Verify the photo matches the reported construction phase.</div>
                                    </li>
                                    <li class="mb-3 d-flex">
                                        <span class="badge bg-primary rounded-circle me-3" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">2</span>
                                        <div>Cross-check reported remarks against typical physical work milestones.</div>
                                    </li>
                                    <li class="mb-3 d-flex">
                                        <span class="badge bg-primary rounded-circle me-3" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">3</span>
                                        <div>Upon approval, the system updates financial utilization ratios and alerts the CEO.</div>
                                    </li>
                                </ul>
                            </div>
                            <div class="card p-4 bg-white border-0 shadow-sm">
                                <h5 class="fw-bold mb-3">Reminder / Blocker Dispatcher</h5>
                                <form id="sachivReminderForm" onsubmit="handleReminderDispatchSubmit(event)">
                                    <div class="mb-3">
                                        <label for="sachivReminderSchool" class="form-label fw-semibold">Select School</label>
                                        <select id="sachivReminderSchool" class="form-select"></select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="sachivReminderType" class="form-label fw-semibold">Alert Type</label>
                                        <select id="sachivReminderType" class="form-select">
                                            <option value="reminder">Reminder</option>
                                            <option value="blocker">Blocker</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="sachivReminderTitle" class="form-label fw-semibold">Title</label>
                                        <input type="text" id="sachivReminderTitle" class="form-control" placeholder="e.g. Pending material delivery">
                                    </div>
                                    <div class="mb-3">
                                        <label for="sachivReminderMessage" class="form-label fw-semibold">Message</label>
                                        <textarea id="sachivReminderMessage" class="form-control" rows="3" placeholder="Share the blocker or follow-up action for the HM and CEO." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">Send Alert</button>
                                </form>
                                <div class="mt-3">
                                    <h6 class="fw-semibold">Recent Dispatches</h6>
                                    <div id="sachivReminderList" class="small"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- SACHIV VIEW: SCHOOL PERFORMANCE GRID -->
                <!-- ============================================== -->
                <div id="sachiv-schools-view" class="view-panel d-none">
                    <div class="card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="fw-bold mb-1"><i class="fa-solid fa-school me-2 text-success"></i>School Performance & Funding Grid</h4>
                                <p class="text-muted mb-0">Monitor school-wise construction milestones matched against fund expenditure patterns</p>
                            </div>
                        </div>

                        <div class="table-responsive shadow-sm">
                            <table class="table align-middle bg-white mb-0">
                                <thead>
                                    <tr>
                                        <th>School</th>
                                        <th>Work Type</th>
                                        <th>Progress Milestone</th>
                                        <th>Budget Allocated</th>
                                        <th>Funds Utilized</th>
                                        <th>Expenditure Rate</th>
                                    </tr>
                                </thead>
                                <tbody id="sachivPerformanceTableBody">
                                    <!-- Dynamic performance data loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Shared Database Layer -->
    <script src="js/db.js"></script>
    <!-- Sachiv Application Logic -->
    <script src="js/sachiv.js"></script>
</body>
</html>