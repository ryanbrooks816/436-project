<?php
require_once "header.php";
require_once "modules/require-login.php";
?>

<main class="page-wrapper">
    <div class="top-space header-margin bg-primary text-white text-center py-5">
        <h1 class="text-white">Submit Support Ticket</h1>
    </div>

    <div class="alert alert-info notification d-none" id="mainAlert" role="alert"><i class="bi bi-bell"></i></div>

    <section class="page-bottom">
        <div class="container">
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer'): ?>
                <div class="form-container d-flex justify-content-center flex-column" style="width: 50%;">
                    <h2>Support Ticket Form</h2>
                    <p>Please fill out the form below to submit a support ticket. Our support team will get back to you
                        as soon as possible.</p>
                    <form id="supportForm">
                        <div class="alert alert-danger mt-3 d-none" role="alert"></div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?php echo htmlspecialchars($_SESSION['cust_name_first'] . ' ' . $_SESSION['cust_name_last']); ?>"
                                readonly>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="ticketType" class="form-label">Ticket Type</label>
                            <select class="form-control" id="ticketType" name="ticketType" required>
                                <option value="New Game Request">New Game Request</option>
                                <option value="Missing/Incorrect Game Details">Missing/Incorrect Game Details</option>
                                <option value="Bug Report">Bug Report</option>
                                <option value="Feature Request">Feature Request</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="criticalIssue" name="criticalIssue">
                                <label class="form-check-label" for="criticalIssue">Is this a critical issue?</label>
                            </div>
                        </div>
                        <button type="button" id="submitButton" class="btn btn-primary w-100">Submit</button>
                    </form>
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
    $(document).ready(function () {
        $('#submitButton').click(function () {
            const formData = {
                name: $('#name').val(),
                email: $('#email').val(),
                ticket_type: $('#ticketType').val(),
                ticket_name: $('#subject').val(),
                ticket_text: $('#message').val(),
                status: 'Open',
                priority: $('#criticalIssue').is(':checked') ? 'Urgent' : 'Medium',
                customer_id: <?php echo json_encode($_SESSION['user_type'] === 'customer' ? $_SESSION['customer_id'] : null); ?>
            };

            $.ajax({
                url: 'endpoints/submit_ticket.php',
                type: 'POST',
                data: formData,
                success: function (response) {
                    $('#mainAlert')
                        .removeClass('alert-danger')
                        .addClass('alert-success')
                        .text('Support ticket successfully submitted!')
                        .show();
                    $('#supportForm')[0].reset(); // Reset the form fields
                },
                error: function (xhr, status, error) {
                    $('#mainAlert')
                        .removeClass('alert-success')
                        .addClass('alert-danger')
                        .text('An error occurred while submitting the form.')
                        .show();
                }
            });
        });
    });
</script>