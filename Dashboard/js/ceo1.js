function handleAssignTaskSubmit(event) {
    event.preventDefault();

    let formData = new FormData();

    formData.append(
        "school_name",
        document.getElementById("ceoTaskSchoolSelect").value
    );

    formData.append(
        "work_type",
        document.getElementById("ceoTaskWorkType").value
    );

    formData.append(
        "budget_lakhs",
        document.getElementById("ceoTaskBudget").value
    );

    formData.append(
        "funding_source",
        document.getElementById("ceoTaskFundingSource").value
    );

    formData.append(
        "task_description",
        document.getElementById("ceoTaskDescription").value
    );

   fetch("ceo_assign_task_db.php", {
    method: "POST",
    body: formData
})
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            alert(data.message);
            document.getElementById("ceoAssignTaskForm").reset();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error(error);
        alert("Something went wrong");
    });
}