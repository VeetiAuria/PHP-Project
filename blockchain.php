<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database configuration
include_once '/u/g/e2202982/public_html/php/projekti/PHP-Project/config/db_config.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy(); // Destroy the session
    header("Location: login.php"); // Redirect to login page after logout
    exit();
}

// Establish database connection
$connection = mysqli_connect($servername, $username, $password, $dbname);
date_default_timezone_set("Europe/Helsinki");

// Check for connection errors
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
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
            'user_id' => $row['user_id'],
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
    <title>Voting Results</title>
    <link rel="stylesheet" type="text/css" href="css/blockchain.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <!-- Voting Blockchain Data -->
    <h2>Voting Blockchain Data</h2>
    <table>
        <thead>
            <tr>
                <th>Block Index</th>
                <th>Timestamp</th>
                <th>Vote</th>
                <th>Hashed User ID</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($blockchainData as $block) {
                echo "<tr>";
                echo "<td>{$block['index']}</td>";
                echo "<td>{$block['timestamp']}</td>";
                echo "<td>{$block['vote']}</td>";
                echo "<td>" . hash("sha256", $block['user_id']) . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Votes in Charts -->
    <h2>Votes in Charts</h2>
    <div id="chartContainer" style="display: flex; justify-content: space-between;">
        <div style="width: 45%;">
            <canvas id="voteChart"></canvas>
        </div>
        <div style="width: 45%;">
            <canvas id="totalVotesChart"></canvas>
        </div>
    </div>

    <!-- Logout link -->
    <div class="logout-link">
        <p>You are logged in as <?php echo $_SESSION['username']; ?>. <br>
            <a href="?logout=1">Logout</a>
        </p>
    </div>

    <!-- JavaScript code -->
    <script>
        var blockchainData = <?php echo json_encode($blockchainData); ?>;

        var voteCounts = {};
        blockchainData.forEach(function (block) {
            var vote = block.vote;
            if (voteCounts[vote]) {
                voteCounts[vote]++;
            } else {
                voteCounts[vote] = 1;
            }
        });

        var ctx = document.getElementById('voteChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(voteCounts),
                datasets: [{
                    label: 'Vote Counts',
                    data: Object.values(voteCounts),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Calculate and display total votes
        var totalVotes = Object.values(voteCounts).reduce(function (acc, count) {
            return acc + count;
        }, 0);

        // Create chart for total votes
        var totalVotesData = {
            labels: ['Total Votes'],
            datasets: [{
                label: 'Total Votes',
                data: [totalVotes],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        };

        var totalVotesCtx = document.getElementById('totalVotesChart').getContext('2d');
        var totalVotesChart = new Chart(totalVotesCtx, {
            type: 'bar',
            data: totalVotesData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>
