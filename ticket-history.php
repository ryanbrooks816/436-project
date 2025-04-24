<?php
require "classes/db.php";
require "modules/require-login.php";
require "header.php";

if (isset($_GET['tid'])) {
    $ticketId = $_GET['tid']; // Get the ticket ID from the query string

    // Convert the ticket ID to binary, valid tickets are hexadecimal strings
    $binaryTicketId = hex2bin($ticketId);

    if ($binaryTicketId === false) {
        error_log("400 Bad Request: Invalid ticket ID format.");
        http_response_code(400);
        header("Location: 404.php");
        exit;
    }

    error_log("Ticket ID: $ticketId, Binary Ticket ID: $binaryTicketId");

    // Check if the ticket exists
    $sql = "SELECT * FROM Tickets WHERE ticket_pub_id = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$binaryTicketId]);
    $ticket = $stmt->fetch();

    if (!$ticket) {
        error_log("404 Not Found: Ticket not found. Ticket ID: $ticketId");
        http_response_code(404);
        header("Location: 404.php");
        exit;
    }

    // Make sure the right user is accessing this ticket.
    // if ($_SESSION['user_type'] === 'employee') {
    //     $sql = "SELECT employee_id FROM Tickets WHERE ticket_pub_id = ?";
    //     $stmt = $pdo->prepare($sql);
    //     $stmt->execute([$binaryTicketId]);
    //     $employeeId = $stmt->fetchColumn();

    //     if ($employeeId === false) {
    //         error_log("404 Not Found: Ticket not associated with any employee. Ticket ID: $ticketId");
    //         http_response_code(404);
    //         header("Location: 404.php");
    //         exit;
    //     }

    //     if ($employeeId !== $_SESSION['employee_id']) {
    //         error_log("403 Forbidden: Unauthorized employee access attempt to ticket. Ticket ID: $ticketId");
    //         http_response_code(403);
    //         header("Location: 403.php");
    //         exit;
    //     }
    // Let employees access each others' tickets, restrict customers
    if ($_SESSION['user_type'] !== 'employee') {
        $sql = "SELECT cust_id FROM Tickets WHERE ticket_pub_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$binaryTicketId]);
        $custId = $stmt->fetchColumn();

        if ($custId === false) {
            error_log("404 Not Found: Ticket not associated with any customer. Ticket ID: $ticketId");
            http_response_code(404);
            header("Location: 404.php");
            exit;
        }

        $isValidCustomer = ($custId === $_SESSION['customer_id']);

        if (!$isValidCustomer) {
            error_log("403 Forbidden: Unauthorized access attempt to ticket. Ticket ID: $ticketId");
            http_response_code(403);
            header("Location: 404.php"); // don't say they don't have access
            exit;                                // just pretend the ticket doesn't exist
        }
    }

    // Get the information related to the ticket
    $sql = "SELECT * FROM Customers WHERE customer_id = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ticket['customer_id']]);
    $customer = $stmt->fetch();

    $sql = "SELECT * FROM Employees WHERE employee_id = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ticket['employee_id']]);
    $employee = $stmt->fetch();

    $sql = "SELECT * FROM Ticket_Feedback WHERE ticket_id = ? ORDER BY timestamp ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ticket['ticket_id']]);
    $feedbacks = $stmt->fetchAll();

} else {
    error_log("404 Not Found: No ticket ID provided.");
    http_response_code(404);
    header("Location: 404.php");
    exit;
}
?>

<main class="page-wrapper top-space bottom-space">
    <div class="alert alert-info notification d-none" id="mainAlert" role="alert"><i class="bi bi-bell"></i></div>
    <section class="tickets-section">
        <!-- Sidebar toggle button for mobile screens -->
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <!-- Overlay for mobile when sidebar is open -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <aside class="sidebar" id="sidebar">
            <div class="ticket-info">
                <h2>Ticket Details</h2>
                <div class="info-item">
                    <div class="label">Ticket ID</div>
                    <div class="value"><?= $ticketId ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Status</div>
                    <div class="value">
                        <span
                            class="status <?= strtolower(str_replace(' ', '-', $ticket['status'])) ?>"><?= $ticket['status'] ?></span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Priority</div>
                    <div class="value">
                        <span
                            class="priority <?= strtolower($ticket['priority']) ?>"><?= ucfirst($ticket['priority']) ?></span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Created</div>
                    <div class="value"><?= date("F j, Y, g:i A", strtotime($ticket['submission_date'])) ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Last Updated</div>
                    <div class="value"><?= date("F j, Y, g:i A", strtotime($ticket['last_updated_date'])) ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Category</div>
                    <?php
                    $sql = "SELECT type_name FROM Ticket_Types WHERE type_id = ? LIMIT 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$ticket['ticket_type_id']]);
                    $ticketType = $stmt->fetch();
                    ?>
                    <div class="value"><?= $ticketType['type_name'] ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Assigned to</div>
                    <div class="value">
                        <?= $employee['em_name_first'] . ' ' . $employee['em_name_last'] ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">User</div>
                    <div class="value"><?= $customer['cust_name_first'] . ' ' . $customer['cust_name_last'] ?></div>
                </div>
                <div class="info-item">
                    <div class="label">User Email</div>
                    <div class="value"><?= htmlspecialchars($customer['cust_email']) ?></div>
                </div>
            </div>
            <?php if ($_SESSION['user_type'] === 'employee'): ?>
                <div class="actions">
                    <?php if ($ticket['employee_id'] === $_SESSION['employee_id']): ?>
                        <button id="reassign-ticket" class="btn btn-primary w-100" data-bs-toggle="modal"
                            data-bs-target="#assignModal">
                            Reassign Ticket
                        </button>
                    <?php else: ?>
                        <div class="btn-group w-100">
                            <button id="assign-to-me" class="btn btn-primary" style="flex: 7;"
                                onclick="assignToCurrentUser()">Assign to Me</button>
                            <button id="assign-to-others" class="btn btn-primary" style="flex: 3;" data-bs-toggle="modal"
                                data-bs-target="#assignModal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-chevron-down" viewBox="0 0 16 16" stroke="currentColor" stroke-width="1">
                                    <path fill-rule="evenodd"
                                        d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" />
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?>
                    <button id="change-status" class="btn btn-outline-primary w-100" data-bs-toggle="modal"
                        data-bs-target="#changeStatusModal">Change Status</button>
                    <button id="update-priority" class="btn btn-outline-primary w-100" data-bs-toggle="modal"
                        data-bs-target="#updatePriorityModal">Update Priority</button>
                    <button id="close-ticket" class="btn btn-danger w-100" data-bs-toggle="modal"
                        data-bs-target="#closeTicketModal" <?= $ticket['status'] === 'Resolved' ? 'disabled' : '' ?>>
                        <?= $ticket['status'] === 'Resolved' ? 'Ticket is Closed' : 'Close Ticket' ?>
                    </button>
                </div>
            <?php endif; ?>
        </aside>

        <div class="container">
            <div class="ticket-content-wrapper">
                <div class="ticket-content">
                    <div class="ticket-header">
                        <h1 class="ticket-title"><?= $ticket['ticket_name'] ?></h1>
                        <div class="ticket-meta">
                            Reported by
                            <strong><?= $customer['cust_name_first'] . ' ' . $customer['cust_name_last'] ?></strong> on
                            <?= date("F j, Y, g:i A", strtotime($ticket['submission_date'])) ?>
                        </div>
                    </div>
                    <div class="conversation">
                        <div class="message">
                            <div class="message-header">
                                <div class="message-sender">
                                    <div class="message-avatar">M</div>
                                    <div>
                                        <span
                                            class="message-name"><?= $customer['cust_name_first'] . ' ' . $customer['cust_name_last'] ?></span>
                                        <span class="message-role">User</span>
                                    </div>
                                </div>
                                <div class="message-time">
                                    <?= date("F j, Y, g:i A", strtotime($ticket['submission_date'])) ?>
                                </div>

                            </div>
                            <div class="message-content">
                                <?= $ticket['ticket_text'] ?>
                            </div>
                            <div class="message-attachments">
                                <a href="#" class="attachment">
                                    <i class="bi bi-download"></i>
                                    error_screenshot.png
                                </a>
                            </div>
                        </div>

                        <?php foreach ($feedbacks as $feedback): ?>
                            <div class="message">
                                <div class="message-header">
                                    <div class="message-sender">
                                        <div class="message-avatar">S</div>
                                        <div>
                                            <?php
                                            if (isset($feedback['employee_id'])) {
                                                $sql = "SELECT * FROM Employees WHERE employee_id = ? LIMIT 1";
                                                $stmt = $pdo->prepare($sql);
                                                $stmt->execute([$feedback['employee_id']]);
                                                $employee = $stmt->fetch();
                                                $name = $employee['em_name_first'] . ' ' . $employee['em_name_last'];
                                                $role = "Support Agent";
                                            } elseif (isset($feedback['customer_id'])) {
                                                $sql = "SELECT * FROM Customers WHERE customer_id = ? LIMIT 1";
                                                $stmt = $pdo->prepare($sql);
                                                $stmt->execute([$feedback['customer_id']]);
                                                $customer = $stmt->fetch();
                                                $name = $customer['cust_name_first'] . ' ' . $customer['cust_name_last'];
                                                $role = "User";
                                            }
                                            ?>
                                            <span class="message-name"><?= $name ?></span>
                                            <span class="message-role"><?= $role ?></span>
                                        </div>
                                    </div>
                                    <div class="message-time">
                                        <?= date("F j, Y, g:i A", strtotime($feedback['timestamp'])) ?>
                                    </div>
                                </div>
                                <div class="message-content">
                                    <?= $feedback['message'] ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>

                <div class="reply-box">
                    <h3>Reply to Ticket</h3>
                    <form id="replyForm">
                        <div id="editor">
                        Type your reply here...
                        </div>
                        <input type="hidden" class="ticketId" name="ticket_id" value="<?= $ticket['ticket_id'] ?>">
                        <input type="hidden" name="sender_id"
                            value="<?= $_SESSION['user_type'] === 'employee' ? $_SESSION['employee_id'] : $_SESSION['customer_id'] ?>">
                        <input type="hidden" name="sender_type" value="<?= $_SESSION['user_type'] ?>">
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button type="button" class="attachment-btn">
                                <i class="bi bi-paperclip"></i>
                                Add Attachment
                            </button>
                            <div class="d-flex">
                                <button type="button" id="sendReply" class="btn btn-primary w-auto m-0">
                                    <i class="bi bi-send-fill me-2"></i> Send Reply
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Assign to Others Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignModalLabel">Assign Ticket to Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignTicketForm">
                    <div class="alert alert-danger mt-3 d-none" id="reassignEmployeeAlert" role="alert"></div>
                    <input type="hidden" class="ticketId" name="ticket_id" value="<?= $ticket['ticket_id'] ?>">
                    <ul class="list-group">
                        <?php
                        $sql = "SELECT employee_id, em_name_first, em_name_last, profile_picture FROM Employees";
                        $stmt = $pdo->query($sql);
                        $employees = $stmt->fetchAll();
                        foreach ($employees as $employee):
                            $isAssigned = $employee['employee_id'] === $ticket['employee_id'];
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <img src="<?= htmlspecialchars($employee['profile_picture'] ?: 'images/placeholder.jpg') ?>"
                                        alt="Profile Picture" class="rounded-circle me-3" width="40" height="40">
                                    <?= htmlspecialchars($employee['em_name_first'] . ' ' . $employee['em_name_last']) ?>
                                </div>
                                <?php if ($isAssigned): ?>
                                    <button class="btn btn-sm btn-secondary w-auto" disabled>Assigned</button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-primary assign-btn w-auto"
                                        data-employee-id="<?= $employee['employee_id'] ?>">Assign</button>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeStatusModalLabel">Change Ticket Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changeStatusForm">
                    <div class="alert alert-danger mt-3 d-none" id="statusAlert" role="alert"></div>
                    <div class="form-group">
                        <label for="statusSelect">Select New Status</label>
                        <select class="form-control mt-2" id="statusSelect" name="status">
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Pending">Pending</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>
                    <input type="hidden" class="ticketId" name="ticket_id" value="<?= $ticket['ticket_id'] ?>">
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-danger w-auto" data-bs-dismiss="modal"><i
                                class="bi bi-x-lg"></i></button>
                        <button type="button" id="saveStatusChanges" class="btn btn-success w-auto">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Update Priority Modal -->
<div class="modal fade" id="updatePriorityModal" tabindex="-1" aria-labelledby="updatePriorityModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePriorityModalLabel">Update Ticket Priority</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatePriorityForm">
                    <div class="alert alert-danger mt-3 d-none" id="priorityAlert" role="alert"></div>
                    <div class="form-group">
                        <label for="prioritySelect">Select New Priority</label>
                        <select class="form-control form-select" id="prioritySelect" name="priority">
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Urgent</option>
                        </select>
                    </div>
                    <input type="hidden" class="ticketId" value="<?= $ticketId ?>">
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-danger w-auto" data-bs-dismiss="modal"><i
                                class="bi bi-x-lg"></i></button>
                        <button type="button" id="savePriorityChanges" class="btn btn-success w-auto">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Close Ticket Modal -->
<div class="modal fade" id="closeTicketModal" tabindex="-1" aria-labelledby="closeTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="closeTicketModalLabel">Confirm Close Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to close this ticket? This action will mark the ticket as "Resolved".
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmCloseTicket" class="btn btn-danger">Close Ticket</button>
            </div>
        </div>
    </div>
</div>

<script>
    const quill = new Quill("#editor", {
        theme: "snow",
    });
</script>

<?php require "footer.php"; ?>

<script>
    function assignToCurrentUser() {
        const ticketId = $('.ticketId').val();
        const employeeId = <?= $_SESSION['employee_id'] ?>;

        $.ajax({
            url: 'endpoints/update_ticket.php',
            type: 'POST',
            data: {
                field: 'employee_id',
                value: employeeId,
                ticket_id: ticketId
            },
            success: function (response) {
                const res = JSON.parse(response);
                const mainAlert = $('#mainAlert');
                mainAlert.removeClass('d-none alert-success alert-danger');

                if (res.success) {
                    mainAlert.addClass('alert-success');
                    mainAlert.text(res.message);
                    location.reload();
                } else {
                    mainAlert.addClass('alert-danger');
                    mainAlert.text(res.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                const mainAlert = $('#mainAlert');
                mainAlert.removeClass('d-none alert-success').addClass('alert-danger');
                mainAlert.text('An error occurred while assigning the ticket.');
            }
        });
    }
</script>