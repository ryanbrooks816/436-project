<?php
require_once "header.php";
require_once "modules/require-login.php";
?>

<main class="page-wrapper">
    <div class="top-space header-margin bg-primary text-white text-center py-5">
        <h1 class="text-white">Submit Support Ticket</h1>
    </div>
    <?php include 'modules/notification-alert.php'; ?>
    <section class="bottom-space">
        <div class="container">
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer'): ?>
                <div class="d-flex justify-content-center flex-column m-auto" style="width: 800px;">
                    <h2>Support Ticket Form</h2>
                    <p>Please fill out the form below to submit a support ticket. Our support team will get back to you
                        as soon as possible.</p>
                    <div class="form-container" style="width: 800px;">
                        <form id="supportForm">
                            <div id="submitTicketAlert" class="alert alert-danger mt-3 d-none" role="alert"></div>
                            <div class="form-group">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control-input" id="name" name="name"
                                    value="<?php echo htmlspecialchars($_SESSION['cust_name_first'] . ' ' . $_SESSION['cust_name_last']); ?>"
                                    readonly>
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control-input" id="email" name="email"
                                    value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="ticketType" class="form-label">Ticket Type</label>
                                <select class="form-control-select form-select" id="ticketType" name="ticketType" required>
                                    <option value="New Game Request">New Game Request</option>
                                    <option value="Missing/Incorrect Game Details">Missing/Incorrect Game Details</option>
                                    <option value="Bug Report">Bug Report</option>
                                    <option value="Feature Request">Feature Request</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control-input" id="subject" name="subject" required>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="criticalIssue" name="criticalIssue">
                                    <label class="form-check-label" for="criticalIssue">Is this a critical issue?</label>
                                </div>
                            </div>
                            <hr class="mt-2 mb-5">
                            <div id="editor">
                                Type your message here...
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-5">
                                <?php require_once 'modules/ticket-attachments.php' ?>
                                <div class="d-flex">
                                    <button type="button" id="submitButton" class="btn btn-primary w-auto m-0">
                                        <i class="bi bi-send-fill me-2"></i> Submit
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
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

<?php require "footer.php"; ?>

<script>
    const quill = new Quill("#editor", {
        theme: "snow",
    });
</script>

<script>
    $(document).ready(function () {
        $('#submitButton').click(function () {
            const formData = new FormData();
            formData.append('name', $('#name').val());
            formData.append('email', $('#email').val());
            formData.append('ticket_type', $('#ticketType').val());
            formData.append('ticket_name', $('#subject').val());
            formData.append('ticket_text', quill.root.innerHTML);
            formData.append('status', 'Open');
            formData.append('priority', $('#criticalIssue').is(':checked') ? 'Urgent' : 'Medium');
            formData.append('customer_id', <?php echo json_encode($_SESSION['user_type'] === 'customer' ? $_SESSION['customer_id'] : null); ?>);

            // Add attachments to FormData
            for (let i = 0; i < selectedFiles.length; i++) {
                if (selectedFiles[i].size > 2 * 1024 * 1024) { // 2MB limit
                    $('#mainAlert')
                        .removeClass('d-none alert-success')
                        .addClass('alert-danger')
                        .text(`File "${selectedFiles[i].name}" exceeds the 2MB size limit.`)
                        .show();
                    return;
                }
                formData.append('attachments[]', selectedFiles[i]);
            }

            $.ajax({
                url: 'endpoints/submit_ticket.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    const res = JSON.parse(response);
                    const submitTicketAlert = $('#submitTicketAlert');
                    submitTicketAlert.removeClass('d-none alert-success alert-danger');

                    if (res.success) {
                        submitTicketAlert.addClass('alert-success');
                        submitTicketAlert.text(res.message);
                        // Reset the form
                        $('#supportForm')[0].reset();
                        quill.setText(''); // Clear the Quill editor
                        clearFiles(); // Clear attachments
                    } else {
                        submitTicketAlert.addClass('alert-danger');
                        submitTicketAlert.text(res.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    const mainAlert = $('#submitTicketAlert');
                    mainAlert.removeClass('d-none alert-success').addClass('alert-danger');
                    submitTicketAlert.text('An error occurred while submitting the ticket.');
                }
            });
        });
    });
</script>