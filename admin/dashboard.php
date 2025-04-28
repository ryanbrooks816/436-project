






<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard - Accessible Game Database</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .sidebar {
      width: 250px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background-color: #343a40;
      padding-top: 60px;
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
  </style>
</head>

<?php include '../header.php'; ?>

?>
<body>


<!-- Sidebar -->
<div class="sidebar">
  <a href="dashboard.php">Dashboard</a>
  <a href="game-list.php">Manage Games</a>
  <a href="#">Manage Accessibility Features</a>
  <a href="#">Manage Users</a>
  <a href="#">Reports</a>
  <a href="#">Settings</a>
</div>

<!-- Main Content -->
<div class="content">
  <div class="container-fluid mt-4">
    <h1 class="mb-4">Welcome, Admin!</h1>

    <div class="row">
      <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
          <div class="card-body">
            <h5 class="card-title">Total Games</h5>
            <p class="card-text display-6">1580</p>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
          <div class="card-body">
            <h5 class="card-title">Accessibility Features</h5>
            <p class="card-text display-6">45</p>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning">
          <div class="card-body">
            <h5 class="card-title">Registered Users</h5>
            <p class="card-text display-6">120</p>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-4">
        <div class="card text-white bg-danger">
          <div class="card-body">
            <h5 class="card-title">Pending Reports</h5>
            <p class="card-text display-6">3</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Add more dashboard components here -->
    <div class="card mt-4">
      <div class="card-body">
        <h5 class="card-title">Recent Activity</h5>
        <p class="card-text">No new activities to show.</p>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
