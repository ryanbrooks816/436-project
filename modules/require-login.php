<?php
session_start();

// Check if the user is logged in as either an employee or a customer. If not, redirect them.
if (!isset($_SESSION['employee_id']) && !isset($_SESSION['customer_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: require-login.php");
    exit;
}
