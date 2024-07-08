<?php
session_start();
include 'config.php';
include 'functions.php';

$dbCo = connectDb();
$themes = $dbCo->query("SELECT * FROM theme")->fetchAll();
if (!isset($_SESSION['token'])) {
    generateCsrfToken();
}

$tasks = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!validateCsrfToken()) {
        die('Erreur CSRF : Token invalide. Veuillez réessayer.');
    }

    if (isset($_POST["move"])) {
        $taskId = (int)$_POST["move"];
        $direction = $_POST["direction"];
        moveTask($dbCo, $taskId, $direction);
    }

    handleTaskCompletion($dbCo);
    handleTaskDescriptions($dbCo);

    if (isset($_POST['task'])) {
        $taskIds = $_POST['task'];
        $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
        $stmt = $dbCo->prepare("DELETE FROM task WHERE Id_task IN ($placeholders)");
        $stmt->execute($taskIds);
        echo "<p class='success-message'>Tâches cochées supprimées avec succès !</p>";
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

// Récupération des tâches dont la date de rappel est aujourd'hui
$tasksDueToday = array_filter($tasks, function ($task) {
    return !$task['completed'] && $task['reminder_date'] === date('Y-m-d');
});

// Affichage de la notification
if (!empty($tasksDueToday) && (!isset($_SESSION['notification_dismissed']) || $_SESSION['notification_dismissed'] !== date('Y-m-d'))) {
    echo "<div class='notification' id='notification'>";
    echo "<button id='dismissNotification'>Fermer</button>";
    echo "<p>Tâches à effectuer aujourd'hui :</p>";
    echo "<ul class='notification-list'>";
    foreach ($tasksDueToday as $task) {
        echo "<li>{$task['description']}</li>";
    }
    echo "</ul>";
    echo "</div>";
}
$newTask = handleNewTask($dbCo);
if ($newTask) {
    handleTaskThemes($dbCo); // Gérer les thèmes de la nouvelle tâche
    array_unshift($tasks, $newTask);
}


include 'task_list.php';
?>