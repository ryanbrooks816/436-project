<?php
require 'header.php';

$register_error = "";
$register_success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first = $_POST['first'] ?? '';
    $last = $_POST['last'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $age = $_POST['age'] ?? '';
    $profile_picture = null;

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
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $register_error = "Password must be at least 8 characters and include uppercase, lowercase, special character, and a number.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Handle profile picture upload
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $fileType = $_FILES['profile_picture']['type'];
            $fileSize = $_FILES['profile_picture']['size'];

            if (!in_array($fileType, $allowedTypes)) {
                $register_error = "Only JPG and PNG files are allowed.";
            } elseif ($fileSize > $maxSize) {
                $register_error = "Profile picture must be under 2MB.";
            } else {
                // Hash the email to create a unique folder name
                $userFolder = 'images/pfps/' . md5($email); // Use MD5 hash of the email
                if (!is_dir($userFolder)) {
                    mkdir($userFolder, 0777, true);
                }

                // Generate a unique file name
                $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                $profile_picture = uniqid('profile_', true) . '.' . $extension;

                // Move the uploaded file to the user's folder
                $destination = $userFolder . '/' . $profile_picture;
                if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                    $register_error = "Failed to save the profile picture.";
                }
            }
        }

        // Use default profile picture if none uploaded and no previous error
        if (!$profile_picture && !$register_error) {
            $profile_picture = 'placeholder.jpg'; // Ensure this file exists in the `pfps` folder
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

<section class="section-spo top-space bottom-space">
    <div class="container">
        <div class="d-flex justify-content-center flex-column">
            <h1>Create An Account</h1>
            <p>Already have an account? Then please <a class="white" href="login.php">Sign In</a></p>
            <div class="form-container">
                <div class="form-message">
                    <?php if ($register_error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($register_error); ?></div>
                    <?php elseif ($register_success): ?>
                        <div class="alert alert-success"><?php echo $register_success; ?></div>
                    <?php endif; ?>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-6 form-group">
                            <label class="form-label">First Name <span style="color: red">*</span></label>
                            <input type="text" name="first" class="form-control-input" required>
                        </div>
                        <div class="col-6 form-group">
                            <label class="form-label">Last Name <span style="color: red">*</span></label>
                            <input type="text" name="last" class="form-control-input" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span style="color: red">*</span></label>
                        <input type="email" name="email" class="form-control-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"> Password <span style="color: red">*</span>
                            <span data-toggle="tooltip"
                                title="At least 8 characters, with uppercase, lowercase, special character and a number."
                                style="cursor: help;">
                                <i class="bi bi-info-circle"></i>
                            </span>
                        </label>
                        <input type="password" name="password" class="form-control-input"
                            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Retype Password <span style="color: red">*</span></label>
                        <input type="password" name="confirm" class="form-control-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Age <span style="color: red">*</span></label>
                        <input type="number" name="age" class="form-control-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Profile Picture (optional)</label>
                        <input type="file" name="profile_picture" class="form-control-input" accept="image/*">
                    </div>
                    <div class="g-recaptcha form-group" data-sitekey="<?= $recaptcha_site ?>"></div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<?php require 'footer.php'; ?>