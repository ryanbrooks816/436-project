<?php

// Include the database connection script
require 'config.php';  // Ensure this file contains the connection setup

try {
    // Prepare the INSERT SQL query
    $sql = "INSERT INTO Employee (employee_id, employee_name_first, employee_name_last, employee_email, position) 
            VALUES (:id, :first_name, :last_name, :email, :position)";

    // Prepare the statement
    $stmt = $pdo->prepare($sql);

    // Example employee data to insert
    $data = [
        ':id' => 1,
        ':first_name' => 'John',
        ':last_name' => 'Doe',
        ':email' => 'john.doe@example.com',
        ':position' => 'Software Engineer'
    ];

    // Execute the statement
    $stmt->execute($data);

    echo "Employee added successfully!";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

?>
