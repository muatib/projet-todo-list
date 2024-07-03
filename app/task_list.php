<!DOCTYPE html>
<html>
<head>
    <title>ToDoList</title>
    <link rel="stylesheet" href="list.css">
</head>
<body>
    <h1 class='main-ttl'>My ToDo List</h1>

    <?php if (isset($_SESSION['error_message'])) : ?>
        <p class="error-message"><?= htmlspecialchars($_SESSION['error_message']) ?></p>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])) : ?>
        <p class="success-message"><?= htmlspecialchars($_SESSION['success_message']) ?></p>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="form-container">
        <?php if (!empty($tasks)) : ?>
        <form method="post" action="" id="taskForm">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token']) ?>">
            <div id="taskList"> 
                <?php foreach ($tasks as $task) : ?>
                    <?php if (!$task["completed"]) : ?>
                        <div class='task-box' data-id="<?= $task["Id_task"] ?>">
                            <input type="checkbox" name="task" value="<?= $task["Id_task"] ?>" class='task-checkbox' onchange="this.form.submit()">
                            <input type="text" name="description_<?= $task["Id_task"] ?>" value="<?= htmlspecialchars($task["description"]) ?>" class='task-description'>
                            <input type="hidden" name="completed" value="1">
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            </form>
        <?php else : ?>
        <p>No tasks found</p>
        <?php endif; ?>
    </div>

    <form method="post" action="" class="add-task-form">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token']) ?>">
        <label for="new_description">Description:</label>
        <input type="text" name="new_description" id="new_description" required>
        <br>
        <input type="submit" value="Add task" class='add-button'>
    </form>

    <script>
        window.addEventListener('load', function() {
            const taskBoxes = document.querySelectorAll('.task-box');
            taskBoxes.forEach(box => {
                const checkbox = box.querySelector('.task-checkbox');
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        box.classList.add('completed');
                    } else {
                        box.classList.remove('completed');
                    }
                });
            });
        });
    </script>
</body>
</html>