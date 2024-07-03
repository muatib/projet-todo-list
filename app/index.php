<?php
session_start();
include 'config.php';
include 'functions.php';

$dbCo = connectDb();

// CSRF token management
if (!isset($_SESSION['token']) || (time() - $_SESSION['token_time'] > 3600)) {
    generateCsrfToken();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    validateCsrfToken();
    handleTaskCompletion($dbCo);
    handleTaskDescriptions($dbCo);
    handleNewTask($dbCo);
    generateCsrfToken();
}

// Fetch tasks (en utilisant une requête préparée)
$stmt = $dbCo->prepare("SELECT Id_task, description, completed, `order` FROM task ORDER BY `order` ASC");
$stmt->execute();
$tasks = $stmt->fetchAll();

// Include the HTML for the task list
include 'task_list.php';
?>