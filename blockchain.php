<?php
session_start();

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database configuration
include_once '/u/g/e2202982/public_html/php/projekti/config/db_config.php';

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
    $sender = mysqli_real_escape_string($connection, $_POST['sender']);
    $receiver = mysqli_real_escape_string($connection, $_POST['receiver']);
    $amount = floatval($_POST['amount']); // Convert amount to float

    // Retrieve the latest blockchain index
    $sqlLatestIndex = "SELECT MAX(block_index) AS max_index FROM votes";
    $resultLatestIndex = mysqli_query($connection, $sqlLatestIndex);
    $rowLatestIndex = mysqli_fetch_assoc($resultLatestIndex);
    $blockchain_index = ($rowLatestIndex['max_index'] !== null) ? $rowLatestIndex['max_index'] + 1 : 1;

    // Retrieve the previous block's current hash
    $sqlPreviousHash = "SELECT hash FROM votes WHERE id = $blockchain_index - 1";
    $resultPreviousHash = mysqli_query($connection, $sqlPreviousHash);
    $rowPreviousHash = mysqli_fetch_assoc($resultPreviousHash);
    $previousHash = ($rowPreviousHash['hash'] !== null) ? $rowPreviousHash['hash'] : "0";

    $timestamp = date("Y-m-d H:i:s");
    $hashedData = $blockchain_index . $timestamp . $previousHash . $sender . $receiver . $amount;
    $currentHash = hash("sha256", $hashedData);

    // Insert data into the database
    $sql = "INSERT INTO votes (user_id, vote, timestamp, previous_hash, hash) VALUES ({$_SESSION['user_id']}, '$sender $receiver $amount', '$timestamp', '$previousHash', '$currentHash')";

    $result = mysqli_query($connection, $sql);

    if (!$result) {
        die("Query failed: " . mysqli_error($connection));
    }

    // Redirect to avoid resubmission and that it doesn't create new timestamp every time when refreshing.
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

// Function to fetch blockchain data from the database
function getBlockchainData($connection)
{
    $query = "SELECT * FROM votes";
    $result = mysqli_query($connection, $query);

    $blockchainData = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $blockchainData[] = array(
            'index' => $row['id'],
            'timestamp' => $row['timestamp'],
            'prev_hash' => $row['previous_hash'],
            'curr_hash' => $row['hash'],
            'vote' => $row['vote'],
        );
    }

    return $blockchainData;
}

// Fetch blockchain data
$blockchainData = getBlockchainData($connection);

// Close the database connection
mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blockchain Voting Web App</title>
    <style>
        h2 {
            text-align: center;
            color: orangered;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: orangered;
            color: white;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tbody tr:nth-child(odd) {
            background-color: white;
        }

        .logout-link {
            text-align: center;
            margin-top: 10px;
            border-radius: 5px;
            background-color: #f2f2f2;
            padding: 20px;
            font-size: 20px;
        }

        .logout-link a {
            color: red;
        }
    </style>
</head>

<body>
    <h2>Voting Blockchain Data</h2>
    <table>
        <thead>
            <tr>
                <th>Block Index</th>
                <th>Timestamp</th>
                <th>Previous Hash</th>
                <th>Current Hash</th>
                <th>Vote</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($blockchainData as $block) {
                echo "<tr>";
                echo "<td>{$block['index']}</td>";
                echo "<td>{$block['timestamp']}</td>";
                echo "<td>{$block['prev_hash']}</td>";
                echo "<td>{$block['curr_hash']}</td>";
                echo "<td>{$block['vote']}</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    <div class="logout-link">
        <p>You are logged in as
            <?php echo $_SESSION['username']; ?>. <br>
            <a href="?logout=1">Logout</a>
        </p>
    </div>
</body>

</html>
