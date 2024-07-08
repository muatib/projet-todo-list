document.addEventListener("DOMContentLoaded", () => {
    const taskList = document.getElementById("taskList");
    const checkboxes = document.querySelectorAll(".task-checkbox");
    const updateButton = document.querySelector(".update-button");
    const toggleCompletedButton = document.getElementById("toggleCompletedTasks");

    
    function handleCheckboxChange(checkbox) {
        const taskBox = checkbox.closest(".task-box");
        checkbox.checked ? taskBox.classList.add("completed") : taskBox.classList.remove("completed");
    }

    
    function updateTaskOrderOnServer() {
        
    }

    
    function deleteCompletedTasks() {
        const checkedTaskIds = Array.from(document.querySelectorAll(".task-checkbox:checked")).map(checkbox => checkbox.value);

        fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `task_ids=${checkedTaskIds.join(",")}&token=${document.querySelector('input[name="token"]').value}`,
        }).then((response) => {
            if (response.ok) {
                document.querySelectorAll(".task-checkbox:checked").forEach((checkbox) => checkbox.closest(".task-box").remove());
            } else {
                console.error("Erreur lors de la suppression des tâches.");
            }
        });
    }

    
    function toggleCompletedTasksVisibility() {
        const completedTasks = document.querySelectorAll(".task-box.completed");
        const isHidden = completedTasks.length > 0 && completedTasks[0].style.display === "none";

        completedTasks.forEach((task) => {
            task.style.display = isHidden ? "flex" : "none";
        });

        toggleCompletedButton.textContent = isHidden ? "Masquer les tâches terminées" : "Afficher les tâches terminées";
    }

   

    checkboxes.forEach(checkbox => checkbox.addEventListener("change", () => handleCheckboxChange(checkbox)));

    updateButton.addEventListener("click", (event) => {
        event.preventDefault();
        deleteCompletedTasks();
    });

    toggleCompletedButton.addEventListener("click", toggleCompletedTasksVisibility);

    
    new Sortable(taskList, {
        animation: 150,
        ghostClass: "blue-background-class",
        onUpdate: updateTaskOrderOnServer, 
    });
});
