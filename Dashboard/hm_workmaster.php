<?php
session_start();

// Basic access guard (adjust role name as needed)
// Enable error reporting for development (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_SESSION['role']) && $_SESSION['role'] !== 'HM') {
    // If not HM, you can change redirect as appropriate
    header("Location: ../login.php");
    exit();
}
// debug helper removed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HM Work Master</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <style>
    /* Page base */
    .hm-workmaster-page {
        font-family: 'Outfit', sans-serif;
        background: #f4f7fb;
        min-height: 100vh;
        color: #223;
    }

    /* Card styling to mimic screenshot */
    .card.master-card {
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(40, 69, 112, 0.08);
        overflow: visible;
    }

    .master-header {
        padding: 28px 24px;
        background: linear-gradient(90deg,#fff 0%, #fff 60%);
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }

    /* Search / filters row */
    .search-row { gap:12px; }
    .search-input {
        position: relative;
    }
    .search-input input {
        padding-left: 40px;
        border-radius: 8px;
        height: 44px;
    }
    .search-input .fa-search {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9aa7bf;
    }

    .btn-export, .btn-import {
        color: #fff;
        border-radius: 8px;
        height:44px;
        min-width: 140px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-export {
        background: #1fa65a;
    }

    .btn-import {
        background: #2563eb;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table th:first-child,
    .table td:first-child {
        position: sticky;
        left: 0;
        background: #fff;
        z-index: 3;
        min-width: 50px;
    }

    .table thead th:first-child {
        z-index: 4;
    }

    .table th:first-child {
        box-shadow: 2px 0 5px rgba(0,0,0,0.08);
    }

    .table td:first-child {
        box-shadow: 2px 0 5px rgba(0,0,0,0.05);
    }

    /* Table header */
    .table thead th {
        background: #183b63;
        color: #fff;
        border: none;
        vertical-align: middle;
    }
    /* Sortable headers */
    th.sortable { cursor: pointer; user-select: none; }
    th.sortable::after { content: '\2195'; font-size: 0.8rem; margin-left: 8px; opacity: 0.7; }
    th.sorted-asc::after { content: '\25B2'; }
    th.sorted-desc::after { content: '\25BC'; }
    .table tbody tr td { vertical-align: middle; }

    .badge-stage { font-weight:600; }

    /* Action buttons */
    .action-btn { width:36px; height:36px; padding:0; border-radius:50%; }
    .action-view { background:#1e88e5; color:#fff; }
    .action-edit { background:#ffb020; color:#fff; }
    .action-delete { background:#f44336; color:#fff; }

    /* Pagination */
    .hm-pagination { padding:18px 24px; display:flex; justify-content:flex-end; align-items:center; gap:10px; }

    /* responsive tweaks */
    @media (max-width: 992px) {
        .search-row { flex-direction: column; }
        .search-row .col { width: 100%; }
    }
    html, body {
        min-height: 100%;
        height: 100%;
    }

    body.hm-workmaster-page {
        margin: 0;
        padding: 0;
        min-height: 100vh;
        height: 100vh;
        overflow: hidden;
        background: #f4f7fb;
        font-family: 'Outfit', sans-serif;
    }

    #wrapper {
        display: flex;
        height: 100vh;
        overflow: hidden;
        position: relative;
    }

    #content {
        position: absolute;
        top: 0;
        left: 300px;
        right: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: transparent;
        z-index: 1;
    }

    .hm-fixed-header {
        position: fixed;
        top: 0;
        left: 300px;
        right: 0;
        z-index: 1050;
        background: #f4f7fb;
        width: auto;
        box-sizing: border-box;
    }

    .hm-page-body {
        position: relative;
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        padding: 115px 8px 90px 8px;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .hm-page-body .container-fluid {
        max-width: 1280px;
        margin: 0 auto;
        padding-left: 10px;
        padding-right: 10px;
    }

    .card.master-card {
        margin-bottom: 16px;
    }

    .master-header {
        padding: 20px 18px;
    }

    .hm-fixed-footer {
        position: fixed;
        bottom: 0;
        left: 280px;
        right: 0;
        z-index: 1050;
        background: #ffffff;
        border-top: 1px solid rgba(229, 231, 235, 0.8);
        width: auto;
        box-sizing: border-box;
    }

    #content,
    .hm-page-body,
    .hm-page-body > .container-fluid,
    .hm-page-body .container-fluid,
    .hm-page-body .navbar .container-fluid,
    .hm-page-body .row,
    .hm-page-body .card,
    .hm-page-body .table-responsive,
    .hm-page-body .navbar,
    .hm-page-body .form-control,
    .hm-page-body .input-group,
    .hm-page-body .form-label {
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .site-banner-wrapper {
        position: relative;
        z-index: 1;
    }
    </style>
</head>
<body class="hm-workmaster-page">

<div id="wrapper">
    <?php include '../include/sidebar.php'; ?>

    <div id="content">
        <div class="hm-fixed-header">
            <?php include '../include/website_header.php'; ?>
        </div>

        <div class="hm-page-body">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid px-0">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link d-lg-none" onclick="document.getElementById('wrapper').classList.toggle('toggled')">
                        <i class="fa fa-bars"></i>
                    </button>
                    <h4 class="mb-0 ms-2">HM Work Master</h4>
                </div>
            </div>
        </nav>

        <div class="container-fluid mt-3 px-0">

            <!-- Header card -->

            <!-- Master card with compact search and table -->
            <div class="card master-card mb-4">
                <div class="master-header">
                    <div class="row align-items-center search-row">
                        <div class="col-lg-5 col-md-6 mb-2 mb-md-0">
                            <div class="search-input">
                                <i class="fa fa-search"></i>
                                <input type="text" id="globalSearch" class="form-control" placeholder="काम शोधा... / Search work">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-3 mb-2 mb-md-0">
                            <select id="categoryFilter" class="form-select">
                                <option value="">-- श्रेणी निवडा --</option>
                                <option value="Civilian">CIVILIAN</option>
                                <option value="Non-Civilian">NON-CIVILIAN</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-3 mb-2 mb-md-0">
                            <select id="statusFilter" class="form-select">
                                <option value="">-- स्थिती निवडा --</option>
                                <option value="Not Started">Not Started</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-12 d-flex justify-content-lg-end gap-2 align-items-center">
                            <button id="importBtn" class="btn btn-import btn-sm"><i class="fa fa-file-import"></i> आयात</button>
                            <button id="exportBtn" class="btn btn-export btn-sm"><i class="fa fa-file-excel"></i> निर्यात</button>
                            <input type="file" id="importFileInput" accept=".csv" style="display:none">
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width:50px">अनु.क्र.</th>
                                    <th class="sortable" data-field="work_type_name">कामाचा प्रकार</th>
                                    <th class="sortable" data-field="work_name">कामाचे नाव</th>
                                    <th class="sortable" data-field="school_name">शाळा</th>
                                    <th style="width:120px">टप्पे</th>
                                    <th class="sortable" data-field="completed_percentage">एकूण टक्केवारी</th>
                                    <th class="sortable" data-field="status">स्थिती</th>
                                    <th>क्रिय्या</th>
                                </tr>
                            </thead>
                            <tbody id="workTableBody">
                                <!-- Rows injected by JS -->
                            </tbody>
                        </table>
                    </div>

                    <div class="hm-pagination mt-3">
                        <div class="me-auto d-flex align-items-center gap-3">
                            <div class="text-muted" id="recordInfo">नोंदी 0 ते 0 (एकूण 0 नोंदी)</div>
                            <div>
                                <select id="perPageSelect" class="form-select form-select-sm">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination mb-0">
                                    <!-- pagination items rendered by JS -->
                                </ul>
                            </nav>
                    </div>

                </div>
            </div>

        </div>

        <div class="hm-fixed-footer">
            <?php include '../include/website_footer.php'; ?>
        </div>

    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">View Work</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="viewModalBody">
        <!-- Dynamic content -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Work</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="editModalBody">
        <!-- Dynamic form -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveEditBtn">Save</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const workTableBody = document.getElementById('workTableBody');
    const recordInfoEl = document.getElementById('recordInfo');

    // Pagination state
    let currentPage = 1;
    let perPage = 10;
    // Sorting state
    let sortBy = 'created_at';
    let sortDir = 'desc';

    // Initial load (will apply default sort)
    updateSortIndicators = function () {
        document.querySelectorAll('th.sortable').forEach(th => {
            th.classList.remove('sorted-asc', 'sorted-desc');
            if (th.dataset && th.dataset.field === sortBy) th.classList.add(sortDir === 'asc' ? 'sorted-asc' : 'sorted-desc');
        });
    };

    // wire sortable headers
    document.querySelectorAll('th.sortable').forEach(th => {
        th.addEventListener('click', function () {
            const field = th.dataset.field;
            if (!field) return;
            if (sortBy === field) sortDir = (sortDir === 'desc' ? 'asc' : 'desc'); else { sortBy = field; sortDir = 'desc'; }
            currentPage = 1;
            updateSortIndicators();
            loadWorkData();
        });
    });

    updateSortIndicators();
    loadWorkData();

    // Export button placeholders (safe attach)
    const exportBtn = document.getElementById('exportBtn');
    const importBtn = document.getElementById('importBtn');
    const importFileInput = document.getElementById('importFileInput');
    const exportTopBtn = document.getElementById('exportTopBtn');
    if (exportBtn) exportBtn.addEventListener('click', function () { alert('Export not yet implemented.'); });
    if (exportTopBtn) exportTopBtn.addEventListener('click', function () { alert('Export not yet implemented.'); });
    if (importBtn && importFileInput) {
        importBtn.addEventListener('click', function () {
            importFileInput.value = '';
            importFileInput.click();
        });
    }
    if (importFileInput) {
        importFileInput.addEventListener('change', function () {
            if (!this.files || !this.files[0]) return;
            const file = this.files[0];
            const formData = new FormData();
            formData.append('file', file);
            fetch('api_import_work.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(resp => {
                    if (!resp || resp.status === false) {
                        alert('Import failed: ' + (resp.message || 'Unknown error'));
                        return;
                    }
                    alert('Import successful: ' + (resp.message || 'Work records imported'));
                    loadWorkData();
                }).catch(err => {
                    console.error(err);
                    alert('Import failed.');
                });
        });
    }

    document.getElementById('addTopBtn').addEventListener('click', function () {
        // Open edit modal with empty form for creation (to be implemented)
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        document.getElementById('editModalBody').innerHTML = '<p>Create form will go here.</p>';
        editModal.show();
    });

    // Save edit placeholder
    document.getElementById('saveEditBtn').addEventListener('click', function () {
        alert('Save functionality not implemented yet.');
    });

    window.viewWork = function (id) {
        const modal = new bootstrap.Modal(document.getElementById('viewModal'));
        const body = document.getElementById('viewModalBody');
        body.innerHTML = '<p>Loading...</p>';
        modal.show();

        fetch('api_get_work_master.php?id=' + encodeURIComponent(id))
            .then(r => r.json())
            .then(data => {
                if (data.error) { body.innerHTML = '<div class="text-danger">' + data.error + '</div>'; return; }
                let html = '<dl class="row">';
                html += '<dt class="col-sm-3">Work Type</dt><dd class="col-sm-9">' + escapeHtml(data.work_type_name || '') + '</dd>';
                html += '<dt class="col-sm-3">Work Name</dt><dd class="col-sm-9">' + escapeHtml(data.work_name || '') + '</dd>';
                html += '<dt class="col-sm-3">School</dt><dd class="col-sm-9">' + escapeHtml(data.school_name || '') + '</dd>';
                html += '<dt class="col-sm-3">Assigned To</dt><dd class="col-sm-9">' + escapeHtml(data.assigned_to || '') + '</dd>';
                html += '<dt class="col-sm-3">Status</dt><dd class="col-sm-9">' + escapeHtml(data.status || '') + '</dd>';
                html += '<dt class="col-sm-3">Created</dt><dd class="col-sm-9">' + escapeHtml(data.created_at || '') + '</dd>';
                html += '<dt class="col-sm-3">Updated</dt><dd class="col-sm-9">' + escapeHtml(data.updated_at || '') + '</dd>';
                if (Array.isArray(data.stages) && data.stages.length) {
                    html += '<dt class="col-sm-3">Stages</dt><dd class="col-sm-9">';
                    html += '<ul class="mb-0">';
                    data.stages.forEach(s => html += '<li>' + escapeHtml(s.stage_name) + ' (' + escapeHtml(s.stage_percentage) + '%)</li>');
                    html += '</ul>';
                    html += '</dd>';
                }
                html += '</dl>';
                body.innerHTML = html;
            }).catch(err => { body.innerHTML = '<div class="text-danger">Failed to load details.</div>'; console.error(err); });
    }

    window.editWork = function (id) {
        const modalEl = document.getElementById('editModal');
        const modal = new bootstrap.Modal(modalEl);
        const body = document.getElementById('editModalBody');
        body.innerHTML = '<p>Loading...</p>';
        modal.show();

        // fetch options and record in parallel
        Promise.all([
            fetch('api_get_work_options.php').then(r => r.json()),
            fetch('api_get_work_master.php?id=' + encodeURIComponent(id)).then(r => r.json())
        ]).then(([opts, data]) => {
            if (data.error) { body.innerHTML = '<div class="text-danger">' + data.error + '</div>'; return; }

            const work_types = Array.isArray(opts.work_types) ? opts.work_types : [];
            const work_names = Array.isArray(opts.work_names) ? opts.work_names : [];

            let html = '<form id="editForm">';
            html += '<input type="hidden" name="id" value="' + escapeHtml(data.id) + '">';
            html += '<div class="mb-3"><label class="form-label">Work Type</label><select name="work_type_id" class="form-select">';
            html += '<option value="">Select type</option>';
            work_types.forEach(t => html += '<option value="' + t.id + '"' + (t.id == data.work_type_id ? ' selected' : '') + '>' + escapeHtml(t.work_type_name) + '</option>');
            html += '</select></div>';

            html += '<div class="mb-3"><label class="form-label">Work Name</label><select name="work_name_id" class="form-select">';
            html += '<option value="">Select name</option>';
            work_names.forEach(n => html += '<option value="' + n.id + '"' + (n.id == data.work_name_id ? ' selected' : '') + '>' + escapeHtml(n.work_name) + '</option>');
            html += '</select></div>';

            html += '<div class="mb-3"><label class="form-label">School</label><input name="school_name" class="form-control" value="' + escapeHtml(data.school_name || '') + '"></div>';
            html += '<div class="mb-3"><label class="form-label">Assigned To</label><input name="assigned_to" class="form-control" value="' + escapeHtml(data.assigned_to || '') + '"></div>';
            html += '<div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select">';
            ['Pending','In Progress','Completed'].forEach(s => html += '<option value="' + s + '"' + (s == data.status ? ' selected' : '') + '>' + s + '</option>');
            html += '</select></div>';
            html += '<div class="mb-3"><label class="form-label">Additional Notes</label><textarea name="additional_notes" class="form-control">' + escapeHtml(data.additional_notes || '') + '</textarea></div>';

            html += '</form>';
            body.innerHTML = html;

            // wire save button
            const saveBtn = document.getElementById('saveEditBtn');
            saveBtn.onclick = function () {
                const form = document.getElementById('editForm');
                const fd = new FormData(form);
                fetch('api_update_work.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(resp => {
                        if (resp.error) { alert('Error: ' + resp.error); return; }
                        modal.hide();
                        loadWorkData();
                        alert(resp.message || 'Saved');
                    }).catch(err => { console.error(err); alert('Save failed'); });
            };

        }).catch(err => { body.innerHTML = '<div class="text-danger">Failed to load form data.</div>'; console.error(err); });
    }

    window.deleteWork = function (id) {
        if (!confirm('Are you sure you want to delete ID ' + id + '?')) return;
        const fd = new FormData(); fd.append('id', id);
        fetch('api_delete_work.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(resp => {
                if (resp.error) { alert('Error: ' + resp.error); return; }
                alert(resp.message || 'Deleted');
                loadWorkData();
            }).catch(err => { console.error(err); alert('Delete failed'); });
    }

    // Wire filters: search input and selects
    function gatherParams() {
        const params = new URLSearchParams();
        const q = document.getElementById('globalSearch').value.trim();
        if (q) params.append('q', q);
        const cat = document.getElementById('categoryFilter').value;
        if (cat) params.append('category', cat);
        const st = document.getElementById('statusFilter').value;
        if (st) params.append('status', st);
        params.append('page', currentPage);
        params.append('per_page', perPage);
        params.append('sort_by', sortBy);
        params.append('sort_dir', sortDir);
        return params.toString();
    }

    // debounce helper
    function debounce(fn, ms) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), ms);
        }
    }

    document.getElementById('globalSearch').addEventListener('keyup', debounce(function () {
        loadWorkData();
    }, 450));
    document.getElementById('categoryFilter').addEventListener('change', function () { loadWorkData(); });
    document.getElementById('statusFilter').addEventListener('change', function () { loadWorkData(); });

    function loadWorkData(formData) {
        let params = '';
        if (formData) {
            const urlParams = new URLSearchParams();
            for (const pair of formData.entries()) urlParams.append(pair[0], pair[1]);
            params = urlParams.toString();
        } else {
            params = gatherParams();
        }

        // Fetch from API endpoint (to be wired to actual DB later)
        fetch('api_get_work_masters.php' + (params ? ('?' + params) : ''))
            .then(res => res.json())
            .then(json => {
                // Response { data: [], total, page, per_page }
                const records = Array.isArray(json.data) ? json.data : [];
                const total = parseInt(json.total || 0, 10);
                currentPage = parseInt(json.page || currentPage, 10);
                perPage = parseInt(json.per_page || perPage, 10);
                workTableBody.innerHTML = '';
                if (records.length === 0) {
                    workTableBody.innerHTML = "<tr><td colspan='8' class='text-center p-3'>No records found.</td></tr>";
                    recordInfoEl.textContent = `नोंदी 0 ते 0 (एकूण 0 नोंदी)`;
                    renderPagination(0, currentPage, perPage);
                    return;
                }

                // update record info and table rows
                const start = (currentPage - 1) * perPage + 1;
                const end = Math.min(total, currentPage * perPage);
                recordInfoEl.textContent = `नोंदी ${start} ते ${end} (एकूण ${total} नोंदी)`;

                records.forEach((row, idx) => {
                    const tr = document.createElement('tr');
                    const percentVal = Number(row.completed_percentage || row.total_percentage || 0);
                    const percent = isNaN(percentVal) ? '-' : Math.max(0, Math.min(100, percentVal));
                    const textStatus = row.status || '';
                    const statusLabel = percent === '-' ? (textStatus || 'Pending') : (percent === 100 ? 'Completed' : percent === 0 ? 'Not Started' : 'In Progress');
                    const badgeClass = statusLabel === 'Completed' ? 'bg-success' : statusLabel === 'Not Started' ? 'bg-secondary text-white' : statusLabel === 'In Progress' ? 'bg-warning text-dark' : 'bg-info text-dark';
                    tr.innerHTML = `
                        <td style="width:50px">${idx+1}</td>
                        <td>${escapeHtml(row.work_type_name || '')}</td>
                        <td>${escapeHtml(row.work_name || '')}</td>
                        <td>${escapeHtml(row.school_name || '')}</td>
                        <td>${escapeHtml(row.stage_name || '-')}</td>
                        <td>
                            ${percent === '-' ? '-' : `<div class="progress" style="height:10px"><div class="progress-bar" role="progressbar" style="width:${percent}%" aria-valuenow="${percent}" aria-valuemin="0" aria-valuemax="100"></div></div><div class="mt-1"><strong>${percent}%</strong></div>`}
                        </td>
                        <td><span class="badge ${badgeClass}">${escapeHtml(statusLabel)}</span></td>
                        <td>
                            <button class="btn action-btn action-view me-1" title="View" onclick="viewWork(${row.id})"><i class="fa fa-eye"></i></button>
                            <button class="btn action-btn action-edit me-1" title="Edit" onclick="editWork(${row.id})"><i class="fa fa-edit"></i></button>
                            <button class="btn action-btn action-delete" title="Delete" onclick="deleteWork(${row.id})"><i class="fa fa-trash"></i></button>
                        </td>
                    `;
                    workTableBody.appendChild(tr);
                });

                renderPagination(total, currentPage, perPage);
            }).catch(err => {
                console.error(err);
                workTableBody.innerHTML = "<tr><td colspan='8' class='text-center p-3'>Failed to load data.</td></tr>";
                recordInfoEl.textContent = `नोंदी 0 ते 0 (एकूण 0 नोंदी)`;
            });
    }

    function renderPagination(total, page, perPage) {
        const container = document.querySelector('.hm-pagination .pagination');
        if (!container) return;
        const totalPages = Math.max(1, Math.ceil(total / perPage));
        container.innerHTML = '';

        const addPageItem = (p, label, active) => {
            const li = document.createElement('li');
            li.className = 'page-item' + (active ? ' active' : '');
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = label;
            a.onclick = function (e) { e.preventDefault(); if (p === page) return; currentPage = p; loadWorkData(); };
            li.appendChild(a);
            container.appendChild(li);
        };

        // First
        addPageItem(1, '« प्रथम', false);

        // prev
        addPageItem(Math.max(1, page - 1), '‹ मागे', false);

        // simple window of pages
        const windowSize = 5;
        let startPage = Math.max(1, page - Math.floor(windowSize / 2));
        let endPage = Math.min(totalPages, startPage + windowSize - 1);
        if (endPage - startPage < windowSize - 1) startPage = Math.max(1, endPage - windowSize + 1);

        for (let p = startPage; p <= endPage; p++) addPageItem(p, p.toString(), p === page);

        // next
        addPageItem(Math.min(totalPages, page + 1), 'पुढे ›', false);

        // Last
        addPageItem(totalPages, 'शेवट »', false);
    }

    // per-page selector wiring
    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect) {
        perPageSelect.value = perPage.toString();
        perPageSelect.addEventListener('change', function () {
            perPage = parseInt(this.value, 10) || 10;
            currentPage = 1;
            loadWorkData();
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/[&<>"']/g, function (s) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[s];
        });
    }
});
</script>
</body>
</html>
