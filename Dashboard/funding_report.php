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

// Fetch distinct talukas for dropdown
$talukas = [];
$talukaSql = "SELECT DISTINCT taluka_name FROM talukas_school_data WHERE is_active = 1 ORDER BY taluka_name";
$talukaRes = $conn->query($talukaSql);
if ($talukaRes) {
    while ($row = $talukaRes->fetch_assoc()) {
        $talukas[] = $row['taluka_name'];
    }
}

// Fetch all schools and their talukas for dynamic dropdown filtering
$schoolsList = [];
$schoolSql = "SELECT DISTINCT school_name, taluka_name FROM talukas_school_data WHERE is_active = 1 ORDER BY school_name";
$schoolRes = $conn->query($schoolSql);
if ($schoolRes) {
    while ($row = $schoolRes->fetch_assoc()) {
        $schoolsList[] = $row;
    }
}

// Fetch report rows by aggregating amount_utilization per school project
$reportRows = [];
$reportSql = "SELECT 
                t.id,
                t.taluka_name,
                t.school_name,
                t.work_type,
                t.fund_source,
                t.work_name,
                MAX(COALESCE(a.sanctioned_amount, 0)) AS sanctioned_amount,
                SUM(COALESCE(a.amount_spent, 0)) AS total_spent,
                MAX(COALESCE(a.remarks, a.expense_remark, '')) AS remarks
            FROM talukas_school_data t
            LEFT JOIN amount_utilization a 
                ON a.work_name = t.work_name 
                AND a.work_type = t.work_type
            WHERE t.is_active = 1
            GROUP BY t.id, t.taluka_name, t.school_name, t.work_type, t.fund_source, t.work_name
            ORDER BY t.taluka_name ASC, t.school_name ASC";

$reportRes = $conn->query($reportSql);
if ($reportRes) {
    while ($row = $reportRes->fetch_assoc()) {
        $sanctioned = (float)$row['sanctioned_amount'];
        $spent = (float)$row['total_spent'];
        $balance = $sanctioned - $spent;
        if ($balance < 0) {
            $balance = 0.0;
        }
        $row['sanctioned_amount'] = $sanctioned;
        $row['total_spent'] = $spent;
        $row['balance'] = $balance;
        $reportRows[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funding Report - Samruddha Shala E-Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <link href="css/ceo_dashboard.css?v=3" rel="stylesheet">
    <style>
        body.ceo-dashboard-page { background: #f4f7fb; font-family: 'Outfit', sans-serif; }
        .dashboard-card { border-radius: 24px; border: 1px solid rgba(226, 232, 240, 0.95); box-shadow: 0 18px 36px rgba(61, 84, 117, 0.08); background: #ffffff; }
        .language-switcher .btn { min-width: 100px; }
        .table thead th { background: #f7f3ff; color: #4c2a7a; border-bottom: 0; padding: 15px; }
        .table tbody td { padding: 15px; border-bottom: 1px solid rgba(226, 232, 240, 0.6); vertical-align: middle; }
    </style>
</head>
<body class="ceo-dashboard-page">
    <div id="wrapper">
        <?php include '../include/sidebar.php'; ?>
        <div id="content">
            <div class="ceo-fixed-header">
                <?php include '../include/website_header.php'; ?>
            </div>

            <!-- Top Header Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light p-3 mb-4" style="background: rgba(255,255,255,0.92); border-radius: 22px; box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);">
                <div class="container-fluid px-2">
                    <div class="d-flex align-items-center flex-grow-1 gap-3">
                        <button class="btn btn-light d-lg-none me-2 shadow-sm border-0 d-flex justify-content-center align-items-center" style="width: 42px; height: 42px; background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%); color: white;" type="button" id="mobileSidebarToggle" aria-label="Toggle Sidebar">
                            <i class="fa-solid fa-bars fs-6"></i>
                        </button>
                        <div>
                            <h4 class="fw-bold mb-1 text-truncate" id="pageMainHeader" data-i18n="navTitle">Funding Report</h4>
                            <p class="mb-0 text-muted" style="font-size: 0.95rem;" id="pageMainDescription" data-i18n="navDescription">School-wise sanctioned budget, utilization, and remaining balance report.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap language-switcher">
                        <button id="langMarBtn" class="btn btn-sm btn-primary">मराठी</button>
                        <button id="langEngBtn" class="btn btn-sm btn-outline-primary">English</button>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-0">
                <div class="card dashboard-card p-4 mb-4">
                    <h4 class="fw-bold text-center mb-4 text-primary border-bottom pb-3" data-i18n="reportCardTitle" style="font-family:'Outfit';">निधी अहवाल</h4>
                    
                    <!-- Filters section matches attached layout -->
                    <div class="row g-3 mb-4 align-items-end p-3 rounded-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label fw-bold text-dark small" data-i18n="filterTalukaLabel">तालुका निवडा:</label>
                            <select id="filterTaluka" class="form-select">
                                <option value="ALL" data-i18n="allTalukasOpt">सर्व तालुके</option>
                                <?php foreach ($talukas as $tal): ?>
                                    <option value="<?php echo htmlspecialchars($tal); ?>"><?php echo htmlspecialchars($tal); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <label class="form-label fw-bold text-dark small" data-i18n="filterSchoolLabel">शाळेचे नाव:</label>
                            <select id="filterSchool" class="form-select">
                                <option value="ALL" data-i18n="allSchoolsOpt">सर्व शाळा</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-8">
                            <label class="form-label fw-bold text-dark small" style="visibility:hidden;">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="text" id="filterSearch" class="form-control border-start-0" placeholder="इतर शोधा..." data-i18n-placeholder="filterSearchPlaceholder">
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-4 text-end">
                            <button onclick="exportToExcel()" class="btn btn-success w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-file-excel"></i>
                                <span data-i18n="btnExcel">एक्सेल</span>
                            </button>
                        </div>
                    </div>

                    <!-- Entries page size dropdown -->
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span data-i18n="showEntriesLabel">दाखवा</span>
                        <select id="pageSize" class="form-select form-select-sm d-inline-block" style="width: auto;">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span data-i18n="entriesLabel">नोंदी</span>
                    </div>

                    <!-- Funding Report Table -->
                    <div class="table-responsive">
                        <table id="reportTable" class="table table-hover border rounded-3 overflow-hidden">
                            <thead>
                                <tr>
                                    <th class="fw-bold" data-i18n="thSr">अनु.क्र.</th>
                                    <th class="fw-bold" data-i18n="thTaluka">तालुका</th>
                                    <th class="fw-bold" data-i18n="thSchool">शाळेचे नाव</th>
                                    <th class="fw-bold" data-i18n="thFundType">निधीचा प्रकार</th>
                                    <th class="fw-bold" data-i18n="thFundSource">निधीचा स्रोत</th>
                                    <th class="fw-bold" data-i18n="thWorkName">कामाचे नाव</th>
                                    <th class="fw-bold text-end" data-i18n="thSanctioned">मंजूर रक्कम (₹)</th>
                                    <th class="fw-bold text-end" data-i18n="thSpent">एकूण विनियोग (₹)</th>
                                    <th class="fw-bold text-end" data-i18n="thBalance">शिल्लक (₹)</th>
                                    <th class="fw-bold" data-i18n="thRemarks">शेरा</th>
                                    <th class="fw-bold text-center" data-i18n="thAction">कृती</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <!-- Data will be loaded and filtered dynamically by Javascript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer count & Pagination -->
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mt-3">
                        <div id="entriesInfo" class="text-muted small"></div>
                        <nav>
                            <ul id="pagination" class="pagination pagination-sm mb-0">
                                <!-- Pagination links dynamically created -->
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>

            <div class="ceo-fixed-footer">
                <?php include '../include/website_footer.php'; ?>
            </div>
        </div>
    </div>

    <!-- Details View Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:24px; box-shadow:0 20px 50px rgba(0,0,0,0.15);">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #7f2ab3 0%, #2d064d 100%); border-top-left-radius:24px; border-top-right-radius:24px;">
                    <h5 class="modal-title fw-bold" id="detailsModalLabel" data-i18n="modalTitle">तपशीलवार निधी अहवाल</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-dark">
                    <div class="mb-3 d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-bold" data-i18n="modalSchool">शाळा:</span>
                        <span id="modalSchoolName" class="fw-semibold"></span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-bold" data-i18n="modalTaluka">तालुका:</span>
                        <span id="modalTalukaName" class="fw-semibold"></span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-bold" data-i18n="modalWork">काम:</span>
                        <span id="modalWorkName" class="fw-semibold"></span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-bold" data-i18n="thSanctioned">मंजूर रक्कम (₹):</span>
                        <span id="modalSanctioned" class="fw-bold text-primary"></span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-bold" data-i18n="modalSpent">खर्च:</span>
                        <span id="modalSpentAmt" class="fw-bold text-success"></span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted fw-bold" data-i18n="modalBalance">शिल्लक:</span>
                        <span id="modalBalanceAmt" class="fw-bold text-warning"></span>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted fw-bold d-block mb-1" data-i18n="thRemarks">शेरा:</span>
                        <div id="modalRemarksText" class="p-3 bg-light rounded-3 text-muted small" style="min-height: 60px;"></div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" data-i18n="modalClose">बंद करा</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const langStrings = {
            navTitle: { mar: 'निधी अहवाल', eng: 'Funding Report' },
            navDescription: { mar: 'शाळानिहाय मंजूर निधी, विनियोग आणि शिल्लक रकमेचा अहवाल.', eng: 'School-wise sanctioned budget, utilization, and remaining balance report.' },
            reportCardTitle: { mar: 'निधी अहवाल', eng: 'Funding Report' },
            filterTalukaLabel: { mar: 'तालुका निवडा:', eng: 'Select Taluka:' },
            filterSchoolLabel: { mar: 'शाळेचे नाव:', eng: 'Select School Name:' },
            filterSearchPlaceholder: { mar: 'इतर शोधा...', eng: 'Search...' },
            btnExcel: { mar: 'एक्सेल', eng: 'Excel' },
            showEntriesLabel: { mar: 'दाखवा', eng: 'Show' },
            entriesLabel: { mar: 'नोंदी', eng: 'entries' },
            thSr: { mar: 'अनु.क्र.', eng: 'Sr. No.' },
            thTaluka: { mar: 'तालुका', eng: 'Taluka' },
            thSchool: { mar: 'शाळेचे नाव', eng: 'School Name' },
            thFundType: { mar: 'निधीचा प्रकार', eng: 'Fund Type' },
            thFundSource: { mar: 'निधीचा स्रोत', eng: 'Funding Source' },
            thWorkName: { mar: 'कामाचे नाव', eng: 'Work Name' },
            thSanctioned: { mar: 'मंजूर रक्कम (₹)', eng: 'Sanctioned (₹)' },
            thSpent: { mar: 'एकूण विनियोग (₹)', eng: 'Total Spent (₹)' },
            thBalance: { mar: 'शिल्लक (₹)', eng: 'Balance (₹)' },
            thRemarks: { mar: 'शेरा', eng: 'Remarks' },
            thAction: { mar: 'कृती', eng: 'Action' },
            btnView: { mar: 'पहा', eng: 'View' },
            btnPrev: { mar: 'मागील', eng: 'Previous' },
            btnNext: { mar: 'पुढील', eng: 'Next' },
            allTalukasOpt: { mar: 'सर्व तालुके', eng: 'All Talukas' },
            allSchoolsOpt: { mar: 'सर्व शाळा', eng: 'All Schools' },
            selectTalukaFirstOpt: { mar: 'आधी तालुका निवडा', eng: 'Select Taluka First' },
            modalTitle: { mar: 'तपशीलवार निधी अहवाल', eng: 'Detailed Funding Statement' },
            modalSchool: { mar: 'शाळा:', eng: 'School:' },
            modalTaluka: { mar: 'तालुका:', eng: 'Taluka:' },
            modalWork: { mar: 'काम:', eng: 'Work:' },
            modalSpent: { mar: 'खर्च:', eng: 'Spent:' },
            modalBalance: { mar: 'शिल्लक:', eng: 'Balance:' },
            modalClose: { mar: 'बंद करा', eng: 'Close' },
            sideDashboard: { mar: 'CEO डॅशबोर्ड', eng: 'CEO Dashboard' },
            sideCreateStages: { mar: 'टप्पे तयार करा', eng: 'Create Stages' },
            sideStagesReport: { mar: 'टप्प्यांचा अहवाल', eng: 'Stages Report' },
            sideWorkReport: { mar: 'कामाचा अहवाल', eng: 'Work Report' },
            sideFundingReport: { mar: 'निधी अहवाल', eng: 'Funding Report' },
            sideCreateUser: { mar: 'युझर तयार करा', eng: 'Create User' },
            sideFundUtil: { mar: 'निधी वापर तपशील', eng: 'Fund Utilization Details' }
        };

        const schoolsDb = <?php echo json_encode($schoolsList); ?>;
        const reportData = <?php echo json_encode($reportRows); ?>;

        let filteredData = [...reportData];
        let currentPage = 1;
        let pageSize = 25;

        // Populate schools dropdown based on taluka selection
        function updateSchoolsDropdown(taluka) {
            const schoolSelect = document.getElementById('filterSchool');
            schoolSelect.innerHTML = '';

            const currentLang = localStorage.getItem('ceoLang') || 'mar';

            if (taluka === 'ALL') {
                const opt = document.createElement('option');
                opt.value = 'ALL';
                opt.textContent = langStrings.selectTalukaFirstOpt[currentLang];
                schoolSelect.appendChild(opt);
                return;
            }

            const allOpt = document.createElement('option');
            allOpt.value = 'ALL';
            allOpt.textContent = langStrings.allSchoolsOpt[currentLang];
            schoolSelect.appendChild(allOpt);

            const filteredSchools = schoolsDb.filter(s => s.taluka_name === taluka);
            filteredSchools.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.school_name;
                opt.textContent = s.school_name;
                schoolSelect.appendChild(opt);
            });
        }

        // Apply filters
        function applyFilters() {
            const taluka = document.getElementById('filterTaluka').value;
            const school = document.getElementById('filterSchool').value;
            const query = document.getElementById('filterSearch').value.toLowerCase().trim();

            filteredData = reportData.filter(row => {
                const matchesTaluka = (taluka === 'ALL' || row.taluka_name === taluka);
                const matchesSchool = (school === 'ALL' || row.school_name === school);
                
                const matchesQuery = !query || 
                    (row.taluka_name && row.taluka_name.toLowerCase().includes(query)) ||
                    (row.school_name && row.school_name.toLowerCase().includes(query)) ||
                    (row.work_name && row.work_name.toLowerCase().includes(query)) ||
                    (row.work_type && row.work_type.toLowerCase().includes(query)) ||
                    (row.fund_source && row.fund_source.toLowerCase().includes(query)) ||
                    (row.remarks && row.remarks.toLowerCase().includes(query));

                return matchesTaluka && matchesSchool && matchesQuery;
            });

            currentPage = 1;
            renderTable();
        }

        // Render table page
        function renderTable() {
            const start = (currentPage - 1) * pageSize;
            const end = Math.min(start + pageSize, filteredData.length);
            const pageData = filteredData.slice(start, end);

            const tableBody = document.getElementById('tableBody');
            tableBody.innerHTML = '';

            const currentLang = localStorage.getItem('ceoLang') || 'mar';
            const btnText = langStrings.btnView[currentLang];

            if (pageData.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="11" class="text-center text-muted py-4">माहिती उपलब्ध नाही (No records found)</td></tr>`;
                document.getElementById('entriesInfo').textContent = '';
                document.getElementById('pagination').innerHTML = '';
                return;
            }

            pageData.forEach((row, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${start + index + 1}</td>
                    <td><strong class="text-dark">${escapeHtml(row.taluka_name)}</strong></td>
                    <td>${escapeHtml(row.school_name)}</td>
                    <td><span class="badge ${row.work_type === 'CIVILIAN' ? 'bg-info-soft text-info' : 'bg-primary-soft text-primary'} px-2 py-1">${escapeHtml(row.work_type)}</span></td>
                    <td><span class="text-muted small fw-semibold">${escapeHtml(row.fund_source)}</span></td>
                    <td>${escapeHtml(row.work_name)}</td>
                    <td class="text-end font-monospace fw-semibold">₹${Number(row.sanctioned_amount).toLocaleString('en-IN')}</td>
                    <td class="text-end font-monospace fw-semibold text-success">₹${Number(row.total_spent).toLocaleString('en-IN')}</td>
                    <td class="text-end font-monospace fw-semibold text-warning">₹${Number(row.balance).toLocaleString('en-IN')}</td>
                    <td class="text-truncate" style="max-width: 150px;" title="${escapeHtml(row.remarks)}">${escapeHtml(row.remarks) || '-'}</td>
                    <td class="text-center">
                        <button onclick="showDetails(${row.id})" class="btn btn-sm btn-outline-primary px-3 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                            <i class="fa-solid fa-eye small"></i>
                            <span>${btnText}</span>
                        </button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });

            // Update entries info
            let infoText = "";
            if (currentLang === 'mar') {
                infoText = `नोंदी ${start + 1} ते ${end} (एकूण ${filteredData.length} पैकी)`;
            } else {
                infoText = `Showing ${start + 1} to ${end} of ${filteredData.length} entries`;
            }
            document.getElementById('entriesInfo').textContent = infoText;

            renderPagination();
        }

        // Render pagination links
        function renderPagination() {
            const pageCount = Math.ceil(filteredData.length / pageSize);
            const container = document.getElementById('pagination');
            container.innerHTML = '';

            const currentLang = localStorage.getItem('ceoLang') || 'mar';

            // Previous button
            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
            prevLi.innerHTML = `<button class="page-link" onclick="changePage(${currentPage - 1})">${langStrings.btnPrev[currentLang]}</button>`;
            container.appendChild(prevLi);

            // Page numbers
            for (let i = 1; i <= pageCount; i++) {
                if (i === 1 || i === pageCount || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    const li = document.createElement('li');
                    li.className = `page-item ${currentPage === i ? 'active' : ''}`;
                    li.innerHTML = `<button class="page-link" onclick="changePage(${i})">${i}</button>`;
                    container.appendChild(li);
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    const li = document.createElement('li');
                    li.className = 'page-item disabled';
                    li.innerHTML = '<span class="page-link">...</span>';
                    container.appendChild(li);
                }
            }

            // Next button
            const nextLi = document.createElement('li');
            nextLi.className = `page-item ${currentPage === pageCount ? 'disabled' : ''}`;
            nextLi.innerHTML = `<button class="page-link" onclick="changePage(${currentPage + 1})">${langStrings.btnNext[currentLang]}</button>`;
            container.appendChild(nextLi);
        }

        function changePage(page) {
            currentPage = page;
            renderTable();
        }

        // Modal statement popup
        function showDetails(id) {
            const row = reportData.find(r => r.id === id);
            if (!row) return;

            document.getElementById('modalSchoolName').textContent = row.school_name;
            document.getElementById('modalTalukaName').textContent = row.taluka_name;
            document.getElementById('modalWorkName').textContent = row.work_name;
            document.getElementById('modalSanctioned').textContent = '₹' + Number(row.sanctioned_amount).toLocaleString('en-IN');
            document.getElementById('modalSpentAmt').textContent = '₹' + Number(row.total_spent).toLocaleString('en-IN');
            document.getElementById('modalBalanceAmt').textContent = '₹' + Number(row.balance).toLocaleString('en-IN');
            document.getElementById('modalRemarksText').textContent = row.remarks || 'No remarks added.';

            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
        }

        // Export data to XLS sheet
        function exportToExcel() {
            const table = document.getElementById("reportTable");
            
            // Build a clean minimal HTML table for excel download
            let excelHtml = '<table border="1"><thead><tr>';
            excelHtml += `<th>Sr. No.</th><th>Taluka</th><th>School Name</th><th>Fund Type</th><th>Funding Source</th><th>Work Name</th><th>Sanctioned Amount</th><th>Total Spent</th><th>Balance</th><th>Remarks</th>`;
            excelHtml += '</tr></thead><tbody>';

            filteredData.forEach((row, i) => {
                excelHtml += `<tr>
                    <td>${i + 1}</td>
                    <td>${row.taluka_name}</td>
                    <td>${row.school_name}</td>
                    <td>${row.work_type}</td>
                    <td>${row.fund_source}</td>
                    <td>${row.work_name}</td>
                    <td>${row.sanctioned_amount}</td>
                    <td>${row.total_spent}</td>
                    <td>${row.balance}</td>
                    <td>${row.remarks}</td>
                </tr>`;
            });
            excelHtml += '</tbody></table>';

            const blob = new Blob(['\ufeff' + excelHtml], {
                type: 'application/vnd.ms-excel;charset=utf-8'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Samruddha_Shala_Funding_Report.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        function setLanguagePage(lang) {
            // Apply simple translations
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (langStrings[key] && langStrings[key][lang]) {
                    el.textContent = langStrings[key][lang];
                }
            });

            // Translate placeholders
            document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                const key = el.getAttribute('data-i18n-placeholder');
                if (langStrings[key] && langStrings[key][lang]) {
                    el.setAttribute('placeholder', langStrings[key][lang]);
                }
            });

            // Update dropdown defaults text
            const selectedTaluka = document.getElementById('filterTaluka').value;
            updateSchoolsDropdown(selectedTaluka);

            // Re-render table and pagination
            renderTable();

            document.getElementById('langMarBtn').classList.toggle('btn-primary', lang === 'mar');
            document.getElementById('langMarBtn').classList.toggle('btn-outline-primary', lang !== 'mar');
            document.getElementById('langEngBtn').classList.toggle('btn-primary', lang === 'eng');
            document.getElementById('langEngBtn').classList.toggle('btn-outline-primary', lang !== 'eng');
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"'`]/g, m => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;', '`': '&#96;'
            }[m]));
        }

        document.addEventListener('DOMContentLoaded', () => {
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const sidebar = document.getElementById('sidebar');

            if (mobileSidebarToggle && sidebar) {
                mobileSidebarToggle.addEventListener('click', e => {
                    e.stopPropagation();
                    sidebar.classList.toggle('active');
                });
                document.addEventListener('click', e => {
                    if (window.innerWidth <= 991 && sidebar.classList.contains('active')) {
                        if (!sidebar.contains(e.target) && e.target !== mobileSidebarToggle) {
                            sidebar.classList.remove('active');
                        }
                    }
                });
            }

            // Filters listeners
            document.getElementById('filterTaluka').addEventListener('change', (e) => {
                updateSchoolsDropdown(e.target.value);
                applyFilters();
            });
            document.getElementById('filterSchool').addEventListener('change', applyFilters);
            document.getElementById('filterSearch').addEventListener('input', applyFilters);

            document.getElementById('pageSize').addEventListener('change', (e) => {
                pageSize = parseInt(e.target.value);
                currentPage = 1;
                renderTable();
            });

            document.getElementById('langMarBtn').addEventListener('click', () => {
                localStorage.setItem('ceoLang', 'mar');
                setLanguagePage('mar');
            });
            document.getElementById('langEngBtn').addEventListener('click', () => {
                localStorage.setItem('ceoLang', 'eng');
                setLanguagePage('eng');
            });

            // Init language
            const urlParams = new URLSearchParams(window.location.search);
            const saved = urlParams.get('lang') || localStorage.getItem('ceoLang') || 'mar';
            
            updateSchoolsDropdown('ALL');
            setLanguagePage(saved);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
