<?php
require_once "../classes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : null;
    $message = isset($_POST['message']) ? trim($_POST['message']) : null;
    $sender_id = isset($_POST['sender_id']) ? intval($_POST['sender_id']) : null;
    $sender_type = isset($_POST['sender_type']) ? trim($_POST['sender_type']) : null;

    if (empty($ticket_id) || empty($message) || empty($sender_id) || empty($sender_type)) {
        echo json_encode(['success' => false, 'message' => 'Ticket ID, message, sender ID, and sender type are required.']);
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
            echo json_encode(['success' => true, 'message' => 'Reply successfully added to the ticket.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add reply to the ticket.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>