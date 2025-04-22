<?php
session_start();

// Check if the user is logged in. If not, redirect them.
if (!isset($_SESSION['customer_id']) || !isset($_SESSION['cust_name_first']) || !isset($_SESSION['cust_name_last'])) {
    header("Location: require-login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$cust_name_first = $_SESSION['cust_name_first'];
$cust_name_last = $_SESSION['cust_name_last'];