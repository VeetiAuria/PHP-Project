<?php
session_start();

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database configuration and other existing code
include_once '/u/g/e2202982/public_html/php/harj10/config/db_config.php';

// Handle logout if the logout parameter is set
if (isset($_GET['logout'])) {
    session_destroy(); // Destroy the session
    header("Location: login.php"); // Redirect to login page after logout
    exit();
}

// Establish database connection!
$connection = mysqli_connect($servername, $username, $password, $dbname);
date_default_timezone_set("Europe/Helsinki");

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Close the database connection
mysqli_close($connection);
?>


<!DOCTYPE html>
<html>
<head>
  <title>Money Transfer Blockchain Web App</title>
</head>
<body>

  <h2>Money Transfer Blockchain Web App.</h2>

  <div>
    <form action="blockchain.php" method="POST">
      <label for="sender">Sender:</label>
      <input type="text" id="sender" name="sender" placeholder="Write sender's name here.">

      <label for="receiver">Receiver:</label>
      <input type="text" id="receiver" name="receiver" placeholder="Write receiver's name here.">

      <label for="amount">Amount:</label>
      <input type="text" id="amount" name="amount" placeholder="Write amount here.">

      <input type="submit" value="Submit">
    </form>
  </div>

  <div class="logout-link">
        <p>You are logged in as <?php echo $_SESSION['username']; ?>. <br>
        <a href="?logout=1">Logout</a></p>
    </div>
</body>
</html>
