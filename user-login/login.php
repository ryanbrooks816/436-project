<?php
require_once '../classes/db.php';

session_start();

$login_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  if ($username && $password) {
    // Check Employees table
    $sql = "SELECT em_password FROM Employees WHERE em_email = ?";
    $stmt = pdo($pdo, $sql, [$username]);
    $employee = $stmt->fetch();

    if ($employee && password_verify($password, $employee['em_password'])) {
      $_SESSION['user_type'] = 'employee';
      $_SESSION['email'] = $username;
      header("Location: ../index.php");
      exit;
    }

    // Check Customers table
    $sql = "SELECT customer_id, cust_name_first, cust_name_last, cust_password FROM Customers WHERE cust_email = ?";
    $stmt = pdo($pdo, $sql, [$username]);
    $customer = $stmt->fetch();

    if ($customer && password_verify($password, $customer['cust_password'])) {
      $_SESSION['user_type'] = 'customer';
      $_SESSION['email'] = $username;
      $_SESSION['customer_id'] = $customer['customer_id'];
      $_SESSION['cust_name_first'] = $customer['cust_name_first'];
      $_SESSION['cust_name_last'] = $customer['cust_name_last'];
      header("Location: ../index.php");
      exit;
    }

    // No match found
    $login_error = "Invalid username or password.";
  } else {
    $login_error = "Please fill in both fields.";
  }
}
?>



<?php include '../header.php'; ?>
<body>
  <div class="container login-container">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <div class="card shadow p-4">
          <h4 class="text-center mb-4">Login</h4>
          <?php if ($login_error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
          <?php endif; ?>
          <form method="POST" action="">
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
          </form>
        </div>
      </div>
    </div>
    <div class="text-center mt-3">
      <a href="./user-login/register.php">New user? Create an account</a>
    </div>
  </div>
</body>
<?php include '../footer.php'; ?>