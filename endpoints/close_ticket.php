<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1); // Enable error logging
ini_set('error_log', __DIR__ . '/error.log'); // Set the error log file path

require_once '../classes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = isset($_POST['ticket_id']) ? $_POST['ticket_id'] : null;
    $employee_id = isset($_POST['employee_id']) ? $_POST['employee_id'] : null;

    if (!$ticket_id || !$employee_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit;
    }

    try {
        // Update the ticket status to 'Resolved'
        $sql = "UPDATE Tickets SET status = 'Resolved', employee_id = ? WHERE ticket_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([null, $ticket_id]);

        // Check if any rows were affected
        if ($stmt->rowCount() > 0) {
            // Increment the employee's num_resolved count
            $updateEmployeeSql = "UPDATE Employees SET num_resolved = num_resolved + 1 WHERE employee_id = ?";
            $updateEmployeeStmt = $pdo->prepare($updateEmployeeSql);
            $updateEmployeeStmt->execute([$employee_id]);

            echo json_encode(['success' => true, 'message' => 'Ticket closed successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to close ticket.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}