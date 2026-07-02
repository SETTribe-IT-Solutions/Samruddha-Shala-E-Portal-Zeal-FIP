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
    <title>Samruddha Shala E-Portal - CEO Assign Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <link rel="stylesheet" href="css/ceo_create_work.css">
</head>
<body class="ceo-dashboard-page">
    <div id="wrapper">
        <?php include '../include/sidebar.php'; ?>

        <div id="content">
            <div class="ceo-fixed-header">
                <?php include '../include/website_header.php'; ?>
            </div>
            
            <!-- Navbar Header with Language Switcher -->
            <nav class="navbar navbar-expand-lg navbar-light p-3" style="position: relative;">
                <div class="container-fluid d-flex flex-nowrap align-items-center px-1">
                    <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                        <!-- Mobile Sidebar Toggle -->
                        <button class="btn btn-light d-lg-none me-2 shadow-sm border-0 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 40px; height: 40px; background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%); color: white;" type="button" id="mobileSidebarToggle" aria-label="Toggle Sidebar">
                            <i class="fa-solid fa-bars fs-6"></i>
                        </button>
                        <h4 class="fw-bold mb-0 text-truncate" id="pageMainHeader" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: clamp(1.1rem, 4vw, 1.4rem); font-weight: 800 !important; line-height: 1.2;" data-i18n="navTitle">Stages Report</h4>
                    </div>
                    <div class="ms-2 d-flex align-items-center flex-shrink-0 gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap language-switcher me-2">
                            <button id="langMarBtn" class="btn btn-sm btn-primary">मराठी</button>
                            <button id="langEngBtn" class="btn btn-sm btn-outline-primary">English</button>
                        </div>
                        <div class="position-relative me-3">
                            <a href="ceo_alerts.php" class="btn btn-link text-dark p-1 text-decoration-none" id="notifBellButton" title="View Alerts & Notifications">
                                <i class="fa-regular fa-bell fs-5"></i>
                                <span id="alertsHeaderBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.65rem;">0</span>
                            </a>
                        </div>
                        <div class="d-flex align-items-center border-start ps-2">
                            <h4 class="fw-bold mb-0"><span class="role-badge badge-ceo" style="font-size: 0.85rem; padding: 4px 8px;">CEO</span></h4>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                <div id="ceo-task-view" class="view-panel">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card p-4">
                                <h4 class="fw-bold mb-1"><i class="fa-solid fa-file-signature me-2 text-primary"></i> <span data-i18n="panelTitle">Create Task for HM</span></h4>
                                <p class="text-muted mb-4" data-i18n="panelDesc">Create or update the active task for a school in Kolhapur District, Maharashtra. This resets work progress to 0% and marks the task as pending HM action.</p>
                                
                                <form id="ceoAssignTaskForm" action="ceo_create_work_db.php" onsubmit="handleAssignTaskSubmit(event)">
                                    <div class="mb-3">
                                        <label for="ceoTaskSchoolSelect" class="form-label fw-semibold" data-i18n="lblSchool">Select School</label>
                                        <select id="ceoTaskSchoolSelect" name="school_name" class="form-select" required></select>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="ceoTaskWorkType" class="form-label fw-semibold" data-i18n="lblWorkType">Work Type</label>
                                            <select id="ceoTaskWorkType" name="work_type" class="form-select" required>
                                                <option value="Construction" data-i18n="optConstruction">Construction</option>
                                                <option value="Non-Construction" data-i18n="optNonConstruction">Non-Construction</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ceoTaskBudget" class="form-label fw-semibold" data-i18n="lblBudget">Budget (Lakhs)</label>
                                            <input type="number" min="0" step="0.1" id="ceoTaskBudget" class="form-control" placeholder="e.g. 4.0">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="ceoTaskFundingSource" class="form-label fw-semibold" data-i18n="lblFundingSource">Funding Source</label>
                                            <select id="ceoTaskFundingSource" name="funding_source" class="form-select" required>
                                                <option value="" disabled selected data-i18n="optSelectFunding">Select Funding Source</option>
                                                <option value="Annual Plan" data-i18n="optAnnual">Annual Plan</option>
                                                <option value="Minor Mineral Fund" data-i18n="optMinor">Minor Mineral Fund</option>
                                                <option value="ZP Own Fund" data-i18n="optZP">ZP Own Fund</option>
                                                <option value="CSR Fund" data-i18n="optCSR">CSR Fund</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ceoTaskDescription" class="form-label fw-semibold" data-i18n="lblDescription">Task Description</label>
                                        <textarea id="ceoTaskDescription" class="form-control" rows="4" placeholder="Describe the task details, expected outcomes, and priority..." required></textarea>
                                    </div>
                                    <div class="d-flex gap-3 mt-4">
                                        <button type="reset" class="btn btn-ceo-gold flex-fill py-2 fw-semibold">
                                            <i class="fa-solid fa-rotate-right me-2"></i><span data-i18n="btnReset">Reset</span>
                                        </button>
                                        <button type="submit" class="btn btn-ceo-purple flex-fill py-2 fw-semibold">
                                            <i class="fa-solid fa-paper-plane me-2"></i><span data-i18n="btnAssign">Assign Task</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ceo-fixed-footer">
                <?php include '../include/website_footer.php'; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/db.js"></script>
    <script src="js/ceo.js?v=5"></script>
    <script>
        const langStrings = {
            navTitle: { mar: 'टप्प्यांचा अहवाल', eng: 'Stages Report' },
            panelTitle: { mar: 'HM साठी कार्य तयार करा', eng: 'Create Task for HM' },
            panelDesc: { mar: 'कोल्हापूर जिल्ह्यातील शाळेसाठी सक्रिय कार्य तयार करा किंवा अपडेट करा. हे कामाची प्रगती ०% वर आणेल आणि कार्य प्रलंबित म्हणून घोषित करेल.', eng: 'Create or update the active task for a school in Kolhapur District, Maharashtra. This resets work progress to 0% and marks the task as pending HM action.' },
            lblSchool: { mar: 'शाळा निवडा', eng: 'Select School' },
            lblWorkType: { mar: 'कामाचा प्रकार', eng: 'Work Type' },
            optConstruction: { mar: 'बांधकाम (Construction)', eng: 'Construction' },
            optNonConstruction: { mar: 'बिगर बांधकाम (Non-Construction)', eng: 'Non-Construction' },
            lblBudget: { mar: 'बजेट (लाखात)', eng: 'Budget (Lakhs)' },
            lblFundingSource: { mar: 'निधीचा स्रोत', eng: 'Funding Source' },
            optSelectFunding: { mar: 'निधी स्रोत निवडा', eng: 'Select Funding Source' },
            optAnnual: { mar: 'वार्षिक योजना (Annual Plan)', eng: 'Annual Plan' },
            optMinor: { mar: 'गौण खनिज निधी (Minor Mineral Fund)', eng: 'Minor Mineral Fund' },
            optZP: { mar: 'जि. प. स्विनिधी (ZP Own Fund)', eng: 'ZP Own Fund' },
            optCSR: { mar: 'सी.एस.आर. निधी (CSR Fund)', eng: 'CSR Fund' },
            lblDescription: { mar: 'कामाचे वर्णन / तपशील', eng: 'Task Description' },
            btnReset: { mar: 'पुन्हा सेट करा', eng: 'Reset' },
            btnAssign: { mar: 'कार्य सोपवा', eng: 'Assign Task' },
            
            sideDashboard: { mar: 'CEO डॅशबोर्ड', eng: 'CEO Dashboard' },
            sideCreateStages: { mar: 'टप्पे तयार करा', eng: 'Create Stages' },
            sideStagesReport: { mar: 'टप्प्यांचा अहवाल', eng: 'Stages Report' },
            sideWorkReport: { mar: 'कामाचा अहवाल', eng: 'Work Report' },
            sideFundingReport: { mar: 'निधी अहवाल', eng: 'Funding Report' },
            sideCreateUser: { mar: 'युझर तयार करा', eng: 'Create User' },
            sideFundUtil: { mar: 'निधी वापर तपशील', eng: 'Fund Utilization Details' }
        };

        function setLanguage(lang) {
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (langStrings[key] && langStrings[key][lang]) {
                    el.textContent = langStrings[key][lang];
                }
            });

            document.getElementById('langMarBtn').classList.toggle('btn-primary', lang === 'mar');
            document.getElementById('langMarBtn').classList.toggle('btn-outline-primary', lang !== 'mar');
            document.getElementById('langEngBtn').classList.toggle('btn-primary', lang === 'eng');
            document.getElementById('langEngBtn').classList.toggle('btn-outline-primary', lang !== 'eng');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            if (mobileSidebarToggle && sidebar) {
                mobileSidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('active');
                });
                
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 991 && sidebar.classList.contains('active')) {
                        if (!sidebar.contains(e.target) && e.target !== mobileSidebarToggle) {
                            sidebar.classList.remove('active');
                        }
                    }
                });
            }

            document.getElementById('langMarBtn').addEventListener('click', () => {
                localStorage.setItem('ceoLang', 'mar');
                setLanguage('mar');
            });
            document.getElementById('langEngBtn').addEventListener('click', () => {
                localStorage.setItem('ceoLang', 'eng');
                setLanguage('eng');
            });

            const savedLang = localStorage.getItem('ceoLang') || 'mar';
            setLanguage(savedLang);
        });
    </script>
</body>
</html>
