<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samruddha Shala E-Portal - Create Work</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div id="wrapper">
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
            <ul class="list-unstyled components">
                <p>CEO Modules</p>
                <li id="nav-ceo-overview">
                    <a href="ceo_dashboard.php">
                        <i class="fa-solid fa-chart-pie"></i>Overview Report
                    </a>
                </li>
                <li id="nav-ceo-alerts">
                    <a href="ceo_alerts.php">
                        <i class="fa-solid fa-bell"></i>View Alerts & Notifications
                        <span id="alertsSidebarBadge" class="badge bg-danger ms-auto rounded-pill d-none">0</span>
                    </a>
                </li>
                <li class="active" id="nav-ceo-create-work">
                    <a href="ceo_create_work.php">
                        <i class="fa-solid fa-plus-circle"></i>Create Work
                    </a>
                </li>
                <li id="nav-ceo-task">
                    <a href="ceo_assign_task.php">
                        <i class="fa-solid fa-file-signature"></i>Assign Work to HM
                    </a>
                </li>
            </ul>
        </nav>

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <div class="ms-3 d-flex align-items-center">
                        <h5 class="mb-0 font-weight-bold" id="pageMainHeader">Create Work</h5>
                    </div>
                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown me-4 position-relative">
                            <button class="btn btn-link text-dark p-1 text-decoration-none" id="notifBellButton" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-regular fa-bell fs-5"></i>
                                <span id="alertsHeaderBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" id="notifBellDropdown" style="width: 320px; border-radius: 12px;">
                                <li class="dropdown-header font-weight-bold d-flex justify-content-between align-items-center border-bottom pb-2">
                                    <span>Notifications Center</span>
                                    <span class="badge bg-danger rounded-pill" id="notifBellCountText">0 Alerts</span>
                                </li>
                                <div id="notifBellList" class="my-2" style="max-height: 250px; overflow-y: auto;"></div>
                                <li class="text-center pt-2 border-top">
                                    <a class="text-decoration-none text-primary fw-bold" href="ceo_alerts.php" style="font-size: 0.8rem;">View All Critical Alerts</a>
                                </li>
                            </ul>
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
                                <h4 class="fw-bold mb-1"><i class="fa-solid fa-plus-circle me-2 text-primary"></i>Create Work</h4>
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
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="addStageBtn">
                                                <i class="fa-solid fa-plus me-1"></i> Add Stage
                                            </button>
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

            <footer class="border-top bg-white mt-4">
                <div class="container-fluid px-4 py-3 d-flex flex-column flex-md-row justify-content-between align-items-center text-muted small">
                    <span>© 2026 Samruddha Shala E-Portal</span>
                    <span>Kolhapur District CEO Dashboard</span>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/db.js"></script>
    <script src="js/ceo.js?v=4"></script>
    <script src="js/create_work.js"></script>
</body>
</html>

