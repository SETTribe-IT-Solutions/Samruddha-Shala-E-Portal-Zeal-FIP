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

include '../include/dbConfig.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $work_id = mysqli_real_escape_string($conn, $_POST['work_name']);
    $stages = $_POST['stage_name'] ?? [];
    $percentages = $_POST['percentage'] ?? [];
    $is_completed = $_POST['is_completed'] ?? [];

    if (!empty($work_id)) {
        for ($i = 0; $i < 4; $i++) {
            $stage_name = mysqli_real_escape_string($conn, $stages[$i] ?? '');
            $pct = isset($percentages[$i]) && $percentages[$i] !== '' ? (float)$percentages[$i] : 0;
            $completed = isset($is_completed[$i]) ? 1 : 0;
            $stage_no = $i + 1;

            if (!empty($stage_name)) {
                $sql = "INSERT INTO hm_work_progress (work_id, stage_no, stage_name, is_completed, progress_percentage, created_date, updated_date) 
                        VALUES ('$work_id', '$stage_no', '$stage_name', '$completed', '$pct', NOW(), NOW())
                        ON DUPLICATE KEY UPDATE 
                        stage_name = VALUES(stage_name), 
                        is_completed = VALUES(is_completed), 
                        progress_percentage = VALUES(progress_percentage), 
                        updated_date = NOW()";
                mysqli_query($conn, $sql);
            }
        }
        $success_msg = "Work progress submitted successfully!";
    } else {
        $error_msg = "Please select a work name.";
    }
}

// Fetch distinct work types
$work_types = [];
$wt_query = mysqli_query($conn, "SELECT DISTINCT work_type FROM talukas_school_data WHERE work_type IS NOT NULL AND work_type != '' ORDER BY work_type ASC");
if ($wt_query) {
    while($row = mysqli_fetch_assoc($wt_query)){
        $work_types[] = $row['work_type'];
    }
}

// Fetch works with their IDs and work_types for filtering
$works = [];
$wn_query = mysqli_query($conn, "SELECT id, work_name, work_type FROM talukas_school_data WHERE work_name IS NOT NULL AND work_name != '' ORDER BY work_name ASC");
if ($wn_query) {
    while($row = mysqli_fetch_assoc($wn_query)){
        $works[] = $row;
    }
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
    <link href="css/hm_dashboard.css?v=2.0.2" rel="stylesheet">
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
        
        <div class="hm-main-content">
            <?php if(!empty($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show m-3 mb-0" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> <?php echo $success_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if(!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show m-3 mb-0" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $error_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Header Top Bar (Navbar) -->
            <nav class="navbar navbar-expand-lg navbar-light p-3 flex-shrink-0">
                <div class="container-fluid d-flex flex-nowrap align-items-center px-1">
                    <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                        <!-- Mobile Sidebar Toggle -->
                        <button class="btn btn-light d-lg-none me-2 shadow-sm border-0 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 40px; height: 40px; background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%); color: white;" type="button" id="sidebarCollapse" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
                            <i class="fa-solid fa-bars fs-6"></i>
                        </button>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-0 flex-grow-1 d-flex flex-column" style="overflow: hidden;">
                
                <div class="pt-1 pb-2 px-4 flex-grow-1 d-flex flex-column">
                    
                    <div class="hm-card p-4" style="width: 100%; border-radius: 20px;">
                        <form id="progressForm" action="" method="POST">
                        
                        <!-- Main Title -->
                        <div class="text-center mb-3">
                            <h2 class="fw-bold text-dark mb-2" style="color: #0f172a;">Update Stage Progress</h2>
                            <div style="height: 3px; width: 120px; background-color: #3b82f6; margin: 0 auto; border-radius: 2px;"></div>
                        </div>

                        <!-- General Information -->
                        <div class="mb-3 border rounded-4 p-3" style="border-color: #e2e8f0 !important;">
                            <div class="text-center mb-3">
                                <h5 class="fw-bold mb-2" style="color: #1e293b;">General Information</h5>
                                <div style="height: 3px; width: 150px; background-color: #3b82f6; margin: 0 auto; border-radius: 2px;"></div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" style="color: #334155; font-size: 14px;">Work Type <span class="text-danger">*</span></label>
                                    <select name="work_type" id="work_type" class="form-select py-2" style="border-radius: 8px; border: 1px solid #cbd5e1;" required onchange="filterWorkNames()">
                                        <option value="">-- Select Work Type --</option>
                                        <?php foreach($work_types as $wt): ?>
                                            <option value="<?php echo htmlspecialchars($wt); ?>"><?php echo htmlspecialchars($wt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" style="color: #334155; font-size: 14px;">Work Name <span class="text-danger">*</span></label>
                                    <select name="work_name" id="work_name" class="form-select py-2" style="border-radius: 8px; border: 1px solid #cbd5e1;" required>
                                        <option value="">-- Select Work Name --</option>
                                        <?php foreach($works as $work): ?>
                                            <option value="<?php echo htmlspecialchars($work['id']); ?>" data-type="<?php echo htmlspecialchars($work['work_type']); ?>"><?php echo htmlspecialchars($work['work_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Stage Progress Area -->
                        <div class="p-3 flex-grow-1" style="background-color: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;">
                            <div class="text-center mb-3">
                                <h5 class="fw-bold mb-2" style="color: #1e293b;">Stage Progress</h5>
                                <div style="height: 3px; width: 100px; background-color: #3b82f6; margin: 0 auto; border-radius: 2px;"></div>
                            </div>

                            <!-- Row 1 -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex justify-content-center align-items-center text-white me-3 flex-shrink-0" style="width: 28px; height: 28px; border-radius: 50%; background-color: #0ea5e9; font-weight: 700; font-size: 14px;">1</div>
                                <input name="is_completed[0]" class="form-check-input me-3 flex-shrink-0" type="checkbox" value="1" style="width: 22px; height: 22px; margin-top: 0;">
                                <input name="stage_name[0]" type="text" class="form-control me-3 flex-grow-1 py-2" placeholder="Stage Name" style="border-radius: 8px; border: 1px solid #cbd5e1;">
                                <div class="input-group flex-shrink-0" style="width: 140px;">
                                    <input name="percentage[0]" type="number" step="0.01" class="form-control py-2" placeholder="Enter" style="border-radius: 8px 0 0 8px; border: 1px solid #cbd5e1;">
                                    <span class="input-group-text bg-white fw-bold" style="border-radius: 0 8px 8px 0; border: 1px solid #cbd5e1; border-left: none; color: #475569;">%</span>
                                </div>
                            </div>

                            <!-- Row 2 -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex justify-content-center align-items-center text-white me-3 flex-shrink-0" style="width: 28px; height: 28px; border-radius: 50%; background-color: #0ea5e9; font-weight: 700; font-size: 14px;">2</div>
                                <input name="is_completed[1]" class="form-check-input me-3 flex-shrink-0" type="checkbox" value="1" style="width: 22px; height: 22px; margin-top: 0;">
                                <input name="stage_name[1]" type="text" class="form-control me-3 flex-grow-1 py-2" placeholder="Stage Name" style="border-radius: 8px; border: 1px solid #cbd5e1;">
                                <div class="input-group flex-shrink-0" style="width: 140px;">
                                    <input name="percentage[1]" type="number" step="0.01" class="form-control py-2" placeholder="Enter" style="border-radius: 8px 0 0 8px; border: 1px solid #cbd5e1;">
                                    <span class="input-group-text bg-white fw-bold" style="border-radius: 0 8px 8px 0; border: 1px solid #cbd5e1; border-left: none; color: #475569;">%</span>
                                </div>
                            </div>

                            <!-- Row 3 -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex justify-content-center align-items-center text-white me-3 flex-shrink-0" style="width: 28px; height: 28px; border-radius: 50%; background-color: #0ea5e9; font-weight: 700; font-size: 14px;">3</div>
                                <input name="is_completed[2]" class="form-check-input me-3 flex-shrink-0" type="checkbox" value="1" style="width: 22px; height: 22px; margin-top: 0;">
                                <input name="stage_name[2]" type="text" class="form-control me-3 flex-grow-1 py-2" placeholder="Stage Name" style="border-radius: 8px; border: 1px solid #cbd5e1;">
                                <div class="input-group flex-shrink-0" style="width: 140px;">
                                    <input name="percentage[2]" type="number" step="0.01" class="form-control py-2" placeholder="Enter" style="border-radius: 8px 0 0 8px; border: 1px solid #cbd5e1;">
                                    <span class="input-group-text bg-white fw-bold" style="border-radius: 0 8px 8px 0; border: 1px solid #cbd5e1; border-left: none; color: #475569;">%</span>
                                </div>
                            </div>

                            <!-- Row 4 -->
                            <div class="d-flex align-items-center mb-2">
                                <div class="d-flex justify-content-center align-items-center text-white me-3 flex-shrink-0" style="width: 28px; height: 28px; border-radius: 50%; background-color: #0ea5e9; font-weight: 700; font-size: 14px;">4</div>
                                <input name="is_completed[3]" class="form-check-input me-3 flex-shrink-0" type="checkbox" value="1" style="width: 22px; height: 22px; margin-top: 0;">
                                <input name="stage_name[3]" type="text" class="form-control me-3 flex-grow-1 py-2" placeholder="Stage Name" style="border-radius: 8px; border: 1px solid #cbd5e1;">
                                <div class="input-group flex-shrink-0" style="width: 140px;">
                                    <input name="percentage[3]" type="number" step="0.01" class="form-control py-2" placeholder="Enter" style="border-radius: 8px 0 0 8px; border: 1px solid #cbd5e1;">
                                    <span class="input-group-text bg-white fw-bold" style="border-radius: 0 8px 8px 0; border: 1px solid #cbd5e1; border-left: none; color: #475569;">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-center gap-4 mt-4 mb-1">
                            <button type="reset" class="btn btn-light py-2 fw-bold shadow-sm" style="border-radius: 8px; font-size: 16px; width: 200px; border: 1px solid #cbd5e1; color: #475569;">Reset</button>
                            <button type="submit" class="btn btn-primary py-2 fw-bold shadow-sm" style="border-radius: 8px; background-color: #096bc5; border-color: #096bc5; font-size: 16px; width: 200px;">Save</button>
                        </div>
                        
                        </form>
                    </div>
                </div>

            </div> <!-- End container-fluid -->
        </div> <!-- End hm-main-content -->

        <div class="hm-fixed-footer">
            <?php include '../include/website_footer.php'; ?>
        </div>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
    }

    function filterWorkNames() {
        var typeSelect = document.getElementById('work_type');
        var nameSelect = document.getElementById('work_name');
        var selectedType = typeSelect.value;
        
        // Reset selection
        nameSelect.value = '';
        
        var options = nameSelect.getElementsByTagName('option');
        for (var i = 0; i < options.length; i++) {
            var opt = options[i];
            if (opt.value === '') {
                continue; // Always show the default placeholder
            }
            if (selectedType === '' || opt.getAttribute('data-type') === selectedType) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        }
    }
</script>
</body>
</html>