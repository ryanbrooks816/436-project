<?php

require '../header.php';
require_once "../modules/require-login.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'employee') {
    header("Location: ../403.php");
}
?>

<?php require '../modules/admin-sidebar.php'?>

<main class="page-wrapper">
    <div class="top-space bg-primary text-white text-center py-5">
        <h1 class="text-white">My Assigned Tickets</h1>
    </div>
    <div class="after-sidebar-content">
        <div class="container">
            <!-- Assigned Tickets -->
            <?php
            $sql = "SELECT * FROM Tickets WHERE employee_id = ? AND status != 'Resolved'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['employee_id']]);
            $tickets = $stmt->fetchAll();
            ?>
            <h2 class="mt-5">Your Tickets</h2>
            <?php if (empty($tickets)): ?>
                <p class="my-3 fs-5">You're not assigned to any tickets.</p>
            <?php else: ?>
                <?php foreach ($tickets as $ticket) {
                    include '../modules/ticket-card.php';
                } ?>
            <?php endif; ?>

            <hr class="my-5">

            <!-- Unassigned Tickets -->
            <?php
            $sql = "SELECT * FROM Tickets WHERE employee_id IS NULL AND status != 'Resolved'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $tickets = $stmt->fetchAll();
            ?>
            <h2 class="mt-5">Available Tickets</h2>
            <?php if (empty($tickets)): ?>
                <p class="my-3 fs-5">No tickets available.</p>
            <?php else: ?>
                <?php foreach ($tickets as $ticket) {
                    include '../modules/ticket-card.php';
                } ?>
            <?php endif; ?>

            <hr class="my-5">

            <!-- Tickets Assigned to Others -->
            <?php
            $sql = "SELECT * FROM Tickets WHERE employee_id IS NOT NULL AND employee_id != ? AND status != 'Resolved'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['employee_id']]);
            $tickets = $stmt->fetchAll();
            ?>
            <h2 class="mt-5">Tickets Assigned to Others</h2>
            <?php if (empty($tickets)): ?>
                <p class="my-3 fs-5">No tickets assigned to others.</p>
            <?php else: ?>
                <?php foreach ($tickets as $ticket) {
                    include '../modules/ticket-card.php';
                } ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require '../footer.php' ?>
