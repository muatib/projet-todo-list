<?php
session_start();
include 'config.php';
include 'functions.php';


$dbCo = connectDb();


if (!isset($_SESSION['token']) || (time() - $_SESSION['token_time'] > 3600)) {
    generateCsrfToken();
}


$tasks = []; 


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    validateCsrfToken();

    if (isset($_POST["move_up"])) {
        $taskId = (int)$_POST["move_up"];
        moveTaskUp($dbCo, $taskId);
    } elseif (isset($_POST["move_down"])) {
        $taskId = (int)$_POST["move_down"];
        moveTaskDown($dbCo, $taskId);
    }
    
    handleTaskCompletion($dbCo);
    handleTaskDescriptions($dbCo);

   
    if (isset($_POST["task"])) {
        $taskIds = $_POST["task"]; 
        $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
        $stmt = $dbCo->prepare("DELETE FROM task WHERE Id_task IN ($placeholders)"); 
        $stmt->execute($taskIds);
        echo "<p class='success-message'>Completed tasks deleted successfully!</p>";
    }

    
    $newTask = handleNewTask($dbCo); 
    if ($newTask) {
        array_unshift($tasks, $newTask); 
    }

    handleTaskOrder($dbCo);
    generateCsrfToken(); 
}


$tasksResult = $dbCo->query("SELECT Id_task, description, completed, `order`, reminder_date FROM task ORDER BY `order` ASC");
if ($tasksResult) {
    $tasks = $tasksResult->fetchAll();
}


$tasksDueToday = array_filter($tasks, function($task) {
    return !$task['completed'] && $task['reminder_date'] === date('Y-m-d');
});


include 'task_list.php';

if (!empty($tasksDueToday)) {
    echo "<div class='notification'>";
    echo "<p>Tâches à effectuer aujourd'hui :</p>";
    echo "<ul class='notification-list'>";
    foreach ($tasksDueToday as $task) {
        echo "<li>{$task['description']}</li>";
    }
    echo "</ul>";
    echo "</div>";
}
?>