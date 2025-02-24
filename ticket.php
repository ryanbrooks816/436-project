<?php

require 'config.php';

// Function to insert a new ticket into the database
function addTicket($pdo, $ticket_id, $ticket_text, $status, $priority, $ticket_type) {
    try {
        // Prepare the INSERT SQL query 
        $sql = "INSERT INTO Ticket (ticket_id, ticket_text, submission_date, last_updated_date, status, priority, ticket_type) 
                VALUES (ticket_id, :ticket_text, :submission_date, :last_updated_date, :status, :priority, :ticket_type)";

        // Prepare the statement
        $stmt = $pdo->prepare($sql);

        // Bind the values
        $data = [
            ':ticket_id' => 1,  
            ':ticket_text' => 'Sample ticket text',
            ':submission_date' => '2025-2-01 12:00:00',  
            ':last_updated_date' => '2025-2-01 18:00:00', 
            ':status' => 'open',
            ':priority' => 'high',
            ':ticket_type' => 'bug'
        ];

        // Execute the statement
        return $stmt->execute($data);
    } catch (PDOException $e) {
        // Handle error (logging or displaying)
        die("Error: " . $e->getMessage());
    }
}

if (addTicket($pdo, $ticket_id, $ticket_text, $status, $priority, $ticket_type)) {
    echo "Ticket added successfully!";
} else {
    echo "Failed to add the ticket.";
}

?>
