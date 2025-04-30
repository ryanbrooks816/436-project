<div
    class="ticket-card card my-4 shadow-sm priority-<?= strtolower(htmlspecialchars($ticket['priority'])); ?> status-<?= strtolower(htmlspecialchars($ticket['status'])); ?>">
    <div class="card-body">
        <div class="ticket-card-header mb-2">
            <h5 class="card-title ticket-title">
                <a href="#" class="text-decoration-none text-dark"><?= htmlspecialchars($ticket['ticket_name']); ?></a>
            </h5>
            <div>
                <span
                    class="status-badge status-<?= strtolower(str_replace(' ', '-', htmlspecialchars($ticket['status']))); ?> me-2"><?= ucfirst(str_replace(' ', '-', htmlspecialchars($ticket['status']))); ?></span>
                <span
                    class="priority-badge priority-<?= strtolower(htmlspecialchars($ticket['priority'])); ?>"><?= ucfirst(htmlspecialchars($ticket['priority'])); ?>
                    Priority</span>
            </div>
        </div>
        <span class="text-muted me-3">
            <?php
            $sql = "SELECT type_name FROM Ticket_Types WHERE type_id = ? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ticket['ticket_type_id']]);
            $ticketType = $stmt->fetch();
            ?>
            <?= htmlspecialchars($ticketType['type_name']) ?>
        </span>

        <div class="ticket-preview-text"><?= htmlspecialchars_decode($ticket['ticket_text']); ?></div>

        <div class="ticket-card-footer">
            <div>
                <div class="text-muted small">
                    <i class="bi bi-clock me-1"></i>
                    <?php if ($ticket['last_updated_date'] === "0000-00-00 00:00:00"): ?>
                        Created:
                        <?= htmlspecialchars(date("F j, Y, g:i A", strtotime($ticket['submission_date']))); ?>
                    <?php else: ?>
                        Last Updated:
                        <?= htmlspecialchars(date("F j, Y, g:i A", strtotime($ticket['last_updated_date']))); ?>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="text-muted small me-3">
                        <?php
                        $sql = "SELECT COUNT(*) as feedback_count FROM Ticket_Feedback WHERE ticket_id = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$ticket['ticket_id']]);
                        $feedbackCount = $stmt->fetch();
                        ?>
                        <i class="bi bi-chat-dots me-1"></i>
                        <?= htmlspecialchars($feedbackCount['feedback_count']); ?> replies
                    </span>
                </div>
            </div>
            <a href="<?= $isAdminPage ? '../' : '' ?>ticket-history.php?tid=<?= bin2hex($ticket['ticket_pub_id']); ?>"
                class="btn btn-sm btn-outline-primary">View Details</a>
        </div>
    </div>
</div>