
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDoList</title>
    <link rel="stylesheet" href="list.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
</head>

<body>
    <h1 class='main-ttl'>My ToDo List</h1>

    <div class="form-container">
        <?php if (!empty($tasks)): ?>
            <form method="post" action="" id="taskForm">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token']) ?>">
                <input type="hidden" name="task_order" id="taskOrder">
                <div id="taskList">
                    <?php foreach ($tasks as $task): ?>
                        <?php if (!$task["completed"]): ?>
                            <div class='task-box' data-id="<?= $task["Id_task"] ?>">
                                <span class="drag-handle">☰</span>
                                <input type="checkbox" name="task" value="<?= $task["Id_task"] ?>" class='task-checkbox'>
                                <input type="text" name="description_<?= $task["Id_task"] ?>" value="<?= htmlspecialchars($task["description"]) ?>" class='task-description'>
                                <input type="hidden" name="completed" value="1">
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <input type="submit" value="Update tasks" class='update-button'>
            </form>
        <?php else: ?>
            <p>No tasks found</p>
        <?php endif; ?>

        <form method="post" action="" class="add-task-form">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token']) ?>">
            <label for="new_description">Description:</label>
            <input type="text" name="new_description" id="new_description" required>
            <br>
            <input type="submit" value="Add task" class='add-button'>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let taskList = document.getElementById('taskList');
            let sortable = new Sortable(taskList, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: function () {
                    updateTaskOrder();
                }
            });

            function updateTaskOrder() {
                let tasks = taskList.querySelectorAll('.task-box');
                let order = Array.from(tasks).map(task => task.dataset.id).join(',');
                document.getElementById('taskOrder').value = order;
            }

           
            updateTaskOrder();
        });
    </script>
</body>

</html>