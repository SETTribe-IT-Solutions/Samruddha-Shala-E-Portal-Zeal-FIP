<?php
session_start();

if(empty($_SESSION['user_id']) || empty($_SESSION['username'])){
    header("Location: ../login.php");
    exit();
}

if($_SESSION['role'] != 'HM'){
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samruddha Shala E-Portal - Update Work Progress</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="../css/sidebar.css" rel="stylesheet">
    <link href="css/hm_dashboard.css?v=1.0.1" rel="stylesheet">
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
                    <a href="hm_dashboard.php" class="btn btn-sm btn-outline-secondary fw-semibold">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </nav>

        <!-- Form and List Content -->
        <div class="container-fluid p-0">

            <div class="card p-4 mb-4">
                <h3 class="fw-bold mb-1 text-dark">
                    <i class="fa-solid fa-file-pen me-2 text-primary"></i>
                    Submit Progress Report
                </h3>
                <p class="text-muted mb-0">
                    Select a school, update physical construction details, record expenses, capture geo-tagged proof, and submit for verification.
                </p>
            </div>

            <div class="row g-4">
                <!-- LEFT COLUMN: SCHOOLS LIST -->
                <div class="col-lg-5">
                    <div class="card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="fa-solid fa-list-check text-primary me-2"></i>Assigned Works
                            </h5>
                            <span class="badge bg-primary rounded-pill" id="worksCountBadge">0 Schools</span>
                        </div>
                        
                        <!-- Search Bar -->
                        <div class="search-wrapper mb-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="hmSchoolSearch" class="form-control" placeholder="Search by school or block name..." onkeyup="filterHMSchools()">
                        </div>

                        <!-- Filter Pills -->
                        <div class="filter-pills-container mb-3 scroll-panel">
                            <span class="filter-pill active" onclick="setSchoolFilter('all', this)">All</span>
                            <span class="filter-pill" onclick="setSchoolFilter('active', this)">Active</span>
                            <span class="filter-pill" onclick="setSchoolFilter('completed', this)">Completed</span>
                            <span class="filter-pill" onclick="setSchoolFilter('blocked', this)">Blocked</span>
                        </div>

                        <!-- Scrollable Card list -->
                        <div id="hmSchoolList" class="scroll-panel" style="max-height: 480px; overflow-y: auto; padding-right: 2px;">
                            <!-- Dynamic Card listing populated by JS -->
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: UPDATE FORM -->
                <div class="col-lg-7">
                    <div class="card p-4">
                        <h5 class="fw-bold mb-1 text-dark">
                            <i class="fa-solid fa-square-plus text-primary me-2"></i>Update Progress Log
                        </h5>
                        <p class="text-muted small mb-3">
                            Submit actual physical construction stages, release details, blockers, and geo-tag photos.
                        </p>

                        <!-- Hidden select element to bridge with script logic -->
                        <select id="hmSchoolSelect" class="form-select d-none" onchange="loadHMSchoolSpecificDetails(this.value)"></select>

                        <!-- Active Indicator banner -->
                        <div class="alert alert-info-premium mb-3 d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-uppercase text-muted fw-bold d-block" style="font-size: 0.65rem; letter-spacing: 0.5px;">Active Selection</small>
                                <strong id="hmSelectedSchoolName" class="text-primary" style="font-size: 0.95rem;">No School Selected</strong>
                            </div>
                            <span class="badge bg-secondary" id="hmSelectedBlockBadge">- Block</span>
                        </div>

                        <!-- CEO Task Directive Banner if any -->
                        <div id="hmTaskAlertBanner" class="alert alert-warning py-2 px-3 mb-3 d-none" style="border-radius: 12px; border-left: 4px solid #f59e0b;">
                            <div class="d-flex align-items-start">
                                <i class="fa-solid fa-circle-info mt-1 me-2 text-warning"></i>
                                <div class="small">
                                    <strong>Task Directive:</strong> <span id="hmTaskAlertText">None</span>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form id="hmUpdateForm" onsubmit="handleHMUpdateSubmit(event)">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="hmWorkType" class="form-label fw-semibold small text-muted mb-1">Work Category</label>
                                    <input type="text" id="hmWorkType" class="form-control" readonly style="background-color: #f1f5f9;">
                                </div>
                                <div class="col-md-6">
                                    <label for="hmFundingSource" class="form-label fw-semibold small text-muted mb-1">Funding Source</label>
                                    <input type="text" id="hmFundingSource" class="form-control" readonly style="background-color: #f1f5f9;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="hmProgressRange" class="form-label fw-semibold small text-muted mb-0">Current Progress Stage</label>
                                    <span id="hmProgressValueText" class="fw-bold text-primary">0%</span>
                                </div>
                                <input type="range" id="hmProgressRange" class="form-range" min="0" max="100" value="0" oninput="updateHMProgressSliderText(this.value)">
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="hmSpentAmount" class="form-label fw-semibold small text-muted mb-1">Amount Spent (Lakhs)</label>
                                    <input type="number" id="hmSpentAmount" class="form-control" placeholder="e.g. 1.50" min="0" step="0.01">
                                </div>
                                <div class="col-md-6">
                                    <label for="hmBudgetNotes" class="form-label fw-semibold small text-muted mb-1">Financial Notes</label>
                                    <input type="text" id="hmBudgetNotes" class="form-control" placeholder="e.g. Materials purchased">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="hmBlockerSelector" class="form-label fw-semibold small text-muted mb-1">Report Blocker Status</label>
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
                                <label for="hmBlockerDetails" class="form-label fw-semibold small text-muted mb-1">Blocker Description</label>
                                <textarea id="hmBlockerDetails" class="form-control" rows="2" placeholder="Describe the blocker details..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-muted mb-1">Geo-Tagging Coordinates</label>
                                <div class="input-group">
                                    <input type="text" id="hmGeoCoordinates" class="form-control" placeholder="GPS coordinates" readonly required>
                                    <button type="button" class="btn btn-outline-secondary small fw-bold" onclick="captureGeoTaggedPhoto()">
                                        <i class="fa-solid fa-location-dot me-1"></i> Capture GPS
                                    </button>
                                </div>
                                <input type="hidden" id="hmGeotagInput" value="Missing">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-muted mb-1">Photo Proof Upload</label>
                                <div class="upload-zone-premium" onclick="triggerPhotoUpload()">
                                    <i class="fa-solid fa-cloud-arrow-up fs-3 mb-2 text-primary" id="uploadZoneIcon" style="opacity: 0.85;"></i>
                                    <p class="mb-0 text-muted small fw-semibold" id="uploadZoneText">Click to select site photo proof</p>
                                    <img id="photoPreview" class="upload-preview d-none" src="#" alt="Preview">
                                </div>
                                <input type="file" id="hmPhotoFile" class="d-none" accept="image/*" onchange="previewHMUploadedPhoto(this)" required>
                            </div>

                            <div class="mb-3">
                                <label for="hmRemarks" class="form-label fw-semibold small text-muted mb-1">Progress Remarks</label>
                                <textarea id="hmRemarks" class="form-control" rows="2" placeholder="Enter what physical work has been completed..." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold mt-2">
                                <i class="fa-solid fa-paper-plane me-2"></i>Submit Progress Report
                            </button>
                        </form>
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

<!-- Database scripts -->
<script src="js/db.js"></script>

<!-- Dashboard dynamic controller -->
<script src="js/hm_update.js"></script>

</body>
</html>