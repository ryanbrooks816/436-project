<?php
require 'header.php';
require 'modules/require-login.php';

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
        $sql = "SELECT customer_id FROM Tickets WHERE ticket_pub_id = ?";
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

<!-- Sidebar toggle button for mobile screens -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="bi bi-list"></i>
</button>

<nav id="sidebar" class="c-sidebar tickets-sidebar top-space" style="background-color: #fff; width: 300px">
    <div class="ticket-info">
        <h2>Ticket Details</h2>
        <div class="info-item">
            <div class="label">Ticket ID</div>
            <div class="value"><?= htmlspecialchars($ticketId) ?></div>
        </div>
        <div class="info-item">
            <div class="label">Status</div>
            <div class="value">
                <span
                    class="status-badge status-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $ticket['status']))) ?>"><?= htmlspecialchars($ticket['status']) ?></span>
            </div>
        </div>
        <div class="info-item">
            <div class="label">Priority</div>
            <div class="value">
                <span
                    class="priority-badge priority-<?= htmlspecialchars(strtolower($ticket['priority'])) ?>"><?= htmlspecialchars(ucfirst($ticket['priority'])) ?></span>
            </div>
        </div>
        <div class="info-item">
            <div class="label">Created</div>
            <div class="value">
                <?= htmlspecialchars(date("F j, Y, g:i A", strtotime($ticket['submission_date']))) ?>
            </div>
        </div>
        <div class="info-item">
            <div class="label">Last Updated</div>
            <div class="value">
                <?php if ($ticket['last_updated_date'] === "0000-00-00 00:00:00"): ?>
                    No Activity Yet
                <?php else: ?>
                    <?= htmlspecialchars(date("F j, Y, g:i A", strtotime($ticket['last_updated_date']))) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="info-item">
            <div class="label">Category</div>
            <?php
            $sql = "SELECT type_name FROM Ticket_Types WHERE type_id = ? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ticket['ticket_type_id']]);
            $ticketType = $stmt->fetch();
            ?>
            <div class="value"><?= htmlspecialchars($ticketType['type_name']) ?></div>
        </div>
        <div class="info-item">
            <div class="label">Assigned to</div>
            <div class="value">
                <?= isset($employee['em_name_first'], $employee['em_name_last'])
                    ? htmlspecialchars($employee['em_name_first'] . ' ' . $employee['em_name_last'])
                    : 'Unassigned' ?>
            </div>
        </div>
        <div class="info-item">
            <div class="label">User</div>
            <div class="value">
                <?= htmlspecialchars($customer['cust_name_first'] . ' ' . $customer['cust_name_last']) ?>
            </div>
        </div>
        <div class="info-item">
            <div class="label">User Email</div>
            <div class="value"><?= htmlspecialchars($customer['cust_email']) ?></div>
        </div>
    </div>
    <?php if ($_SESSION['user_type'] === 'employee'): ?>
        <div class="actions">
            <?php if ($ticket['employee_id'] === $_SESSION['employee_id']): ?>
                <button id="reassign-ticket" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#assignModal">
                    Reassign Ticket
                </button>
            <?php else: ?>
                <div class="btn-group w-100">
                    <button id="assign-to-me" class="btn btn-primary flex-grow-1" style="text-wrap: nowrap;" onclick="assignToCurrentUser()">Assign to
                        Me</button>
                    <button id="assign-to-others" class="btn btn-primary flex-grow-0" data-bs-toggle="modal"
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
            <button id="close-ticket" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#closeTicketModal"
                <?= $ticket['status'] === 'Resolved' ? 'disabled' : '' ?>>
                <?= $ticket['status'] === 'Resolved' ? 'Ticket is Closed' : 'Close Ticket' ?>
            </button>
        </div>
    <?php endif; ?>
</nav>

<main class="page-wrapper top-space bottom-space">
    <!-- Override default sidebar width -->
    <div class="after-sidebar-content tickets-sidebar">
        <div class="container">
            <div class="ticket-content-wrapper">
                <div class="ticket-content">
                    <div class="ticket-header">
                        <h1 class="ticket-title"><?= htmlspecialchars($ticket['ticket_name']) ?></h1>
                        <div class="ticket-meta">
                            Reported by
                            <strong><?= htmlspecialchars($customer['cust_name_first'] . ' ' . $customer['cust_name_last']) ?></strong>
                            on
                            <?= htmlspecialchars(date("F j, Y, g:i A", strtotime($ticket['submission_date']))) ?>
                        </div>
                    </div>
                    <div class="conversation">
                        <div class="message">
                            <div class="message-header">
                                <div class="message-sender">
                                    <?php
                                    $customer_pfp = getProfilePicturePath($pdo, 'customer', $customer['customer_id']); // defined in navbar.php
                                    ?>
                                    <img class="message-avatar" src="<?= $customer_pfp ?>">
                                    <div>
                                        <span
                                            class="message-name"><?= htmlspecialchars($customer['cust_name_first'] . ' ' . $customer['cust_name_last']) ?></span>
                                        <span class="message-role">User</span>
                                    </div>
                                </div>
                                <div class="message-time">
                                    <?= htmlspecialchars(date("F j, Y, g:i A", strtotime($ticket['submission_date']))) ?>
                                </div>
                            </div>
                            <div class="message-content">
                                <?= htmlspecialchars_decode($ticket['ticket_text'], ENT_QUOTES) ?>
                            </div>
                            <div class="message-attachments">
                                <?php
                                $sql = "SELECT * FROM Ticket_Attachments WHERE ticket_id = ? AND feedback_id IS NULL";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$ticket['ticket_id']]);
                                $attachments = $stmt->fetchAll();
                                foreach ($attachments as $attachment): ?>
                                    <a href="<?= htmlspecialchars($attachment['file_path']) ?>" class="attachment"
                                        data-bs-toggle="modal"
                                        data-bs-target="#lightboxModal-<?= htmlspecialchars($attachment['attachment_id']) ?>">
                                        <i class="bi bi-eye"></i>
                                        <span><?= htmlspecialchars(basename($attachment['file_path'])) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php foreach ($feedbacks as $feedback): ?>
                            <div class="message">
                                <div class="message-header">
                                    <div class="message-sender">
                                        <?php
                                        if (isset($feedback['employee_id'])) {
                                            $sql = "SELECT * FROM Employees WHERE employee_id = ? LIMIT 1";
                                            $stmt = $pdo->prepare($sql);
                                            $stmt->execute([$feedback['employee_id']]);
                                            $employee = $stmt->fetch();
                                            $name = $employee['em_name_first'] . ' ' . $employee['em_name_last'];
                                            $role = "Support Agent";
                                            $profile_picture = getProfilePicturePath($pdo, 'employee', $feedback['employee_id']);
                                        } elseif (isset($feedback['customer_id'])) {
                                            $sql = "SELECT * FROM Customers WHERE customer_id = ? LIMIT 1";
                                            $stmt = $pdo->prepare($sql);
                                            $stmt->execute([$feedback['customer_id']]);
                                            $customer = $stmt->fetch();
                                            $name = $customer['cust_name_first'] . ' ' . $customer['cust_name_last'];
                                            $role = "User";
                                            $profile_picture = getProfilePicturePath($pdo, 'customer', $feedback['customer_id']);
                                        }
                                        ?>
                                        <img class="message-avatar" src="<?= $profile_picture ?>">
                                        <div>
                                            <span class="message-name"><?= htmlspecialchars($name) ?></span>
                                            <span class="message-role"><?= htmlspecialchars($role) ?></span>
                                        </div>
                                    </div>
                                    <div class="message-time">
                                        <?= htmlspecialchars(date("F j, Y, g:i A", strtotime($feedback['timestamp']))) ?>
                                    </div>
                                </div>
                                <div class="message-content">
                                    <?= htmlspecialchars_decode($feedback['message'], ENT_QUOTES) ?>
                                </div>
                                <div class="message-attachments">
                                    <?php
                                    $sql = "SELECT * FROM Ticket_Attachments WHERE ticket_id = ? AND feedback_id = ?";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute([$ticket['ticket_id'], $feedback['feedback_id']]);
                                    $attachments = $stmt->fetchAll();
                                    foreach ($attachments as $attachment): ?>
                                        <a href="<?= htmlspecialchars($attachment['file_path']) ?>" class="attachment"
                                            data-bs-toggle="modal"
                                            data-bs-target="#lightboxModal-<?= htmlspecialchars($attachment['attachment_id']) ?>">
                                            <i class="bi bi-eye"></i>
                                            <span><?= htmlspecialchars(basename($attachment['file_path'])) ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>

                <div class="reply-box">
                    <h3>Reply to Ticket</h3>
                    <!-- Alert -->
                    <div class="alert alert-info d-none alert-dismissible fade show" id="mainAlert" role="alert">
                    </div>
                    <form id="replyForm">
                        <div id="editor">
                            Type your reply here...
                        </div>
                        <input type="hidden" class="ticketId" name="ticket_id"
                            value="<?= htmlspecialchars($ticket['ticket_id']) ?>">
                        <input type="hidden" name="ticket_pub_id" value="<?= htmlspecialchars($ticketId) ?>">
                        <input type="hidden" name="sender_id"
                            value="<?= htmlspecialchars($_SESSION['user_type'] === 'employee' ? $_SESSION['employee_id'] : $_SESSION['customer_id']) ?>">
                        <input type="hidden" name="sender_type" value="<?= htmlspecialchars($_SESSION['user_type']) ?>">
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <?php require_once 'modules/ticket-attachments.php' ?>
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
    </div>
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
                            $employee_pfp = getProfilePicturePath($pdo, 'employee', $employee['employee_id']); // defined in navbar.php
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <img src="<?= htmlspecialchars($employee_pfp) ?>" alt="Profile Picture"
                                        class="rounded-circle me-3" width="40" height="40">
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
            <form id="closeTicketForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="closeTicketModalLabel">Confirm Close Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger mt-2 d-none" id="closeTicketAlert" role="alert"></div>
                    <p> Are you sure you want to close this ticket? This action will mark the ticket as "Resolved" and
                        unassign you.</p>
                    <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket['ticket_id']) ?>">
                    <input type="hidden" name="employee_id" value="<?= htmlspecialchars($_SESSION['employee_id']) ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Close Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lightbox Modals -->
<?php
$sql = "SELECT * FROM Ticket_Attachments WHERE ticket_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$ticket['ticket_id']]);
$allAttachments = $stmt->fetchAll();
foreach ($allAttachments as $attachment): ?>
    <div class="modal fade" id="lightboxModal-<?= htmlspecialchars($attachment['attachment_id']) ?>" tabindex="-1"
        aria-labelledby="lightboxModalLabel-<?= htmlspecialchars($attachment['attachment_id']) ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lightboxModalLabel-<?= htmlspecialchars($attachment['attachment_id']) ?>">
                        <?= htmlspecialchars(basename($attachment['file_path'])) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="<?= htmlspecialchars($attachment['file_path']) ?>"
                        alt="<?= htmlspecialchars(basename($attachment['file_path'])) ?>" class="img-fluid"
                        onerror="this.onerror=null; this.style.display='none'; document.getElementById('error-<?= htmlspecialchars($attachment['attachment_id']) ?>').classList.remove('d-none');">
                    <div id="error-<?= htmlspecialchars($attachment['attachment_id']) ?>" class="alert alert-danger d-none">
                        Error: The image could not be loaded.
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require "footer.php"; ?>

<script>
    const quill = new Quill("#editor", {
        theme: "snow",
    });
</script>

<script src="js/ticket-history.js"></script>

<?php if ($_SESSION['user_type'] === 'employee'): ?>
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
<?php endif; ?>

<script>
    $(document).ready(function () {
        $('#sidebarToggle').on('click', function () {
            $(this).toggleClass('active');
            $(this).toggleClass('active');
        });

        // Close the sidebar when clicking outside of it
        $(document).on('click', function (event) {
            const $sidebar = $('#sidebar');
            const $toggleButton = $('#sidebarToggle');
            if (!$sidebar.is(event.target) && $sidebar.has(event.target).length === 0 &&
                !$toggleButton.is(event.target) && $toggleButton.has(event.target).length === 0) {
                $sidebar.removeClass('active');
                $toggleButton.removeClass('active');
            }
        });
    });
</script>