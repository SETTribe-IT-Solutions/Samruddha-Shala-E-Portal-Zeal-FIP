// HM Progress Update Controller

let currentSchoolFilter = 'all';
let schoolSearchQuery = '';

// Toggle mobile sidebar
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

// Update UI Alert Badges & Notifications Bell for HM
function updateAlertBadges() {
    const totalAlerts = db.alerts.length;
    const headerBadge = document.getElementById('alertsHeaderBadge');
    const bellsBadgeText = document.getElementById('notifBellCountText');

    if (headerBadge) {
        if (totalAlerts > 0) {
            headerBadge.textContent = totalAlerts;
            headerBadge.classList.remove('d-none');
        } else {
            headerBadge.classList.add('d-none');
        }
    }

    if (bellsBadgeText) bellsBadgeText.textContent = `${totalAlerts} Alerts`;

    renderNotificationsBellDropdown();
}

// Render drop-down quick links inside HM header bell
function renderNotificationsBellDropdown() {
    const list = document.getElementById('notifBellList');
    if (!list) return;
    list.innerHTML = '';

    if (db.alerts.length === 0) {
        list.innerHTML = `<li class="text-center py-4 text-muted"><i class="fa-regular fa-circle-check fs-2 mb-2 text-success"></i><br>No alerts active</li>`;
        return;
    }

    db.alerts.slice(0, 5).forEach(alert => {
        let badgeClass = 'bg-secondary';
        let iconClass = 'fa-circle-question';
        if (alert.type === 'blocker') { badgeClass = 'bg-danger'; iconClass = 'fa-circle-xmark'; }
        else if (alert.type === 'delay') { badgeClass = 'bg-warning text-dark'; iconClass = 'fa-triangle-exclamation'; }
        else if (alert.type === 'geotag') { badgeClass = 'bg-dark'; iconClass = 'fa-location-dot'; }
        else if (alert.type === 'pending') { badgeClass = 'bg-info text-dark'; iconClass = 'fa-envelope-open-text'; }
        else if (alert.type === 'task') { badgeClass = 'bg-primary'; iconClass = 'fa-file-signature'; }

        const li = document.createElement('li');
        li.className = "px-3 py-2 border-bottom hover-bg";
        li.style.cursor = "pointer";
        li.onclick = () => {
            if (alert.type === 'task' || alert.type === 'geotag' || alert.type === 'blocker') {
                const select = document.getElementById('hmSchoolSelect');
                if (select) {
                    select.value = alert.school_id;
                    loadHMSchoolSpecificDetails(alert.school_id);
                }
            } else {
                window.location.href = 'hm_dashboard.php?view=history';
            }
        };
        li.innerHTML = `
            <div class="d-flex align-items-start">
                <span class="badge ${badgeClass} p-2 me-2 mt-1"><i class="fa-solid ${iconClass}"></i></span>
                <div style="font-size: 0.85rem;">
                    <span class="fw-bold d-block text-truncate" style="max-width: 200px;">${alert.school_name}</span>
                    <span class="text-muted d-block text-truncate" style="max-width: 220px;">${alert.title}</span>
                    <small class="text-muted" style="font-size:0.75rem;">${alert.date}</small>
                </div>
            </div>
        `;
        list.appendChild(li);
    });
}

// Initialize active school select
function renderHMReportPortal() {
    const select = document.getElementById('hmSchoolSelect');
    if (!select) return;

    // Save current selected value if any
    const prevSelected = select.value;

    select.innerHTML = '';

    db.schools.forEach(school => {
        const opt = document.createElement('option');
        opt.value = school.id;
        opt.textContent = school.name;
        select.appendChild(opt);
    });

    if (prevSelected && db.schools.find(s => s.id === prevSelected)) {
        select.value = prevSelected;
    } else if (db.schools.length > 0) {
        // Read URL query parameter for pre-selection
        const params = new URLSearchParams(window.location.search);
        const urlSchoolId = params.get('school_id');
        if (urlSchoolId && db.schools.find(s => s.id === urlSchoolId)) {
            select.value = urlSchoolId;
        } else {
            select.value = db.schools[0].id;
        }
        loadHMSchoolSpecificDetails(select.value);
    }
}

// Search and Filter logic for Assigned Works
function setSchoolFilter(filter, el) {
    currentSchoolFilter = filter;
    document.querySelectorAll('.filter-pill').forEach(pill => pill.classList.remove('active'));
    el.classList.add('active');
    renderHMSchoolList();
}

function filterHMSchools() {
    const input = document.getElementById('hmSchoolSearch');
    schoolSearchQuery = input ? input.value.toLowerCase().trim() : '';
    renderHMSchoolList();
}

function renderHMSchoolList() {
    const container = document.getElementById('hmSchoolList');
    if (!container) return;
    container.innerHTML = '';

    const select = document.getElementById('hmSchoolSelect');
    const activeSchoolId = select ? select.value : '';

    const filtered = db.schools.filter(school => {
        // Status filter
        if (currentSchoolFilter === 'active' && (school.progress === 100 || school.blocker !== 'None')) return false;
        if (currentSchoolFilter === 'completed' && school.progress < 100) return false;
        if (currentSchoolFilter === 'blocked' && school.blocker === 'None') return false;

        // Search query filter
        if (schoolSearchQuery) {
            const matchesName = school.name.toLowerCase().includes(schoolSearchQuery);
            const matchesBlock = school.block.toLowerCase().includes(schoolSearchQuery);
            const matchesType = school.work_type.toLowerCase().includes(schoolSearchQuery);
            return matchesName || matchesBlock || matchesType;
        }
        return true;
    });

    const countBadge = document.getElementById('worksCountBadge');
    if (countBadge) {
        countBadge.textContent = `${filtered.length} School${filtered.length !== 1 ? 's' : ''}`;
    }

    if (filtered.length === 0) {
        container.innerHTML = `<div class="text-center py-4 text-muted"><i class="fa-regular fa-folder-open fs-3 mb-2"></i><br>No matching works found.</div>`;
        return;
    }

    filtered.forEach(school => {
        const isActive = school.id === activeSchoolId;
        const card = document.createElement('div');
        card.id = `school-card-${school.id}`;
        card.className = `school-list-card ${isActive ? 'active' : ''}`;
        card.onclick = () => {
            if (select) {
                select.value = school.id;
                loadHMSchoolSpecificDetails(school.id);
            }
        };

        let progressBadgeStyle = 'bg-info-soft text-info border border-info';
        if (school.progress === 100) progressBadgeStyle = 'bg-success-soft text-success border border-success';
        else if (school.blocker !== 'None') progressBadgeStyle = 'bg-danger-soft text-danger border border-danger';

        card.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-1">
                <strong class="text-dark" style="font-size: 0.9rem;">${school.name}</strong>
                <span class="badge ${progressBadgeStyle} px-2 py-1" style="font-size: 0.72rem; font-weight: 700;">${school.progress}%</span>
            </div>
            <div class="d-flex justify-content-between text-muted" style="font-size: 0.78rem;">
                <span><i class="fa-solid fa-location-dot me-1"></i>${school.block} Block</span>
                <span>Category: ${school.work_type}</span>
            </div>
            <div class="d-flex justify-content-between mt-1 pt-1 border-top border-light text-muted" style="font-size: 0.75rem;">
                <span>Budget: ₹${school.budget.toFixed(1)}L</span>
                <span>Spent: ₹${school.spent.toFixed(1)}L</span>
            </div>
        `;
        container.appendChild(card);
    });
}

// Load Details for Selected School
function loadHMSchoolSpecificDetails(schoolId) {
    const school = db.schools.find(s => s.id === schoolId);
    if (!school) return;

    // Highlight the active card in the scroll list
    document.querySelectorAll('.school-list-card').forEach(c => c.classList.remove('active'));
    const activeCard = document.getElementById(`school-card-${school.id}`);
    if (activeCard) activeCard.classList.add('active');

    // Sync select dropdown in background
    const select = document.getElementById('hmSchoolSelect');
    if (select) select.value = schoolId;

    // Update active school indicators
    const nameEl = document.getElementById('hmSelectedSchoolName');
    const blockEl = document.getElementById('hmSelectedBlockBadge');
    if (nameEl) nameEl.textContent = school.name;
    if (blockEl) blockEl.textContent = `${school.block} Block`;

    // Category and source inputs
    const workTypeEl = document.getElementById('hmWorkType');
    const fundingSourceEl = document.getElementById('hmFundingSource');
    if (workTypeEl) workTypeEl.value = school.work_type;
    if (fundingSourceEl) fundingSourceEl.value = school.funding_source;

    // Task instruction box banner
    const taskBanner = document.getElementById('hmTaskAlertBanner');
    const taskText = document.getElementById('hmTaskAlertText');
    if (taskBanner && taskText) {
        if (school.task_status === 'Pending HM Action' && school.task_description) {
            taskText.textContent = `${school.task_description} (Allotted Budget: ₹${school.task_budget || school.budget}L)`;
            taskBanner.classList.remove('d-none');
        } else {
            taskBanner.classList.add('d-none');
        }
    }

    // Set slider value
    const slider = document.getElementById('hmProgressRange');
    if (slider) {
        slider.value = school.progress;
        updateHMProgressSliderText(school.progress);
    }

    // Spent amounts
    const spentEl = document.getElementById('hmSpentAmount');
    if (spentEl) spentEl.value = school.spent || '';

    // Blocker selector
    const blockerSel = document.getElementById('hmBlockerSelector');
    if (blockerSel) {
        blockerSel.value = school.blocker || 'None';
        toggleHMBlockerDetailsInput(blockerSel.value);
    }
    const blockerDetailsEl = document.getElementById('hmBlockerDetails');
    if (blockerDetailsEl) blockerDetailsEl.value = school.blocker_details || '';

    // Remarks
    const remarksEl = document.getElementById('hmRemarks');
    if (remarksEl) remarksEl.value = '';

    // Pre-fill coordinates
    const geotagEl = document.getElementById('hmGeotagInput');
    if (geotagEl) geotagEl.value = school.geo_tag || 'Missing';

    const coordinatesEl = document.getElementById('hmGeoCoordinates');
    if (coordinatesEl) {
        if (school.geo_tag === 'Tagged' && school.latitude && school.longitude) {
            coordinatesEl.value = `${school.latitude}, ${school.longitude}`;
        } else {
            coordinatesEl.value = '';
        }
    }

    // Reset image preview or show school photo if available
    const preview = document.getElementById('photoPreview');
    const uploadText = document.getElementById('uploadZoneText');
    const uploadIcon = document.getElementById('uploadZoneIcon');
    if (school.photo) {
        if (preview) {
            preview.src = school.photo;
            preview.classList.remove('d-none');
        }
        if (uploadText) uploadText.textContent = "Photo uploaded";
        if (uploadIcon) uploadIcon.className = "fa-solid fa-circle-check fs-3 mb-2 text-success";
    } else {
        if (preview) {
            preview.className = "upload-preview d-none";
            preview.src = "#";
        }
        if (uploadText) uploadText.textContent = "Click to select site photo proof";
        if (uploadIcon) uploadIcon.className = "fa-solid fa-cloud-arrow-up fs-3 mb-2 text-primary";
    }
    const photoFileEl = document.getElementById('hmPhotoFile');
    if (photoFileEl) photoFileEl.value = '';
}

function updateHMProgressSliderText(val) {
    const label = document.getElementById('hmProgressValueText');
    if (label) label.textContent = `${val}%`;
}

function toggleHMBlockerDetailsInput(val) {
    const container = document.getElementById('hmBlockerDetailsContainer');
    if (container) {
        if (val !== 'None') {
            container.classList.remove('d-none');
        } else {
            container.classList.add('d-none');
        }
    }
}

// Simulating Photo upload selection
function triggerPhotoUpload() {
    const photoFileEl = document.getElementById('hmPhotoFile');
    if (photoFileEl) photoFileEl.click();
}

function previewHMUploadedPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById('photoPreview');
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
            const uploadText = document.getElementById('uploadZoneText');
            if (uploadText) uploadText.textContent = input.files[0].name;
            const uploadIcon = document.getElementById('uploadZoneIcon');
            if (uploadIcon) {
                uploadIcon.className = "fa-solid fa-circle-check fs-3 mb-2 text-success";
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function captureGeoTaggedPhoto() {
    const coordInput = document.getElementById('hmGeoCoordinates');
    if (!coordInput) return;

    if (!navigator.geolocation) {
        coordInput.value = '16.7050, 74.2433';
        document.getElementById('hmGeotagInput').value = 'Tagged';
        alert('Geolocation is not supported by this browser. Using Kolhapur fallback coordinates.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude.toFixed(4);
            const lng = position.coords.longitude.toFixed(4);
            coordInput.value = `${lat}, ${lng}`;
            document.getElementById('hmGeotagInput').value = 'Tagged';
        },
        () => {
            coordInput.value = '16.7050, 74.2433';
            document.getElementById('hmGeotagInput').value = 'Tagged';
            alert('Location permission was denied. Using Kolhapur fallback coordinates.');
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

// Submit HM Report form
function handleHMUpdateSubmit(e) {
    e.preventDefault();

    const schoolId = document.getElementById('hmSchoolSelect').value;
    const school = db.schools.find(s => s.id === schoolId);
    if (!school) return;

    const oldProgress = school.progress;
    const newProgress = parseInt(document.getElementById('hmProgressRange').value);
    const remarks = document.getElementById('hmRemarks').value;
    const blocker = document.getElementById('hmBlockerSelector').value;
    const blocker_details = document.getElementById('hmBlockerDetails').value;
    const geo_tag = document.getElementById('hmGeotagInput').value;
    const spentAmount = parseFloat(document.getElementById('hmSpentAmount').value || 0);
    const budgetNotes = document.getElementById('hmBudgetNotes').value.trim();
    const coordText = document.getElementById('hmGeoCoordinates').value.trim();

    // Photo preview checking
    const preview = document.getElementById('photoPreview');
    const photoSrc = preview ? preview.src : '';
    if (!photoSrc || photoSrc === '#' || photoSrc.endsWith('#')) {
        alert('Please upload or capture a photo proof before submitting the completion report.');
        return;
    }

    // Coordinates requirement check
    if (!coordText) {
        alert('Please capture GPS Geo-Tagging Coordinates before submitting.');
        return;
    }

    // Geotag generation
    let lat = "";
    let lng = "";
    if (geo_tag === 'Tagged') {
        const parts = coordText.split(',');
        lat = parts[0] ? parts[0].trim() : '16.7050';
        lng = parts[1] ? parts[1].trim() : '74.2433';
    }

    if (spentAmount > 0) {
        if (spentAmount > school.budget) {
            alert(`Spent amount (₹${spentAmount}L) cannot exceed school budget (₹${school.budget}L).`);
            return;
        }
        school.spent = Math.max(school.spent, spentAmount);
        school.remarks = budgetNotes || remarks || school.remarks;
    }

    // Create Pending approval log entry
    const newPending = {
        id: "PEND-" + Date.now(),
        school_id: schoolId,
        school_name: school.name,
        work_type: school.work_type,
        old_progress: oldProgress,
        new_progress: newProgress,
        remarks: remarks,
        blocker: blocker,
        blocker_details: blocker_details,
        geo_tag: geo_tag,
        latitude: lat,
        longitude: lng,
        photo: photoSrc,
        submitted_at: new Date().toISOString().split('T')[0]
    };

    db.pending.unshift(newPending);
    if (school.task_status === 'Pending HM Action') {
        school.task_status = 'Pending Sachiv Review';
    }

    alert(`Completion report submitted! Waiting verification from Sachiv desk.\n\nOpen 'sachiv_dashboard.php' to review the geo-tagged proof.`);

    saveDatabase();

    // Redirect to Dashboard Submission History
    window.location.href = 'hm_dashboard.php?view=history';
}

// Initial setup on page load
window.addEventListener('DOMContentLoaded', () => {
    initDatabase();
    updateAlertBadges();
    renderHMReportPortal();
    renderHMSchoolList();
});

// React on database updates
window.addEventListener('db_updated', () => {
    updateAlertBadges();
    renderHMSchoolList();
});

// Listen to storage events for cross-tab synchronizations
window.addEventListener('storage', (e) => {
    if (e.key === 'eportal_schools' || e.key === 'eportal_pending') {
        db.schools = JSON.parse(localStorage.getItem('eportal_schools'));
        db.pending = JSON.parse(localStorage.getItem('eportal_pending'));
        calculateAlerts();
        updateAlertBadges();
        renderHMSchoolList();
    }
});
