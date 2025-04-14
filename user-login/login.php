<?php
require '../classes/db.php';

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
        $sql = "SELECT cust_password FROM Customers WHERE cust_email = ?";
        $stmt = pdo($pdo, $sql, [$username]);
        $customer = $stmt->fetch();

        if ($customer && password_verify($password, $customer['cust_password'])) {
            $_SESSION['user_type'] = 'customer';
            $_SESSION['email'] = $username;
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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .login-container {
      margin-top: 100px;
    }
    .card {
      border-radius: 1rem;
    }
  </style>
</head>
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
</html>
