// CEO Dashboard Controller

// Chart pointers
let physicalProgressChartInstance = null;
let fundingChartInstance = null;
let detailedPhysicalChartInstance = null;
let detailedFundingChartInstance = null;

// UI alerts badges counts updates for CEO
function updateAlertBadges() {
    const totalAlerts = db.alerts.length;
    const blockerCount = db.alerts.filter(a => a.type === 'blocker').length;

    // Badges elements
    const sidebarBadge = document.getElementById('alertsSidebarBadge');
    const headerBadge = document.getElementById('alertsHeaderBadge');
    const kpiAlertsText = document.getElementById('kpi-alerts');
    const blockerCountText = document.getElementById('kpi-blocker-count-badge');
    const overviewBadge = document.getElementById('ceo-overview-alerts-pill');
    const bellsBadgeText = document.getElementById('notifBellCountText');
    const pulseDot = document.getElementById('kpi-pulse-dot');

    if (sidebarBadge) {
        if (totalAlerts > 0) {
            sidebarBadge.textContent = totalAlerts;
            sidebarBadge.classList.remove('d-none');
        } else {
            sidebarBadge.classList.add('d-none');
        }
    }

    if (headerBadge) {
        if (totalAlerts > 0) {
            headerBadge.textContent = totalAlerts;
            headerBadge.classList.remove('d-none');
        } else {
            headerBadge.classList.add('d-none');
        }
    }

    if (kpiAlertsText) kpiAlertsText.textContent = totalAlerts;
    if (blockerCountText) blockerCountText.textContent = `${blockerCount} Blockers Active`;
    if (overviewBadge) overviewBadge.textContent = `${totalAlerts} Active Alerts`;
    if (bellsBadgeText) bellsBadgeText.textContent = `${totalAlerts} Alerts`;

    if (pulseDot) {
        if (blockerCount > 0) {
            pulseDot.style.display = 'inline-block';
        } else {
            pulseDot.style.display = 'none';
        }
    }

    // Update notifications dropdown lists
    renderNotificationsBellDropdown();
}

// Render drop-down quick links
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

        const li = document.createElement('li');
        li.className = "px-3 py-2 border-bottom hover-bg";
        li.style.cursor = "pointer";
        li.onclick = () => {
            switchTab('ceo-alerts');
            filterAlerts(alert.type);
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

    if (id === 'ceo-overview-view') {
        renderCEOOverview();
    } else if (id === 'ceo-task-view') {
        renderCEOTaskAssignmentView();
    } else if (id === 'ceo-physical-view') {
        renderCEOPhysical();
    } else if (id === 'ceo-alerts-view') {
        renderCEOAlerts();
    }
}

// --- CEO OVERVIEW ---
function renderCEOOverview() {
    const totalWorks = db.schools.length;
    const avgProgress = Math.round(db.schools.reduce((acc, curr) => acc + curr.progress, 0) / totalWorks);
    
    const totalAllocated = db.schools.reduce((acc, curr) => acc + curr.budget, 0);
    const totalSpent = db.schools.reduce((acc, curr) => acc + curr.spent, 0);
    const utilizationRate = Math.round((totalSpent / totalAllocated) * 100);

    // Set text counters
    document.getElementById('kpi-total-works').textContent = totalWorks;
    document.getElementById('kpi-overall-progress').textContent = `${avgProgress}%`;
    document.getElementById('kpi-progress-bar').style.width = `${avgProgress}%`;
    document.getElementById('kpi-progress-bar').setAttribute('aria-valuenow', avgProgress);
    document.getElementById('kpi-funding').textContent = `₹${totalAllocated.toFixed(1)} L`;
    document.getElementById('kpi-funding-utilization').textContent = `${utilizationRate}%`;

    // Alerts lists integration
    const alertsList = document.getElementById('overviewAlertsContainer');
    alertsList.innerHTML = '';
    
    if (db.alerts.length === 0) {
        alertsList.innerHTML = `<div class="text-center py-4 text-muted">No pending alerts. System clear!</div>`;
    } else {
        db.alerts.slice(0, 3).forEach(alert => {
            let borderClass = 'border-secondary';
            let badgeBg = 'bg-secondary';
            if (alert.type === 'blocker') { borderClass = 'border-danger'; badgeBg = 'bg-danger'; }
            else if (alert.type === 'delay') { borderClass = 'border-warning'; badgeBg = 'bg-warning text-dark'; }
            else if (alert.type === 'geotag') { borderClass = 'border-secondary'; badgeBg = 'bg-secondary'; }
            else if (alert.type === 'pending') { borderClass = 'border-info'; badgeBg = 'bg-info text-dark'; }

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert-item ${borderClass}`;
            alertDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <span class="badge ${badgeBg} me-3 text-uppercase" style="font-size: 0.65rem;">${alert.type}</span>
                    <div>
                        <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">${alert.school_name}</h6>
                        <small class="text-muted" style="font-size: 0.75rem;">${alert.title}</small>
                    </div>
                </div>
                <span class="text-muted small">${alert.date}</span>
            `;
            alertsList.appendChild(alertDiv);
        });
    }

    // High Priority interventions Table (Blockers and Delayed progress items)
    const interventionTableBody = document.getElementById('overviewHighPriorityTable');
    interventionTableBody.innerHTML = '';

    const criticalSchools = db.schools.filter(s => s.blocker !== "None" || s.progress < 50).slice(0, 4);

    if (criticalSchools.length === 0) {
        interventionTableBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-3">No schools require immediate physical intervention.</td></tr>`;
    } else {
        criticalSchools.forEach(school => {
            let blockerBadge = `<span class="badge bg-secondary">None</span>`;
            if (school.blocker !== "None") {
                blockerBadge = `<span class="badge bg-danger">${school.blocker}</span>`;
            } else if (school.progress < 30) {
                blockerBadge = `<span class="badge bg-warning text-dark">Slight Progress</span>`;
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong class="text-primary" style="cursor:pointer;" onclick="openProjectModal('${school.id}')">${school.name}</strong></td>
                <td>${school.work_type}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="me-2 text-muted small">${school.progress}%</span>
                        <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: ${school.progress}%"></div>
                        </div>
                    </div>
                </td>
                <td>${blockerBadge}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-light" onclick="openProjectModal('${school.id}')"><i class="fa-regular fa-eye"></i></button>
                </td>
            `;
            interventionTableBody.appendChild(tr);
        });
    }

    // Setup Chart.js previews
    setupOverviewCharts();

    // Render Active Task Queue
    renderCEOTaskSummary();
}

function setupOverviewCharts() {
    // Destroy existing instances if they exist
    if (physicalProgressChartInstance) physicalProgressChartInstance.destroy();
    if (fundingChartInstance) fundingChartInstance.destroy();

    // Calculate values
    const categories = ["Classrooms", "Toilets", "Fencing", "Water Facilities"];
    const averages = categories.map(cat => {
        const matches = db.schools.filter(s => s.work_type === cat);
        return matches.length ? Math.round(matches.reduce((sum, s) => sum + s.progress, 0) / matches.length) : 0;
    });

    // Draw Physical Chart
    const ctx1 = document.getElementById('overviewPhysicalChart').getContext('2d');
    physicalProgressChartInstance = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: categories,
            datasets: [{
                label: 'Average Progress %',
                data: averages,
                backgroundColor: ['#0d6efd', '#10b981', '#f59e0b', '#06b6d4'],
                borderRadius: 6,
                borderWidth: 0,
                barThickness: 35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    ticks: { callback: value => value + '%' }
                }
            }
        }
    });

    // Funding sources percentages
    const sources = ["Annual Plan", "Minor Mineral Fund", "ZP Own Fund", "CSR Fund"];
    const fundingSums = sources.map(src => {
        return db.schools.filter(s => s.funding_source === src).reduce((sum, s) => sum + s.budget, 0);
    });

    const ctx2 = document.getElementById('overviewFundingChart').getContext('2d');
    fundingChartInstance = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: sources,
            datasets: [{
                data: fundingSums,
                backgroundColor: ['#6366f1', '#f59e0b', '#3b82f6', '#10b981'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

// --- CEO TASK ASSIGNMENT ---
function renderCEOTaskAssignmentView() {
    populateCEOAssignTaskSchools();
    renderCEOTaskSummary();
}

function renderCEOTaskSummary() {
    const summary = document.getElementById('ceoTaskSummary');
    if (!summary) return;
    summary.innerHTML = '';

    const pendingTasks = db.schools.filter(s => s.task_status === 'Pending HM Action');
    if (pendingTasks.length === 0) {
        summary.innerHTML = `<div class="text-muted text-center py-3">No active CEO task is pending HM action right now.</div>`;
        return;
    }

    let tableHTML = `
        <div class="table-responsive" style="max-height: 500px;">
            <table class="table table-sm table-hover align-middle bg-white border mb-0" style="font-size: 0.85rem;">
                <thead class="table-light sticky-top">
                    <tr>
                        <th scope="col" style="width: 40%">School</th>
                        <th scope="col" style="width: 20%">Task</th>
                        <th scope="col" style="width: 15%">Budget</th>
                        <th scope="col" style="width: 25%">Status</th>
                    </tr>
                </thead>
                <tbody>
    `;

    pendingTasks.forEach(school => {
        tableHTML += `
            <tr>
                <td>
                    <div class="fw-semibold text-dark text-wrap text-break">${school.name}</div>
                    <div class="small text-muted">${school.block}</div>
                </td>
                <td><span class="badge bg-primary-soft text-primary text-wrap">${school.task_title || school.work_type}</span></td>
                <td>₹${school.task_budget}L</td>
                <td><span class="badge bg-warning text-dark text-wrap lh-base">${school.task_status}</span></td>
            </tr>
        `;
    });

    tableHTML += `
                </tbody>
            </table>
        </div>
    `;
    summary.innerHTML = tableHTML;
}

function populateCEOAssignTaskSchools() {
    const select = document.getElementById('ceoTaskSchoolSelect');
    if (!select) return;
    
    select.innerHTML = '';

    db.schools.forEach(school => {
        const option = document.createElement('option');
        option.value = school.id;
        option.textContent = `${school.name} (${school.block})`;
        select.appendChild(option);
    });

    // Dynamically populate work type dropdown from db.work_types
    populateWorkTypeDropdowns();
}

function handleAssignTaskSubmit(e) {
    e.preventDefault();

    const schoolId = document.getElementById('ceoTaskSchoolSelect').value;
    const school = db.schools.find(s => s.id === schoolId);
    if (!school) return;

    const workType = document.getElementById('ceoTaskWorkType').value;
    const budget = parseFloat(document.getElementById('ceoTaskBudget').value || 0);
    const fundingSource = document.getElementById('ceoTaskFundingSource').value;
    const description = document.getElementById('ceoTaskDescription').value.trim();

    if (!description || Number.isNaN(budget) || budget <= 0) {
        alert('Please enter a valid task description and budget before assigning the work.');
        return;
    }

    const submitBtn = e.target.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Assigning...';
    }

    const formData = new FormData();
    formData.append('school_name', school.name);
    formData.append('work_type', workType);
    formData.append('budget_lakhs', budget);
    formData.append('funding_source', fundingSource);
    formData.append('task_description', description);

    fetch('ceo_assign_task_db.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Assign Task';
        }
        
        if (data.status) {
            school.work_type = workType;
            school.budget = budget;
            school.funding_source = fundingSource;
            school.task_title = workType;
            school.task_description = description;
            school.task_budget = budget;
            school.task_funding_source = fundingSource;
            school.task_status = 'Pending HM Action';
            school.task_assigned_at = new Date().toISOString().split('T')[0];
            school.progress = 0;
            school.blocker = 'None';
            school.blocker_details = '';
            school.geo_tag = 'Missing';
            school.latitude = '';
            school.longitude = '';
            school.remarks = 'Task assigned by CEO. HM to review and start execution.';

            saveDatabase();
            document.getElementById('ceoAssignTaskForm').reset();
            showSuccessPopup('Task assign successfully.');
            renderCEOTaskSummary();
        } else {
            alert('Failed to save to database: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Assign Task';
        }
        alert('An error occurred while saving. Please try again.');
    });
}

function showSuccessPopup(message) {
    let modalHtml = `
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-5">
                    <i class="fa-solid fa-circle-check text-success" style="font-size: 4rem;"></i>
                    <h4 class="mt-4 fw-bold">Success!</h4>
                    <p class="text-muted fs-5">${message}</p>
                    <button type="button" class="btn btn-primary px-4 mt-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>`;
    
    let modalEl = document.getElementById('successModal');
    if (modalEl) {
        modalEl.remove();
    }
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    modalEl = document.getElementById('successModal');
    
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

// --- CEO PHYSICAL PROGRESS DETAIL ---
function renderCEOPhysical() {
    const categories = ["Classrooms", "Toilets", "Fencing", "Water Facilities"];
    
    categories.forEach(cat => {
        const list = db.schools.filter(s => s.work_type === cat);
        const count = list.length;
        const avg = count ? Math.round(list.reduce((sum, s) => sum + s.progress, 0) / count) : 0;
        
        const catLower = cat.toLowerCase().replace(" ", "");
        document.getElementById(`phys-cnt-${catLower}`).textContent = `${count} Works`;
        document.getElementById(`phys-avg-${catLower}`).textContent = `${avg}%`;
        document.getElementById(`phys-bar-${catLower}`).style.width = `${avg}%`;
    });

    // Multi-bar breakdown: Stages (<30%, 30-70%, 70-99%, 100%)
    if (detailedPhysicalChartInstance) detailedPhysicalChartInstance.destroy();

    const datasets = [
        { label: 'Not Started (<30%)', data: [], backgroundColor: '#ef4444' },
        { label: 'In Progress (30-70%)', data: [], backgroundColor: '#f59e0b' },
        { label: 'Finishing Phase (70-99%)', data: [], backgroundColor: '#06b6d4' },
        { label: 'Completed (100%)', data: [], backgroundColor: '#10b981' }
    ];

    categories.forEach(cat => {
        const list = db.schools.filter(s => s.work_type === cat);
        datasets[0].data.push(list.filter(s => s.progress < 30).length);
        datasets[1].data.push(list.filter(s => s.progress >= 30 && s.progress <= 70).length);
        datasets[2].data.push(list.filter(s => s.progress > 70 && s.progress < 100).length);
        datasets[3].data.push(list.filter(s => s.progress === 100).length);
    });

    const ctx = document.getElementById('detailedPhysicalChart').getContext('2d');
    detailedPhysicalChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categories,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true },
                y: { stacked: true, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

// --- CEO FUNDING DISTRIBUTION DETAIL ---
function renderCEOFunding() {
    const sources = ["Annual Plan", "Minor Mineral Fund", "ZP Own Fund", "CSR Fund"];
    const glowRow = document.getElementById('fundingSourceGlowRow');
    glowRow.innerHTML = '';

    const tableBody = document.getElementById('detailedFundingTable');
    tableBody.innerHTML = '';

    const colors = ['#6366f1', '#f59e0b', '#3b82f6', '#10b981'];
    const fundingSums = [];

    sources.forEach((src, idx) => {
        const list = db.schools.filter(s => s.funding_source === src);
        const allocated = list.reduce((sum, s) => sum + s.budget, 0);
        const spent = list.reduce((sum, s) => sum + s.spent, 0);
        const utilization = allocated ? Math.round((spent / allocated) * 100) : 0;
        fundingSums.push(allocated);

        // Glow Cards
        const col = document.createElement('div');
        col.className = 'col-md-6 col-lg-3';
        col.innerHTML = `
            <div class="card p-3 border-top border-4" style="border-top-color: ${colors[idx]} !important;">
                <h6 class="text-muted text-uppercase mb-1" style="font-size:0.75rem;">${src}</h6>
                <h3 class="fw-bold mb-2">₹${allocated.toFixed(2)} L</h3>
                <div class="d-flex justify-content-between text-muted small">
                    <span>Spent: ₹${spent.toFixed(2)} L</span>
                    <span class="fw-semibold text-dark">${utilization}% Utilized</span>
                </div>
            </div>
        `;
        glowRow.appendChild(col);

        // Table rows
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><i class="fa-solid fa-circle me-2" style="color: ${colors[idx]};"></i><strong>${src}</strong></td>
            <td>₹${allocated.toFixed(2)} Lakhs</td>
            <td>₹${spent.toFixed(2)} Lakhs</td>
            <td>
                <div class="d-flex align-items-center">
                    <span class="me-2 fw-semibold text-dark">${utilization}%</span>
                    <div class="progress flex-grow-1" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: ${utilization}%; background-color: ${colors[idx]};"></div>
                    </div>
                </div>
            </td>
        `;
        tableBody.appendChild(tr);
    });

    // Detailed chart
    if (detailedFundingChartInstance) detailedFundingChartInstance.destroy();

    const ctx = document.getElementById('detailedFundingChart').getContext('2d');
    detailedFundingChartInstance = new Chart(ctx, {
        type: 'polarArea',
        data: {
            labels: sources,
            datasets: [{
                data: fundingSums,
                backgroundColor: [
                    'rgba(99, 102, 241, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(16, 185, 129, 0.7)'
                ],
                borderColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

// --- CEO ALERTS & NOTIFICATIONS CENTER ---
let currentAlertFilter = 'all';
function renderCEOAlerts() {
    const feed = document.getElementById('detailedAlertsContainer');
    feed.innerHTML = '';

    // Update sub-category badges counts
    document.getElementById('alerts-cnt-all').textContent = db.alerts.length;
    document.getElementById('alerts-cnt-blocker').textContent = db.alerts.filter(a => a.type === 'blocker').length;
    document.getElementById('alerts-cnt-delay').textContent = db.alerts.filter(a => a.type === 'delay').length;
    document.getElementById('alerts-cnt-geotag').textContent = db.alerts.filter(a => a.type === 'geotag').length;
    document.getElementById('alerts-cnt-pending').textContent = db.alerts.filter(a => a.type === 'pending').length;

    const filteredAlerts = currentAlertFilter === 'all' 
        ? db.alerts 
        : db.alerts.filter(a => a.type === currentAlertFilter);

    if (filteredAlerts.length === 0) {
        feed.innerHTML = `
            <div class="text-center py-5 border rounded bg-white">
                <i class="fa-regular fa-circle-check fs-1 text-success mb-3"></i>
                <h5>No Alerts Active</h5>
                <p class="text-muted mb-0">All school projects conform to the timeline standards in this category.</p>
            </div>
        `;
        return;
    }

    filteredAlerts.forEach(alert => {
        let borderClass = 'border-secondary';
        let iconHtml = '<i class="fa-solid fa-circle-info fs-4"></i>';
        let actionBtnHtml = '';

        if (alert.type === 'blocker') {
            borderClass = 'border-danger';
            iconHtml = '<i class="fa-solid fa-circle-xmark fs-4 text-danger"></i>';
            actionBtnHtml = `<button class="btn btn-sm btn-outline-danger me-2" onclick="initiateIntervention('${alert.school_id}')"><i class="fa-solid fa-phone me-1"></i> Contact HM</button>`;
        } else if (alert.type === 'delay') {
            borderClass = 'border-warning';
            iconHtml = '<i class="fa-solid fa-triangle-exclamation fs-4 text-warning"></i>';
            actionBtnHtml = `<button class="btn btn-sm btn-outline-warning" onclick="sendWarningNotification('${alert.school_id}')"><i class="fa-solid fa-envelope me-1"></i> Send Push Warning</button>`;
        } else if (alert.type === 'geotag') {
            borderClass = 'border-secondary';
            iconHtml = '<i class="fa-solid fa-location-dot fs-4 text-secondary"></i>';
        } else if (alert.type === 'pending') {
            borderClass = 'border-info';
            iconHtml = '<i class="fa-solid fa-envelope-open-text fs-4 text-info"></i>';
            actionBtnHtml = `
                <a href="sachiv_dashboard.php" class="btn btn-sm btn-outline-info text-decoration-none me-2">
                    <i class="fa-solid fa-clipboard-check me-1"></i> Go to Sachiv Portal
                </a>
            `;
        } else if (alert.type === 'task') {
            borderClass = 'border-primary';
            iconHtml = '<i class="fa-solid fa-file-signature fs-4 text-primary"></i>';
        }

        const card = document.createElement('div');
        card.className = `alert-item bg-white shadow-sm border-start border-4 ${borderClass} mb-3 p-3`;
        card.innerHTML = `
            <div class="d-flex align-items-start flex-grow-1">
                <div class="me-3 mt-1">${iconHtml}</div>
                <div>
                    <span class="badge bg-light text-dark mb-1">${alert.school_name}</span>
                    <h6 class="fw-bold mb-1">${alert.title}</h6>
                    <p class="text-muted mb-0 small" style="max-width: 600px;">${alert.desc}</p>
                </div>
            </div>
            <div class="text-end ms-3">
                <small class="text-muted d-block mb-2">${alert.date}</small>
                <div class="d-flex justify-content-end">
                    ${actionBtnHtml}
                    <button class="btn btn-sm btn-light" onclick="openProjectModal('${alert.school_id}')">Details</button>
                </div>
            </div>
        `;
        feed.appendChild(card);
    });
}

function filterAlerts(type) {
    currentAlertFilter = type;
    
    // Highlight button active state
    const buttons = document.querySelectorAll('#alertsNavPills button');
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('onclick').includes(`'${type}'`)) {
            btn.classList.add('active');
        }
    });

    renderCEOAlerts();
}

function initiateIntervention(schoolId) {
    const school = db.schools.find(s => s.id === schoolId);
    alert(`Initiating administrative support contact to ${school.name}.\n\nMessage generated: "District CEO requested urgent clearance report for ${school.work_type} due to reported blocker (${school.blocker})."`);
}

function sendWarningNotification(schoolId) {
    const school = db.schools.find(s => s.id === schoolId);
    alert(`Sending automated system warning to HM of ${school.name} regarding delays. Last reported stage of progress: ${school.progress}%.`);
}

// --- CEO MONITOR TABLE ---
function renderCEOMonitorTable() {
    const tbody = document.getElementById('schoolProjectsTableBody');
    tbody.innerHTML = '';

    db.schools.forEach(school => {
        // Determine alert badges
        let alertHtml = '<span class="badge bg-success">No Alerts</span>';
        if (school.blocker !== 'None') {
            alertHtml = `<span class="badge bg-danger"><i class="fa-solid fa-circle-xmark me-1"></i>Blocker</span>`;
        } else if (school.geo_tag === 'Missing') {
            alertHtml = `<span class="badge bg-secondary"><i class="fa-solid fa-location-dot me-1"></i>Geo-Tag Missing</span>`;
        } else {
            // Check delays
            const currentDate = new Date("2026-06-18");
            const lastUpd = new Date(school.last_update);
            const diffTime = Math.abs(currentDate - lastUpd);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            if (diffDays > 30 && school.progress < 90) {
                alertHtml = `<span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i>Delayed</span>`;
            }
        }

        // Geo-tag coordinate string
        const coordStr = school.geo_tag === 'Tagged' 
            ? `<span class="badge bg-light text-dark"><i class="fa-solid fa-circle-check text-success me-1"></i>${school.latitude}, ${school.longitude}</span>`
            : '<span class="badge bg-danger-soft text-danger"><i class="fa-solid fa-location-pin-lock me-1"></i>Missing</span>';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong class="text-dark">${school.name}</strong></td>
            <td>${school.block}</td>
            <td><span class="badge bg-primary-soft">${school.work_type}</span></td>
            <td><span class="badge bg-light text-dark">${school.funding_source}</span></td>
            <td>
                <div class="d-flex align-items-center">
                    <span class="me-2 fw-semibold" style="font-size:0.85rem;">${school.progress}%</span>
                    <div class="progress flex-grow-1" style="height: 6px; width: 80px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: ${school.progress}%" aria-valuenow="${school.progress}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </td>
            <td>${coordStr}</td>
            <td>${alertHtml}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-primary" onclick="openProjectModal('${school.id}')"><i class="fa-solid fa-eye me-1"></i> Details</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function filterSchoolsTable() {
    const query = document.getElementById('schoolSearchInput').value.toLowerCase();
    const workType = document.getElementById('filterWorkType').value;
    const fundingSource = document.getElementById('filterFundingSource').value;
    const alertStatus = document.getElementById('filterAlertStatus').value;

    const rows = document.querySelectorAll('#schoolProjectsTableBody tr');

    db.schools.forEach((school, index) => {
        const row = rows[index];
        if (!row) return;

        // Match search query
        const matchesQuery = school.name.toLowerCase().includes(query) || school.block.toLowerCase().includes(query);
        
        // Match Work Type
        const matchesWorkType = !workType || school.work_type === workType;

        // Match Funding Source
        const matchesFunding = !fundingSource || school.funding_source === fundingSource;

        // Match Alert status
        let matchesAlert = true;
        if (alertStatus) {
            if (alertStatus === 'Blocker') {
                matchesAlert = school.blocker !== 'None';
            } else if (alertStatus === 'MissingGeotag') {
                matchesAlert = school.geo_tag === 'Missing';
            } else if (alertStatus === 'Delay') {
                const currentDate = new Date("2026-06-18");
                const lastUpd = new Date(school.last_update);
                const diffTime = Math.abs(currentDate - lastUpd);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                matchesAlert = diffDays > 30 && school.progress < 90;
            } else if (alertStatus === 'Clean') {
                // All clear
                const currentDate = new Date("2026-06-18");
                const lastUpd = new Date(school.last_update);
                const diffTime = Math.abs(currentDate - lastUpd);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                matchesAlert = school.blocker === 'None' && school.geo_tag !== 'Missing' && !(diffDays > 30 && school.progress < 90);
            }
        }

        if (matchesQuery && matchesWorkType && matchesFunding && matchesAlert) {
            row.classList.remove('d-none');
        } else {
            row.classList.add('d-none');
        }
    });
}

function exportToCSV() {
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "School Name,Block / Taluka,Work Type,Funding Source,Progress Percentage,Geo-tag Status,Blocker Status,Last Update\n";

    db.schools.forEach(s => {
        csvContent += `"${s.name}","${s.block}","${s.work_type}","${s.funding_source}",${s.progress}%,"${s.geo_tag}","${s.blocker}","${s.last_update}"\n`;
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "samruddha_shala_works_report.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// ==============================================
// DETAILS MODAL POPUP
// ==============================================

function openProjectModal(schoolId) {
    const school = db.schools.find(s => s.id === schoolId);
    if (!school) return;

    const modalBody = document.getElementById('projectModalBody');
    
    let blockerHtml = `<span class="badge bg-success">No Blockers Reported</span>`;
    if (school.blocker !== 'None') {
        blockerHtml = `
            <div class="alert alert-danger mb-0 py-2 border-0">
                <strong>🛑 Blocked: ${school.blocker}</strong><br>
                <small>${school.blocker_details}</small>
            </div>
        `;
    }

    const imgHtml = school.photo 
        ? `<img src="${school.photo}" class="img-fluid rounded shadow-sm border mb-3 w-100" style="max-height: 280px; object-fit: cover;" alt="Site Photograph">`
        : `<div class="bg-light text-muted py-5 rounded text-center mb-3"><i class="fa-regular fa-image fs-1 mb-2"></i><br>No Photo uploaded</div>`;

    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h4 class="fw-bold text-dark">${school.name}</h4>
                <p class="text-muted"><i class="fa-solid fa-map-location-dot me-2"></i>Block: ${school.block} | ${school.district || 'Kolhapur District'}</p>
                
                <div class="mb-4">
                    <strong>Physical Work Status:</strong>
                    <div class="d-flex align-items-center mt-2">
                        <h2 class="mb-0 me-3 fw-bold text-primary">${school.progress}%</h2>
                        <div class="progress flex-grow-1" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: ${school.progress}%"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <strong>Project Metrics:</strong>
                    <div class="row g-2 mt-1">
                        <div class="col-6">
                            <div class="bg-light p-2 rounded text-center">
                                <small class="text-muted d-block">Work Type</small>
                                <span class="fw-semibold text-primary">${school.work_type}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light p-2 rounded text-center">
                                <small class="text-muted d-block">Funding Source</small>
                                <span class="fw-semibold text-dark">${school.funding_source}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <strong>Financial Allocation status:</strong>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Budget Released:</span>
                        <span class="fw-semibold">₹${school.budget.toFixed(2)} Lakhs</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Total Spent:</span>
                        <span class="fw-semibold text-success">₹${school.spent.toFixed(2)} Lakhs</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                ${imgHtml}
                
                <div class="mb-3">
                    <strong>Geo-Tag Mapping:</strong>
                    <div class="mt-1">
                        ${school.geo_tag === 'Tagged' 
                            ? `<span class="badge bg-success-soft text-success p-2"><i class="fa-solid fa-circle-check me-1"></i>Tagged: Lat ${school.latitude}, Lng ${school.longitude}</span>`
                            : `<span class="badge bg-danger-soft text-danger p-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>Missing Geo-Coordinates</span>`}
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Administrative Remarks:</strong>
                    <p class="bg-light p-2 rounded small text-muted font-italic mb-0 border-start border-3 border-secondary">
                        "${school.remarks || 'No remarks provided.'}"
                    </p>
                </div>

                <div class="mb-3">
                    <strong>Active Blockers:</strong>
                    <div class="mt-1">${blockerHtml}</div>
                </div>
            </div>
        </div>
    `;

    // Open the Modal using Bootstrap API
    const myModal = new bootstrap.Modal(document.getElementById('projectDetailsModal'));
    myModal.show();
}

// ==============================================
// WORK TYPES MANAGEMENT
// ==============================================

function renderCEOWorkTypes() {
    const tbody = document.getElementById('workTypesTableBody');
    const emptyState = document.getElementById('workTypesEmptyState');
    const countBadge = document.getElementById('worktypeCountBadge');
    const tableEl = document.getElementById('workTypesTable');
    if (!tbody) return;

    const activeTypes = getWorkTypes();
    tbody.innerHTML = '';

    if (countBadge) countBadge.textContent = `${activeTypes.length} Work Type${activeTypes.length !== 1 ? 's' : ''}`;

    if (activeTypes.length === 0) {
        if (tableEl) tableEl.classList.add('d-none');
        if (emptyState) emptyState.classList.remove('d-none');
        return;
    }

    if (tableEl) tableEl.classList.remove('d-none');
    if (emptyState) emptyState.classList.add('d-none');

    activeTypes.forEach((wt, index) => {
        const tr = document.createElement('tr');
        tr.className = 'worktype-table-row worktype-animate-in';
        tr.style.animationDelay = `${index * 0.05}s`;

        tr.innerHTML = `
            <td>
                <div class="worktype-serial">${index + 1}</div>
            </td>
            <td>
                <div class="fw-semibold text-dark" style="font-size: 1rem;">${wt.name}</div>
                <small class="text-muted">ID: ${wt.id}</small>
            </td>
            <td>
                <span class="text-muted">
                    <i class="fa-regular fa-calendar me-1"></i>${wt.created_at}
                </span>
            </td>
            <td>
                <span class="worktype-badge worktype-badge-active">
                    <i class="fa-solid fa-circle-check me-1"></i>${wt.status}
                </span>
            </td>
            <td class="text-center">
                <button class="worktype-edit-btn" onclick="openEditWorkTypeModal('${wt.id}')" title="Edit Work Type">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function openAddWorkTypeModal() {
    document.getElementById('workTypeEditId').value = '';
    document.getElementById('workTypeNameInput').value = '';
    document.getElementById('workTypeModalLabel').innerHTML = '<i class="fa-solid fa-layer-group me-2"></i>Add New Work Type';
    document.getElementById('workTypeSubmitBtnText').textContent = 'Save Work Type';
    document.getElementById('workTypeFormError').classList.add('d-none');

    const modal = new bootstrap.Modal(document.getElementById('workTypeModal'));
    modal.show();
}

function openEditWorkTypeModal(id) {
    const workType = db.work_types.find(wt => wt.id === id);
    if (!workType) return;

    document.getElementById('workTypeEditId').value = workType.id;
    document.getElementById('workTypeNameInput').value = workType.name;
    document.getElementById('workTypeModalLabel').innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i>Edit Work Type';
    document.getElementById('workTypeSubmitBtnText').textContent = 'Update Work Type';
    document.getElementById('workTypeFormError').classList.add('d-none');

    const modal = new bootstrap.Modal(document.getElementById('workTypeModal'));
    modal.show();
}

function handleWorkTypeFormSubmit(e) {
    e.preventDefault();

    const editId = document.getElementById('workTypeEditId').value;
    const name = document.getElementById('workTypeNameInput').value;
    const errorEl = document.getElementById('workTypeFormError');

    let result;
    if (editId) {
        result = updateWorkType(editId, name);
    } else {
        result = addWorkType(name);
    }

    if (!result.success) {
        errorEl.textContent = result.message;
        errorEl.classList.remove('d-none');
        return;
    }

    // Close modal and refresh
    const modalEl = document.getElementById('workTypeModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

    renderCEOWorkTypes();
    populateWorkTypeDropdowns();
}

// Dynamically populate all work type dropdowns across the CEO dashboard
function populateWorkTypeDropdowns() {
    const activeTypes = getWorkTypes();



    // 2. School Project Monitor filter dropdown
    const filterWorkTypeSelect = document.getElementById('filterWorkType');
    if (filterWorkTypeSelect) {
        const currentVal = filterWorkTypeSelect.value;
        filterWorkTypeSelect.innerHTML = '<option value="">All Work Types</option>';
        activeTypes.forEach(wt => {
            const option = document.createElement('option');
            option.value = wt.name;
            option.textContent = wt.name;
            filterWorkTypeSelect.appendChild(option);
        });
        // Restore selection if still valid
        if (currentVal && [...filterWorkTypeSelect.options].some(o => o.value === currentVal)) {
            filterWorkTypeSelect.value = currentVal;
        }
    }
}

// Initial setup on page load
window.addEventListener('DOMContentLoaded', () => {
    initDatabase();
    updateAlertBadges();
    populateWorkTypeDropdowns();

    const params = new URLSearchParams(window.location.search);
    const requestedView = params.get('view');
    const viewMap = {
        overview: 'ceo-overview',
        task: 'ceo-task',
        physical: 'ceo-physical',
        alerts: 'ceo-alerts'
    };

    if (requestedView && viewMap[requestedView]) {
        switchTab(viewMap[requestedView]);
    } else {
        renderActiveViewData();
    }
});

// React on database update events (from other windows/dashboards)
window.addEventListener('db_updated', () => {
    updateAlertBadges();
    renderActiveViewData();
});
// Also listen to localstorage storage events for cross-tab updates!
window.addEventListener('storage', (e) => {
    if (e.key === 'eportal_schools' || e.key === 'eportal_pending') {
        db.schools = JSON.parse(localStorage.getItem('eportal_schools'));
        db.pending = JSON.parse(localStorage.getItem('eportal_pending'));
        calculateAlerts();
        updateAlertBadges();
        renderActiveViewData();
    }
});
