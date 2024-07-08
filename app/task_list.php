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
        <?php if (!empty($tasks)) : ?>
            <form method="post" action="" id="taskForm">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token']) ?>">
                <input type="hidden" name="task_order" id="taskOrder">
                <div id="taskList">
                    <?php foreach ($tasks as $task) : ?>
                        <div class='task-box' data-id="<?= $task["Id_task"] ?>">
                            <div class="task-content">
                                <input type="checkbox" name="task[]" value="<?= $task["Id_task"] ?>" class='task-checkbox' <?= $task['completed'] ? 'checked' : '' ?>>
                                <input type="hidden" name="task_id[]" value="<?= $task["Id_task"] ?>">
                                <input type="text" name="description_<?= $task["Id_task"] ?>" value="<?= htmlspecialchars($task["description"]) ?>" class='task-description'>
                                <input type="date" name="reminder_date[]" value="<?= $task["reminder_date"] ?? '' ?>">
                                <div class="task-themes">
                                    <?php foreach (getTaskThemes($dbCo, $task["Id_task"]) as $themeName) : ?>
                                        <span class="theme-tag"><?= $themeName ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="button-container">
                                <form method="post" action="">
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token']) ?>">
                                    <input type="hidden" name="move" value="<?= $task["Id_task"] ?>">
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit">▲</button>
                                </form>
                                <form method="post" action="">
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token']) ?>">
                                    <input type="hidden" name="move" value="<?= $task["Id_task"] ?>">
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit">▼</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="submit" value="Update tasks" class='update-button'>
                <button id="toggleCompletedTasks">Afficher les tâches terminées</button>
            </form>
        <?php else : ?>
            <p>No tasks found</p>
        <?php endif; ?>
    </div>
    <h2 class='add-task-ttl'>Add a new task</h2>
    <form method="post" action="" class="add-task-form">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token']) ?>">
        <label for="new_description">Description:</label>
        <input type="text" name="new_description" id="new_description">
        <div class="themes-container">
            <?php foreach ($themes as $theme) : ?>
                <label>
                    <input type="checkbox" name="themes[]" value="<?= $theme['id_theme'] ?>">
                    <?= $theme['name'] ?>
                </label>
            <?php endforeach; ?>
        </div>
        <input type="submit" value="Add task" class='add-button'>
    </form>
</body>
<script src="script.js"></script>

</html>