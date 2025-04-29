<?php
require 'header.php';
require 'modules/require-login.php';

// Handle logout if the logout button was pressed
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$userId = $email = $first = $last = $age = $profile_picture = null;

if (isset($_SESSION['user_type'])) {
    $userType = $_SESSION['user_type'];
    if ($userType === 'customer') {
        $userId = $_SESSION['customer_id'];
        $sql = "SELECT * FROM Customers WHERE customer_id = ?";
        $customer = pdo($pdo, $sql, [$userId])->fetch();
        $email = $customer['cust_email'] ?? '';
        $first = $customer['cust_name_first'] ?? '';
        $last = $customer['cust_name_last'] ?? '';
        $age = $customer['cust_age'] ?? '';
        $profile_picture = $customer['profile_picture'] ?? 'placeholder.jpg';
    } elseif ($userType === 'employee') {
        $userId = $_SESSION['employee_id'];
        $sql = "SELECT * FROM Employees WHERE employee_id = ?";
        $employee = pdo($pdo, $sql, [$userId])->fetch();
        $email = $employee['em_email'] ?? '';
        $first = $employee['em_name_first'] ?? '';
        $last = $employee['em_name_last'] ?? '';
        $profile_picture = $employee['profile_picture'] ?? 'placeholder.jpg';
    }
} else {
    die("User not logged in.");
}

// Construct the path to the profile picture
$profilePicPath = getProfilePicturePath($pdo, $userType, $userId); // defined in navbar.php

$update_success = "";
$update_error = "";

// Update profile if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['logout']) && isset($_POST['update_profile'])) {
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
            // Create a folder for the user based on their user ID
            $userFolder = 'images/pfps/' . md5($userId); // Use hashed user ID for folder name
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
        if ($userType === 'customer') {
            $sql = "UPDATE Customers SET cust_name_first = ?, cust_name_last = ?, cust_age = ?, profile_picture = ? WHERE customer_id = ?";
            pdo($pdo, $sql, [$first, $last, $age, $profile_picture, $userId]);
            $update_success = "Profile updated successfully.";
            // Refresh customer info
            $customer = pdo($pdo, "SELECT * FROM Customers WHERE customer_id = ?", [$userId])->fetch();
            $profile_picture = $customer['profile_picture'];
            $profilePicPath = getProfilePicturePath($pdo, $userType, $userId);
        } elseif ($userType === 'employee') {
            $sql = "UPDATE Employees SET em_name_first = ?, em_name_last = ?, profile_picture = ? WHERE employee_id = ?";
            pdo($pdo, $sql, [$first, $last, $profile_picture, $userId]);
            $update_success = "Profile updated successfully.";
            // Refresh employee info
            $employee = pdo($pdo, "SELECT * FROM Employees WHERE employee_id = ?", [$userId])->fetch();
            $profile_picture = $employee['profile_picture'];
            $profilePicPath = getProfilePicturePath($pdo, $userType, $userId);
        }
    }
}
?>

<main class="page-wrapper top-space bottom-space">
    <div class="container mt-4">
        <?php if ($update_success): ?>
            <div class="alert alert-success"><?php echo $update_success; ?></div>
        <?php elseif ($update_error): ?>
            <div class="alert alert-danger"><?php echo $update_error; ?></div>
        <?php endif; ?>

        <!-- Profile Details -->
        <div class="d-flex justify-content-center align-items-center">
            <div class="form-group align-items-center text-center">
                <div class="position-relative d-inline-block">
                    <img src="<?php echo htmlspecialchars($profilePicPath, ENT_QUOTES, 'UTF-8'); ?>" class="pfp"
                        alt="Change Profile Picture">
                </div>
                <h3 class="mt-3"><?= $first . ' ' . $last ?></h3>
                <p><?= $email ?></p>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="logout" class="btn btn-sm btn-danger">Log Out</button>
                </form>
            </div>
        </div>

        <h2 class="text-primary my-4">Manage Your Profile</h2>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-details-tab" data-bs-toggle="tab"
                    data-bs-target="#profile-details" type="button" role="tab" aria-controls="profile-details"
                    aria-selected="true">Profile Details</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button"
                    role="tab" aria-controls="security" aria-selected="false">Security</button>
            </li>
        </ul>

        <div class="tab-content" id="profileTabsContent">
            <!-- Profile Details Tab -->
            <div class="tab-pane fade show active" id="profile-details" role="tabpanel"
                aria-labelledby="profile-details-tab">
                <form method="POST" enctype="multipart/form-data" class="mt-4">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="form-group">
                        <label class="form-label">Change Profile Picture</label>
                        <input type="file" name="profile_picture" accept="image/*" class="form-control-file">
                    </div>

                    <div class="row">
                        <div class="col-6 form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first" class="form-control-input"
                                value="<?php echo htmlspecialchars($first, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>

                        <div class="col-6 form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last" class="form-control-input"
                                value="<?php echo htmlspecialchars($last, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email (read-only)</label>
                        <input type="email" class="form-control-input"
                            value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                    </div>

                    <?php if ($userType === 'customer'): ?>
                        <div class="form-group">
                            <label class="form-label">Age</label>
                            <input type="number" name="age" class="form-control-input"
                                value="<?php echo htmlspecialchars($age, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary w-auto">Save Changes</button>
                    </div>
                </form>
            </div>

            <!-- Security Tab -->
            <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                <div class="mt-4">
                    <?php require 'modules/change-password.php'; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require 'footer.php'; ?>

<script>
    $(document).ready(function () {
        // Save active tab to localStorage
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (event) {
            localStorage.setItem('activeTab', $(event.target).data('bs-target'));
        });

        // On page load, check if there's an active tab stored
        const activeTab = localStorage.getItem('activeTab');
        if (activeTab) {
            const $activeTabButton = $(`button[data-bs-target="${activeTab}"]`);
            if ($activeTabButton.length) {
                const tab = new bootstrap.Tab($activeTabButton[0]);
                tab.show();
            }
        }
    });
</script>