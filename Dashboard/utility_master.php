<?php
session_start();

if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'CEO') {
    header("Location: ceo_dashboard.php");
    exit();
}

require_once '../include/dbConfig.php';

$reportRows = [];
$sqlReport = "SELECT 
                a.work_name,
                a.work_type,
                a.fund_source,
                MAX(a.sanctioned_amount) AS sanctioned_amount,
                SUM(a.amount_spent) AS amount_spent,
                MAX(a.created_at) AS last_updated,
                MAX(t.school_name) AS school_name,
                MAX(t.taluka_name) AS taluka_name
            FROM amount_utilization a
            LEFT JOIN talukas_school_data t
                ON a.work_name = t.work_name
                AND a.work_type = t.work_type
            GROUP BY a.work_name, a.work_type, a.fund_source
            ORDER BY last_updated DESC";

$resReport = $conn->query($sqlReport);
if ($resReport) {
    while ($row = $resReport->fetch_assoc()) {
        $reportRows[] = $row;
    }
}

$totalProjects = count($reportRows);
$totalSanctioned = 0.0;
$totalSpent = 0.0;
$averageUtilization = 0.0;

foreach ($reportRows as $row) {
    $totalSanctioned += (float)$row['sanctioned_amount'];
    $totalSpent += (float)$row['amount_spent'];
}

if ($totalProjects > 0) {
    $averageUtilization = $totalSanctioned > 0 ? round(($totalSpent / $totalSanctioned) * 100, 2) : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utility Master - CEO</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/ceo_dashboard.css?v=<?php echo time(); ?>">
    <style>
        body.ceo-dashboard-page {
            font-family: 'Outfit', sans-serif;
            background: #f6f7fb;
        }
        .utility-card {
            border-radius: 24px;
            background: rgba(255,255,255,0.98);
            border: 1px solid rgba(228, 232, 239, 0.95);
            box-shadow: 0 18px 36px rgba(54, 78, 105, 0.05);
        }
        .utility-table th {
            background: #f7f3ff;
            color: #3f2b67;
            font-weight: 700;
            border-bottom: 2px solid #e6e6f2;
        }
        .utility-table td {
            vertical-align: middle;
            font-size: 0.9rem;
            color: #3d4151;
        }
        .filter-label {
            font-size: 0.82rem;
            font-weight: 700;
            color: #5f6b7a;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.45rem 0.65rem;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-on-track { background: #d1fae5; color: #166534; }
        .status-warning { background: #fef3c7; color: #92400e; }
        .status-over { background: #fee2e2; color: #9b1c1c; }
        .table-responsive { overflow-x: auto; }
        .sticky-header thead th { position: sticky; top: 0; z-index: 2; }
        @media (max-width: 991px) {
            .utility-card { border-radius: 18px; }
        }
    </style>
</head>
<body class="ceo-dashboard-page">
    <div id="wrapper">
        <?php include '../include/sidebar.php'; ?>
        <div id="content">
            <div class="ceo-fixed-header">
                <?php include '../include/website_header.php'; ?>
            </div>
            <div class="container-fluid p-4">
                <div class="mb-4">
                    <h3 class="fw-bold mb-1"><i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>Utility Master</h3>
                    <p class="text-muted mb-0">Monitor HM-submitted amount utilization across projects with live status, school, taluka, and balance data.</p>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="utility-card p-3 h-100">
                            <p class="mb-2 text-muted text-uppercase" style="font-size:0.72rem; letter-spacing:0.16em;">Total Projects</p>
                            <h2 class="fw-bold mb-0"><?php echo number_format($totalProjects); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="utility-card p-3 h-100">
                            <p class="mb-2 text-muted text-uppercase" style="font-size:0.72rem; letter-spacing:0.16em;">Total Sanctioned</p>
                            <h2 class="fw-bold text-primary mb-0">₹<?php echo number_format($totalSanctioned, 2); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="utility-card p-3 h-100">
                            <p class="mb-2 text-muted text-uppercase" style="font-size:0.72rem; letter-spacing:0.16em;">Total Utilized</p>
                            <h2 class="fw-bold text-warning mb-0">₹<?php echo number_format($totalSpent, 2); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="utility-card p-3 h-100">
                            <p class="mb-2 text-muted text-uppercase" style="font-size:0.72rem; letter-spacing:0.16em;">Avg Utilization</p>
                            <h2 class="fw-bold text-success mb-0"><?php echo number_format($averageUtilization, 2); ?>%</h2>
                        </div>
                    </div>
                </div>

                <div class="utility-card p-4 mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="filter-label" for="filterWorkName">Work Name</label>
                            <input type="text" id="filterWorkName" class="form-control form-control-sm" placeholder="Search Work Name">
                        </div>
                        <div class="col-md-3">
                            <label class="filter-label" for="filterSchoolName">School Name</label>
                            <input type="text" id="filterSchoolName" class="form-control form-control-sm" placeholder="Search School Name">
                        </div>
                        <div class="col-md-3">
                            <label class="filter-label" for="filterTaluka">Taluka</label>
                            <input type="text" id="filterTaluka" class="form-control form-control-sm" placeholder="Search Taluka">
                        </div>
                        <div class="col-md-2">
                            <label class="filter-label" for="filterStatus">Status</label>
                            <select id="filterStatus" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <option value="on track">On Track</option>
                                <option value="close to limit">Close to Limit</option>
                                <option value="over utilized">Over Utilized</option>
                            </select>
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" id="clearFilters" class="btn btn-sm btn-outline-secondary w-100">Reset</button>
                        </div>
                    </div>
                </div>

                <div class="utility-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Amount Utilization Report</h5>
                            <p class="text-muted mb-0">Live HM-submitted utilization progress for every work across the district.</p>
                        </div>
                        <span class="badge bg-secondary py-2 px-3">Records: <?php echo number_format($totalProjects); ?></span>
                    </div>

                    <div class="table-responsive" style="max-height: 68vh; overflow-y: auto;">
                        <table class="table table-hover utility-table mb-0 sticky-header">
                            <thead>
                                <tr>
                                    <th>Work Name</th>
                                    <th>School Name</th>
                                    <th>Taluka</th>
                                    <th>Funding Source</th>
                                    <th class="text-end">Sanctioned</th>
                                    <th class="text-end">Utilized</th>
                                    <th class="text-end">Remaining</th>
                                    <th class="text-center">Utilization %</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Last Updated</th>
                                </tr>
                            </thead>
                            <tbody id="utilityReportBody">
                                <?php if (empty($reportRows)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">No utilization records available.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reportRows as $row): ?>
                                        <?php
                                            $sanctioned = (float)$row['sanctioned_amount'];
                                            $spent = (float)$row['amount_spent'];
                                            $remaining = $sanctioned - $spent;
                                            if ($remaining < 0) { $remaining = 0; }
                                            $percentage = $sanctioned > 0 ? round(($spent / $sanctioned) * 100, 2) : 0;
                                            if ($percentage > 100) {
                                                $status = 'Over Utilized';
                                                $statusClass = 'status-over';
                                            } elseif ($percentage >= 75) {
                                                $status = 'Close to Limit';
                                                $statusClass = 'status-warning';
                                            } else {
                                                $status = 'On Track';
                                                $statusClass = 'status-on-track';
                                            }
                                            $schoolName = trim($row['school_name'] ?: 'N/A');
                                            $talukaName = trim($row['taluka_name'] ?: 'N/A');
                                        ?>
                                        <tr data-work-name="<?php echo htmlspecialchars(strtolower($row['work_name'])); ?>" data-school-name="<?php echo htmlspecialchars(strtolower($schoolName)); ?>" data-taluka="<?php echo htmlspecialchars(strtolower($talukaName)); ?>" data-status="<?php echo htmlspecialchars(strtolower($status)); ?>">
                                            <td><?php echo htmlspecialchars($row['work_name']); ?></td>
                                            <td><?php echo htmlspecialchars($schoolName); ?></td>
                                            <td><?php echo htmlspecialchars($talukaName); ?></td>
                                            <td><?php echo htmlspecialchars($row['fund_source']); ?></td>
                                            <td class="text-end fw-semibold text-primary">₹<?php echo number_format($sanctioned, 2); ?></td>
                                            <td class="text-end fw-semibold text-warning">₹<?php echo number_format($spent, 2); ?></td>
                                            <td class="text-end fw-semibold text-success">₹<?php echo number_format($remaining, 2); ?></td>
                                            <td class="text-center"><?php echo number_format($percentage, 2); ?>%</td>
                                            <td class="text-center"><span class="status-badge <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                                            <td class="text-center"><?php echo htmlspecialchars(date('d-M-Y', strtotime($row['last_updated']))); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="ceo-fixed-footer">
                <?php include '../include/website_footer.php'; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const filterWorkName = document.getElementById('filterWorkName');
        const filterSchoolName = document.getElementById('filterSchoolName');
        const filterTaluka = document.getElementById('filterTaluka');
        const filterStatus = document.getElementById('filterStatus');
        const clearFilters = document.getElementById('clearFilters');
        const tableRows = document.querySelectorAll('#utilityReportBody tr');

        function normalize(value) {
            return (value || '').toString().trim().toLowerCase();
        }

        function applyFilters() {
            const workValue = normalize(filterWorkName.value);
            const schoolValue = normalize(filterSchoolName.value);
            const talukaValue = normalize(filterTaluka.value);
            const statusValue = normalize(filterStatus.value);

            tableRows.forEach(row => {
                const rowWork = normalize(row.dataset.workName);
                const rowSchool = normalize(row.dataset.schoolName);
                const rowTaluka = normalize(row.dataset.taluka);
                const rowStatus = normalize(row.dataset.status);

                const matchesWork = !workValue || rowWork.includes(workValue);
                const matchesSchool = !schoolValue || rowSchool.includes(schoolValue);
                const matchesTaluka = !talukaValue || rowTaluka.includes(talukaValue);
                const matchesStatus = !statusValue || rowStatus === statusValue;

                row.style.display = matchesWork && matchesSchool && matchesTaluka && matchesStatus ? '' : 'none';
            });
        }

        [filterWorkName, filterSchoolName, filterTaluka, filterStatus].forEach(input => {
            input.addEventListener('input', applyFilters);
        });

        clearFilters.addEventListener('click', function() {
            filterWorkName.value = '';
            filterSchoolName.value = '';
            filterTaluka.value = '';
            filterStatus.value = '';
            applyFilters();
        });
    </script>
</body>
</html>
