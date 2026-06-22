<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samruddha Shala E-Portal - Head Master Portal</title>
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
        <nav id="sidebar">
            <div class="sidebar-header d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-0 text-white font-weight-bold"><i class="fa-solid fa-graduation-cap me-2 text-primary"></i>Samruddha Shala</h4>
                    <small class="text-muted text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 1px;">E-Portal System</small>
                </div>
                <button type="button" id="sidebarCollapse" class="btn btn-outline-light btn-sm sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                    <i class="fas fa-align-left"></i>
                </button>
            </div>

            <!-- HM Specific Sidebar Menu -->
            <ul class="list-unstyled components">
                <p>School reporting</p>
                <li class="active" id="nav-hm-report">
                    <a href="javascript:void(0)" onclick="switchTab('hm-report')">
                        <i class="fa-solid fa-cloud-arrow-up"></i>Upload Progress
                    </a>
                </li>
                <li id="nav-hm-history">
                    <a href="javascript:void(0)" onclick="switchTab('hm-history')">
                        <i class="fa-solid fa-clock-rotate-left"></i>Report History
                    </a>
                </li>
            </ul>

            <div class="mt-auto p-4 border-top border-secondary border-opacity-10 text-center text-muted" style="font-size: 0.75rem;">
                <p class="mb-0">Kolhapur District, Maharashtra</p>
                <span style="font-size: 0.7rem;">Version 2.4 (Zeal FIP)</span>
            </div>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Header Top Bar -->
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <div class="ms-3 d-flex align-items-center">
                        <h5 class="mb-0 font-weight-bold" id="pageMainHeader">Head Master Upload Portal</h5>
                    </div>

                    <div class="ms-auto d-flex align-items-center">
                        <!-- Active User Indicator -->
                        <div class="d-flex align-items-center border-start ps-4">
                            <div class="text-end me-3 d-none d-md-block">
                                <p class="mb-0 fw-bold fs-6">Shri. Maruti Kadam</p>
                                <small class="text-muted">ZP School In-charge</small>
                            </div>
                            <span class="role-badge badge-hm">Head Master</span>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Container -->
            <div class="container-fluid p-4">

                <!-- ============================================== -->
                <!-- HM VIEW: UPLOAD PROGRESS UPDATE -->
                <!-- ============================================== -->
                <div id="hm-report-view" class="view-panel">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="card p-4">
                                <h4 class="fw-bold mb-1"><i class="fa-solid fa-cloud-arrow-up me-2 text-primary"></i>Submit Progress Update</h4>
                                <p class="text-muted mb-4">Use this form to submit actual physical construction logs, report blockers, specify geo-tags, and attach photographs</p>

                                <div class="alert alert-info mb-3" id="hmTaskNotificationBox">
                                    <strong>CEO Task Notification</strong>
                                    <div id="hmTaskNotificationText" class="small mt-1">No task assigned yet.</div>
                                </div>

                                <form id="hmProgressUpdateForm" onsubmit="handleHMUpdateSubmit(event)">
                                    <div class="mb-3">
                                        <label for="hmSchoolSelect" class="form-label fw-semibold">Reporting for School</label>
                                        <select id="hmSchoolSelect" class="form-select" onchange="loadHMSchoolSpecificDetails(this.value)">
                                            <!-- Dynamic schools loaded here -->
                                        </select>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Work Type</label>
                                            <input type="text" id="hmWorkType" class="form-control bg-light" value="Classrooms" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Funding Source</label>
                                            <input type="text" id="hmFundingSource" class="form-control bg-light" value="Annual Plan" readonly>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="hmProgressRange" class="form-label fw-semibold d-flex justify-content-between">
                                            <span>Physical Progress (%)</span>
                                            <span id="hmProgressValueText" class="text-primary fw-bold">50%</span>
                                        </label>
                                        <input type="range" class="form-range" min="0" max="100" step="5" id="hmProgressRange" oninput="updateHMProgressSliderText(this.value)">
                                        <div class="d-flex justify-content-between text-muted" style="font-size: 0.8rem;">
                                            <span>0% (Not Started)</span>
                                            <span>50% (Slab/Plinth)</span>
                                            <span>100% (Completed)</span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="hmBlockerSelector" class="form-label fw-semibold">Report Blocker</label>
                                            <select id="hmBlockerSelector" class="form-select" onchange="toggleHMBlockerDetailsInput(this.value)">
                                                <option value="None">None - No active blockers</option>
                                                <option value="Material Shortage">Material Shortage (Cement/Steel/Brick)</option>
                                                <option value="Labour Shortage">Labour Shortage (Contractor delay)</option>
                                                <option value="Weather Delay">Weather Delay (Heavy rain/Floods)</option>
                                                <option value="Fund Delay">Fund Allocation/Disbursement Delay</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="hmGeotagInput" class="form-label fw-semibold">Geo-Tag Status</label>
                                            <select id="hmGeotagInput" class="form-select">
                                                <option value="Tagged">Tagged (Add Coordinates)</option>
                                                <option value="Missing">Missing (Coordinates Unavailable)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="card border-primary mb-3">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="fw-bold mb-1">Funding Distribution</h6>
                                                    <small class="text-muted">Allocate funds from the CEO's approved budget</small>
                                                </div>
                                            </div>
                                            <div class="row mt-3 g-2">
                                                <div class="col-md-6">
                                                    <label for="hmSpentAmount" class="form-label small fw-semibold">Amount Spent (Lakhs)</label>
                                                    <input type="number" id="hmSpentAmount" class="form-control" min="0" step="0.1" placeholder="e.g. 4.0">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="hmBudgetNotes" class="form-label small fw-semibold">Budget Notes</label>
                                                    <input type="text" id="hmBudgetNotes" class="form-control" placeholder="Material / staff / transport">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 d-none" id="hmBlockerDetailsContainer">
                                        <label for="hmBlockerDetails" class="form-label fw-semibold">Provide Blocker Details</label>
                                        <textarea id="hmBlockerDetails" class="form-control" rows="2" placeholder="Explain what is causing the delay in detail..."></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="hmRemarks" class="form-label fw-semibold">HM Remarks / Remarks</label>
                                        <textarea id="hmRemarks" class="form-control" rows="3" placeholder="Describe current physical progress on the ground..." required></textarea>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Attach Photo (Proof of work)</label>
                                        <div class="upload-zone" onclick="triggerPhotoUpload()">
                                            <i class="fa-solid fa-camera fs-1 text-muted mb-2"></i>
                                            <p class="mb-1 fw-semibold">Click to select photo or drag & drop files here</p>
                                            <small class="text-muted">Supports JPEG, PNG. Max size: 2MB.</small>
                                            <input type="file" id="hmPhotoFile" class="d-none" accept="image/*" onchange="previewHMUploadedPhoto(this)" required>
                                            <img id="photoPreview" class="upload-preview d-none" src="" alt="Upload Preview">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <button type="button" class="btn btn-outline-primary" onclick="captureGeoTaggedPhoto()">
                                            <i class="fa-solid fa-location-dot me-2"></i>Capture Geo-Tagged Photo
                                        </button>
                                    </div>

                                    <div class="mb-3">
                                        <label for="hmGeoCoordinates" class="form-label fw-semibold">Captured Coordinates</label>
                                        <input type="text" id="hmGeoCoordinates" class="form-control bg-light" placeholder="Lat, Lng" readonly>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold"><i class="fa-solid fa-paper-plane me-2"></i>Submit Progress Log</button>
                                </form>
                            </div>
                        </div>

                        <!-- Side panel showing school details -->
                        <div class="col-lg-5">
                            <div class="card p-4 bg-light border-0">
                                <h5 class="fw-bold mb-3">Active School Summary</h5>
                                <div id="hmSchoolSummaryPanel">
                                    <!-- Dynamic details based on selector -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- HM VIEW: HISTORY LOGS -->
                <!-- ============================================== -->
                <div id="hm-history-view" class="view-panel d-none">
                    <div class="card p-4">
                        <h4 class="fw-bold mb-1"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Submission Timeline & History</h4>
                        <p class="text-muted mb-4">View past updates and verify approval statuses from the Sachiv and administrative desks</p>

                        <div class="timeline" id="hmTimelineContainer">
                            <!-- Dynamic timeline items logged here -->
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
    <!-- HM Application Logic -->
    <script src="js/hm.js"></script>
</body>
</html>
