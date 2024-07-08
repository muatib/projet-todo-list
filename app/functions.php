<?php

/**
 * Connecte à la base de données.
 * 
 * @return PDO Objet de connexion PDO.
 */
function connectDb() {
    global $dbConfig;
    try {
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
        return new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        die('Erreur de connexion à la base de données : ' . $e->getMessage());
    }
}

// === Fonctions de gestion des tokens CSRF ===

/**
 * Génère un nouveau token CSRF et le stocke dans la session.
 */
function generateCsrfToken() {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

/**
 * Valide le token CSRF soumis avec un formulaire.
 * 
 * @return bool Vrai si le token est valide, faux sinon.
 */
function validateCsrfToken() {
    $isValid = isset($_POST['token']) && hash_equals($_SESSION['token'], $_POST['token']);
    error_log("Validation CSRF : " . ($isValid ? "Succès" : "Échec")); // Journalisation pour le débogage
    return $isValid;
}

// === Fonctions de gestion des tâches ===

/**
 * Met à jour l'ordre des tâches dans la base de données.
 * 
 * @param PDO $dbCo Connexion à la base de données.
 */
function updateTaskOrderOnServer($dbCo) {
    if (isset($_POST['taskOrder']) && validateCsrfToken()) { // Ajout de la validation du token CSRF
        $taskOrder = json_decode($_POST['taskOrder'], true);
        $stmt = $dbCo->prepare("UPDATE task SET `order` = ? WHERE Id_task = ?");

        try {
            $dbCo->beginTransaction(); // Début de la transaction

            foreach ($taskOrder as $order => $taskId) {
                $stmt->execute([$order + 1, $taskId]);
            }

            $dbCo->commit(); // Validation de la transaction

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $dbCo->rollBack(); // Annulation de la transaction en cas d'erreur
            error_log("Erreur lors de la mise à jour de l'ordre des tâches : " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour.']);
        }

    } else {
        echo json_encode(['success' => false, 'error' => 'Données manquantes ou token CSRF invalide.']);
    }
}

/**
 * Handles the update of task completion status based on form submission.
 *
 * @param PDO $dbCo The database connection object.
 */
function handleTaskCompletion($dbCo)
{
    if (isset($_POST["task"], $_POST["completed"])) {
        $stmt = $dbCo->prepare("UPDATE task SET completed = ? WHERE Id_task = ?");
        if ($stmt->execute([(int)$_POST["completed"], (int)$_POST["task"]])) {
            echo "<p class='success-message'>Task updated successfully!</p>";
        } else {
            echo "<p class='error-message'>Error updating task.</p>";
        }
    }
}

/**
 * Handles the update of task descriptions based on form submission.
 *
 * @param PDO $dbCo The database connection object.
 */
function handleTaskDescriptions($dbCo)
{
    $updateStmt = $dbCo->prepare("UPDATE task SET description = ?, reminder_date = ? WHERE Id_task = ?");
    $descriptionsUpdated = false;

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'description_') === 0) {
            $taskId = (int)substr($key, strlen('description_'));
            $reminderDate = isset($_POST['reminder_date'][$taskId]) ? $_POST['reminder_date'][$taskId] : null;


            $reminderDateKey = str_replace('description_', 'reminder_date_', $key);


            $reminderDate = isset($_POST[$reminderDateKey]) && !empty($_POST[$reminderDateKey])
                ? $_POST[$reminderDateKey]
                : null;

            if ($updateStmt->execute([$value, $reminderDate, $taskId])) {
                $descriptionsUpdated = true;
            }
        }
    }

    if ($descriptionsUpdated) {
        echo "<p class='success-message'>Descriptions and reminder dates updated successfully!</p>";
    }
    if ($descriptionsUpdated) {
        echo "<p class='success-message'>Descriptions updated successfully!</p>";
    }


    if (isset($_POST["task"])) {
        $taskIds = $_POST["task_id"];
        $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
        $stmt = $dbCo->prepare("DELETE FROM task WHERE Id_task IN ($placeholders) AND completed = 1");
        $stmt->execute($taskIds);
        echo "<p class='success-message'>Selected tasks deleted successfully!</p>";
    }
}
/**
 * Handles the addition of a new task based on form submission.
 *
 * @param PDO $dbCo The database connection object.
 * @return array|null The newly added task as an associative array, or null if the addition failed.
 */
function handleNewTask($dbCo)
{
    if (!empty($_POST["new_description"])) {
        $dbCo->beginTransaction();
        try {

            $dbCo->exec("UPDATE task SET `order` = `order` + 1");


            $stmt = $dbCo->prepare("INSERT INTO task (create_date, description, completed, `order`) VALUES (CURRENT_DATE, ?, 0, 1)");
            $stmt->execute([$_POST["new_description"]]);

            $dbCo->commit();
            echo "<p class='success-message'>New task added successfully!</p>";

            $newTask = $dbCo->query("SELECT Id_task, description, completed, `order` FROM task WHERE `order` = 1")->fetch();
            return $newTask;
        } catch (Exception $e) {
            $dbCo->rollBack();
            echo "<p class='error-message'>Error adding new task: " . $e->getMessage() . "</p>";
        }
    }
    return null;
}

/**
 * Handles the updating of task order based on form submission.
 *
 * @param PDO $dbCo The database connection object.
 */
function handleTaskOrder($dbCo)
{
    if (isset($_POST['task_order'])) {
        $order = 1;
        $updateOrderStmt = $dbCo->prepare("UPDATE task SET `order` = ? WHERE Id_task = ?");
        foreach (explode(',', $_POST['task_order']) as $taskId) {
            $updateOrderStmt->execute([$order++, (int)$taskId]);
        }
        echo "<p class='success-message'>Task order updated successfully!</p>";
    }
}
function moveTask($dbCo, $taskId, $direction) {
    $stmt = $dbCo->prepare("SELECT `order` FROM task WHERE Id_task = ?");
    $stmt->execute([$taskId]);
    $currentOrder = $stmt->fetchColumn();

    $newOrder = $direction === 'up' ? $currentOrder - 1 : $currentOrder + 1;
    $oppositeOrder = $direction === 'up' ? $currentOrder + 1 : $currentOrder - 1;

    $dbCo->beginTransaction();
    try {
        $dbCo->exec("UPDATE task SET `order` = $currentOrder WHERE `order` = $newOrder");
        $dbCo->exec("UPDATE task SET `order` = $newOrder WHERE Id_task = $taskId");
        $dbCo->commit();
    } catch (Exception $e) {
        $dbCo->rollBack();
        echo "<p class='error-message'>Error moving task: " . $e->getMessage() . "</p>";
    }
}

function handleTaskThemes($dbCo) {
    if (isset($_POST['new_description']) && !empty($_POST['new_description'])) {
        $taskId = $dbCo->lastInsertId(); // Récupère l'ID de la tâche nouvellement insérée
        if (isset($_POST['themes'])) {
            $themeIds = $_POST['themes']; // Récupère les IDs des thèmes sélectionnés
            $insertStmt = $dbCo->prepare("INSERT INTO task_theme (id_task, id_theme) VALUES (?, ?)");
            foreach ($themeIds as $themeId) {
                $insertStmt->execute([$taskId, $themeId]);
            }
        }
    }
}

function getTaskThemes($dbCo, $taskId) {
    $stmt = $dbCo->prepare("SELECT t.name FROM theme t 
                            INNER JOIN task_theme tt ON t.id_theme = tt.id_theme
                            WHERE tt.id_task = ?");
    $stmt->execute([$taskId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN); // Récupère les noms des thèmes
}

/**
 * Stores an error message in the session.
 *
 * @param string $message The error message to store.
 */
function setErrorMessage($message)
{
    $_SESSION['error_message'] = $message;
}

/**
 * Retrieves and clears an error message from the session.
 *
 * @return string|null The error message if one exists, otherwise null.
 */
function getErrorMessage()
{
    if (isset($_SESSION['error_message'])) {
        $message = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        return $message;
    }
    return null;
}

/**
 * Stores a success message in the session.
 *
 * @param string $message The success message to store.
 */
function setSuccessMessage($message)
{
    $_SESSION['success_message'] = $message;
}

/**
 * Retrieves and clears a success message from the session.
 *
 * @return string|null The success message if one exists, otherwise null.
 */
function getSuccessMessage()
{
    if (isset($_SESSION['success_message'])) {
        $message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        return $message;
    }
    return null;
}