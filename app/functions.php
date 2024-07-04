<?php


/**
 * Connects to the database using the configuration defined in config.php.
 *
 * @return PDO|null The PDO database connection object, or null if the connection fails.
 */
function connectDb()
{
    global $dbConfig;
    try {
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
        return new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

/**
 * Generates a new CSRF token and stores it in the session.
 * Also sets the timestamp for token generation.
 */
function generateCsrfToken()
{
    $_SESSION['token'] = bin2hex(random_bytes(32));
    $_SESSION['token_time'] = time();
}

/**
 * Validates the CSRF token submitted with a form.
 * If the token is invalid or expired, generates a new one and terminates the script.
 */
function validateCsrfToken()
{
    if (!hash_equals($_SESSION['token'], $_POST['token'] ?? '')) {
        generateCsrfToken();
        die('Invalid CSRF token. Please try again.');
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
function moveTaskUp($dbCo, $taskId)
{
    $stmt = $dbCo->prepare("SELECT `order` FROM task WHERE Id_task = ?");
    $stmt->execute([$taskId]);
    $currentOrder = $stmt->fetchColumn();

    if ($currentOrder > 1) {
        $newOrder = $currentOrder - 1;
        $dbCo->exec("UPDATE task SET `order` = $currentOrder WHERE `order` = $newOrder");
        $dbCo->exec("UPDATE task SET `order` = $newOrder WHERE Id_task = $taskId");
    }
}

function moveTaskDown($dbCo, $taskId)
{
    $stmt = $dbCo->prepare("SELECT `order` FROM task WHERE Id_task = ?");
    $stmt->execute([$taskId]);
    $currentOrder = $stmt->fetchColumn();

    $maxOrderStmt = $dbCo->query("SELECT MAX(`order`) FROM task");
    $maxOrder = $maxOrderStmt->fetchColumn();

    if ($currentOrder < $maxOrder) {
        $newOrder = $currentOrder + 1;
        $dbCo->exec("UPDATE task SET `order` = $currentOrder WHERE `order` = $newOrder");
        $dbCo->exec("UPDATE task SET `order` = $newOrder WHERE Id_task = $taskId");
    }
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
