<?php
// Start the session
session_start();

// Generate a unique CSRF token
$token = uniqid(rand(), true);

// Store the token and its creation time in the session
$_SESSION['token'] = $token;
$_SESSION['token_time'] = time();

// Database connection
try {
  $dbCo = new PDO(
    'mysql:host=db;dbname=todolist;charset=utf8',
    'user1',
    'passworduser'
  );
  $dbCo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
  die('Unable to connect to the database.' . $e->getMessage());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Verify the CSRF token
  if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
    die('Invalid CSRF token.');
  }

  if (isset($_POST["task"])) {
    // Update task completion status
    $sql = "UPDATE task SET completed = :completed WHERE Id_task = :Id_task";
    $stmt = $dbCo->prepare($sql);
    $stmt->bindParam(':completed', $_POST["completed"]);
    $stmt->bindParam(':Id_task', $_POST["task"]);
    $stmt->execute();
  } else {
    // Add new task
    if (!empty($_POST["description"])) {
     
      $stmt = $dbCo->prepare("INSERT INTO task (create_date, description, completed) VALUES (CURRENT_DATE, :description, FALSE)");
      $stmt->bindParam(':description', $_POST["description"]);
      $stmt->execute();
    }
  }
}

// Display tasks
$stmt = $dbCo->prepare("SELECT Id_task, description, completed FROM task ORDER BY create_date DESC");
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDoList</title>
    <link rel="stylesheet" href="list.css">
   
</head>
<body>

<h1 class='main-ttl'>My ToDo List</h1>

<div class="form-container">
    <?php
    if ($stmt->rowCount() > 0) {
      echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
      echo "<input type='hidden' name='token' value='" . $token . "'>";
      while ($row = $stmt->fetch()) {
        if (!$row["completed"]) {
          echo "<div class='task-box'>";
          echo "<input type='checkbox' name='task' value='" . $row["Id_task"] . "' class='task-checkbox'>";
          echo "<label for='task_" . $row["Id_task"] . "'>" . $row["description"] . "</label>";
          echo "<input type='hidden' name='completed' value='1'>";
          echo "</div>";
        }
      }
      echo "<input type='submit' value='Update tasks' class='update-button'>";
      echo "</form>";
    } else {
      echo "No tasks found";
    }

    // Close database connection
    $dbCo = null;
    ?>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="add-task-form">
      <input type="hidden" name="token" value="<?php echo $token; ?>">
      <label for="description">Description:</label>
      <input type="text" name="description" id="description" required>
      <br>
      <input type="submit" value="Add task" class="add-button">
    </form>
</div>

</body>
</html>