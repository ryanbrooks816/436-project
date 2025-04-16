<?php
require_once 'db.php';

$sql = "SELECT recaptcha_secret, recaptcha_site FROM config LIMIT 1";
$config = pdo($pdo, $sql, [])->fetch();

if ($config) {
    $recaptcha_secret = $config['recaptcha_secret'];
    $recaptcha_site = $config['recaptcha_site'];
} else {
    die("Configuration not found.");
}
