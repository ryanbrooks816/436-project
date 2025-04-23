<?php
require "header.php";

if (!isset($_SESSION['customer_id']) || !isset($_SESSION['cust_name_first']) || !isset($_SESSION['cust_name_last'])) {
    header("Location: user-login/login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];

require 'classes/db.php';

//Fetch tickets for the logged-in user
$query = "SELECT ticket_name, ticket_text, status FROM tickets WHERE customer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<h1>Your Submitted Tickets</h1>

<div class="ticket-list-container" style="width: 80%; margin: auto; max-height: 500px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;">
    <?php if ($result->num_rows > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Ticket Name</th>
                    <th>Description</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ticket_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['ticket_text']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No tickets found.</p>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
require "footer.php";
?>