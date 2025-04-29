<?php
require '../header.php';

if (!isset($_SESSION['employee_id'])) {
  die("You must be logged in to view this page.");
}

$employee_id = $_SESSION['employee_id'];

// Fetch current employee info
$stmt = $pdo->prepare("SELECT * FROM Employees WHERE employee_id = ?");
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
  die("Employee not found.");
}

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $first = $_POST['em_name_first'] ?? '';
  $last = $_POST['em_name_last'] ?? '';
  $email = $_POST['em_email'] ?? '';

  // Update DB
  $update = $pdo->prepare("
    UPDATE Employees 
    SET em_name_first = :first, em_name_last = :last, em_email = :email
    WHERE employee_id = :id
  ");
  $update->execute([
    ':first' => $first,
    ':last' => $last,
    ':email' => $email,
    ':id' => $employee_id
  ]);

  header("Location: account-settings.php?success=1");
  exit;
}
?>

<div class="container py-5">
  <h1 class="mb-4">My Account</h1>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Account updated successfully.</div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">First Name</label>
      <input type="text" name="em_name_first" class="form-control" value="<?= htmlspecialchars($employee['em_name_first']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Last Name</label>
      <input type="text" name="em_name_last" class="form-control" value="<?= htmlspecialchars($employee['em_name_last']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="em_email" class="form-control" value="<?= htmlspecialchars($employee['em_email']) ?>" required>
    </div>

    <button type="submit" class="btn btn-primary">Update Account</button>
  </form>
</div>

<?php require '../footer.php'; ?>
