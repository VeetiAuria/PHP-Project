<?php
// Include database configuration
include_once '/u/g/e2202982/public_html/php/projekti/PHP-Project/config/db_config.php';

// Establish database connection
$connection = mysqli_connect($servername, $username, $password, $dbname);
date_default_timezone_set("Europe/Helsinki");

// Check for connection errors
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data to prevent SQL injection
    $loginUsername = mysqli_real_escape_string($connection, $_POST['loginUsername']);
    // Use prepared statements to prevent SQL injection for passwords
    $loginPassword = $_POST['loginPassword'];

    $sqlLogin = "SELECT * FROM users WHERE username='$loginUsername'";
    $resultLogin = mysqli_query($connection, $sqlLogin);

    if ($resultLogin && mysqli_num_rows($resultLogin) > 0) {
        $row = mysqli_fetch_assoc($resultLogin);
        if (password_verify($loginPassword, $row['password'])) {
            // After successful login
            session_start();
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: index.php");
            exit();
        } else {
            $loginError = "Incorrect password!";
        }
    } else {
        $loginError = "User not found!";
    }
}

// Close the database connection
mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <title>Login</title>
</head>

<body>
    <!-- Navigation bar -->
    <nav>
        <ul>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    </nav>

    <!-- Login form -->
    <h2>Login</h2>
    <p>Please login to proceed to voting!</p>
    <form action="login.php" method="POST">
        <label for="loginUsername">Username:</label>
        <input type="text" id="loginUsername" name="loginUsername" required>

        <label for="loginPassword">Password:</label>
        <input type="password" id="loginPassword" name="loginPassword" required>

        <input type="submit" value="Login">
    </form>

    <!-- Display login error, if any -->
    <div class="error">
        <?php echo isset($loginError) ? $loginError : ''; ?>
    </div>

    <!-- Register link -->
    <div class="register-link">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>

</html>
