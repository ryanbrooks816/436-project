<?php
require "db.php";

echo "Hello world";

$sql = "SELECT * FROM Game"; // SQL query to fetch all rows from the Game table
$games = pdo($pdo, $sql)->fetchAll(); // Execute the query and fetch all results

foreach ($games as $game) {
    print_r($game);
    echo "<br>";
}
