<?php
require 'header.php';
require "modules/require-login.php";

// Only intended for customers, employees use admin view
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer') {
    $sql = "SELECT * FROM Tickets WHERE customer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['customer_id']]);
    $tickets = $stmt->fetchAll();
}
?>

<main class="page-wrapper top-space bottom-space">
    <?php require 'modules/notification-alert.php'; ?>
    <section>
        <div class="container mt-4">
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>My Support Tickets</h2>
                    <a class="btn btn-sm btn-success" href="submit-ticket.php">
                        <i class="bi bi-plus-lg me-1"></i>
                        New Ticket
                    </a>
                </div>
                <hr class="my-5">

                <!-- Open Tickets -->
                <div class="d-flex justify-content-center flex-column" style="min-height: 200px;">
                    <h3>Open Tickets</h3>
                    <?php
                    $openTickets = array_filter($tickets, function ($ticket) {
                        return strtolower($ticket['status']) !== 'resolved';
                    });
                    ?>
                    <?php if (!empty($openTickets)): ?>
                        <?php foreach ($openTickets as $ticket) {
                            include 'modules/ticket-card.php';
                        } ?>
                    <?php else: ?>
                        <p>You have no open tickets.</p>
                    <?php endif; ?>
                </div>

                <hr class="my-5">

                <!-- Resolved Tickets -->
                <div class="d-flex justify-content-center flex-column" style="min-height: 200px;">
                    <h3>Resolved Tickets</h3>
                    <?php
                    $resolvedTickets = array_filter($tickets, function ($ticket) {
                        return strtolower($ticket['status']) === 'resolved';
                    });
                    ?>
                    <?php if (!empty($resolvedTickets)): ?>
                        <?php foreach ($resolvedTickets as $ticket) {
                            include 'modules/ticket-card.php';
                        } ?>
                    <?php else: ?>
                        <p>You have no resolved tickets.</p>
                    <?php endif; ?>
                </div>

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

<?php require 'footer.php'; ?>