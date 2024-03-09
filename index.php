<?php
// Backend (PHP + MySQL)
class Block {
    public $timestamp;
    public $data;
    public $previousHash;
    public $hash;

    function __construct($timestamp, $data, $previousHash = '') {
        $this->timestamp = $timestamp;
        $this->data = $data;
        $this->previousHash = $previousHash;
        $this->hash = $this->calculateHash();
    }

    function calculateHash() {
        return hash("sha256", $this->previousHash . $this->timestamp . json_encode($this->data));
    }
}

class Blockchain {
    public $chain = [];

    function __construct() {
        $this->chain[] = $this->createGenesisBlock();
    }

    function createGenesisBlock() {
        return new Block(date("Y-m-d H:i:s"), [], "0");
    }

    function getLatestBlock() {
        return $this->chain[count($this->chain) - 1];
    }

    function addBlock($newBlock) {
        $newBlock->previousHash = $this->getLatestBlock()->hash;
        $newBlock->hash = $newBlock->calculateHash();
        $this->chain[] = $newBlock;
    }

    function isChainValid() {
        for ($i = 1; $i < count($this->chain); $i++) {
            $currentBlock = $this->chain[$i];
            $previousBlock = $this->chain[$i - 1];

            if ($currentBlock->hash != $currentBlock->calculateHash()) {
                return false;
            }

            if ($currentBlock->previousHash != $previousBlock->hash) {
                return false;
            }
        }

        return true;
    }
}

//Äänien lisääminen ja hakeminen tietokannasta
class VoteDB {
    protected $db;

    function __construct($db) {
        $this->db = $db;
    }

    function addVote($username, $vote) {
        // Lisää ääni tietokantaan.
    }

    function getVotes() {
        // Hae äänet tietokannasta.
    }
}

// Frontend (HTML + JavaScript)
?>
<!DOCTYPE html>
<html>
<head>
    <title>Blockchain Voting System</title>
</head>
<body>
    <h1>Blockchain Voting System</h1>
    <form id="vote-form">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username"><br>
        <label for="vote">Vote:</label><br>
        <input type="text" id="vote" name="vote"><br>
        <input type="submit" value="Submit">
    </form>
    <div id="votes"></div>
    <script>
    document.getElementById('vote-form').addEventListener('submit', function(e) {
        e.preventDefault();

        var username = document.getElementById('username').value;
        var vote = document.getElementById('vote').value;

        // Lähetä ääni backendiin.
    });

    </script>
</body>
</html>