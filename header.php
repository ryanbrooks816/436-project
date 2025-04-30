<?php require "classes/config.php" ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      padding-top: 100px;
    }
    .sidebar {
      width: 190px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background-color: #343a40;
      padding-top: 140px;
    }
    .sidebar a {
      padding: 15px;
      text-decoration: none;
      font-size: 18px;
      color: #ccc;
      display: block;
    }
    .sidebar a:hover {
      background-color: #495057;
      color: white;
    }
    .content {
      margin-left: 250px;
      padding: 20px;
      flex-grow: 1;
    }
    .navbar {
      z-index: 1001;
    }
    #main-content {
      padding-left: 250px;
    }
  </style>

</head>

<body>
    <?php require 'modules/navbar.php'; ?>
