<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1); // Enable error logging
ini_set('error_log', __DIR__ . '/error.log'); // Set the error log file path


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $isAdminPage ? "../classes/db.php" : "db.php";

// Recaptcha configuration
$sql = "SELECT recaptcha_secret, recaptcha_site FROM config LIMIT 1";
$config = pdo($pdo, $sql, [])->fetch();

if ($config) {
    $recaptcha_secret = $config['recaptcha_secret'];
    $recaptcha_site = $config['recaptcha_site'];
} else {
    error_log("Configuration not found.");
    die("Configuration not found.");
}
