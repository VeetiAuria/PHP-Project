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

function getBlockchainData($connection)
{
    $query = "SELECT * FROM votes ORDER BY timestamp ASC";
    $result = mysqli_query($connection, $query);

    $blockchainData = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $blockchainData[] = array(
            'index' => $row['id'],
            'timestamp' => $row['timestamp'],
            'prev_hash' => $row['previous_hash'],
            'vote' => $row['hashed_vote'],
            'user_id' => $row['user_id'],
            'user_vote' => $row['vote'],
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
            <th>User ID</th>
            <th>User Vote</th>
            <th>Timestamp</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $firstBlock = true;
        foreach ($blockchainData as $block) {
            echo "<tr>";
            echo "<td>{$block['index']}</td>";
            echo "<td>{$block['user_id']}</td>";
            echo "<td>{$block['user_vote']}</td>";
            echo "<td>{$block['timestamp']}</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>


    <!-- Votes in Charts -->
    <h2>Votes in Charts</h2>
    <div id="chartContainer" class="chart-container">
        <div class="chart">
            <!-- Total Votes Chart -->
            <canvas id="totalVotesChart"></canvas>
        </div>
        <div class="chart">
            <!-- User Participation Chart -->
            <canvas id="userParticipationChart"></canvas>
        </div>
        <div class="chart">
            <!-- User Votes Chart -->
            <canvas id="userVotesChart"></canvas>
        </div>
    </div>

    <!-- JavaScript code -->
    <script>
var blockchainData = <?php echo json_encode($blockchainData); ?>;

// Calculate and display total votes
var voteCounts = {};
blockchainData.forEach(function (block) {
    var nonHashedVote = block.vote;
    if (voteCounts[nonHashedVote]) {
        voteCounts[nonHashedVote]++;
    } else {
        voteCounts[nonHashedVote] = 1;
    }
});

// Create chart for total votes
var totalVotes = Object.values(voteCounts).reduce(function (acc, count) {
    return acc + count;
}, 0);

var totalVotesData = {
    labels: ['Total Votes'],
    datasets: [{
        label: 'Total Votes',
        data: [totalVotes],
        backgroundColor: 'rgba(255, 99, 132, 0.5)',
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

// Calculate and display user participation
var uniqueUserIds = new Set(blockchainData.map(block => block.user_id));
var userParticipation = uniqueUserIds.size;

// Create chart for user participation
var userParticipationData = {
    labels: ['User Participation'],
    datasets: [{
        label: 'User Participation',
        data: [userParticipation],
        backgroundColor: 'rgba(54, 162, 235, 0.5)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
    }]
};

var userParticipationCtx = document.getElementById('userParticipationChart').getContext('2d');
var userParticipationChart = new Chart(userParticipationCtx, {
    type: 'bar',
    data: userParticipationData,
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Calculate and display user votes
var userVoteCounts = {};
blockchainData.forEach(function (block) {
    var userVote = block.user_vote;
    if (userVoteCounts[userVote]) {
        userVoteCounts[userVote]++;
    } else {
        userVoteCounts[userVote] = 1;
    }
});

// Create chart for user votes
var userVotesData = {
    labels: Object.keys(userVoteCounts),
    datasets: [{
        label: 'User Votes',
        data: Object.values(userVoteCounts),
        backgroundColor: 'rgba(255, 206, 86, 0.5)',
        borderColor: 'rgba(255, 206, 86, 1)',
        borderWidth: 1
    }]
};

var userVotesCtx = document.getElementById('userVotesChart').getContext('2d');
var userVotesChart = new Chart(userVotesCtx, {
    type: 'bar',
    data: userVotesData,
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