<?php
require_once 'classes/db.php';

$user_id = null;
$user_type = $_SESSION['user_type'] ?? null;

// Fetch current user info based on type
if (isset($_SESSION['user_type'])) {
    if ($user_type === 'customer') {
        $user_id = $_SESSION['customer_id'] ?? null;
        $sql = "SELECT cust_password FROM Customers WHERE customer_id = ?";
    } elseif ($user_type === 'employee') {
        $user_id = $_SESSION['employee_id'] ?? null;
        $sql = "SELECT em_password FROM Employees WHERE employee_id = ?";
    } else {
        die("Invalid user type.");
    }
} else {
    die("User not logged in.");
}

if (!$user_id) {
    die("User ID not found.");
}

$user_password = pdo($pdo, $sql, [$user_id])->fetchColumn();
if (!$user_password) {
    die("No user found with the provided ID.");
}

$change_success = "";
$change_error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validate current password
    if (!password_verify($current, $user_password)) {
        $change_error = "Incorrect current password.";
    } elseif ($new !== $confirm) {
        $change_error = "New passwords do not match.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new)) {
        $change_error = "Password must be at least 8 characters, include uppercase, lowercase, number, and special character.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        if ($user_type === 'customer') {
            $update = "UPDATE Customers SET cust_password = ? WHERE customer_id = ?";
        } elseif ($user_type === 'employee') {
            $update = "UPDATE Employees SET em_password = ? WHERE employee_id = ?";
        } else {
            die("Invalid user type.");
        }
        pdo($pdo, $update, [$hashed, $user_id]);
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
                <div class="alert alert-success"><?= $change_success; ?></div>
            <?php elseif ($change_error): ?>
                <div class="alert alert-danger"><?= $change_error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="change_password" value="1">
                <div class="form-group">
                    <label>Current Password <span class="text-danger">*</span></label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>New Password <span class="text-danger">*</span>
                        <span data-toggle="tooltip"
                            title="At least 8 characters, with uppercase, lowercase, special character and a number."
                            style="cursor: help;">
                            <i class="bi bi-info-circle"></i>
                        </span>
                    </label>
                    <input type="password" name="new_password" class="form-control" required>
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