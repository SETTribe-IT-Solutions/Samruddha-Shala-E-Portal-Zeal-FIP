document.addEventListener("DOMContentLoaded", () => {
    let allWorkNames = [];
    const workTypeSelect = document.getElementById("workTypeSelect");
    const workNameSelect = document.getElementById("workNameSelect");
    const stagesTableBody = document.getElementById("stagesTableBody");
    const totalPercentageDisplay = document.getElementById("totalPercentageDisplay");
    const createWorkForm = document.getElementById("createWorkForm");
    const submitBtn = document.getElementById("submitWorkBtn");
    const formError = document.getElementById("formError");

    // Fetch initial master data
    fetch("api_get_work_masters.php")
        .then(res => res.json())
        .then(data => {
            if (data.status) {
                // Populate Work Types
                workTypeSelect.innerHTML = '<option value="" disabled selected>Select Work Type</option>';
                data.work_types.forEach(type => {
                    const opt = document.createElement("option");
                    opt.value = type.id;
                    opt.textContent = type.work_type_name;
                    workTypeSelect.appendChild(opt);
                });
                
                // Store work names to filter later
                allWorkNames = data.work_names;
            } else {
                showError("Failed to load master data: " + data.message);
            }
        })
        .catch(err => showError("Network error while loading data."));

    // Handle Work Type Change
    workTypeSelect.addEventListener("change", () => {
        const typeId = workTypeSelect.value;
        workNameSelect.innerHTML = '<option value="" disabled selected>Select Work Name</option>';
        const filteredNames = allWorkNames.filter(n => n.work_type_id == typeId);
        
        filteredNames.forEach(name => {
            const opt = document.createElement("option");
            opt.value = name.id;
            opt.textContent = name.work_name;
            workNameSelect.appendChild(opt);
        });
    });


    function addStageRow(nameValue, pctValue) {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>
                <input type="text" class="form-control stage-name" placeholder="e.g. Planning" value="${nameValue}" required>
            </td>
            <td>
                <input type="number" class="form-control stage-percentage" placeholder="0 - 100" min="0.1" max="100" step="0.1" value="${pctValue}" required>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-success add-stage-btn m-1" style="width: 32px; height: 32px; padding: 0;"><i class="fa-solid fa-plus"></i></button>
                <button type="button" class="btn btn-sm btn-outline-danger remove-stage-btn m-1" style="width: 32px; height: 32px; padding: 0;"><i class="fa-solid fa-trash"></i></button>
            </td>
        `;
        stagesTableBody.appendChild(tr);

        // Attach events to new elements
        tr.querySelector(".add-stage-btn").addEventListener("click", () => {
            addStageRow("", "");
        });

        tr.querySelector(".remove-stage-btn").addEventListener("click", () => {
            const rows = document.querySelectorAll("#stagesTableBody tr");
            if (rows.length > 1) {
                tr.remove();
                calculateTotalPercentage();
            } else {
                showError("You must have at least one stage.");
            }
        });

        tr.querySelector(".stage-percentage").addEventListener("input", calculateTotalPercentage);
        
        calculateTotalPercentage();
    }

    function calculateTotalPercentage() {
        const pctInputs = document.querySelectorAll(".stage-percentage");
        let total = 0;
        pctInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val)) total += val;
        });

        totalPercentageDisplay.textContent = total.toFixed(2) + "%";
        
        if (Math.abs(total - 100) > 0.01) {
            totalPercentageDisplay.classList.remove("text-success");
            totalPercentageDisplay.classList.add("text-danger");
        } else {
            totalPercentageDisplay.classList.remove("text-danger");
            totalPercentageDisplay.classList.add("text-success");
        }
        
        return total;
    }

    function showError(msg) {
        formError.textContent = msg;
        formError.classList.remove("d-none");
        window.scrollTo(0, formError.offsetTop - 100);
    }

    function hideError() {
        formError.classList.add("d-none");
    }

    // Default stage row
    addStageRow("", "");

    // Form submission
    createWorkForm.addEventListener("submit", (e) => {
        e.preventDefault();
        hideError();

        const schoolNameSelect = document.getElementById("schoolNameSelect");
        const schoolName = schoolNameSelect ? schoolNameSelect.value : "";
        const assignedToSelect = document.getElementById("assignedToSelect");
        const assignedTo = assignedToSelect ? assignedToSelect.value : "";
        const workTypeId = workTypeSelect.value;
        const workNameId = workNameSelect.value;
        const additionalNotes = document.getElementById("additionalNotes").value;
        
        if (!schoolName) return showError("School Name is required.");
        if (!assignedTo) return showError("Assigned To role is required.");
        if (!workTypeId) return showError("Work Type is required.");
        if (!workNameId) return showError("Work Name is required.");

        const stageRows = document.querySelectorAll("#stagesTableBody tr");
        if (stageRows.length === 0) return showError("At least one stage is required.");

        const stages = [];
        let totalPct = 0;
        let validStages = true;

        stageRows.forEach(row => {
            const name = row.querySelector(".stage-name").value.trim();
            const pct = parseFloat(row.querySelector(".stage-percentage").value);
            
            if (!name) {
                validStages = false;
                showError("Stage Name is required for all stages.");
            }
            if (isNaN(pct) || pct <= 0) {
                validStages = false;
                showError("Percentage must be a positive number.");
            }
            totalPct += pct;
            stages.push({ name, percentage: pct });
        });

        if (!validStages) return;

        if (Math.abs(totalPct - 100) > 0.01) {
            return showError("Total stage percentage must equal exactly 100%. (Currently " + totalPct.toFixed(2) + "%)");
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...';

        const payload = {
            school_name: schoolName,
            assigned_to: assignedTo,
            work_type_id: workTypeId,
            work_name_id: workNameId,
            additional_notes: additionalNotes,
            stages: stages
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
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Create Work';

            if (data.status) {
                showSuccessPopup("Work created successfully.");
                createWorkForm.reset();
                stagesTableBody.innerHTML = '';
                addStageRow("", "");
                calculateTotalPercentage();
                // Reset work name dropdown
                workNameSelect.innerHTML = '<option value="" disabled selected>Select Work Type first</option>';
            } else {
                showError(data.message || "Failed to save work.");
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Create Work';
            showError("Network error while submitting.");
        });
    });

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
});
