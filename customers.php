$customers = [
    ['customer_id' => 00000001, 'cust_name_first' => 'Alice', 'cust_name_last' => 'Doe', 'cust_email' => 'alice@example.com', 'cust_age' => 40, 'cust_password' => 'test'],
    ['customer_id' => 00000002, 'cust_name_first' => 'Bob', 'cust_name_last' => 'Doe', 'cust_email' => 'bob@example.com', 'cust_age' => 40, 'cust_password' => 'test'],
    ['customer_id' => 00000003, 'cust_name_first' => 'Charlie', 'cust_name_last' => 'Doe', 'cust_email' => 'charlie@example.com', 'cust_age' => 40, 'cust_password' => 'test'],
    ['customer_id' => 00000004, 'cust_name_first' => 'Donna', 'cust_name_last' => 'Doe', 'cust_email' => 'donna@example.com', 'cust_age' => 40, 'cust_password' => 'test'],
    ['customer_id' => 00000005, 'cust_name_first' => 'Eric', 'cust_name_last' => 'Doe', 'cust_email' => 'eric@example.com', 'cust_age' => 40, 'cust_password' => 'test']
];

$stmt = $pdo->prepare("INSERT INTO customers (customer_id, cust_name_first, cust_name_last, cust_email, cust_age, cust_password) VALUES (:customer_id, :cust_name_first, :cust_name_last, :cust_email, :cust_age, :cust_password)");

foreach ($customers as $customer) {
    $stmt->execute([
        'customer_id' => $customer['customer_id'],
        'cust_name_first' => $customer['cust_name_first'],
        'cust_name_last' => $customer['cust_name_last'],
        'cust_email' => $customer['cust_email'],
        'cust_age' => $customer['cust_age'],
        'cust_password' => $customer['cust_password],
    ]);
}

echo "Multiple customers added successfully!";
