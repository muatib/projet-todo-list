<?php
session_start();
include 'config.php';
include 'functions.php';

// Database connection
$dbCo = connectDb();

// CSRF token management
if (!isset($_SESSION['token']) || (time() - $_SESSION['token_time'] > 3600)) {
    generateCsrfToken();
}

// Initialize tasks as an empty array to prevent errors if the query fails
$tasks = []; 

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    validateCsrfToken();
    handleTaskCompletion($dbCo);
    handleTaskDescriptions($dbCo);

    // Add new task at the top of the list and update $tasks
    $newTask = handleNewTask($dbCo); 
    if ($newTask) {
        array_unshift($tasks, $newTask); 
    }

    handleTaskOrder($dbCo);
    generateCsrfToken(); 
}

// Fetch tasks 
$tasksResult = $dbCo->query("SELECT Id_task, description, completed, `order` FROM task ORDER BY `order` ASC");
if ($tasksResult) {
    $tasks = $tasksResult->fetchAll();
}

// Include the HTML for the task list
include 'task_list.php';
?>