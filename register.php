<?php
// Include database configuration
include_once '/u/g/e2202982/public_html/php/harj10/config/db_config.php';

// Establish database connection
$connection = mysqli_connect($servername, $username, $password, $dbname);
date_default_timezone_set("Europe/Helsinki");

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Process registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data to prevent SQL injection
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username or email already exists
    $checkExistingUser = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $resultExistingUser = mysqli_query($connection, $checkExistingUser);

    if (mysqli_num_rows($resultExistingUser) > 0) {
        $registrationError = "Username or email already exists!";
    } else {
        // Insert new user into the database
        $sqlRegister = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        $resultRegister = mysqli_query($connection, $sqlRegister);

        if (!$resultRegister) {
            die("Registration failed: " . mysqli_error($connection));
        }

        // Redirect to login page after successful registration
        header("Location: login.php");
        exit();
    } 
}

// Close the database connection
mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
</head>
<body>

<h2>Register</h2>

<form action="" method="POST">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <input type="submit" value="Register">
</form>

<div class="error">
    <?php echo isset($registrationError) ? $registrationError : ''; ?>
</div>

<div class="login-link">
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>
