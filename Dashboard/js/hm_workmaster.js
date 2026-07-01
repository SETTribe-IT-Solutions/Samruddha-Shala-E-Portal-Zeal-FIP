document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterForm');
    const workTableBody = document.getElementById('workTableBody');
    const totalBadge = document.getElementById('totalBadge');

    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        loadWorkData(new FormData(filterForm));
    });

    // Initial load
    loadWorkData();

    // Export button placeholder
    document.getElementById('exportBtn').addEventListener('click', function () {
        alert('Export not yet implemented.');
    });

    document.getElementById('addWorkBtn').addEventListener('click', function () {
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
        document.getElementById('viewModalBody').innerHTML = '<p>Loading details for ID: ' + id + '</p>';
        modal.show();
    }

    window.editWork = function (id) {
        const modal = new bootstrap.Modal(document.getElementById('editModal'));
        document.getElementById('editModalBody').innerHTML = '<p>Edit form for ID: ' + id + '</p>';
        modal.show();
    }

    function loadWorkData(formData) {
        // If no formData provided, send empty params
        let params = '';
        if (formData) {
            const urlParams = new URLSearchParams();
            for (const pair of formData.entries()) urlParams.append(pair[0], pair[1]);
            params = urlParams.toString();
        }

        // Fetch from API endpoint (to be wired to actual DB later)
        fetch('api_get_work_masters.php' + (params ? ('?' + params) : ''))
            .then(res => res.json())
            .then(data => {
                // Expecting array of records; fallback to empty
                const records = Array.isArray(data) ? data : [];
                workTableBody.innerHTML = '';
                if (records.length === 0) {
                    workTableBody.innerHTML = "<tr><td colspan='9' class='text-center p-3'>No records found.</td></tr>";
                    totalBadge.textContent = '0';
                    return;
                }
                totalBadge.textContent = records.length;
                records.forEach((row, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${idx+1}</td>
                        <td>${escapeHtml(row.work_name || '')}</td>
                        <td>${escapeHtml(row.work_type_name || '')}</td>
                        <td>${escapeHtml(row.school_name || '')}</td>
                        <td>${escapeHtml(row.completed_stage || '-')}</td>
                        <td>${escapeHtml(row.status || '')}</td>
                        <td>${escapeHtml(row.created_at || '')}</td>
                        <td>${escapeHtml(row.updated_at || '')}</td>
                        <td>
                            <button class="btn btn-sm btn-primary me-1" onclick="viewWork(${row.id})"><i class="fa fa-eye"></i></button>
                            <button class="btn btn-sm btn-success" onclick="editWork(${row.id})"><i class="fa fa-edit"></i></button>
                        </td>
                    `;
                    workTableBody.appendChild(tr);
                });
            }).catch(err => {
                console.error(err);
                workTableBody.innerHTML = "<tr><td colspan='9' class='text-center p-3'>Failed to load data.</td></tr>";
                totalBadge.textContent = '0';
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
