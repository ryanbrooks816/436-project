<?php

require '../header.php';
require '../modules/require-login.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'employee') {
  header("Location: 403.php");
}

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

<?php require '../modules/admin-sidebar.php' ?>

<main class="page-wrapper">
  <div class="top-space bg-primary text-white text-center py-5">
    <h1 class="text-white">Welcome, Admin!</h1>
  </div>
  <div class="after-sidebar-content">
    <div class="container">
      <div class="mt-5 row">
        <div class="col-3 mb-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Total Games</h5>
              <p class="card-text display-6"><?php echo htmlspecialchars($totalGames); ?></p>
            </div>
          </div>
        </div>

        <div class="col-3 mb-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Accessibility Features</h5>
              <p class="card-text display-6"><?php echo htmlspecialchars($totalFeatures); ?></p>
            </div>
          </div>
        </div>

        <div class="col-3 mb-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Registered Users</h5>
              <p class="card-text display-6"><?php echo htmlspecialchars($totalUsers); ?></p>
            </div>
          </div>
        </div>

        <div class="col-3 mb-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Tickets Assigned to You</h5>
              <p class="card-text display-6"><?php echo htmlspecialchars($ticketCount); ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
</main>

<?php require '../footer.php'; ?>