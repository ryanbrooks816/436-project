<?php
require '../header.php';
require_once "../modules/require-login.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'employee') {
  header("Location: 403.php");
}

$searchResults = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['search_term'])) {
  $searchTerm = '%' . trim($_POST['search_term']) . '%';

  try {
    $stmt = $pdo->prepare("SELECT * FROM Customers WHERE cust_name_first LIKE :term1 OR cust_name_last LIKE :term2 OR cust_email LIKE :term3");
    $stmt->execute(['term1' => $searchTerm, 'term2' => $searchTerm, 'term3' => $searchTerm]);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $error = "Error searching users: " . $e->getMessage();
  }
}
?>


<main class="page-wrapper">
  <div class="top-space bg-primary text-white text-center py-5">
    <h1 class="text-white">Manage Users</h1>
  </div>
  <div class="after-sidebar-content">
    <div class="container py-5">
      <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="input-group shadow-sm">
          <input type="text" class="form-control form-control-lg" name="search_term"
            placeholder="Search users by name or email..." required>
          <button class="btn btn-primary mb-0" style="border-radius: 0.3rem;" type="submit"> <i
              class="bi bi-search"></i>
          </button>
        </div>
      </form>

      <?php if (!empty($searchResults)): ?>
        <h2 class="mt-5">Search Results</h2>
        <table class="table table-striped mt-3">
          <thead>
            <tr>
              <th>User ID</th>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Email</th>
              <th>Age</th>
              <!-- <th>Actions</th> -->
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
                <!-- <td>
                  <a href="edit-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                  <a href="delete-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-danger btn-sm"
                    onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                </td> -->
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
        <div class="mt-5">
          <div class="alert alert-danger">No users found.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php require '../footer.php'; ?>