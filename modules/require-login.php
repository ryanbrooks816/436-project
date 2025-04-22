<?php
session_start();

// Check if the user is logged in as either an employee or a customer. If not, redirect them.
if (!isset($_SESSION['employee_id']) && !isset($_SESSION['customer_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: require-login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$cust_name_first = $_SESSION['cust_name_first'];
$cust_name_last = $_SESSION['cust_name_last'];