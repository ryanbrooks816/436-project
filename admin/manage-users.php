<?php

require '../header.php';

$searchResults = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['search_term'])) {
    $searchTerm = '%' . trim($_POST['search_term']) . '%';

    try {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE cust_name_first LIKE :term1 OR cust_name_last LIKE :term2 OR cust_email LIKE :term3");
        $stmt->execute(['term1' => $searchTerm, 'term2' => $searchTerm, 'term3' => $searchTerm]);
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error searching users: " . $e->getMessage();
    }
}
?>

<!-- Sidebar -->
<div class="sidebar">
  <a href="dashboard.php">Dashboard</a>
  <a href="game-list.php">Manage Games</a>
  <a href="manage-game-details.php">Manage Game Details</a>
  <a href="manage-users.php">Manage Users</a>
  <a href="my-tickets.php">Tickets</a>
</div>

<div class="container mt-5" id='main-content'>
  <h1>Manage Users</h1>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="input-group mb-4">
      <input type="text" class="form-control" name="search_term" placeholder="Search users by name or email..." required>
      <button class="btn btn-primary" type="submit">Search</button>
    </div>
  </form>

  <?php if (!empty($searchResults)): ?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>User ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($searchResults as $user): ?>
        <tr>
          <td><?php echo htmlspecialchars($user['customer_id']); ?></td>
          <td><?php echo htmlspecialchars($user['cust_name_first']); ?></td>
          <td><?php echo htmlspecialchars($user['cust_name_last']); ?></td>
          <td><?php echo htmlspecialchars($user['cust_email']); ?></td>
          <td><?php echo htmlspecialchars($user['cust_age']); ?></td>
          <td>
            <a href="edit-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="delete-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
    <div class="alert alert-info">No users found.</div>
  <?php endif; ?>
</div>

<?php require '../footer.php'; ?>
