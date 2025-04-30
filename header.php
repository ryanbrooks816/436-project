<?php 
ob_start();

$isAdminPage = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;

require $isAdminPage ? "../classes/config.php" : "classes/config.php";

$isEmployee = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'employee';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <?php $prefix = $isAdminPage ? '../' : ''; ?>
    <link rel="stylesheet" href="<?= $prefix; ?>css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $prefix; ?>css/style.css">

</head>

<body>
    <?php require $isAdminPage ? '../modules/navbar.php' : 'modules/navbar.php'; ?>
