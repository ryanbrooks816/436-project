<?php
require_once '../classes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field = isset($_POST['field']) ? $_POST['field'] : null;
    $value = isset($_POST['value']) ? $_POST['value'] : null;
    $ticket_id = isset($_POST['ticket_id']) ? $_POST['ticket_id'] : null;

    // Validate the field to ensure it's either 'status' or 'priority'
    if ($field && $value && $ticket_id && in_array($field, ['status', 'priority', 'employee_id'])) {
        try {
            // Prepare SQL query to update the specified field
            $sql = "UPDATE Tickets SET $field = ? WHERE ticket_id = ?";
            $stmt = $pdo->prepare($sql);

            // Execute the query with parameters
            if ($stmt->execute([$value, $ticket_id])) {
                echo json_encode(['success' => true, 'message' => ucfirst($field) . ' updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update ' . $field . '.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    }
}
?>