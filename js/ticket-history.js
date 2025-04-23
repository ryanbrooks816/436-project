$(document).ready(function () {
    // Set up employee change modal
    $('.assign-btn').on('click', function () {
        const employeeId = $(this).data('employee-id');
        // Perform the assignment logic here (e.g., send an AJAX request)
        console.log('Assigning ticket to employee ID:', employeeId);
    });

    // Handle reassign employee modal submit
    $('.assign-btn').on('click', function () {
        const employeeId = $(this).data('employee-id');
        const ticketId = $('.ticketId').val();

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
                const reassignEmployeeAlert = $('#reassignEmployeeAlert');
                reassignEmployeeAlert.removeClass('d-none alert-success alert-danger');

                if (res.success) {
                    reassignEmployeeAlert.addClass('alert-success');
                    reassignEmployeeAlert.text(res.message);
                    location.reload();
                } else {
                    reassignEmployeeAlert.addClass('alert-danger');
                    reassignEmployeeAlert.text(res.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                const reassignEmployeeAlert = $('#reassignEmployeeAlert');
                reassignEmployeeAlert.removeClass('d-none alert-success').addClass('alert-danger');
                reassignEmployeeAlert.text('An error occurred while assigning the ticket.');
            }
        });
    });

    // Handle status change modal submit
    $('#saveStatusChanges').on('click', function () {
        const status = $('#statusSelect').val();
        const ticketId = $('.ticketId').val();

        $.ajax({
            url: 'endpoints/update_ticket.php',
            type: 'POST',
            data: {
                field: 'status',
                value: status,
                ticket_id: ticketId
            },
            success: function (response) {
                const res = JSON.parse(response);
                const statusAlert = $('#statusAlert');
                statusAlert.removeClass('d-none alert-success alert-danger');

                if (res.success) {
                    statusAlert.addClass('alert-success');
                    statusAlert.text(res.message);
                    location.reload();
                } else {
                    statusAlert.addClass('alert-danger');
                    statusAlert.text(res.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                const statusAlert = $('#statusAlert');
                statusAlert.removeClass('d-none alert-success').addClass('alert-danger');
                statusAlert.text('An error occurred while changing the status.');
            }
        });
    });

    // Handle priority change modal submit
    $('#savePriorityChanges').on('click', function () {
        const priority = $('#prioritySelect').val();
        const ticketId = $('.ticketId').val();

        $.ajax({
            url: 'endpoints/update_ticket.php',
            type: 'POST',
            data: {
                field: 'priority',
                value: priority,
                ticket_id: ticketId
            },
            success: function (response) {
                const res = JSON.parse(response);
                const priorityAlert = $('#priorityAlert');
                priorityAlert.removeClass('d-none alert-success alert-danger');

                if (res.success) {
                    priorityAlert.addClass('alert-success');
                    priorityAlert.text(res.message);
                    location.reload();
                } else {
                    priorityAlert.addClass('alert-danger');
                    priorityAlert.text(res.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                const priorityAlert = $('#priorityAlert');
                priorityAlert.removeClass('d-none alert-success').addClass('alert-danger');
                priorityAlert.text('An error occurred while changing the status.');
            }
        });
    });

    // Handle close ticket modal
    $('#confirmCloseTicket').on('click', function () {
        const ticketId = $('.ticketId').val();

        $.ajax({
            url: 'endpoints/update_ticket.php',
            type: 'POST',
            data: {
                field: 'status',
                value: 'Resolved',
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
                mainAlert.text('An error occurred while closing the ticket.');
            }
        });
    });

    // Handle replying to the ticket
    $('#sendReply').on('click', function () {
        const ticketId = $('.ticketId').val();
        const message = quill.root.innerHTML;
        const senderId = $('input[name="sender_id"]').val();
        const senderType = $('input[name="sender_type"]').val();

        $.ajax({
            url: 'endpoints/ticket_reply.php',
            type: 'POST',
            data: {
                ticket_id: ticketId,
                message: message,
                sender_id: senderId,
                sender_type: senderType
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
                mainAlert.text('An error occurred while sending the reply.');
            }
        });
    });

});

document.addEventListener('DOMContentLoaded', function () {
    // TOGGLE SIDEBAR
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Toggle sidebar visibility
    sidebarToggle.addEventListener('click', function () {
        sidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
    });

    // Close sidebar when clicking on overlay
    sidebarOverlay.addEventListener('click', function () {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
    });

    // Close sidebar when window is resized to larger size
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        }
    });
});

