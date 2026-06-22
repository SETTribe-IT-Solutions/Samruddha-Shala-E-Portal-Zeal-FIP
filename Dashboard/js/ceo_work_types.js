const workTypesApi = 'api/work_types.php';

function showError(message) {
    const errorBox = document.getElementById('workTypeError');
    if (!errorBox) return;
    errorBox.textContent = message;
    errorBox.classList.remove('d-none');
}

function clearError() {
    const errorBox = document.getElementById('workTypeError');
    if (!errorBox) return;
    errorBox.textContent = '';
    errorBox.classList.add('d-none');
}

function resetWorkTypeForm() {
    clearError();
    document.getElementById('workTypeForm').reset();
    document.getElementById('workTypeId').value = '';
    document.getElementById('workTypeModalTitle').textContent = 'Add Work Type';
}

function formatDate(dateString) {
    if (!dateString) return '—';
    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

async function loadWorkTypes() {
    try {
        const response = await fetch(workTypesApi);
        const result = await response.json();
        const tbody = document.getElementById('workTypesTableBody');

        if (!tbody) return;
        if (!result.success || !Array.isArray(result.data)) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">${result.message || 'Unable to load data.'}</td></tr>`;
            return;
        }

        if (result.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No work types found.</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        result.data.forEach((item, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>
                    <div class="fw-semibold">${item.name}</div>
                </td>
                <td>
                    <span class="badge ${item.status === 'Active' ? 'bg-success' : 'bg-secondary'}">${item.status || 'Active'}</span>
                </td>
                <td>${formatDate(item.created_at)}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-info me-1" onclick="viewWorkType(${item.id})">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editWorkType(${item.id})">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteWorkType(${item.id})">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        const tbody = document.getElementById('workTypesTableBody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Unable to load work types.</td></tr>';
        }
    }
}

async function viewWorkType(id) {
    try {
        const response = await fetch(workTypesApi + '?id=' + id);
        const result = await response.json();
        if (!result.success || !result.data) {
            alert(result.message || 'Unable to fetch details.');
            return;
        }
        const item = result.data;
        const viewBody = document.getElementById('viewWorkTypeBody');
        if (viewBody) {
            viewBody.innerHTML = `
                <p><strong>Work Type:</strong> ${item.name}</p>
                <p><strong>Status:</strong> ${item.status}</p>
                <p><strong>Created At:</strong> ${formatDate(item.created_at)}</p>
                <p><strong>Updated At:</strong> ${formatDate(item.updated_at)}</p>
            `;
            const modal = new bootstrap.Modal(document.getElementById('viewWorkTypeModal'));
            modal.show();
        }
    } catch (error) {
        alert('Unable to open details.');
    }
}

async function editWorkType(id) {
    try {
        const response = await fetch(workTypesApi + '?id=' + id);
        const result = await response.json();
        if (!result.success || !result.data) {
            alert(result.message || 'Unable to fetch work type.');
            return;
        }
        const item = result.data;
        document.getElementById('workTypeId').value = item.id;
        document.getElementById('workTypeName').value = item.name;
        document.getElementById('workTypeStatus').value = item.status || 'Active';
        document.getElementById('workTypeModalTitle').textContent = 'Edit Work Type';
        clearError();
        const modal = new bootstrap.Modal(document.getElementById('workTypeModal'));
        modal.show();
    } catch (error) {
        alert('Unable to edit work type.');
    }
}

async function deleteWorkType(id) {
    if (!confirm('Are you sure you want to delete this work type?')) {
        return;
    }

    try {
        const response = await fetch(workTypesApi, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        });
        const result = await response.json();
        if (!result.success) {
            alert(result.message || 'Delete failed.');
            return;
        }
        await loadWorkTypes();
    } catch (error) {
        alert('Unable to delete work type.');
    }
}

async function submitWorkTypeForm(event) {
    event.preventDefault();
    clearError();

    const id = document.getElementById('workTypeId').value;
    const name = document.getElementById('workTypeName').value.trim();
    const status = document.getElementById('workTypeStatus').value;

    if (!name) {
        showError('Work type name is required.');
        return;
    }

    try {
        const response = await fetch(workTypesApi, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: id ? 'update' : 'add',
                id,
                name,
                status
            })
        });

        const result = await response.json();
        if (!result.success) {
            showError(result.message || 'Something went wrong.');
            return;
        }

        const modalEl = document.getElementById('workTypeModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        await loadWorkTypes();
    } catch (error) {
        showError('Unable to save work type.');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('workTypeForm');
    if (form) {
        form.addEventListener('submit', submitWorkTypeForm);
    }
    loadWorkTypes();
});

window.viewWorkType = viewWorkType;
window.editWorkType = editWorkType;
window.deleteWorkType = deleteWorkType;
window.resetWorkTypeForm = resetWorkTypeForm;
