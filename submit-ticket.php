<?php
require "header.php";
require "modules/require-login.php";
?>

<h1>Submit a Support Request</h1>

<div class="card" style="width: 30rem; margin: auto;">
    <div class="card-body">
        <h5 class="card-title">Support Request Form</h5>
        <!-- Alert div for displaying messages -->
        <div id="alertMessage" class="alert" style="display: none;" role="alert"></div>
        <form id="supportForm">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name"
                    value="<?php echo htmlspecialchars($cust_name_first . ' ' . $cust_name_last); ?>" readonly>
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
            <button type="button" id="submitButton" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>

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
                customer_id: <?php echo json_encode($customer_id); ?>
            };

            $.ajax({
                url: 'endpoints/submit_ticket.php',
                type: 'POST',
                data: formData,
                success: function (response) {
                    $('#alertMessage')
                        .removeClass('alert-danger')
                        .addClass('alert-success')
                        .text('Support ticket successfully submitted!')
                        .show();
                    $('#supportForm')[0].reset(); // Reset the form fields
                },
                error: function (xhr, status, error) {
                    $('#alertMessage')
                        .removeClass('alert-success')
                        .addClass('alert-danger')
                        .text('An error occurred while submitting the form.')
                        .show();
                }
            });
        });
    });
</script>

<?php require "footer.php"; ?>