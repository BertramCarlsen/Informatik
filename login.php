<?php
session_start();
require 'config.php';
require 'utils.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = sanitize_input($_POST["username"]);
  $password = sanitize_input($_POST["password"]);

  $sql = "SELECT id, password FROM users WHERE username = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($user_id, $hashed_password);
  $stmt->fetch();

  if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
    $_SESSION["user_id"] = $user_id;
    header("Location: index.php");
  } else {
    $error_message = "Invalid username or password";
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
</head>
<body>
  <h1>Login</h1>
  <?php if (isset($error_message)): ?>
    <p><?php echo $error_message; ?></p>
  <?php endif; ?>
  <form action="login.php" method="POST">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br><br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br><br>
    <input type="submit" value="Login">
  </form>
  <p>Don't have an account? <a href="register.php">Register</a></p>
</body>
</html>