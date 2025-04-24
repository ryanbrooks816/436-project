<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once 'classes/db.php';
include 'header.php';

$email = $_SESSION['email'] ?? null;
if (!$email) {
  die("No user is logged in.");
}

// Fetch current customer info
$sql = "SELECT * FROM Customers WHERE cust_email = ?";
$customer = pdo($pdo, $sql, [$email])->fetch();
if (!$customer) {
  die("No customer found with email: " . htmlspecialchars($email));
}

$change_success = "";
$change_error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $current = $_POST['current_password'] ?? '';
  $new = $_POST['new_password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  // Validate current password
  if (!password_verify($current, $customer['cust_password'])) {
    $change_error = "Incorrect current password.";
  } elseif ($new !== $confirm) {
    $change_error = "New passwords do not match.";
  } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new)) {
    $change_error = "Password must be at least 8 characters, include uppercase, lowercase, number, and special character.";
  } else {
    $hashed = password_hash($new, PASSWORD_DEFAULT);
    $update = "UPDATE Customers SET cust_password = ? WHERE cust_email = ?";
    pdo($pdo, $update, [$hashed, $email]);
    $change_success = "Password changed successfully.";
  }
}
?>

<div class="container mt-5">
  <div class="card">
    <div class="card-header">
      <h4 class="mb-0">Change Password</h4>
    </div>
    <div class="card-body">
      <?php if ($change_success): ?>
        <div class="alert alert-success"><?php echo $change_success; ?></div>
      <?php elseif ($change_error): ?>
        <div class="alert alert-danger"><?php echo $change_error; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label>Current Password <span class="text-danger">*</span></label>
          <input type="password" name="current_password" class="form-control" required>
        </div>

        <div class="form-group">
          <label>New Password <span class="text-danger">*</span></label>
          <input type="password" name="new_password" class="form-control"
            placeholder="At least 8 chars, upper/lower, number, symbol" required>
        </div>

        <div class="form-group">
          <label>Retype New Password <span class="text-danger">*</span></label>
          <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary btn-block">Update Password</button>
        </div>
      </form>
    </div>
  </div>
</div>