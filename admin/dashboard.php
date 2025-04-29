<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../classes/db.php'; 

if (!isset($_SESSION['employee_id'])) {
  header('Location: login.php'); // redirect to login if not logged in
  exit;
}

$loggedInUserId = $_SESSION['employee_id'];

try {
    $stmt = $pdo->query('SELECT COUNT(*) AS total_games FROM games');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalGames = $row['total_games'];
} catch (PDOException $e) {
    echo "Error fetching total games: " . $e->getMessage();
    $totalGames = 0; // fallback in case of error
}

try {
  $stmt = $pdo->query('SELECT COUNT(*) AS total_features FROM accessibility_features');
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $totalFeatures = $row['total_features'];
} catch (PDOException $e) {
  echo "Error fetching total features: " . $e->getMessage();
  $totalFeatures = 0; // fallback in case of error
}

try {
  $stmt = $pdo->query('SELECT COUNT(*) AS total_users FROM customers');
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $totalUsers = $row['total_users'];
} catch (PDOException $e) {
  echo "Error fetching total features: " . $e->getMessage();
  $totalUsers = 0; // fallback in case of error
}

try {
  $stmt = $pdo->prepare('SELECT COUNT(*) AS ticket_count FROM tickets WHERE employee_id = :employee_id');
  $stmt->execute(['employee_id' => $loggedInUserId]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $ticketCount = $row['ticket_count'];
} catch (PDOException $e) {
  echo "Error fetching ticket count: " . $e->getMessage();
  $ticketCount = 0; // fallback
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../header.php'; ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard - Accessible Game Database</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      padding-top: 100px;
    }
    .sidebar {
      width: 250px;
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
  </style>
</head>

?>
<body>

<?php include '../modules/navbar.php'; ?>

<!-- Sidebar -->
<div class="sidebar">
  <a href="dashboard.php">Dashboard</a>
  <a href="game-list.php">Manage Games</a>
  <a href="manage-game-details.php">Manage Game Details</a>
  <a href="manage-users.php">Manage Users</a>
  <a href="my-tickets.php">Tickets</a>
</div>

<!-- Main Content -->
<div class="content">
  <div class="container-fluid mt-4">
    <h1 class="mb-4">Welcome, Admin!</h1>

    <div class="row">
      <div class="mb-4">
        <div class="card text-white bg-primary">
          <div class="card-body">
            <h5 class="card-title">Total Games</h5>
            <p class="card-text display-6"><?php echo htmlspecialchars($totalGames); ?></p>
          </div>
        </div>
      </div>

      <div class="mb-4">
        <div class="card text-white bg-primary">
          <div class="card-body">
            <h5 class="card-title">Accessibility Features</h5>
            <p class="card-text display-6"><?php echo htmlspecialchars($totalFeatures); ?></p>
          </div>
        </div>
      </div>

      <div class="mb-4">
        <div class="card text-white bg-primary">
          <div class="card-body">
            <h5 class="card-title">Registered Users</h5>
            <p class="card-text display-6"><?php echo htmlspecialchars($totalUsers); ?></p>
          </div>
        </div>
      </div>

      <div class="mb-4">
        <div class="card text-white bg-primary">
          <div class="card-body">
            <h5 class="card-title">Tickets Assigned to You</h5>
            <p class="card-text display-6"><?php echo htmlspecialchars($ticketCount); ?></p>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

<?php include '../footer.php'; ?>

</html>
