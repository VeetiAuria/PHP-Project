<?php
session_start();

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database configuration and other existing code
include_once '/u/g/e2202982/public_html/php/projekti/PHP-Project/config/db_config.php';

// Handle logout if the logout parameter is set
if (isset($_GET['logout'])) {
    session_destroy(); // Destroy the session
    header("Location: login.php"); // Redirect to login page after logout
    exit();
}

// Establish database connection
$connection = mysqli_connect($servername, $username, $password, $dbname);
date_default_timezone_set("Europe/Helsinki");

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data to prevent SQL injection
    $selectedChoice = mysqli_real_escape_string($connection, $_POST['selectedChoice']);

    // Get the previous hash from the latest database entry
    $prevHash = '';
    $latestQuery = "SELECT * FROM votes ORDER BY id DESC LIMIT 1";
    $latestResult = mysqli_query($connection, $latestQuery);

    if ($latestRow = mysqli_fetch_assoc($latestResult)) {
        $prevHash = $latestRow['hashed_vote'];
    }

    // Hash the combination of the selected choice and previous hash
    $hashedChoice = hash('sha256', $selectedChoice . $prevHash);

    // Insert data into the database
    $sql = "INSERT INTO votes (user_id, vote, hashed_vote, previous_hash) VALUES ({$_SESSION['user_id']}, '$selectedChoice', '$hashedChoice', '$prevHash')";

    $result = mysqli_query($connection, $sql);

    if (!$result) {
        die("Query failed: " . mysqli_error($connection));
    }

    // Redirect to blockchain.php after successful vote
    header("Location: blockchain.php");
    exit();
}

// Function to fetch voting data from the database
function getVotingData($connection)
{
    $query = "SELECT * FROM votes";
    $result = mysqli_query($connection, $query);

    $votingData = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $votingData[] = array(
            'selected_choice' => $row['vote'],
        );
    }

    return $votingData;
}

// Fetch voting data
$votingData = getVotingData($connection);

// Close the database connection
mysqli_close($connection);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Music Voting Web App</title>
  <link rel="stylesheet" type="text/css" href="css/index.css">
</head>

<body>
  <!-- Navigation bar -->
  <nav>
    <ul>
      <li><a href="index.php">Voting</a></li>
      <li><a href="blockchain.php">Results</a></li>
      <li style="float: right;"><a href="?logout=1">Log Out</a></li>
    </ul>
  </nav>

  <!-- Title - Vote for Your Favorite Music Genre -->
  <h2>Vote for Your Favorite Music Genre</h2>

  <!-- Voting Form -->
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <!-- Choices Box -->
    <div class="choices-box">
      <?php
      $genres = array("rock", "pop", "hiphop", "country", "jazz");

      foreach ($genres as $genre) {
        ?>
        <div class="choice-row">
          <input type="radio" name="selectedChoice" value="<?php echo $genre; ?>" id="choice-<?php echo $genre; ?>"
            style="display: none;" required>
          <label class="choice-label" onclick="toggleChoice('<?php echo $genre; ?>')">
            <?php echo ucfirst($genre); ?>
          </label>
        </div>
        <?php
      }
      ?>
    </div>

    <!-- Vote Button -->
    <input type="submit" value="Vote" id="voteButton" disabled>
  </form>

  <!-- Logout link -->
  <div class="logout-link">
    <p>You are logged in as
      <?php echo $_SESSION['username']; ?>. <br>
      <a href="?logout=1">Logout</a>
    </p>
  </div>

  <!-- JavaScript code -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var voteButton = document.getElementById('voteButton');
      var choiceRows = document.querySelectorAll('.choice-row');

      choiceRows.forEach(function (row) {
        row.addEventListener('click', function () {
          var choice = this.querySelector('input[type="radio"]');
          choice.checked = true;

          // Deselect other choices
          choiceRows.forEach(function (otherRow) {
            if (otherRow !== row) {
              var otherChoice = otherRow.querySelector('input[type="radio"]');
              otherChoice.checked = false;
              otherRow.classList.remove('selected');
            }
          });

          // Toggle 'selected' class for the clicked choice
          this.classList.toggle('selected');

          // Enable/disable the 'Vote' button based on selection
          var selectedChoices = document.querySelectorAll('.choice-row.selected');
          voteButton.disabled = selectedChoices.length === 0;
        });
      });

      // Add click event listener to the 'Vote' button
      voteButton.addEventListener('click', function () {
        // Trigger form submission when the 'Vote' button is clicked
        document.querySelector('form').submit();
      });
    });
  </script>
</body>

</html>