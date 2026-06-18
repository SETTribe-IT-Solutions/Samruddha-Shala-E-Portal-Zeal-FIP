// Sachiv Dashboard Controller

// UI alerts badges counts updates for Sachiv
function updateAlertBadges() {
    const pendingCount = db.pending.length;

    // Sidebar counters for Sachiv Queue
    const sachivQueueBadge = document.getElementById('sachivSidebarQueueBadge');
    const sachivPendingText = document.getElementById('sachiv-pending-queue-count');

    if (sachivQueueBadge) {
        if (pendingCount > 0) {
            sachivQueueBadge.textContent = pendingCount;
            sachivQueueBadge.classList.remove('d-none');
        } else {
            sachivQueueBadge.classList.add('d-none');
        }
    }

    if (sachivPendingText) {
        sachivPendingText.textContent = `${pendingCount} Pending Verification`;
    }
}

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

    if (id === 'sachiv-queue-view') {
        renderSachivQueue();
    } else if (id === 'sachiv-schools-view') {
        renderSachivSchoolsPerformance();
    }
}

// --- SACHIV REVIEW QUEUE ---
function renderSachivQueue() {
    const container = document.getElementById('sachivQueueContainer');
    if (!container) return;
    container.innerHTML = '';
    populateSachivReminderSchoolOptions();
    renderSachivReminders();

    if (db.pending.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5 bg-white border rounded">
                <i class="fa-regular fa-circle-check fs-1 text-success mb-3"></i>
                <h5>All Clear!</h5>
                <p class="text-muted">No progress update records are pending approval at this time.</p>
            </div>
        `;
        return;
    }

    db.pending.forEach(item => {
        const card = document.createElement('div');
        card.className = "card border-start border-4 border-info shadow-sm p-4 mb-4 bg-white";
        
        // Photo check
        const imgHtml = item.photo 
            ? `<img src="${item.photo}" class="img-fluid rounded mb-3" style="max-height: 200px; object-fit: cover; width: 100%;" alt="Progress Photo Proof">`
            : `<div class="bg-light text-muted py-5 rounded text-center mb-3"><i class="fa-regular fa-image fs-1 mb-2"></i><br>No Photo uploaded</div>`;

        let blockerAlertHtml = '';
        if (item.blocker !== 'None') {
            blockerAlertHtml = `
                <div class="alert alert-danger border-0 py-2 small mb-3">
                    <strong>⚠️ Active Blocker Reported:</strong> ${item.blocker}<br>
                    <small>${item.blocker_details}</small>
                </div>
            `;
        }

        card.innerHTML = `
            <div class="row">
                <div class="col-md-7">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary text-uppercase">${item.work_type}</span>
                        <span class="text-muted small">Submitted: ${item.submitted_at}</span>
                    </div>
                    <h4 class="fw-bold text-dark mb-3">${item.school_name}</h4>
                    
                    <div class="d-flex align-items-center bg-light p-3 rounded mb-3">
                        <div class="text-center px-3">
                            <span class="text-muted small d-block">Old Stage</span>
                            <h4 class="mb-0 text-secondary fw-bold">${item.old_progress}%</h4>
                        </div>
                        <div class="px-2 text-muted"><i class="fa-solid fa-arrow-right-long fs-4"></i></div>
                        <div class="text-center px-3">
                            <span class="text-muted small d-block">Proposed</span>
                            <h4 class="mb-0 text-success fw-bold">${item.new_progress}%</h4>
                        </div>
                    </div>

                    ${blockerAlertHtml}

                    <div class="mb-3">
                        <strong>HM Remarks:</strong>
                        <p class="text-muted bg-light p-3 rounded border-start border-3 border-secondary mb-0" style="font-size:0.9rem; font-style:italic;">
                            "${item.remarks}"
                        </p>
                    </div>

                    <div class="d-flex">
                        <button class="btn btn-success me-2 px-4 py-2" onclick="approvePendingUpdate('${item.id}')"><i class="fa-solid fa-check me-2"></i> Approve & Sync</button>
                        <button class="btn btn-outline-danger px-4 py-2" onclick="rejectPendingUpdate('${item.id}')"><i class="fa-solid fa-xmark me-2"></i> Decline</button>
                    </div>
                </div>
                <div class="col-md-5 mt-3 mt-md-0">
                    <strong>Photo Proof Attached:</strong>
                    <div class="mt-2">
                        ${imgHtml}
                    </div>
                    <strong>Geo-Tag Check:</strong>
                    <div class="mt-2 small text-muted">
                        ${item.geo_tag === 'Tagged' 
                            ? `<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Verified Coordinates: ${item.latitude || '18.3414'}, ${item.longitude || '73.9926'}</span>`
                            : '<span class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Location tags disabled by device!</span>'}
                    </div>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

function populateSachivReminderSchoolOptions() {
    const select = document.getElementById('sachivReminderSchool');
    if (!select) return;
    select.innerHTML = '';
    db.schools.forEach(school => {
        const option = document.createElement('option');
        option.value = school.id;
        option.textContent = `${school.name} (${school.block})`;
        select.appendChild(option);
    });
}

function handleReminderDispatchSubmit(e) {
    e.preventDefault();
    const schoolId = document.getElementById('sachivReminderSchool').value;
    const school = db.schools.find(s => s.id === schoolId);
    if (!school) return;

    const alertType = document.getElementById('sachivReminderType').value;
    const title = document.getElementById('sachivReminderTitle').value.trim();
    const message = document.getElementById('sachivReminderMessage').value.trim();

    if (!message) {
        alert('Please enter a reminder or blocker message.');
        return;
    }

    db.reminders.unshift({
        id: 'REM-' + Date.now(),
        school_id: schoolId,
        school_name: school.name,
        title: title || (alertType === 'blocker' ? 'Blocker Alert' : 'Reminder Alert'),
        message,
        alert_type: alertType,
        created_at: new Date().toISOString().split('T')[0]
    });

    saveDatabase();
    alert(`Alert sent to ${school.name} and visible to CEO and HM dashboards.`);
}

function renderSachivReminders() {
    const list = document.getElementById('sachivReminderList');
    if (!list) return;
    list.innerHTML = '';

    if (db.reminders.length === 0) {
        list.innerHTML = '<div class="text-muted">No reminder alerts sent yet.</div>';
        return;
    }

    db.reminders.slice(0, 5).forEach(item => {
        const row = document.createElement('div');
        row.className = 'border rounded p-2 mb-2';
        row.innerHTML = `
            <div class="fw-semibold">${item.school_name}</div>
            <div class="text-muted small">${item.title}</div>
            <div class="small">${item.message}</div>
        `;
        list.appendChild(row);
    });
}

function approvePendingUpdate(pendingId) {
    const item = db.pending.find(p => p.id === pendingId);
    if (!item) return;

    // Update school table data
    const school = db.schools.find(s => s.id === item.school_id);
    if (school) {
        school.progress = item.new_progress;
        school.remarks = item.remarks;
        school.blocker = item.blocker;
        school.blocker_details = item.blocker_details;
        school.last_update = new Date().toISOString().split('T')[0];
        if (item.geo_tag === 'Tagged') {
            school.geo_tag = 'Tagged';
            school.latitude = item.latitude || '18.3414';
            school.longitude = item.longitude || '73.9926';
        }
        if (item.photo) {
            school.photo = item.photo;
        }
    }

    // Remove from queue
    db.pending = db.pending.filter(p => p.id !== pendingId);
    
    alert(`Approved successfully! School records and database updated.`);
    saveDatabase();
}

function rejectPendingUpdate(pendingId) {
    if (confirm("Are you sure you want to decline this progress log? Remarks will be requested back from Head Master.")) {
        db.pending = db.pending.filter(p => p.id !== pendingId);
        alert("Progress log declined.");
        saveDatabase();
    }
}

// --- SACHIV SCHOOL PERFORMANCE GRID ---
function renderSachivSchoolsPerformance() {
    const tbody = document.getElementById('sachivPerformanceTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    db.schools.forEach(school => {
        const utilRate = school.budget ? Math.round((school.spent / school.budget) * 100) : 0;
        
        let progressBadge = 'bg-danger';
        if (school.progress >= 80) progressBadge = 'bg-success';
        else if (school.progress >= 40) progressBadge = 'bg-warning text-dark';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${school.name}</strong><br><small class="text-muted">${school.block}</small></td>
            <td><span class="badge bg-primary-soft">${school.work_type}</span></td>
            <td>
                <span class="badge ${progressBadge} mb-1">${school.progress}% Completion</span>
                <div class="progress" style="height: 6px; width: 150px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: ${school.progress}%"></div>
                </div>
            </td>
            <td>₹${school.budget.toFixed(2)} Lakhs</td>
            <td>₹${school.spent.toFixed(2)} Lakhs</td>
            <td>
                <div class="d-flex align-items-center">
                    <span class="me-2 fw-semibold">${utilRate}%</span>
                    <div class="progress flex-grow-1" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: ${utilRate}%"></div>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Initial setup on page load
window.addEventListener('DOMContentLoaded', () => {
    initDatabase();
    updateAlertBadges();
    renderActiveViewData();
});

// React on database updates
window.addEventListener('db_updated', () => {
    updateAlertBadges();
    renderActiveViewData();
});
// Listen to storage events for cross-tab synchronizations
window.addEventListener('storage', (e) => {
    if (e.key === 'eportal_schools' || e.key === 'eportal_pending') {
        db.schools = JSON.parse(localStorage.getItem('eportal_schools'));
        db.pending = JSON.parse(localStorage.getItem('eportal_pending'));
        calculateAlerts();
        updateAlertBadges();
        renderActiveViewData();
    }
});
