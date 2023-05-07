<?php
session_start();

if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit();
}

require 'config.php';
require 'utils.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST["add_note"])) {
    $title = sanitize_input($_POST["title"]);
    $content = sanitize_input($_POST["content"]);

    $sql = "INSERT INTO notes (user_id, title, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $_SESSION["user_id"], $title, $content);
    $stmt->execute();
    $stmt->close();
  } elseif (isset($_POST["delete_note"])) {
    $note_id = $_POST["note_id"];

    $sql = "DELETE FROM notes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $stmt->close();
  } elseif (isset($_POST["edit_note"])) {
    $note_id = $_POST["note_id"];
    $title = sanitize_input($_POST["title"]);
    $content = sanitize_input($_POST["content"]);
  
    $sql = "UPDATE notes SET title = ?, content = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $title, $content, $note_id, $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->close();
  }

  if (isset($_POST["add_task"])) {
    $title = sanitize_input($_POST["title"]);
    $due_date = !empty($_POST["due_date"]) ? sanitize_input($_POST["due_date"]) : NULL;

    $sql = "INSERT INTO tasks (user_id, title, due_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $_SESSION["user_id"], $title, $due_date);
    $stmt->execute();
    $stmt->close();
  } elseif (isset($_POST["delete_task"])) {
    $task_id = $_POST["task_id"];

    $sql = "DELETE FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $stmt->close();
  } elseif (isset($_POST["edit_task"])) {
    $task_id = $_POST["task_id"];
    $title = sanitize_input($_POST["title"]);
    $due_date = !empty($_POST["due_date"]) ? $_POST["due_date"] : NULL;

    $sql = "UPDATE tasks SET title = ?, due_date = ?, WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiii", $title, $due_date, $task_id, $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit();
  }

}

// Fetch notes from the database
$sql = "SELECT id, title, content, created_at FROM notes WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();

// Fetch tasks from the database
$sql_tasks = "SELECT id, title, due_date, created_at FROM tasks WHERE user_id = ? ORDER BY created_at DESC";
$stmt_tasks = $conn->prepare($sql_tasks);
$stmt_tasks->bind_param("i", $_SESSION["user_id"]);
$stmt_tasks->execute();
$result_tasks = $stmt_tasks->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Notes Website</title>
  <script>
  function toggleEditNoteForm(note_id) {
    var editForms = document.getElementsByClassName("edit-note-form");

    for (var i = 0; i < editForms.length; i++) {
      if (editForms[i].id === "edit-note-form-" + note_id) {
        editForms[i].style.display = editForms[i].style.display === "none" ? "block" : "none";
      } else {
        editForms[i].style.display = "none";
      }
    }
  }

  function toggleEditTaskForm(task_id) {
    var editForms = document.getElementsByClassName("edit-task-form");

    for (var i = 0; i < editForms.length; i++) {
      if (editForms[i].id === "edit-task-form-" + task_id) {
        editForms[i].style.display = editForms[i].style.display === "none" ? "block" : "none";
      } else {
        editForms[i].style.display = "none";
      }
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    var editForms = document.getElementsByClassName("edit-note-form");
    for (var i = 0; i < editForms.length; i++) {
        editForms[i].style.display = "none";
    }

    var editTaskForms = document.getElementsByClassName("edit-task-form");
    for (var i = 0; i < editTaskForms.length; i++) {
        editTaskForms[i].style.display = "none";
    }
  });

</script>
</head>
<body>

<div class="container">
<div class="notes-section">
  <h1>Notes</h1>
  <form action="index.php" method="POST">
    <label for="title">Title:</label>
    <input type="text" id="title" name="title" required><br><br>
    <label for="content">Content:</label>

    <textarea name="content" id="content" rows="4" cols="50"></textarea>
    <input type="submit" name="add_note" value="Add Note">
  </form>

  

  <?php if ($result->num_rows > 0): ?>
    <ul>
      <?php while($row = $result->fetch_assoc()): ?>
        <li>
          <h3><?php echo htmlspecialchars($row["title"]); ?></h3>
          <p><?php echo nl2br(htmlspecialchars($row["content"])); ?></p>
          <p><em>Created on <?php echo $row["created_at"]; ?></em></p>
          <form action="index.php" method="POST">
            <input type="hidden" name="note_id" value="<?php echo $row["id"]; ?>">
            <input type="submit" name="delete_note" value="Delete Note">
          </form>
          <button type="button" onclick="toggleEditNoteForm(<?php echo $row['id']; ?>)">Edit Note</button>
          <form id="edit-note-form-<?php echo $row['id']; ?>" class="edit-note-form" action="index.php" method="POST">
            <input type="hidden" name="note_id" value="<?php echo $row["id"]; ?>">
            <label for="title-<?php echo $row['id']; ?>">Title:</label>
            <input type="text" id="title-<?php echo $row['id']; ?>" name="title" value="<?php echo htmlspecialchars($row["title"]); ?>" required><br><br>
            <label for="content-<?php echo $row['id']; ?>">Content:</label>
            <textarea id="content-<?php echo $row['id']; ?>" name="content" rows="4" cols="50" required><?php echo htmlspecialchars($row["content"]); ?></textarea><br><br>
            <input type="submit" name="edit_note" value="Save Changes">
          </form>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php else: ?>
    <p>No notes found.</p>
  <?php endif; ?>
  </div>

<div class="tasks-section">
  <h1>Tasks</h1>
  <form action="index.php" method="POST">
    <label for="task_title">Task:</label>
    <input type="text" id="task_title" name="title" required><br><br>
    <label for="task_due_date">Due Date:</label>
    <input type="datetime-local" id="task_due_date" name="due_date"><br><br>
    <input type="submit" name="add_task" value="Add Task">
  </form>

  <?php if ($result_tasks->num_rows > 0): ?>
  <ul>
    <?php while($row_tasks = $result_tasks->fetch_assoc()): ?>
    <li>
        <h3><?php echo htmlspecialchars($row_tasks["title"]); ?></h3>
        <p><em>Created on <?php echo $row_tasks["created_at"]; ?></em></p>
        <p><strong>Due on: <?php echo $row_tasks["due_date"]; ?></strong></p>
        <form action="index.php" method="POST" style="display: inline;">
            <input type="hidden" name="task_id" value="<?php echo $row_tasks["id"]; ?>">
            <input type="submit" name="delete_task" value="Delete Task">
        </form>
        <button type="button" onclick="toggleEditTaskForm(<?php echo $row_tasks['id']; ?>)">Edit Task</button>
        <form id="edit-task-form-<?php echo $row_tasks['id']; ?>" class="edit-task-form" action="index.php" method="POST">
            <input type="hidden" name="task_id" value="<?php echo $row_tasks["id"]; ?>">
            <label for="title-<?php echo $row_tasks['id']; ?>">Title:</label>
            <input type="text" id="title-<?php echo $row_tasks['id']; ?>" name="title" value="<?php echo htmlspecialchars($row_tasks["title"]); ?>" required><br><br>
            <label for="task_due_date-<?php echo $row_tasks['id']; ?>">Due Date:</label>
            <input type="datetime-local" id="task_due_date-<?php echo $row_tasks['id']; ?>" name="due_date" value="<?php echo $row_tasks["due_date"]; ?>"><br><br>
            <input type="submit" name="edit_task" value="Save Changes">
        </form>

    </li>
    <?php endwhile; ?>
  </ul>
<?php else: ?>
  <p>No tasks found.</p>
<?php endif; ?>
</div>
</div>

<?php
    $conn->close();
?>

<p><a href="logout.php">Logout</a></p>
</body>
</html>