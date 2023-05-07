<?php
require 'config.php';
require 'utils.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = sanitize_input($_POST["username"]);
  $email = sanitize_input($_POST["email"]);
  $password = sanitize_input($_POST["password"]);

  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sss", $username, $email, $hashed_password);
  $stmt->execute();
  $stmt->close();

  header("Location: login.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
</head>
<body>
  <h1>Register</h1>
  <form action="register.php" method="POST">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br><br>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br><br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br><br>
    <input type="submit" value="Register">
  </form>
  <p>Do you already have a user? <a href="login.php">Login</a></p>
</body>
</html>