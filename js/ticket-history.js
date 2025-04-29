$(document).ready(function () {
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
        const ticketPubId = $('input[name="ticket_pub_id"]').val();
        const message = quill.root.innerHTML;
        const senderId = $('input[name="sender_id"]').val();
        const senderType = $('input[name="sender_type"]').val();
        const attachments = selectedFiles;

        const mainAlert = $('#mainAlert');

        // Create FormData object to handle both text and file data
        const formData = new FormData();
        formData.append('ticket_id', ticketId);
        formData.append('ticket_pub_id', ticketPubId);
        formData.append('message', message);
        formData.append('sender_id', senderId);
        formData.append('sender_type', senderType);

        // Add attachments to FormData
        for (let i = 0; i < attachments.length; i++) {
            if (attachments[i].size > 2 * 1024 * 1024) { // 2MB limit
                mainAlert.removeClass('d-none alert-success alert-danger');
                mainAlert.addClass('alert-danger');
                mainAlert.text(`File "${attachments[i].name}" exceeds the 2MB size limit.`);
                return;
            }
            formData.append('attachments[]', attachments[i]);
        }

        $.ajax({
            url: 'endpoints/reply_to_ticket.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                const res = JSON.parse(response);
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

$(document).ready(function () {
    // TOGGLE SIDEBAR
    const sidebar = $('#sidebar');
    const sidebarOverlay = $('#sidebarOverlay');
    const sidebarToggle = $('#sidebarToggle');

    // Toggle sidebar visibility
    $('#sidebarToggle').on('click', function () {
        sidebar.toggleClass('active');
        sidebarOverlay.toggleClass('active');
        sidebarToggle.toggleClass('active');
    });

    // Close sidebar when clicking on overlay
    sidebarOverlay.on('click', function () {
        sidebar.removeClass('active');
        sidebarOverlay.removeClass('active');
        sidebarToggle.removeClass('active');
    });

    // Close sidebar when window is resized to larger size
    $(window).on('resize', function () {
        if ($(window).width() > 768) {
            sidebar.removeClass('active');
            sidebarOverlay.removeClass('active');
            sidebarToggle.removeClass('active'); s
        }
    });
});

