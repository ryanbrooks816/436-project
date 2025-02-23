<?php

$username = 'rybro8_main';
$password = '1hO9bAOQ8Pr0CfyoaLY';


$type = 'mysql';                        // Type of database
$server = '192.185.2.183';               // Server the database is on
$db = 'rybro8_436-project';  // Name of the database
$port = '3306';                      // Port is usually 3306 in Hostgator
$charset = 'utf8mb4';                 // UTF-8 encoding using 4 bytes per char

// Create DSN
$dsn = "$type:host=$server;dbname=$db;port=$port;charset=$charset";

// Array containing options for configuring PDO
//       Set error mode to throw exceptions
//       Set default fetch mode to associative array
//       Disable emulation of prepared statements
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Try connecting to the db
try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $username, $password, $options);
}
// Catch any exceptions that occur during connection
catch (PDOException $e) {
    // Re-throw exception                    
    throw new PDOException($e->getMessage(), $e->getCode());
}
