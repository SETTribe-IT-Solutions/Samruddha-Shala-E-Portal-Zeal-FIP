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
    <title>Samruddha Shala E-Portal - Head Master Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="../css/sidebar.css" rel="stylesheet">
    <link href="css/hm_dashboard.css" rel="stylesheet">

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="hm-dashboard-page">

<div id="wrapper">

    <!-- Sidebar -->
    <?php include '../include/sidebar.php'; ?>

    <!-- Main Content -->
    <div id="content">

        <!-- Fixed Header -->
        <div class="hm-fixed-header">
            <?php include '../include/website_header.php'; ?>
        </div>

        <!-- Header Top Bar (Navbar) -->
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <button type="button" id="sidebarCollapse" class="btn btn-link text-dark me-2 d-lg-none" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                        <i class="fas fa-align-left fs-5"></i>
                    </button>
                    <h5 class="mb-0 fw-bold" id="pageMainHeader">School Progress Reporting Desk</h5>
                </div>

                <div class="ms-auto d-flex align-items-center">
                    <!-- Notifications Dropdown -->
                    <div class="dropdown me-3 position-relative">
                        <button class="btn btn-link text-dark p-1 text-decoration-none" id="notifBellButton" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-bell fs-5"></i>
                            <span id="alertsHeaderBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                                0
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" id="notifBellDropdown" style="width: 320px; border-radius: 12px;">
                            <li class="dropdown-header fw-bold d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span>Notifications Center</span>
                                <span class="badge bg-danger rounded-pill" id="notifBellCountText">0 Alerts</span>
                            </li>
                            <div id="notifBellList" class="my-2" style="max-height: 250px; overflow-y: auto;">
                                <!-- Dynamic notifications go here -->
                            </div>
                            <li class="text-center pt-2 border-top">
                                <a class="text-decoration-none text-primary fw-bold" href="javascript:void(0)" onclick="switchTab('hm-history')" style="font-size: 0.8rem;">View Submissions History</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Dashboard Content -->
        <div class="container-fluid p-4">

            <!-- PAGE TITLE -->
            <div class="card p-4 mb-4">
                <h3 class="fw-bold mb-1">
                    <i class="fa-solid fa-school me-2 text-primary"></i>
                    Head Master Dashboard
                </h3>
                <p class="text-muted mb-0">
                    Submit progress reports, monitor project activities and manage school updates.
                </p>
            </div>

            <!-- Sub-navigation Tabs -->
            <ul class="nav nav-pills mb-4 gap-2 bg-light p-2 rounded" id="hmDashboardTabs" role="tablist" style="width: fit-content; border: 1px solid rgba(228, 232, 239, 0.95); border-radius: 16px !important;">
                <li class="nav-item">
                    <button class="btn btn-sm btn-outline-primary active" id="nav-hm-report" onclick="switchTab('hm-report')">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Submit Progress Update
                    </button>
                </li>
                <li class="nav-item">
                    <button class="btn btn-sm btn-outline-primary" id="nav-hm-history" onclick="switchTab('hm-history')">
                        <i class="fa-solid fa-clock-rotate-left me-2"></i>Timeline & History
                    </button>
                </li>
            </ul>

            <!-- HM REPORT VIEW -->
            <div id="hm-report-view" class="view-panel">

                <div class="row">

                    <!-- LEFT SIDE FORM -->
                    <div class="col-lg-7">

                        <div class="card p-4">

                            <h4 class="fw-bold mb-1">
                                <i class="fa-solid fa-cloud-arrow-up me-2 text-primary"></i>
                                Submit Progress Update
                            </h4>

                            <p class="text-muted mb-4">
                                Use this form to submit actual physical construction logs, report blockers, geo-tags and photographs.
                            </p>

                            <!-- CEO Task Notification Box -->
                            <div class="alert alert-info mb-3">
                                <strong><i class="fa-solid fa-bell me-2"></i>CEO Task Notification</strong>
                                <div id="hmTaskNotificationText" class="small mt-1">
                                    No task assigned yet.
                                </div>
                            </div>

                            <!-- REPORT SUBMISSION FORM -->
                            <form id="hmUpdateForm" onsubmit="handleHMUpdateSubmit(event)">
                                <div class="mb-3">
                                    <label for="hmSchoolSelect" class="form-label fw-semibold">Select School</label>
                                    <select id="hmSchoolSelect" class="form-select" onchange="loadHMSchoolSpecificDetails(this.value)"></select>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="hmWorkType" class="form-label fw-semibold">Work Category</label>
                                        <input type="text" id="hmWorkType" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="hmFundingSource" class="form-label fw-semibold">Funding Source</label>
                                        <input type="text" id="hmFundingSource" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="hmProgressRange" class="form-label fw-semibold mb-0">Current Stage Progress</label>
                                        <span id="hmProgressValueText" class="fw-bold text-primary">0%</span>
                                    </div>
                                    <input type="range" id="hmProgressRange" class="form-range" min="0" max="100" value="0" oninput="updateHMProgressSliderText(this.value)">
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="hmSpentAmount" class="form-label fw-semibold">Amount Spent (Lakhs)</label>
                                        <input type="number" id="hmSpentAmount" class="form-control" placeholder="e.g. 1.5" min="0" step="0.01">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="hmBudgetNotes" class="form-label fw-semibold">Financial / Budget Notes</label>
                                        <input type="text" id="hmBudgetNotes" class="form-control" placeholder="e.g. Materials purchased, labor paid">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="hmBlockerSelector" class="form-label fw-semibold">Report Blocker Status</label>
                                    <select id="hmBlockerSelector" class="form-select" onchange="toggleHMBlockerDetailsInput(this.value)">
                                        <option value="None">None</option>
                                        <option value="Material Shortage">Material Shortage</option>
                                        <option value="Labor Shortage">Labor Shortage</option>
                                        <option value="Fund Delay">Fund Delay</option>
                                        <option value="Weather Delays">Weather Delays</option>
                                        <option value="Land Boundary Dispute">Land Boundary Dispute</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div id="hmBlockerDetailsContainer" class="mb-3 d-none">
                                    <label for="hmBlockerDetails" class="form-label fw-semibold">Blocker Description</label>
                                    <textarea id="hmBlockerDetails" class="form-control" rows="2" placeholder="Describe the blocker details..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Geo-Tagging Coordinates</label>
                                    <div class="input-group">
                                        <input type="text" id="hmGeoCoordinates" class="form-control" placeholder="Coordinates will show here" readonly required>
                                        <button type="button" class="btn btn-outline-secondary" onclick="captureGeoTaggedPhoto()">
                                            <i class="fa-solid fa-location-dot me-1"></i> Capture GPS
                                        </button>
                                    </div>
                                    <input type="hidden" id="hmGeotagInput" value="Missing">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Photo Proof Upload</label>
                                    <div class="upload-zone" onclick="triggerPhotoUpload()">
                                        <i class="fa-solid fa-cloud-arrow-up fs-2 mb-2 text-muted"></i>
                                        <p class="mb-0 text-muted small">Click to upload site photograph proof</p>
                                        <img id="photoPreview" class="upload-preview d-none" src="#" alt="Preview">
                                    </div>
                                    <input type="file" id="hmPhotoFile" class="d-none" accept="image/*" onchange="previewHMUploadedPhoto(this)" required>
                                </div>

                                <div class="mb-3">
                                    <label for="hmRemarks" class="form-label fw-semibold">Progress Remarks / Logs</label>
                                    <textarea id="hmRemarks" class="form-control" rows="3" placeholder="Enter details of work completed..." required></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                    <i class="fa-solid fa-paper-plane me-2"></i>Submit Progress Report
                                </button>
                            </form>

                        </div>

                    </div>

                    <!-- RIGHT SIDE SUMMARY -->
                    <div class="col-lg-5">

                        <div class="card p-4 bg-light">

                            <h5 class="fw-bold mb-3">
                                Active School Summary
                            </h5>

                            <div id="hmSchoolSummaryPanel">
                                <!-- Dynamic Data -->
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- HISTORY VIEW -->
            <div id="hm-history-view" class="view-panel d-none">

                <div class="card p-4">

                    <h4 class="fw-bold mb-1">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>
                        Submission Timeline & History
                    </h4>

                    <p class="text-muted mb-4">
                        View past updates and approval status.
                    </p>

                    <div id="hmTimelineContainer" class="timeline-container">
                        <!-- Dynamic History -->
                    </div>

                </div>

            </div>

        </div>

        <!-- Fixed Footer -->
        <div class="hm-fixed-footer">
            <?php include '../include/website_footer.php'; ?>
        </div>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Database -->
<script src="js/db.js"></script>

<!-- HM Logic -->
<script src="js/hm.js"></script>

</body>
</html>