<?php
session_start();
if(empty($_SESSION['user_id']) || empty($_SESSION['username'])){
    header("Location: ../login.php");
    exit();
}
// Restrict access to CEO only
if (!isset($_SESSION['role']) || strtoupper($_SESSION['role']) !== 'CEO') {
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
            <nav class="navbar navbar-expand-lg navbar-light p-3" style="position: relative;">
                <div class="container-fluid d-flex flex-nowrap align-items-center px-1">
                    <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                        <!-- Mobile Sidebar Toggle -->
                        <button class="btn btn-light d-lg-none me-2 shadow-sm border-0 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 40px; height: 40px; background: linear-gradient(135deg, #7f2ab3 0%, #f3be46 100%); color: white;" type="button" id="mobileSidebarToggle" aria-label="Toggle Sidebar">
                            <i class="fa-solid fa-bars fs-6"></i>
                        </button>
                        <h4 class="fw-bold mb-0 text-truncate" id="pageMainHeader" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: clamp(1.1rem, 4vw, 1.4rem); font-weight: 800 !important; line-height: 1.2;">Create Task</h4>
                    </div>
                    <div class="ms-2 d-flex align-items-center flex-shrink-0">
                        <!-- Language Selector Dropdown -->
                        <div class="me-3">
                            <select id="langSelect" class="form-select form-select-sm border-secondary-subtle rounded-3 shadow-sm" style="width: auto; font-family: 'Outfit', sans-serif; font-weight: 600; color: #2d064d; cursor: pointer;">
                                <option value="mr" selected>मराठी</option>
                                <option value="en">English</option>
                            </select>
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
                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            
                            <h3 id="mainPageTitle" class="fw-bold text-center mb-4 pb-2 position-relative" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: 2.2rem; letter-spacing: -0.5px;">
                                टप्पे तयार करा
                                <div class="bg-primary mx-auto mt-2" style="width: 80px; height: 4px; border-radius: 2px;"></div>
                            </h3>
                            
                            <form id="ceoCreateTaskForm">
                                
                                <!-- Card 1: मूलभूत माहिती -->
                                <div class="card border-0 shadow-sm p-4 mb-4 rounded-4 bg-white position-relative">
                                    <div class="text-center mb-4">
                                        <div id="basicInfoTitle" class="fw-bold mb-1" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: 1.35rem;">मूलभूत माहिती</div>
                                        <div class="bg-primary mx-auto" style="width: 80px; height: 4px; border-radius: 2px;"></div>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label id="workTypeLabel" for="workTypeSelect" class="form-label fw-bold" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: 0.95rem;">कामाचा प्रकार (Civilian / Non-Civilian) <span class="text-danger">*</span></label>
                                            <select id="workTypeSelect" class="form-select rounded-3 p-3 border-secondary-subtle fs-6" required>
                                                <option value="" disabled selected>- निवडा -</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label id="workNameLabel" for="workNameSelect" class="form-label fw-bold" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: 0.95rem;">कामाचे नाव <span class="text-danger">*</span></label>
                                            <select id="workNameSelect" class="form-select rounded-3 p-3 border-secondary-subtle fs-6" required>
                                                <option value="" disabled selected>-- नाव निवडा --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Card 2: टप्पे -->
                                <div class="card border-0 shadow-sm p-4 mb-4 rounded-4 bg-white position-relative">
                                    <div class="position-absolute" style="top: 25px; right: 25px; z-index: 10;">
                                        <div class="text-center px-3 py-2 border border-success rounded-3 bg-white" id="totalBadge" style="min-width: 80px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                                            <div id="totalBadgeText" class="small text-muted" style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">एकूण</div>
                                            <div class="fw-bold text-success fs-5" id="totalBadgePercent" style="font-family: 'Outfit', sans-serif;">0%</div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mb-4">
                                        <div id="stagesTitle" class="fw-bold mb-1" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: 1.35rem;">टप्पे</div>
                                        <div class="bg-primary mx-auto" style="width: 80px; height: 4px; border-radius: 2px;"></div>
                                    </div>
                                    
                                    <div class="row g-3 align-items-end mb-4">
                                        <div class="col-md-7">
                                            <label id="stageNameLabel" for="newStageName" class="form-label fw-bold" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: 0.95rem;">टप्प्याचे नाव (उदा. बेसमेंट काम)</label>
                                            <input type="text" id="newStageName" class="form-control rounded-3 p-3 border-secondary-subtle fs-6" placeholder="टप्प्याचे नाव (उदा. बेसमेंट काम)">
                                        </div>
                                        <div class="col-md-3 col-8">
                                            <label id="stagePctLabel" for="newStagePct" class="form-label fw-bold" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: 0.95rem;">टक्के</label>
                                            <div class="input-group">
                                                <input type="number" id="newStagePct" class="form-control rounded-start-3 p-3 border-secondary-subtle fs-6" placeholder="टक्के" min="0.1" max="100" step="0.1">
                                                <span class="input-group-text bg-light text-secondary rounded-end-3 px-3">%</span>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-4 text-end text-md-start">
                                            <button type="button" class="btn btn-success d-flex align-items-center justify-content-center p-0 rounded-3" id="btnAddStageBtn" style="width: 50px; height: 50px;">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Added Stages List -->
                                    <div id="addedStagesContainer" class="d-none mb-4">
                                        <div id="addedStagesLabelText" class="fw-bold mb-3" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: 0.95rem;">जोडलेले टप्पे:</div>
                                        <div id="addedStagesList"></div>
                                    </div>
                                    
                                    <!-- Warning / Success Alert -->
                                    <div class="alert alert-primary d-flex align-items-center border-start border-4 border-primary rounded-3" id="stagesAlert">
                                        <i class="fa-solid fa-location-dot text-primary me-3 fs-5" id="stagesAlertIcon"></i>
                                        <span id="stagesAlertText">सर्व टप्प्यांची टक्केवारी मिळून 100% झाली पाहिजे</span>
                                    </div>
                                </div>
                                
                                <!-- Card 3: शेरा -->
                                <div class="card border-0 shadow-sm p-4 mb-4 rounded-4 bg-white position-relative">
                                    <div class="text-center mb-4">
                                        <div id="remarksTitle" class="fw-bold mb-1" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: 1.35rem;">शेरा</div>
                                        <div class="bg-primary mx-auto" style="width: 80px; height: 4px; border-radius: 2px;"></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label id="remarksLabel" for="additionalNotes" class="form-label fw-bold" style="color: #2d064d; font-family: 'Outfit', sans-serif; font-size: 0.95rem;">अतिरिक्त माहिती</label>
                                        <textarea id="additionalNotes" class="form-control rounded-3 p-3 border-secondary-subtle fs-6" rows="4" maxlength="500" placeholder="येथे काही अतिरिक्त माहिती असल्यास लिहा..." style="resize: none;"></textarea>
                                        <div class="text-end text-muted mt-2" style="font-size: 0.85rem;"><span id="charCount" class="fw-semibold">0</span>/500</div>
                                    </div>
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="text-center mb-5">
                                    <button type="submit" class="btn btn-primary rounded-3 px-5 py-3 fw-bold fs-5 shadow-sm" id="btnSubmitWork">
                                        <i class="fa-solid fa-lock me-2"></i>काम तयार करा
                                    </button>
                                </div>
                                
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>

            <div class="ceo-fixed-footer">
                <?php include '../include/website_header.php'; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let allWorkTypes = [];
            let allWorkNames = [];
            let addedStages = [];

            const workTypeSelect = document.getElementById("workTypeSelect");
            const workNameSelect = document.getElementById("workNameSelect");
            const btnAddStageBtn = document.getElementById("btnAddStageBtn");
            const newStageName = document.getElementById("newStageName");
            const newStagePct = document.getElementById("newStagePct");
            const addedStagesContainer = document.getElementById("addedStagesContainer");
            const addedStagesList = document.getElementById("addedStagesList");
            const totalBadge = document.getElementById("totalBadge");
            const totalBadgePercent = document.getElementById("totalBadgePercent");
            const stagesAlert = document.getElementById("stagesAlert");
            const stagesAlertIcon = document.getElementById("stagesAlertIcon");
            const stagesAlertText = document.getElementById("stagesAlertText");
            const additionalNotes = document.getElementById("additionalNotes");
            const charCount = document.getElementById("charCount");
            const ceoCreateTaskForm = document.getElementById("ceoCreateTaskForm");
            const btnSubmitWork = document.getElementById("btnSubmitWork");

            // Language Translations Dictionary
            const translations = {
                mr: {
                    navbarHeader: "टप्पे तयार करा",
                    pageTitle: "टप्पे तयार करा",
                    basicInfo: "मूलभूत माहिती",
                    workType: 'कामाचा प्रकार (Civilian / Non-Civilian) <span class="text-danger">*</span>',
                    workTypePlaceholder: "- निवडा -",
                    workName: 'कामाचे नाव <span class="text-danger">*</span>',
                    workNamePlaceholder: "-- नाव निवडा --",
                    stagesTitle: "टप्पे",
                    stageNameLabel: "टप्प्याचे नाव (उदा. बेसमेंट काम)",
                    stageNamePlaceholder: "टप्प्याचे नाव (उदा. बेसमेंट काम)",
                    stagePctLabel: "टक्के",
                    stagePctPlaceholder: "टक्के",
                    addedStagesLabel: "जोडलेले टप्पे:",
                    alertDefault: "सर्व टप्प्यांची टक्केवारी मिळून 100% झाली पाहिजे",
                    alertSuccess: "✅ सर्व टप्प्यांची टक्केवारी जोडून 100% झाली आहे!",
                    alertExceed: "⚠️ एकूण टक्केवारी १००% पेक्षा जास्त झाली आहे (सध्या: {total}%)",
                    remarksTitle: "शेरा",
                    remarksLabel: "अतिरिक्त माहिती",
                    remarksPlaceholder: "येथे काही अतिरिक्त माहिती असल्यास लिहा...",
                    submitBtn: '<i class="fa-solid fa-lock me-2"></i>काम तयार करा',
                    submitLoading: '<i class="fa-solid fa-spinner fa-spin me-2"></i>काम जतन करत आहे...',
                    alertTotalBadge: "एकूण",
                    warnEmptyStageName: "कृपया टप्प्याचे नाव प्रविष्ट करा.",
                    warnInvalidPct: "कृपया १ ते १०० दरम्यानची वैध टक्केवारी प्रविष्ट करा.",
                    warnExceed100: "एकूण टक्केवारी १००% पेक्षा जास्त असू शकत नाही.",
                    warnTotalNot100: "सर्व टप्प्यांची एकूण टक्केवारी अचूक १००% असणे आवश्यक आहे.",
                    errRequiredFields: "कृपया सर्व आवश्यक फील्ड भरा.",
                    errServer: "सर्व्हरशी संपर्क साधताना अडचण आली.",
                    successTitle: "यशस्वी!",
                    successMsg: "काम आणि टप्पे यशस्वीरित्या तयार केले आहेत.",
                    warningTitle: "चेतावणी!",
                    errorTitle: "त्रुटी!",
                    confirmBtn: "ठीक आहे"
                },
                en: {
                    navbarHeader: "Create Task",
                    pageTitle: "Create Stages",
                    basicInfo: "Basic Information",
                    workType: 'Work Type (Civilian / Non-Civilian) <span class="text-danger">*</span>',
                    workTypePlaceholder: "- Select -",
                    workName: 'Work Name <span class="text-danger">*</span>',
                    workNamePlaceholder: "-- Select Name --",
                    stagesTitle: "Stages",
                    stageNameLabel: "Stage Name (e.g. Basement Work)",
                    stageNamePlaceholder: "Stage Name (e.g. Basement Work)",
                    stagePctLabel: "Percentage",
                    stagePctPlaceholder: "Percentage",
                    addedStagesLabel: "Added Stages:",
                    alertDefault: "Total percentage of all stages must equal 100%",
                    alertSuccess: "✅ Total percentage of all stages equals 100%!",
                    alertExceed: "⚠️ Total percentage exceeds 100% (Current: {total}%)",
                    remarksTitle: "Remarks",
                    remarksLabel: "Additional Notes",
                    remarksPlaceholder: "Write additional notes here if any...",
                    submitBtn: '<i class="fa-solid fa-lock me-2"></i>Create Work',
                    submitLoading: '<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving work...',
                    alertTotalBadge: "Total",
                    warnEmptyStageName: "Please enter the stage name.",
                    warnInvalidPct: "Please enter a valid percentage between 1 and 100.",
                    warnExceed100: "Total percentage cannot exceed 100%.",
                    warnTotalNot100: "The total percentage of all stages must be exactly 100%.",
                    errRequiredFields: "Please fill in all required fields.",
                    errServer: "An error occurred while contacting the server.",
                    successTitle: "Success!",
                    successMsg: "Work and stages have been created successfully.",
                    warningTitle: "Warning!",
                    errorTitle: "Error!",
                    confirmBtn: "OK"
                }
            };

            let currentLang = localStorage.getItem("ceo_create_work_lang") || "mr";
            const langSelect = document.getElementById("langSelect");

            const applyLanguage = (lang) => {
                currentLang = lang;
                localStorage.setItem("ceo_create_work_lang", lang);
                const t = translations[lang];

                // Update text contents
                document.getElementById("pageMainHeader").textContent = t.navbarHeader;
                document.getElementById("mainPageTitle").innerHTML = `${t.pageTitle} <div class="bg-primary mx-auto mt-2" style="width: 80px; height: 4px; border-radius: 2px;"></div>`;
                document.getElementById("basicInfoTitle").textContent = t.basicInfo;
                document.getElementById("workTypeLabel").innerHTML = t.workType;
                document.getElementById("workNameLabel").innerHTML = t.workName;
                document.getElementById("totalBadgeText").textContent = t.alertTotalBadge;
                document.getElementById("stagesTitle").textContent = t.stagesTitle;
                document.getElementById("stageNameLabel").textContent = t.stageNameLabel;
                newStageName.placeholder = t.stageNamePlaceholder;
                document.getElementById("stagePctLabel").textContent = t.stagePctLabel;
                newStagePct.placeholder = t.stagePctPlaceholder;
                document.getElementById("addedStagesLabelText").textContent = t.addedStagesLabel;
                document.getElementById("remarksTitle").textContent = t.remarksTitle;
                document.getElementById("remarksLabel").textContent = t.remarksLabel;
                additionalNotes.placeholder = t.remarksPlaceholder;
                
                if (!btnSubmitWork.disabled) {
                    btnSubmitWork.innerHTML = t.submitBtn;
                } else {
                    btnSubmitWork.innerHTML = t.submitLoading;
                }

                // Populate placeholders in select selectors if nothing is selected
                if (!workTypeSelect.value) {
                    workTypeSelect.innerHTML = `<option value="" disabled selected>${t.workTypePlaceholder}</option>`;
                    if (allWorkTypes.length > 0) {
                        allWorkTypes.forEach(type => {
                            const opt = document.createElement("option");
                            opt.value = type.id;
                            opt.textContent = type.work_type_name;
                            workTypeSelect.appendChild(opt);
                        });
                    }
                } else {
                    // Update text content of placeholder option index 0
                    workTypeSelect.options[0].textContent = t.workTypePlaceholder;
                }
                
                if (!workNameSelect.value) {
                    workNameSelect.innerHTML = `<option value="" disabled selected>${t.workNamePlaceholder}</option>`;
                } else {
                    workNameSelect.options[0].textContent = t.workNamePlaceholder;
                }

                updateStagesState();
            };

            langSelect.value = currentLang;
            langSelect.addEventListener("change", (e) => {
                applyLanguage(e.target.value);
            });

            // Fetch Master Data
            fetch("api_get_work_masters.php")
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        allWorkNames = data.work_names;
                        allWorkTypes = data.work_types;

                        // Populate Work Types Dropdown dynamically
                        const t = translations[currentLang];
                        workTypeSelect.innerHTML = `<option value="" disabled selected>${t.workTypePlaceholder}</option>`;
                        allWorkTypes.forEach(type => {
                            const opt = document.createElement("option");
                            opt.value = type.id;
                            opt.textContent = type.work_type_name;
                            workTypeSelect.appendChild(opt);
                        });
                    } else {
                        console.error("Failed to load master work names");
                    }
                })
                .catch(err => console.error("Error fetching master work data:", err));

            // On Work Type Change
            workTypeSelect.addEventListener("change", () => {
                const selectedTypeId = workTypeSelect.value;
                const t = translations[currentLang];
                workNameSelect.innerHTML = `<option value="" disabled selected>${t.workNamePlaceholder}</option>`;

                const filtered = allWorkNames.filter(wn => wn.work_type_id == selectedTypeId);
                filtered.forEach(wn => {
                    const opt = document.createElement("option");
                    opt.value = wn.id;
                    opt.textContent = wn.work_name;
                    workNameSelect.appendChild(opt);
                });
            });

            // Update Total Percentage & Alerts
            const updateStagesState = () => {
                const t = translations[currentLang];
                let total = 0;
                addedStages.forEach(s => total += s.percentage);
                
                total = parseFloat(total.toFixed(2));
                totalBadgePercent.textContent = total + "%";

                // Update badge and alert styles
                if (total === 100) {
                    totalBadge.className = "text-center px-3 py-2 border border-success rounded-3 bg-white";
                    totalBadgePercent.className = "fw-bold text-success fs-5";
                    
                    stagesAlert.className = "alert alert-success d-flex align-items-center border-start border-4 border-success rounded-3";
                    stagesAlertIcon.className = "fa-solid fa-circle-check me-3 fs-5 text-success";
                    stagesAlertText.textContent = t.alertSuccess;
                } else if (total > 100) {
                    totalBadge.className = "text-center px-3 py-2 border border-danger rounded-3 bg-white";
                    totalBadgePercent.className = "fw-bold text-danger fs-5";
                    
                    stagesAlert.className = "alert alert-danger d-flex align-items-center border-start border-4 border-danger rounded-3";
                    stagesAlertIcon.className = "fa-solid fa-circle-exclamation me-3 fs-5 text-danger";
                    stagesAlertText.textContent = t.alertExceed.replace("{total}", total);
                } else {
                    totalBadge.className = "text-center px-3 py-2 border border-success rounded-3 bg-white";
                    totalBadgePercent.className = "fw-bold text-success fs-5";
                    
                    stagesAlert.className = "alert alert-primary d-flex align-items-center border-start border-4 border-primary rounded-3";
                    stagesAlertIcon.className = "fa-solid fa-location-dot text-primary me-3 fs-5";
                    stagesAlertText.textContent = t.alertDefault;
                }

                // Render List
                if (addedStages.length > 0) {
                    addedStagesContainer.classList.remove("d-none");
                    addedStagesList.innerHTML = "";
                    addedStages.forEach((stage, idx) => {
                        const div = document.createElement("div");
                        div.className = "d-flex align-items-center justify-content-between p-3 bg-light border border-secondary-subtle rounded-3 mb-2";
                        div.innerHTML = `
                            <span class="fw-semibold text-dark">${idx + 1}. ${stage.name}</span>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-primary-subtle text-primary p-2 fs-6 fw-bold">${stage.percentage}%</span>
                                <button type="button" class="btn btn-link text-danger p-1 btn-delete-stage" data-index="${idx}">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        `;
                        addedStagesList.appendChild(div);
                    });

                    // Add delete listeners
                    document.querySelectorAll(".btn-delete-stage").forEach(btn => {
                        btn.addEventListener("click", function() {
                            const index = parseInt(this.getAttribute("data-index"));
                            addedStages.splice(index, 1);
                            updateStagesState();
                        });
                    });
                } else {
                    addedStagesContainer.classList.add("d-none");
                    addedStagesList.innerHTML = "";
                }
            };

            // Add Stage Clicked
            btnAddStageBtn.addEventListener("click", () => {
                const t = translations[currentLang];
                const nameVal = newStageName.value.trim();
                const pctVal = parseFloat(newStagePct.value);

                if (!nameVal) {
                    Swal.fire({
                        title: t.warningTitle,
                        text: t.warnEmptyStageName,
                        icon: 'warning',
                        confirmButtonText: t.confirmBtn
                    });
                    return;
                }

                if (isNaN(pctVal) || pctVal <= 0 || pctVal > 100) {
                    Swal.fire({
                        title: t.warningTitle,
                        text: t.warnInvalidPct,
                        icon: 'warning',
                        confirmButtonText: t.confirmBtn
                    });
                    return;
                }

                // Calculate current total
                let currentTotal = addedStages.reduce((acc, curr) => acc + curr.percentage, 0);
                if (currentTotal + pctVal > 100.01) {
                    Swal.fire({
                        title: t.warningTitle,
                        text: t.warnExceed100,
                        icon: 'warning',
                        confirmButtonText: t.confirmBtn
                    });
                    return;
                }

                // Add stage
                addedStages.push({
                    name: nameVal,
                    percentage: pctVal
                });

                // Clear input
                newStageName.value = "";
                newStagePct.value = "";

                updateStagesState();
            });

            // Remarks Counter
            additionalNotes.addEventListener("input", function() {
                charCount.textContent = this.value.length;
            });

            // Form Submit
            ceoCreateTaskForm.addEventListener("submit", (e) => {
                e.preventDefault();
                const t = translations[currentLang];

                const schoolName = "ZP School Panhala";
                const assignedTo = "Headmaster";
                const workTypeId = workTypeSelect.value;
                const workNameId = workNameSelect.value;

                if (!workTypeId || !workNameId) {
                    Swal.fire({
                        title: t.errorTitle,
                        text: t.errRequiredFields,
                        icon: 'error',
                        confirmButtonText: t.confirmBtn
                    });
                    return;
                }

                let totalPct = addedStages.reduce((acc, curr) => acc + curr.percentage, 0);
                if (Math.abs(totalPct - 100) > 0.01) {
                    Swal.fire({
                        title: t.errorTitle,
                        text: t.warnTotalNot100,
                        icon: 'error',
                        confirmButtonText: t.confirmBtn
                    });
                    return;
                }

                btnSubmitWork.disabled = true;
                btnSubmitWork.innerHTML = t.submitLoading;

                const payload = {
                    school_name: schoolName,
                    assigned_to: assignedTo,
                    work_type_id: parseInt(workTypeId),
                    work_name_id: parseInt(workNameId),
                    additional_notes: additionalNotes.value.trim(),
                    stages: addedStages
                };

                fetch("api_create_work.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    btnSubmitWork.disabled = false;
                    btnSubmitWork.innerHTML = t.submitBtn;

                    if (data.status) {
                        Swal.fire({
                            title: t.successTitle,
                            text: t.successMsg,
                            icon: 'success',
                            confirmButtonText: t.confirmBtn
                        }).then(() => {
                            // Reset form
                            ceoCreateTaskForm.reset();
                            addedStages = [];
                            updateStagesState();
                            charCount.textContent = 0;
                        });
                    } else {
                        Swal.fire({
                            title: t.errorTitle,
                            text: data.message || (currentLang === 'mr' ? 'काम तयार करताना त्रुटी आली.' : 'An error occurred while creating work.'),
                            icon: 'error',
                            confirmButtonText: t.confirmBtn
                        });
                    }
                })
                .catch(err => {
                    btnSubmitWork.disabled = false;
                    btnSubmitWork.innerHTML = t.submitBtn;
                    console.error("Submission error:", err);
                    Swal.fire({
                        title: t.errorTitle,
                        text: t.errServer,
                        icon: 'error',
                        confirmButtonText: t.confirmBtn
                    });
                });
            });

            // Initial Language Trigger
            applyLanguage(currentLang);

            // Mobile sidebar toggle logic
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            if (mobileSidebarToggle && sidebar) {
                mobileSidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('active');
                });
                
                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 991 && sidebar.classList.contains('active')) {
                        if (!sidebar.contains(e.target) && e.target !== mobileSidebarToggle) {
                            sidebar.classList.remove('active');
                        }
                    }
                });
            }

            // Highlight active sidebar item
            const currentPath = window.location.pathname.split('/').pop() || 'ceo_dashboard.php';
            const sidebarLinks = document.querySelectorAll('#sidebar ul li a');
            sidebarLinks.forEach(link => {
                const linkPath = link.getAttribute('href');
                if (linkPath === currentPath) {
                    link.parentElement.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
