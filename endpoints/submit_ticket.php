<?php
require "../classes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input data
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $ticket_type = htmlspecialchars(trim($_POST['ticket_type']));
    $ticket_name = htmlspecialchars(trim($_POST['ticket_name']));
    $ticket_text = htmlspecialchars(trim($_POST['ticket_text']));
    $status = htmlspecialchars(trim($_POST['status']));
    $priority = htmlspecialchars(trim($_POST['priority']));
    $customer_id = htmlspecialchars(trim($_POST['customer_id']));

    // Validate required fields
    if (empty($name) || empty($email) || empty($ticket_type) || empty($ticket_name) || empty($ticket_text) || empty($status) || empty($priority) || empty($customer_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required.']);
        exit;
    }

    try {
        // Look up the ticket type ID
        $type_query = "SELECT type_id FROM Ticket_Types WHERE type_name = ?";
        $stmt = pdo($pdo, $type_query, [$ticket_type]);
        $type_id = $stmt->fetchColumn();

        if (!$type_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid ticket type.']);
            exit;
        }

        // Generate a hexadecimal UUID and convert it to binary
        $hex_uuid = bin2hex(random_bytes(16));
        $binary_uuid = hex2bin($hex_uuid);

        // Prepare the SQL statement for inserting the ticket
        $sql = "INSERT INTO Tickets (ticket_name, ticket_text, submission_date, status, priority, ticket_type_id, customer_id, ticket_pub_id) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)";
        $stmt = pdo($pdo, $sql, [$ticket_name, $ticket_text, $status, $priority, $type_id, $customer_id, $binary_uuid]);

        http_response_code(200);
        echo json_encode(['success' => 'Support ticket submitted successfully.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
