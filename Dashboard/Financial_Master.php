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

$financials = [];
$talukas = [];
$fund_sources = [];

$total_budget = 0;
$total_spent = 0;

if ($conn) {
    $query = "SELECT * FROM talukas_school_data ORDER BY id ASC";
    $res = mysqli_query($conn, $query);
    if ($res) {
        $index = 0;
        while ($row = mysqli_fetch_assoc($res)) {
            if (!empty($row['taluka_name']) && !in_array($row['taluka_name'], $talukas)) {
                $talukas[] = $row['taluka_name'];
            }
            if (!empty($row['fund_source']) && !in_array($row['fund_source'], $fund_sources)) {
                $fund_sources[] = $row['fund_source'];
            }
            
            // --- MOCK FINANCIAL DATA ---
            // Realistic numbers for demonstration since db lacks these columns currently
            srand($row['id']); // seed for deterministic random
            $budget = rand(1, 15) * 100000;
            $spent = round($budget * (rand(10, 95) / 100));
            $balance = $budget - $spent;
            
            $row['mock_budget'] = $budget;
            $row['mock_spent'] = $spent;
            $row['mock_balance'] = $balance;
            
            $percent_spent = round(($spent / $budget) * 100);
            if ($percent_spent > 90) {
                $row['mock_status'] = "Critical";
                $row['mock_status_color'] = "danger";
            } elseif ($percent_spent > 60) {
                $row['mock_status'] = "Warning";
                $row['mock_status_color'] = "warning";
            } else {
                $row['mock_status'] = "Good";
                $row['mock_status_color'] = "success";
            }
            
            $total_budget += $budget;
            $total_spent += $spent;
            
            $financials[] = $row;
            $index++;
        }
    }
}
sort($talukas);
sort($fund_sources);

$total_balance = $total_budget - $total_spent;
$count_total = count($financials);

// Helper function for Indian Rupee format
function formatRupee($num) {
    $explrestunits = "";
    $num = preg_replace('/,+/', '', $num);
    $words = explode(".", $num);
    $des = "00";
    if(count($words)<=2){
        $num=$words[0];
        if(count($words)>=2){$des=$words[1];}
        if(strlen($des)<2){$des="$des"."0";}else{$des=substr($des,0,2);}
    }
    if(strlen($num)>3){
        $lastthree = substr($num, strlen($num)-3, strlen($num));
        $restunits = substr($num, 0, strlen($num)-3); 
        $restunits = (strlen($restunits)%2 == 1)?"0".$restunits:$restunits;
        $expunit = str_split($restunits, 2);
        for($i=0; $i<sizeof($expunit); $i++){
            if($i==0)
            {
                $explrestunits .= (int)$expunit[$i].",";
            }else{
                $explrestunits .= $expunit[$i].",";
            }
        }
        $thecash = $explrestunits.$lastthree;
    } else {
        $thecash = $num;
    }
    return "₹ " . $thecash; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Samruddha Shala E-Portal - Financial Master</title>

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
        border-top: 4px solid #10b981; /* Green top border for financial */
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
        background-color: #10b981; /* Green underline */
        width: 140px;
        margin: 0 auto 30px auto;
        border-radius: 2px;
    }

    /* KPI Cards */
    .kpi-card {
        border-radius: 12px;
        padding: 20px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-5px);
    }
    .kpi-budget { background: linear-gradient(135deg, #0284c7 0%, #38bdf8 100%); }
    .kpi-spent { background: linear-gradient(135deg, #1d4ed8 0%, #60a5fa 100%); }
    .kpi-balance { background: linear-gradient(135deg, #059669 0%, #34d399 100%); }
    
    .kpi-title { font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; }
    .kpi-value { font-size: 26px; font-weight: 700; margin-top: 5px; }

    .search-input {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .btn-export {
        background-color: #10b981;
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
        overflow: hidden;
    }
    .table {
        margin-bottom: 0;
    }
    .table thead th {
        background-color: #1e293b;
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
    
    /* Status Badges */
    .badge-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 12px;
        display: inline-block;
    }
    .status-success { background-color: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
    .status-warning { background-color: #fef08a; color: #ca8a04; border: 1px solid #fde047; }
    .status-danger { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    
    /* Pagination */
    .entries-text { font-size: 13px; color: #64748b; }
    .page-link { font-size: 13px; color: #64748b; border-color: #e2e8f0; padding: 6px 12px; }
    .page-item.active .page-link { background-color: #10b981; border-color: #10b981; color: #fff; }
    
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
                        <h3 class="page-title m-0">Financial Master</h3>
                        <div class="title-underline mx-0 mx-lg-auto mt-2 mb-0" style="margin-bottom: 0;"></div>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="row mb-5 mt-4 g-3">
                    <div class="col-md-4">
                        <div class="kpi-card kpi-budget">
                            <div>
                                <div class="kpi-title">Total Allocated Budget</div>
                                <div class="kpi-value"><?php echo formatRupee($total_budget); ?></div>
                            </div>
                            <div>
                                <i class="fa-solid fa-vault fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kpi-card kpi-spent">
                            <div>
                                <div class="kpi-title">Total Spent Amount</div>
                                <div class="kpi-value"><?php echo formatRupee($total_spent); ?></div>
                            </div>
                            <div>
                                <i class="fa-solid fa-hand-holding-dollar fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="kpi-card kpi-balance">
                            <div>
                                <div class="kpi-title">Total Remaining Balance</div>
                                <div class="kpi-value"><?php echo formatRupee($total_balance); ?></div>
                            </div>
                            <div>
                                <i class="fa-solid fa-wallet fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Controls Top Row -->
                <div class="d-flex flex-wrap justify-content-between mb-4 gap-3">
                    <div class="d-flex flex-wrap gap-3 flex-grow-1" style="max-width: 700px;">
                        <div class="input-group" style="flex: 1 1 200px;">
                            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" class="form-control border-start-0 search-input ps-0" id="filterWorkName" placeholder="Search School or Fund..." onkeyup="handleFilterChange()">
                        </div>
                        <select class="form-select" id="filterTaluka" style="flex: 1 1 150px;" onchange="handleFilterChange()">
                            <option value="">Select Taluka</option>
                            <?php foreach($talukas as $t) { echo '<option value="'.htmlspecialchars($t).'">'.htmlspecialchars($t).'</option>'; } ?>
                        </select>
                        <select class="form-select" id="filterFund" style="flex: 1 1 150px;" onchange="handleFilterChange()">
                            <option value="">Select Fund Source</option>
                            <?php foreach($fund_sources as $f) { echo '<option value="'.htmlspecialchars($f).'">'.htmlspecialchars($f).'</option>'; } ?>
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
                
                <!-- Table -->
                <div class="table-container shadow-sm mb-3">
                    <table class="table table-bordered w-100" id="workTable">
                        <thead>
                            <tr>
                                <th style="width: 6%;">Sr. No. <i class="fa-solid fa-caret-up ms-1 text-white-50"></i></th>
                                <th style="width: 10%;">Taluka</th>
                                <th style="width: 15%;">School Name</th>
                                <th style="width: 15%;">Fund Source</th>
                                <th style="width: 12%;">Allocated Budget</th>
                                <th style="width: 12%;">Spent Amount</th>
                                <th style="width: 12%;">Balance</th>
                                <th style="width: 8%;">Status</th>
                                <th style="width: 8%;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php
                            if (count($financials) > 0) {
                                foreach ($financials as $index => $fin) {
                                    $sr_no = $index + 1;
                                    $taluka = htmlspecialchars($fin['taluka_name']);
                                    $school = htmlspecialchars($fin['school_name']);
                                    $fund = htmlspecialchars($fin['fund_source']);
                                    
                                    $budget = formatRupee($fin['mock_budget']);
                                    $spent = formatRupee($fin['mock_spent']);
                                    $balance = formatRupee($fin['mock_balance']);
                                    
                                    $status = $fin['mock_status'];
                                    $status_class = "status-" . $fin['mock_status_color'];
                                    
                                    echo "<tr class='data-row' data-taluka='".htmlspecialchars($taluka, ENT_QUOTES)."' data-fund='".htmlspecialchars($fund, ENT_QUOTES)."'>";
                                    echo "<td data-label='Sr. No.'>{$sr_no}</td>";
                                    echo "<td data-label='Taluka'>{$taluka}</td>";
                                    echo "<td data-label='School Name' class='school-name-cell'>{$school}</td>";
                                    echo "<td data-label='Fund Source'>{$fund}</td>";
                                    echo "<td data-label='Allocated Budget' class='fw-bold text-dark'>{$budget}</td>";
                                    echo "<td data-label='Spent Amount' class='text-primary'>{$spent}</td>";
                                    echo "<td data-label='Balance' class='text-success fw-bold'>{$balance}</td>";
                                    echo "<td data-label='Status'><span class='badge-status {$status_class}'>{$status}</span></td>";
                                    
                                    echo "<td data-label='Action'><button class='btn-view shadow-sm'><i class='fa-solid fa-eye me-1'></i> View</button></td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                            <tr id="noRecordsRow" style="display: none;">
                                <td colspan="9" class="text-center py-4 text-muted fw-bold">No financial records found matching your filters.</td>
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
<script>
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
        const fundFilter = document.getElementById('filterFund').value;
        
        filteredRows = allRows.filter(row => {
            const school = row.querySelector('.school-name-cell').textContent.toLowerCase();
            const taluka = row.getAttribute('data-taluka');
            const fund = row.getAttribute('data-fund');
            
            if (nameFilter && !school.includes(nameFilter) && !fund.toLowerCase().includes(nameFilter)) return false;
            if (talukaFilter && taluka !== talukaFilter) return false;
            if (fundFilter && fund !== fundFilter) return false;
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

    document.getElementById('btnExport').addEventListener('click', function() {
        let csv = [];
        const headers = ["Sr. No.", "Taluka", "School Name", "Fund Source", "Allocated Budget", "Spent Amount", "Balance", "Status"];
        csv.push(headers.join(","));
        
        filteredRows.forEach(row => {
            let rowData = [];
            const cols = row.querySelectorAll("td");
            // skip View Action column
            for (let j = 0; j < cols.length - 1; j++) {
                let data = cols[j].innerText.replace(/\n/g, ' ').replace(/₹ /g, '').replace(/,/g, '').replace(/"/g, '""');
                rowData.push('"' + data + '"');
            }
            csv.push(rowData.join(","));
        });
        
        const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
        const downloadLink = document.createElement("a");
        downloadLink.download = "financial_master.csv";
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    });
</script>

</body>
</html>