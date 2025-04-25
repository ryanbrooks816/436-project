<?php
require_once "../classes/db.php";
require_once "../modules/upload-attachment.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = intval($_POST['ticket_id'] ?? 0);
    $ticket_pub_id = htmlspecialchars(trim($_POST['ticket_pub_id'] ?? ''), ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');
    $sender_id = intval($_POST['sender_id'] ?? 0);
    $sender_type = htmlspecialchars(trim($_POST['sender_type'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Validate required fields
    if (empty($ticket_id) || empty($ticket_pub_id) || empty($message) || empty($sender_id) || empty($sender_type)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    try {
        // Determine the field to update based on sender_type
        $field = ($sender_type === 'employee') ? 'employee_id' : 'customer_id';

        // Prepare SQL to insert into Ticket_Feedback table
        $query = "INSERT INTO Ticket_Feedback (ticket_id, $field, message) VALUES (:ticket_id, :sender_id, :message)";
        $stmt = $pdo->prepare($query);

        $stmt->bindParam(':ticket_id', $ticket_id);
        $stmt->bindParam(':sender_id', $sender_id);
        $stmt->bindParam(':message', $message);

        if ($stmt->execute()) {
            $feedback_id = $pdo->lastInsertId(); // Get the ID of the newly created feedback

            // Handle file uploads
            if (!empty($_FILES['attachments']['name'][0])) {
                $messages = handle_ticket_attachments($pdo, $ticket_id, $feedback_id, $ticket_pub_id);
                if (!empty($messages)) {
                    echo json_encode(['success' => false, 'message' => implode(" ", $messages)]);
                    exit;
                }
            }
            echo json_encode(['success' => true, 'message' => 'Reply and attachments successfully added to the ticket.']);
        } else {
            error_log("Failed to execute statement for ticket reply.");
            echo json_encode(['success' => false, 'message' => 'Failed to add reply to the ticket.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>