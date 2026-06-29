// HM Dashboard Controller

// Toggle mobile sidebar
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

// Switching Sub-tabs/Views
function switchTab(tabId) {
    if (tabId.endsWith('-view')) {
        tabId = tabId.replace('-view', '');
    }
    if (tabId.startsWith('hm-')) {
        tabId = tabId.replace('hm-', '');
    }

    // Hide all tab containers
    document.querySelectorAll('.view-panel').forEach(div => div.classList.add('d-none'));

    // Show selected container
    const activePanel = document.getElementById(`hm-${tabId}-view`);
    if (activePanel) activePanel.classList.remove('d-none');

    // De-activate all custom navigation pill tab links
    document.querySelectorAll('#hmDashboardTabs button').forEach(btn => btn.classList.remove('active'));

    // Set active state on target nav link pill
    const navLink = document.getElementById(`nav-hm-${tabId}`);
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
        renderRecentNotifications();
    } else if (id === 'hm-history-view') {
        renderHMHistoryTimeline();
    }
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
                switchTab('hm-report');
                const select = document.getElementById('hmSchoolSelect');
                if (select) {
                    select.value = alert.school_id;
                    loadHMSchoolSpecificDetails(alert.school_id);
                }
            } else {
                switchTab('hm-history');
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

// Populate the active school dropdown selector on dashboard
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
        select.value = db.schools[0].id;
        loadHMSchoolSpecificDetails(db.schools[0].id);
    }
}

// Load Details for Selected School
function loadHMSchoolSpecificDetails(schoolId) {
    const school = db.schools.find(s => s.id === schoolId);
    if (!school) return;

    // Sync select dropdown in background
    const select = document.getElementById('hmSchoolSelect');
    if (select) select.value = schoolId;

    // Update top KPI cards
    const kpiAllocatedBudget = document.getElementById('kpiAllocatedBudget');
    const kpiAmountSpent = document.getElementById('kpiAmountSpent');
    const kpiCompletionProgress = document.getElementById('kpiCompletionProgress');
    const kpiCompletionBar = document.getElementById('kpiCompletionBar');
    const kpiBlockerText = document.getElementById('kpiBlockerText');
    const kpiBlockerDetailsText = document.getElementById('kpiBlockerDetailsText');
    const kpiBlockerCard = document.getElementById('kpiBlockerCard');
    const chartPercentText = document.getElementById('chartPercentText');

    if (kpiAllocatedBudget) kpiAllocatedBudget.textContent = school.budget.toFixed(2);
    if (kpiAmountSpent) kpiAmountSpent.textContent = school.spent.toFixed(2);
    if (kpiCompletionProgress) kpiCompletionProgress.textContent = school.progress;
    if (kpiCompletionBar) kpiCompletionBar.style.width = school.progress + '%';
    if (chartPercentText) chartPercentText.textContent = `${school.progress}%`;

    if (kpiBlockerText && kpiBlockerCard) {
        const kpiBlockerIcon = document.getElementById('kpiBlockerIcon');
        if (school.blocker && school.blocker !== 'None') {
            kpiBlockerText.textContent = school.blocker;
            kpiBlockerText.style.fontSize = '1.1rem';
            if (kpiBlockerDetailsText) kpiBlockerDetailsText.textContent = school.blocker_details || 'Blocker reported';
            kpiBlockerCard.style.borderColor = 'rgba(239, 68, 68, 0.4)';
            kpiBlockerCard.style.background = 'linear-gradient(180deg, #ffffff 0%, #fef2f2 100%)';
            if (kpiBlockerIcon) {
                kpiBlockerIcon.className = 'hm-kpi-icon bg-danger-soft text-danger';
            }
        } else {
            kpiBlockerText.textContent = 'None';
            kpiBlockerText.style.fontSize = '1.4rem';
            if (kpiBlockerDetailsText) kpiBlockerDetailsText.textContent = 'No project blockers reported';
            kpiBlockerCard.style.borderColor = 'rgba(228, 230, 239, 0.8)';
            kpiBlockerCard.style.background = '#ffffff';
            if (kpiBlockerIcon) {
                kpiBlockerIcon.className = 'hm-kpi-icon bg-success-soft text-success';
            }
        }
    }

    // Re-render Visualizations
    renderHMProgressChart(school.progress);
    renderHMFundChart(school.budget, school.spent);
}

// Redirect helper to open the reporting form with the currently selected school
function redirectToUpdateProgress() {
    const select = document.getElementById('hmSchoolSelect');
    const schoolId = select ? select.value : '';
    if (schoolId) {
        window.location.href = `hm_update_work_progress.php?school_id=${schoolId}`;
    } else {
        window.location.href = 'hm_update_work_progress.php';
    }
}

// --- HM HISTORY TIMELINE ---
function renderHMHistoryTimeline() {
    const container = document.getElementById('hmTimelineContainer');
    if (!container) return;
    container.innerHTML = '';

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
    historyList.sort((a, b) => new Date(b.date) - new Date(a.date));

    if (historyList.length === 0) {
        container.innerHTML = `<p class="text-muted">No update history logged yet.</p>`;
        return;
    }

    historyList.forEach(item => {
        const div = document.createElement('div');
        div.className = `timeline-item ${item.type === 'approved' ? 'completed' : 'pending'}`;
        div.innerHTML = `
            <div class="mb-1 d-flex justify-content-between align-items-center">
                <small class="text-muted fw-bold">${item.date}</small>
                <span>${item.badge}</span>
            </div>
            <h6 class="fw-bold text-dark mb-1">${item.school_name}</h6>
            <p class="mb-1 text-primary fw-semibold" style="font-size:0.9rem;">${item.title}</p>
            <p class="text-muted small mb-0 font-italic">"${item.desc || 'No remarks provided.'}"</p>
        `;
        container.appendChild(div);
    });
}

// Render dynamic doughnut chart ring for progress
function renderHMProgressChart(progress) {
    const ctx = document.getElementById('hmProgressChart');
    if (!ctx) return;

    if (window.hmChart) {
        window.hmChart.destroy();
    }

    window.hmChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Remaining'],
            datasets: [{
                data: [progress, 100 - progress],
                backgroundColor: ['#06b6d4', '#e2e8f0'],
                hoverBackgroundColor: ['#0891b2', '#cbd5e1'],
                borderWidth: 0,
                cutout: '78%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` ${context.label}: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });
}

// Render dynamic comparison chart for budget vs spent
function renderHMFundChart(allocated, spent) {
    const ctx = document.getElementById('hmFundChart');
    if (!ctx) return;

    if (window.hmFundChartInstance) {
        window.hmFundChartInstance.destroy();
    }

    window.hmFundChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Allotted Budget', 'Amount Utilized'],
            datasets: [{
                data: [allocated, spent],
                backgroundColor: ['#6420a5', '#10b981'],
                hoverBackgroundColor: ['#4c1482', '#059669'],
                borderRadius: 8,
                barThickness: 20,
                maxBarThickness: 24
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` ₹${context.raw.toFixed(2)} Lakhs`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { display: false },
                    ticks: {
                        callback: function(value) {
                            return '₹' + value + 'L';
                        }
                    }
                },
                y: {
                    grid: { display: false }
                }
            }
        }
    });
}

// Render recent alerts timeline feed
function renderRecentNotifications() {
    const feed = document.getElementById('hmAlertsFeed');
    if (!feed) return;
    feed.innerHTML = '';

    if (db.alerts.length === 0) {
        feed.innerHTML = `<div class="text-center py-4 text-muted"><i class="fa-regular fa-circle-check fs-3 mb-2 text-success"></i><br>No active alerts reported.</div>`;
        return;
    }

    db.alerts.forEach(alert => {
        let icon = 'fa-bell';
        let bg = 'bg-primary-soft text-primary border border-primary';
        if (alert.type === 'blocker') { icon = 'fa-triangle-exclamation'; bg = 'bg-danger-soft text-danger border border-danger'; }
        else if (alert.type === 'delay') { icon = 'fa-clock'; bg = 'bg-warning-soft text-warning border border-warning'; }
        else if (alert.type === 'geotag') { icon = 'fa-location-dot'; bg = 'bg-info-soft text-info border border-info'; }
        else if (alert.type === 'task') { icon = 'fa-file-signature'; bg = 'bg-success-soft text-success border border-success'; }

        const item = document.createElement('div');
        item.className = 'alert-timeline-item';
        item.innerHTML = `
            <div class="alert-timeline-icon ${bg}">
                <i class="fa-solid ${icon}"></i>
            </div>
            <div class="small">
                <div class="d-flex justify-content-between mb-0.5">
                    <strong class="text-dark" style="font-size: 0.82rem;">${alert.school_name}</strong>
                    <span class="text-muted" style="font-size: 0.72rem;">${alert.date}</span>
                </div>
                <span class="d-block fw-semibold text-primary" style="font-size: 0.78rem;">${alert.title}</span>
                <p class="text-muted mb-0" style="font-size: 0.75rem; line-height: 1.35;">${alert.title} alert was triggered under district rules.</p>
            </div>
        `;
        feed.appendChild(item);
    });
}

// Initial setup on page load
window.addEventListener('DOMContentLoaded', () => {
    initDatabase();
    updateAlertBadges();

    // Language selector initialization
    const langSelector = document.getElementById('langSelector');
    if (langSelector) {
        const savedLang = localStorage.getItem('hmLang') || 'en';
        langSelector.value = savedLang;
        setHMLanguage(savedLang);
    }

    // Check if view parameter is passed
    const params = new URLSearchParams(window.location.search);
    const requestedView = params.get('view');
    const viewMap = {
        report: 'hm-report',
        history: 'hm-history'
    };

    if (requestedView && viewMap[requestedView]) {
        switchTab(viewMap[requestedView]);
    } else {
        renderHMReportPortal();
        renderActiveViewData();
    }
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

// --- LANGUAGE TRANSLATION ENGINE ---
function setHMLanguage(lang) {
    const elements = document.querySelectorAll('[data-en][data-mr]');
    elements.forEach(el => {
        el.textContent = lang === 'mr' ? el.getAttribute('data-mr') : el.getAttribute('data-en');
    });
    localStorage.setItem('hmLang', lang);
}
