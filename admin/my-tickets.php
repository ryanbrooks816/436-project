<?php
require_once '../classes/db.php';
include '../header.php';
require_once "../modules/require-login.php";

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'employee') {
    $sql = "SELECT * FROM Tickets WHERE employee_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['employee_id']]);
    $tickets = $stmt->fetchAll();
}
?>

<main class="page-wrapper top-space bottom-space">
    <?php include '../modules/notification-alert.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      padding-top: 100px;
      padding-left: 250px;
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

    <section>

    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="game-list.php">Manage Games</a>
        <a href="manage-game-details.php">Manage Game Details</a>
        <a href="manage-users.php">Manage Users</a>
        <a href="#">Tickets</a>
    </div>
        <div class="container mt-4">
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'employee'): ?>

                <!-- Open Tickets -->
                <h3>Open Tickets</h3>
                <?php
                $openTickets = array_filter($tickets, function ($ticket) {
                    return strtolower($ticket['status']) !== 'resolved';
                });
                ?>
                <?php if (!empty($openTickets)): ?>
                    <?php foreach ($openTickets as $ticket) {
                        include '../modules/ticket-card.php';
                    } ?>
                <?php else: ?>
                    <p>You have no open tickets.</p>
                <?php endif; ?>

                <!-- Resolved Tickets -->
                <h3>Resolved Tickets</h3>
                <?php
                $resolvedTickets = array_filter($tickets, function ($ticket) {
                    return strtolower($ticket['status']) === 'resolved';
                });
                ?>
                <?php if (!empty($resolvedTickets)): ?>
                    <?php foreach ($resolvedTickets as $ticket) {
                        include '../modules/ticket-card.php';
                    } ?>
                <?php else: ?>
                    <p>You have no resolved tickets.</p>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center">
                    <h2 class="mb-4">Please Switch to a Customer Account</h2>
                    <p class="lead">Sorry, this page requires you to be signed in as a customer to access.</p>
                    <a href="login.php" class="btn btn-primary mt-3">Go to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require '../footer.php' ?>