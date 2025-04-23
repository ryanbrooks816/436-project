<?php
require_once 'classes/db.php';
require 'modules/require-login.php';
include 'header.php';

// Handle logout if the logout button was pressed
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$email = $_SESSION['email'] ?? null;

$sql = "SELECT * FROM Customers WHERE cust_email = ?";
$customer = pdo($pdo, $sql, [$email])->fetch();
$first = $customer['cust_name_first'] ?? '';
$last = $customer['cust_name_last'] ?? '';
$age = $customer['cust_age'] ?? '';
$profile_picture = $customer['profile_picture'] ?? 'placeholder.jpg';

$update_success = "";
$update_error = "";

// Update profile if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['logout'])) {
    $first = $_POST['first'] ?? '';
    $last = $_POST['last'] ?? '';
    $age = $_POST['age'] ?? '';

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png'];
        $fileType = $_FILES['profile_picture']['type'];
        $fileSize = $_FILES['profile_picture']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $update_error = "Only JPG and PNG files are allowed.";
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $update_error = "File must be under 2MB.";
        } else {
            // Create a folder for the user based on their email
            $userFolder = 'images/' . md5($email); // Use hashed email for folder name
            if (!is_dir($userFolder)) {
                mkdir($userFolder, 0777, true);
            }

            // Generate a unique file name
            $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $profile_picture = uniqid('profile_', true) . '.' . $extension;

            // Move the uploaded file to the user's folder
            $destination = $userFolder . '/' . $profile_picture;
            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                $update_error = "Failed to save the profile picture.";
            }
        }
    }

    if (!$update_error) {
        $sql = "UPDATE Customers SET cust_name_first = ?, cust_name_last = ?, cust_age = ?, profile_picture = ? WHERE cust_email = ?";
        pdo($pdo, $sql, [$first, $last, $age, $profile_picture, $email]);
        $update_success = "Profile updated successfully.";
        // Refresh customer info
        $customer = pdo($pdo, "SELECT * FROM Customers WHERE cust_email = ?", [$email])->fetch();
        $profile_picture = $customer['profile_picture'];
    }
}

// Construct the path to the profile picture
$userFolder = 'images/' . md5($email);
$profilePicPath = $userFolder . '/' . ($profile_picture ?? 'placeholder.jpg');
if (!file_exists($profilePicPath)) {
    $profilePicPath = 'images/placeholder.jpg'; // Fallback to default
}
?>

<div class="container mt-5">
  <h2 class="text-primary mb-4">Manage Your Profile</h2>

  <?php if ($update_success): ?>
    <div class="alert alert-success"><?php echo $update_success; ?></div>
  <?php elseif ($update_error): ?>
    <div class="alert alert-danger"><?php echo $update_error; ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="form-group text-center">
      <img src="<?php echo htmlspecialchars($profilePicPath, ENT_QUOTES, 'UTF-8'); ?>" class="profile-img mb-2" alt="Profile Picture">
      <div>
        <input type="file" name="profile_picture" accept="image/*" class="form-control-file">
      </div>
    </div>
    
    <div class="form-group">
      <label>First Name</label>
      <input type="text" name="first" class="form-control" value="<?php echo htmlspecialchars($first, ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>

    <div class="form-group">
      <label>Last Name</label>
      <input type="text" name="last" class="form-control" value="<?php echo htmlspecialchars($last, ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>

    <div class="form-group">
      <label>Age</label>
      <input type="number" name="age" class="form-control" value="<?php echo htmlspecialchars($age, ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>

    <div class="form-group">
      <label>Email (read-only)</label>
      <input type="email" class="form-control" 
      value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" readonly>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
  </form>

  <div class="text-center mt-3">
    <a href="password.php" class="btn btn-secondary">Change Password</a>
  </div>

  <div class="text-center mt-2">
    <form method="POST" style="display: inline;">
      <button type="submit" name="logout" class="btn btn-danger">Log Out</button>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>