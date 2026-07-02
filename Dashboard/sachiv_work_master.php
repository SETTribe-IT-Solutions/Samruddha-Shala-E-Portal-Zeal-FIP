<?php
session_start();

if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['username']) ||
    ($_SESSION['role'] != 'SACHIV' && $_SESSION['role'] != 'CEO')
){
    header("Location: ../login.php");
    exit();
}

// Database connection
$host = "82.25.121.144";
$db_user = "u196817721_S_Eportal_U";
$db_pass = "Sam_shalaEportal@2026";
$db_name = "u196817721_S_shalaEportal";
$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

$works = [];
$talukas = [];
$taluka_schools = [];

if ($conn) {
    $query = "SELECT * FROM talukas_school_data ORDER BY id ASC";
    $res = mysqli_query($conn, $query);
    if ($res) {
        $index = 0;
        while ($row = mysqli_fetch_assoc($res)) {
            $tName = $row['taluka_name'];
            $sName = $row['school_name'];
            
            if (!empty($tName) && !in_array($tName, $talukas)) {
                $talukas[] = $tName;
            }
            if (!empty($tName) && !empty($sName)) {
                if (!isset($taluka_schools[$tName])) {
                    $taluka_schools[$tName] = [];
                }
                if (!in_array($sName, $taluka_schools[$tName])) {
                    $taluka_schools[$tName][] = $sName;
                }
            }
            
            // Mock Status Data to match image exactly
            if ($index % 3 == 0) {
                $row['mock_status'] = "In Progress";
                $row['duration_days'] = 1;
                $row['overdue_days'] = 0;
            } elseif ($index % 3 == 1) {
                $row['mock_status'] = "Completed";
                $row['duration_days'] = 119;
                $row['overdue_days'] = 16;
            } else {
                $row['mock_status'] = "Pending";
                $row['duration_days'] = 5;
                $row['overdue_days'] = 0;
            }
            
            $wtype = ($row['work_type'] == 'CIVILIAN' || empty($row['work_type'])) ? 'CIVILIAN' : $row['work_type'];
            $row['display_wtype'] = $wtype;
            
            $works[] = $row;
            $index++;
        }
    }
}
sort($talukas);

$count_total = count($works);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Samruddha Shala E-Portal - Work Master</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="../css/sidebar.css" rel="stylesheet">
<link rel="stylesheet" href="css/sachiv_dashboard.css?v=<?php echo time(); ?>">

<style>
    .main-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        border-top: 4px solid #0dcaf0; /* Teal top border matching image */
        padding: 30px;
        margin-top: 10px;
    }

    
    .page-title {
        color: #1e293b;
        font-weight: 700;
        text-align: center;
        margin-bottom: 8px;
    }
    .title-underline {
        height: 3px;
        background-color: #3b82f6; /* Blue underline */
        width: 140px;
        margin: 0 auto 30px auto;
        border-radius: 2px;
    }

    .search-input {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .btn-export {
        background-color: #10b981; /* Green exact match */
        color: white;
        border: none;
        font-weight: 500;
        padding: 8px 16px;
    }
    .btn-export:hover { background-color: #059669; color: white; }

    /* Table Styles */
    .table-container {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow-x: auto;
    }
    .table {
        margin-bottom: 0;
    }
    .table thead th {
        background-color: #1e293b; /* Dark slate matching image */
        color: #ffffff;
        font-weight: 600;
        font-size: 13px;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #334155;
        padding: 14px 10px;
    }
    .table tbody td {
        vertical-align: middle;
        text-align: center;
        font-size: 13px;
        color: #334155;
        border: 1px solid #e2e8f0;
        padding: 12px 10px;
    }
    
    /* Buttons & Badges */
    .btn-view {
        background-color: #0284c7;
        color: white;
        border: none;
        font-size: 12px;
        padding: 4px 16px;
        font-weight: 500;
        border-radius: 4px;
    }
    .badge-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 12px;
        display: inline-block;
    }
    .status-progress { background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
    .status-completed { background-color: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
    .status-pending { background-color: #ffefc2; color: #d97706; border: 1px solid #fde68a; }
    
    .btn-reminder, .btn-blocker {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 4px;
        border: none;
        color: white;
    }
    .btn-reminder { background-color: #f59e0b; } /* Orange */
    .btn-blocker { background-color: #ef4444; } /* Red */
    
    /* Pagination */
    .entries-text { font-size: 13px; color: #64748b; }
    .page-link { font-size: 13px; color: #64748b; border-color: #e2e8f0; padding: 6px 12px; }
    .page-item.active .page-link { background-color: #2563eb; border-color: #2563eb; color: #fff; }
    
    /* Footer matching image */
    .custom-footer {
        text-align: center;
        font-size: 13px;
        color: #475569;
        margin-top: 40px;
        margin-bottom: 20px;
        font-weight: 500;
    }
</style>
</head>
<body class="sachiv-dashboard-page">
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div id="wrapper">
    <!-- SIDEBAR -->
    <?php include '../include/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <!-- HEADER -->
        <div class="sachiv-fixed-header">
            <?php include '../include/website_header.php'; ?>
        </div>

        <!-- CONTENT AREA -->
        <div class="content-area">
            
            <div class="main-card">
                <div class="d-flex align-items-center justify-content-start justify-content-lg-center mb-3">
                    <button class="btn btn-light d-lg-none shadow-sm border-0 d-flex justify-content-center align-items-center me-3 flex-shrink-0" style="width: 44px; height: 44px; background: linear-gradient(135deg, #6420a5 0%, #efbc4d 100%); color: white; border-radius: 12px;" type="button" id="menuToggle" aria-label="Toggle Sidebar">
                        <i class="fa-solid fa-bars fs-5"></i>
                    </button>
                    <div class="text-start text-lg-center">
                        <h3 class="page-title m-0">Work Master</h3>
                        <div class="title-underline mx-0 mx-lg-auto mt-2 mb-0" style="margin-bottom: 0;"></div>
                    </div>
                </div>

                <!-- Controls Top Row -->
                <div class="d-flex flex-wrap justify-content-between mb-4 gap-3">
                    <div class="d-flex flex-wrap gap-3 flex-grow-1" style="max-width: 800px;">
                        <div class="input-group" style="flex: 1 1 200px;">
                            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" class="form-control border-start-0 search-input ps-0" id="filterWorkName" placeholder="Search Work..." onkeyup="handleFilterChange()">
                        </div>
                        <select class="form-select" id="filterTaluka" style="flex: 1 1 150px;">
                            <option value="">Select Taluka</option>
                            <?php foreach($talukas as $t) { echo '<option value="'.htmlspecialchars($t).'">'.htmlspecialchars($t).'</option>'; } ?>
                        </select>
                        <select class="form-select" id="filterSchool" style="flex: 1 1 150px;" onchange="handleFilterChange()">
                            <option value="">Select School</option>
                        </select>
                    </div>
                    <div>
                        <button class="btn btn-export rounded-2 shadow-sm" id="btnExport">
                            <i class="fa-solid fa-file-excel me-2"></i> Export to Excel
                        </button>
                    </div>
                </div>

                <!-- Entries Select & Top Pagination Info -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="entries-text d-flex align-items-center">
                        Show 
                        <select class="form-select form-select-sm mx-2" style="width: 70px;" id="pageSize" onchange="changePageSize()">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        entries
                    </div>
                    <div class="entries-text" id="topPaginationSummary">
                        Showing 1 to 10 of <?php echo $count_total; ?> entries
                    </div>
                </div>

                <!-- Swipe hint for mobile -->
                <div class="d-md-none text-end text-muted small mb-2">
                    <i class="fa-solid fa-arrows-left-right me-1"></i> Swipe table to view more
                </div>
                
                <!-- TABLE -->
                <div class="table-container shadow-sm mb-3">
                    <table class="table table-bordered w-100" id="workTable">
                        <thead>
                            <tr>
                                <th style="width: 6%;">Sr. No. <i class="fa-solid fa-caret-up ms-1 text-white-50"></i></th>
                                <th style="width: 10%;">Taluka</th>
                                <th style="width: 15%;">School Name</th>
                                <th style="width: 10%;">Work Type</th>
                                <th style="width: 18%;">Work Name</th>
                                <th style="width: 7%;">View</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;">Duration<br>(Days)</th>
                                <th style="width: 14%;">Reminder / Blocker</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php
                            if (count($works) > 0) {
                                foreach ($works as $index => $work) {
                                    $sr_no = $index + 1;
                                    $taluka = htmlspecialchars($work['taluka_name']);
                                    $school = htmlspecialchars($work['school_name']);
                                    $work_type = htmlspecialchars($work['display_wtype']);
                                    $work_name = htmlspecialchars($work['work_name']);
                                    
                                    $status = $work['mock_status'];
                                    $status_class = ($status == "Pending") ? "status-pending" : (($status == "In Progress") ? "status-progress" : "status-completed");
                                    
                                    $duration_days = $work['duration_days'];
                                    $overdue_days = $work['overdue_days'];
                                    
                                    // Exact duration styling match
                                    if ($overdue_days > 0) {
                                        $duration_html = "<div class='text-danger fw-bold'>{$duration_days} Days</div>
                                                          <div class='text-danger' style='font-size:10px; margin-top:2px;'>
                                                              <i class='fa-solid fa-triangle-exclamation'></i> {$overdue_days} Days Overdue
                                                          </div>";
                                    } else {
                                        $duration_html = "<div class='fw-bold text-dark'>{$duration_days} Day" . ($duration_days > 1 ? "s" : "") . "</div>";
                                    }
                                    
                                    echo "<tr class='data-row' data-taluka='".htmlspecialchars($taluka, ENT_QUOTES)."' data-school='".htmlspecialchars($school, ENT_QUOTES)."'>";
                                    echo "<td data-label='Sr. No.'>{$sr_no}</td>";
                                    echo "<td data-label='Taluka'>{$taluka}</td>";
                                    echo "<td data-label='School Name'>{$school}</td>";
                                    echo "<td data-label='Work Type'>{$work_type}</td>";
                                    echo "<td data-label='Work Name' class='work-name-cell'>{$work_name}</td>";
                                    
                                    $safe_name = htmlspecialchars($work_name, ENT_QUOTES);
                                    $safe_taluka = htmlspecialchars($taluka, ENT_QUOTES);
                                    $safe_school = htmlspecialchars($school, ENT_QUOTES);
                                    $safe_type = htmlspecialchars($work_type, ENT_QUOTES);
                                    $safe_duration = htmlspecialchars($duration_days . " Days", ENT_QUOTES);
                                    
                                    echo "<td data-label='View'><button class='btn-view shadow-sm' onclick=\"viewDetails('{$sr_no}', '{$safe_taluka}', '{$safe_school}', '{$safe_type}', '{$safe_name}', '{$status}', '{$safe_duration}')\"><i class='fa-solid fa-eye me-1'></i> View</button></td>";
                                    echo "<td data-label='Status'><span class='badge-status {$status_class}'>{$status}</span></td>";
                                    echo "<td data-label='Duration (Days)'>{$duration_html}</td>";
                                    
                                    echo "<td data-label='Reminder / Blocker'>";
                                    echo "<div class='d-flex justify-content-start justify-content-md-center gap-2'>";
                                    echo "<button class='btn-reminder shadow-sm' onclick=\"confirmAction('Reminder', '{$safe_name}', '{$safe_taluka}')\"><i class='fa-solid fa-bell me-1'></i> Reminder</button>";
                                    echo "<button class='btn-blocker shadow-sm' onclick=\"confirmAction('Blocker', '{$safe_name}', '{$safe_taluka}')\"><i class='fa-solid fa-ban me-1'></i> Blocker</button>";
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                            <tr id="noRecordsRow" style="display: none;">
                                <td colspan="9" class="text-center py-4 text-muted fw-bold">No records found matching your filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Bottom Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="entries-text" id="bottomPaginationSummary">
                        Showing 1 to 10 of <?php echo $count_total; ?> entries
                    </div>
                    <nav>
                        <ul class="pagination mb-0" id="paginationControls">
                            <!-- Populated by JS -->
                        </ul>
                    </nav>
                </div>

        </div> <!-- /content-area -->
        
        <!-- FOOTER -->
        <div class="sachiv-fixed-footer">
            <?php include '../include/website_footer.php'; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const talukaSchools = <?php echo json_encode($taluka_schools); ?>;

    // Sidebar Toggle
    document.addEventListener("DOMContentLoaded", function () {
        const menuBtn = document.getElementById("menuToggle");
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("sidebarOverlay");
        if(menuBtn) {
            menuBtn.addEventListener("click", function () {
                sidebar.classList.toggle("active");
                if(overlay) overlay.classList.toggle("active");
            });
        }
        if(overlay) {
            overlay.addEventListener("click", function () {
                sidebar.classList.remove("active");
                overlay.classList.remove("active");
            });
        }
        
        // Populate school dropdown when taluka changes
        document.getElementById('filterTaluka').addEventListener('change', function() {
            const taluka = this.value;
            const schoolSelect = document.getElementById('filterSchool');
            schoolSelect.innerHTML = '<option value="">Select School</option>';
            if (taluka && talukaSchools[taluka]) {
                talukaSchools[taluka].forEach(school => {
                    const opt = document.createElement('option');
                    opt.value = school;
                    opt.textContent = school;
                    schoolSelect.appendChild(opt);
                });
            }
            handleFilterChange();
        });
        
        applyFilters();
    });

    // ---------------------------------
    // Filter and Pagination Logic
    // ---------------------------------
    let currentPage = 1;
    let filteredRows = [];
    const allRows = Array.from(document.querySelectorAll('.data-row'));
    
    function handleFilterChange() {
        currentPage = 1;
        applyFilters();
    }
    
    function changePageSize() {
        currentPage = 1;
        applyFilters();
    }
    
    function changePage(page) {
        currentPage = page;
        renderTable();
    }

    function applyFilters() {
        const nameFilter = document.getElementById('filterWorkName').value.toLowerCase();
        const talukaFilter = document.getElementById('filterTaluka').value;
        const schoolFilter = document.getElementById('filterSchool').value;
        
        filteredRows = allRows.filter(row => {
            const name = row.querySelector('.work-name-cell').textContent.toLowerCase();
            const taluka = row.getAttribute('data-taluka');
            const school = row.getAttribute('data-school');
            
            if (nameFilter && !name.includes(nameFilter)) return false;
            if (talukaFilter && taluka !== talukaFilter) return false;
            if (schoolFilter && school !== schoolFilter) return false;
            return true;
        });
        
        allRows.forEach(row => row.style.display = 'none');
        renderTable();
    }
    
    function renderTable() {
        const pageSize = parseInt(document.getElementById('pageSize').value);
        const totalItems = filteredRows.length;
        
        document.getElementById('noRecordsRow').style.display = (totalItems === 0) ? '' : 'none';
        
        const totalPages = Math.ceil(totalItems / pageSize) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        
        const startIdx = (currentPage - 1) * pageSize;
        const endIdx = Math.min(startIdx + pageSize, totalItems);
        
        for (let i = startIdx; i < endIdx; i++) {
            filteredRows[i].style.display = '';
        }
        
        const summaryText = totalItems === 0 
            ? "Showing 0 entries" 
            : `Showing ${startIdx + 1} to ${endIdx} of ${totalItems} entries`;
        document.getElementById('topPaginationSummary').textContent = summaryText;
        document.getElementById('bottomPaginationSummary').textContent = summaryText;
        
        const ul = document.getElementById('paginationControls');
        ul.innerHTML = '';
        
        if (totalPages > 1 || totalItems > 0) {
            ul.innerHTML += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="changePage(${currentPage - 1})"><i class="fa-solid fa-angle-left me-1"></i> Previous</a></li>`;
            
            for(let p = 1; p <= totalPages; p++) {
                if (p === 1 || p === totalPages || (p >= currentPage - 1 && p <= currentPage + 1)) {
                    ul.innerHTML += `<li class="page-item ${currentPage === p ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" onclick="changePage(${p})">${p}</a></li>`;
                } else if (p === currentPage - 2 || p === currentPage + 2) {
                    ul.innerHTML += `<li class="page-item disabled"><a class="page-link" href="javascript:void(0)">...</a></li>`;
                }
            }
            
            ul.innerHTML += `<li class="page-item ${currentPage === totalPages || totalItems === 0 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="changePage(${currentPage + 1})">Next <i class="fa-solid fa-angle-right ms-1"></i></a></li>`;
        }
    }

    window.confirmAction = function(actionType, workName, taluka) {
        let defaultMsg = '';
        if (actionType === 'Reminder') {
            defaultMsg = 'Priority: This work has not been updated in 15 days. Please update as soon';
        }

        Swal.fire({
            title: `Send ${actionType} to HM`,
            html: `You are sending a ${actionType.toLowerCase()} regarding <b>${workName}</b> in <b>${taluka}</b>.`,
            input: 'textarea',
            inputValue: defaultMsg,
            inputPlaceholder: 'Type your message for the HM here...',
            inputAttributes: {
                'aria-label': 'Type your message here'
            },
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-paper-plane me-1"></i> Send Message',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#ef4444'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Message Sent!',
                    text: `Your ${actionType.toLowerCase()} has been delivered to the HM.`,
                    icon: 'success',
                    confirmButtonColor: '#10b981'
                });
            }
        });
    };
    
    window.viewFullImage = function(url) {
        Swal.fire({
            imageUrl: url,
            imageAlt: 'Work Photo',
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                image: 'img-fluid rounded shadow-sm'
            },
            width: '80%',
            padding: '1em'
        });
    };

    window.viewDetails = function(sr, taluka, school, type, name, status, duration) {
        const statusColor = status === 'Completed' ? 'success' : (status === 'Pending' ? 'warning' : 'primary');
        const img_url = 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=100';
        Swal.fire({
            title: 'Work Details',
            html: `
                <div class="text-start mt-3" style="font-size: 14px;">
                    <div class="mb-3 text-start">
                        <button class="btn btn-outline-primary border-0 shadow-sm" style="padding: 8px 16px; font-weight: 500; background-color: #f8fafc;" onclick="viewFullImage('${img_url}')">
                            <i class="fa-solid fa-camera text-primary me-2"></i>
                            View Live Picture
                        </button>
                    </div>
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr><th class="bg-light" style="width: 40%;">Sr. No.</th><td>${sr}</td></tr>
                            <tr><th class="bg-light">Taluka</th><td>${taluka}</td></tr>
                            <tr><th class="bg-light">School Name</th><td>${school}</td></tr>
                            <tr><th class="bg-light">Work Type</th><td>${type}</td></tr>
                            <tr><th class="bg-light">Work Name</th><td>${name}</td></tr>
                            <tr><th class="bg-light">Status</th><td><span class="badge bg-${statusColor}">${status}</span></td></tr>
                            <tr><th class="bg-light">Duration</th><td>${duration}</td></tr>
                        </tbody>
                    </table>
                </div>
            `,
            confirmButtonText: 'Close',
            confirmButtonColor: '#3085d6',
            width: '600px'
        });
    }

    document.getElementById('btnExport').addEventListener('click', function() {
        let csv = [];
        const headers = ["Sr. No.", "Taluka", "School Name", "Work Type", "Work Name", "Status", "Duration"];
        csv.push(headers.join(","));
        
        filteredRows.forEach(row => {
            let rowData = [];
            const cols = row.querySelectorAll("td");
            // skip View and Actions column
            for (let j = 0; j < cols.length - 1; j++) {
                if (j === 5) continue; // skip view button col
                let data = cols[j].innerText.replace(/\n/g, ' ').replace(/"/g, '""');
                rowData.push('"' + data + '"');
            }
            csv.push(rowData.join(","));
        });
        
        const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
        const downloadLink = document.createElement("a");
        downloadLink.download = "work_master.csv";
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    });
</script>

</body>
</html>