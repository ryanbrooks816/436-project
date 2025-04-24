<?php
require_once 'classes/db.php';
session_start();

function redirect_after_login()
{
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect_url = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        header("Location: $redirect_url");
    } else {
        header("Location: index.php");
    }
}

$login_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        // Check Employees table
        $sql = "SELECT employee_id, em_password FROM Employees WHERE em_email = ?";
        $stmt = pdo($pdo, $sql, [$email]);
        $employee = $stmt->fetch();

        if ($employee && password_verify($password, $employee['em_password'])) {
            $_SESSION['user_type'] = 'employee';
            $_SESSION['email'] = $email;
            $_SESSION['employee_id'] = $employee['employee_id'];
            redirect_after_login();
            exit;
        }

        // Check Customers table
        $sql = "SELECT customer_id, cust_name_first, cust_name_last, cust_password FROM Customers WHERE cust_email = ?";
        $stmt = pdo($pdo, $sql, [$email]);
        $customer = $stmt->fetch();

        if ($customer && password_verify($password, $customer['cust_password'])) {
            $_SESSION['user_type'] = 'customer';
            $_SESSION['email'] = $email;
            $_SESSION['customer_id'] = $customer['customer_id'];
            $_SESSION['cust_name_first'] = $customer['cust_name_first'];
            $_SESSION['cust_name_last'] = $customer['cust_name_last'];
            redirect_after_login();
            exit;
        }

        // No match found
        $login_error = "Invalid username or password.";
    } else {
        $login_error = "Please fill in both fields.";
    }
}
?>

<?php include 'header.php';

// Redirect if already logged in
if (isset($_SESSION['user_type'])) {
    echo '
    <section class="section top-space bottom-space">
        <div class="container">
            <div class="d-flex justify-content-center flex-column text-center">
                <h2 class="mb-4">You\' Already Logged In, Redirecting...</h2>
                <script>setTimeout(function() { window.location.href = "index.php"; }, 3000);</script>
            </div>
        </div>
    </section>
    ';
    exit;
}

?>

<section class="section-spo top-space bottom-space">
    <div class="container">
        <div class="d-flex justify-content-center flex-column">
            <h1>Login</h1>
            <p>Don't have an account yet? Then please <a class="white" href="register.php">Sign Up</a></p>

            <div class="form-container">
                <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                    <div class="form-message">
                        <?php if ($login_error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control-input" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control-input" name="password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>