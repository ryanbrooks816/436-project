<?php
require '../classes/db.php';
session_start();

$register_error = "";
$register_success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first = $_POST['first'] ?? '';
    $last = $_POST['last'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $age = $_POST['age'] ?? '';
    $profilePic = null;

    // Check reCAPTCHA
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response");
    $captcha_success = json_decode($verify)->success;

    if (!$captcha_success) {
        $register_error = "Please verify the CAPTCHA.";
    } elseif (!$first || !$last || !$email || !$password || !$confirm || !$age) {
        $register_error = "All fields except profile picture are required.";
    } elseif ($password !== $confirm) {
        $register_error = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/', $password)) {
        $register_error = "Password must be at least 8 characters and include uppercase, lowercase, and a number.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Handle profile picture upload
        $profile_picture = null;
        $allowedTypes = ['image/jpeg', 'image/png'];
        // This is 2MB
        $maxSize = 2 * 1024 * 1024; 

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $fileType = $_FILES['profile_picture']['type'];
            $fileSize = $_FILES['profile_picture']['size'];

            if (!in_array($fileType, $allowedTypes)) {
                $register_error = "Only JPG and PNG files are allowed.";
            } elseif ($fileSize > $maxSize) {
                $register_error = "Profile picture must be under 2MB.";
            } else {
                $profile_picture = file_get_contents($_FILES['profile_picture']['tmp_name']);
            }
        }

        // Use default profile picture if none uploaded and no previous error
        if (!$profile_picture && !$register_error) {
            $profile_picture = file_get_contents('../images/placeholder.jpg'); // Ensure this file exists
        }

        if (!$register_error) {
            $sql = "INSERT INTO Customers (cust_name_first, cust_name_last, cust_email, cust_password, cust_age, profile_picture)
                    VALUES (?, ?, ?, ?, ?, ?)";
            try {
                pdo($pdo, $sql, [$first, $last, $email, $hashedPassword, $age, $profile_picture]);
                $register_success = "Account created successfully. You may now <a href='./login.php'>log in</a>.";
            } catch (PDOException $e) {
                $register_error = "An account with this email may already exist.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card p-4 shadow-sm">
          <h3 class="text-center mb-4">Create an Account</h3>
          <?php if ($register_error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($register_error); ?></div>
          <?php elseif ($register_success): ?>
            <div class="alert alert-success"><?php echo $register_success; ?></div>
          <?php endif; ?>
          <form method="POST" enctype="multipart/form-data">
            <div class="row mb-3">
              <div class="col">
                <label class="form-label">First Name <span style="color: red">*</span></label>
                <input type="text" name="first" class="form-control" required>
              </div>
              <div class="col">
                <label class="form-label">Last Name <span style="color: red">*</span></label>
                <input type="text" name="last" class="form-control" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Email <span style="color: red">*</span></label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
            <label class="form-label"> Password <span style="color: red">*</span>
              <span data-bs-toggle="tooltip" title="At least 8 characters, with uppercase, lowercase, and a number." style="cursor: help;">
                <i class="bi bi-info-circle"></i>
              </span>
            </label>
              <input type="password" name="password" class="form-control" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Retype Password <span style="color: red">*</span></label>
              <input type="password" name="confirm" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Age <span style="color: red">*</span></label>
              <input type="number" name="age" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Profile Picture (optional)</label>
              <input type="file" name="profile_picture" class="form-control" accept="image/*">
            </div>
            <div class="g-recaptcha mb-3" data-sitekey="<?= $recaptcha_site ?>"></div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(el => new bootstrap.Tooltip(el))
  });
</script>

</body>
</html>
