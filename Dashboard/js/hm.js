// HM Dashboard Controller

// Toggle mobile sidebar
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

// Switching Sub-tabs/Views
function switchTab(tabId) {
    // Hide all tab containers
    document.querySelectorAll('.view-panel').forEach(div => div.classList.add('d-none'));
    
    // Show selected container
    const activePanel = document.getElementById(`${tabId}-view`);
    if (activePanel) activePanel.classList.remove('d-none');

    // De-activate all sidebar nav links
    document.querySelectorAll('#sidebar ul li').forEach(li => li.classList.remove('active'));

    // Set active state on target nav link
    const navLink = document.getElementById(`nav-${tabId}`);
    if (navLink) navLink.classList.add('active');

    // Render specific components if needed (Charts, Grids)
    renderActiveViewData();
}

// Render data based on current tab selection
function renderActiveViewData() {
    const activePanel = document.querySelector('.view-panel:not(.d-none)');
    if (!activePanel) return;

    const id = activePanel.id;

    if (id === 'hm-report-view') {
        renderHMReportPortal();
    } else if (id === 'hm-history-view') {
        renderHMHistoryTimeline();
    } else if (id === 'hm-utilization-view') {
        // This view is rendered server-side by PHP form submission.
    }
}

// --- HM REPORT PORTAL ---
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
        loadHMSchoolSpecificDetails(db.schools[0].id);
    }
}

function loadHMSchoolSpecificDetails(schoolId) {
    const school = db.schools.find(s => s.id === schoolId);
    if (!school) return;

    const workTypeEl = document.getElementById('hmWorkType');
    const fundingSourceEl = document.getElementById('hmFundingSource');
    if (workTypeEl) workTypeEl.value = school.work_type;
    if (fundingSourceEl) fundingSourceEl.value = school.funding_source;

    const taskBox = document.getElementById('hmTaskNotificationText');
    if (taskBox) {
        if (school.task_status === 'Pending HM Action' && school.task_description) {
            taskBox.innerHTML = `<span class="fw-semibold">Task:</span> ${school.task_description}<br><span class="text-muted">Budget: ₹${school.task_budget || school.budget} Lakhs | Source: ${school.task_funding_source || school.funding_source}</span>`;
        } else {
            taskBox.textContent = 'No task assigned yet.';
        }
    }
    
    // Set slider to current value
    const slider = document.getElementById('hmProgressRange');
    if (slider) {
        slider.value = school.progress;
        updateHMProgressSliderText(school.progress);
    }

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

    // Pre-fill coordinate tag selection
    const geotagEl = document.getElementById('hmGeotagInput');
    if (geotagEl) geotagEl.value = school.geo_tag || 'Tagged';

    // Reset image preview
    const preview = document.getElementById('photoPreview');
    if (preview) {
        preview.className = "upload-preview d-none";
        preview.src = "";
    }
    const photoFileEl = document.getElementById('hmPhotoFile');
    if (photoFileEl) photoFileEl.value = '';

    // Update Right Side Detail Panel
    const summary = document.getElementById('hmSchoolSummaryPanel');
    if (summary) {
        summary.innerHTML = `
            <div class="mb-4">
                <h6 class="text-muted uppercase">Active Target</h6>
                <h5 class="fw-bold">${school.name}</h5>
                <span class="badge bg-secondary">${school.block} Block</span>
            </div>

            <div class="mb-3">
                <strong>Work Details:</strong>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>Work Category:</span>
                    <span class="fw-semibold">${school.work_type}</span>
                </div>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>Current Stage Progress:</span>
                    <span class="fw-bold text-primary">${school.progress}%</span>
                </div>
            </div>

            <div class="mb-3">
                <strong>Financials:</strong>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>Budget Allotted:</span>
                    <span class="fw-semibold">₹${school.budget} Lakhs</span>
                </div>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>Utilized / Released:</span>
                    <span class="fw-semibold text-success">₹${school.spent} Lakhs</span>
                </div>
            </div>

            <div>
                <strong>Active Blocker Status:</strong>
                <div class="mt-2">
                    ${school.blocker !== 'None' 
                        ? `<span class="badge bg-danger p-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>${school.blocker}</span>`
                        : '<span class="badge bg-success p-2"><i class="fa-solid fa-circle-check me-1"></i>No current blockers</span>'}
                </div>
            </div>
        `;
    }
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

// Simulating the Photo upload drag and drop trigger
function triggerPhotoUpload() {
    const photoFileEl = document.getElementById('hmPhotoFile');
    if (photoFileEl) photoFileEl.click();
}

function previewHMUploadedPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('photoPreview');
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function captureGeoTaggedPhoto() {
    const coordInput = document.getElementById('hmGeoCoordinates');
    if (!coordInput) return;

    if (!navigator.geolocation) {
        coordInput.value = '16.7050, 74.2433 (fallback)';
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
            coordInput.value = '16.7050, 74.2433 (fallback)';
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
    if (!photoSrc || photoSrc.endsWith('#')) {
        alert('Please upload or capture a photo proof before submitting the completion report.');
        return;
    }

    // Geotag generation
    let lat = "";
    let lng = "";
    if (geo_tag === 'Tagged') {
        if (coordText) {
            const [capturedLat, capturedLng] = coordText.split(',');
            lat = capturedLat ? capturedLat.trim() : '';
            lng = capturedLng ? capturedLng.trim() : '';
        } else {
            lat = '16.7050';
            lng = '74.2433';
        }
    }

    if (spentAmount > 0 && school.budget >= spentAmount) {
        school.spent = Math.max(school.spent, spentAmount);
        school.remarks = budgetNotes || school.remarks;
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
    loadHMSchoolSpecificDetails(schoolId);
}

// --- HM HISTORY TIMELINE ---
function renderHMHistoryTimeline() {
    const container = document.getElementById('hmTimelineContainer');
    if (!container) return;
    container.innerHTML = '';

    // Combine school logs history and pending entries
    const historyList = [];

    // Add pending items
    db.pending.forEach(p => {
        historyList.push({
            type: "pending",
            school_name: p.school_name,
            title: `Proposed Progress: ${p.old_progress}% → ${p.new_progress}%`,
            desc: p.remarks,
            date: p.submitted_at,
            badge: `<span class="badge bg-info text-dark">Awaiting Sachiv Verification</span>`
        });
    });

    // Add historical completed records from school database
    db.schools.forEach(s => {
        historyList.push({
            type: "approved",
            school_name: s.name,
            title: `Approved Milestones reached: ${s.progress}%`,
            desc: s.remarks,
            date: s.last_update,
            badge: `<span class="badge bg-success">Verification Approved</span>`
        });
    });

    // Sort by Date descending
    historyList.sort((a,b) => new Date(b.date) - new Date(a.date));

    if (historyList.length === 0) {
        container.innerHTML = `<p class="text-muted">No update history logged yet.</p>`;
        return;
    }

    historyList.forEach(item => {
        const div = document.createElement('div');
        div.className = `timeline-item ${item.type === 'approved' ? 'completed' : 'pending'}`;
        div.innerHTML = `
            <div class="mb-1">
                <small class="text-muted fw-bold">${item.date}</small>
                <span class="ms-2">${item.badge}</span>
            </div>
            <h6 class="fw-bold text-dark mb-1">${item.school_name}</h6>
            <p class="mb-1 text-primary fw-semibold" style="font-size:0.9rem;">${item.title}</p>
            <p class="text-muted small mb-0 font-italic">"${item.desc}"</p>
        `;
        container.appendChild(div);
    });
}

// Initial setup on page load
window.addEventListener('DOMContentLoaded', () => {
    initDatabase();
    renderHMReportPortal();
    renderActiveViewData();
});

// React on database updates
window.addEventListener('db_updated', () => {
    renderHMReportPortal();
    renderActiveViewData();
});
// Listen to storage events for cross-tab synchronizations
window.addEventListener('storage', (e) => {
    if (e.key === 'eportal_schools' || e.key === 'eportal_pending') {
        db.schools = JSON.parse(localStorage.getItem('eportal_schools'));
        db.pending = JSON.parse(localStorage.getItem('eportal_pending'));
        calculateAlerts();
        renderHMReportPortal();
        renderActiveViewData();
    }
});
