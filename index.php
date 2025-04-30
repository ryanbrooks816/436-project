index:<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_type']) || !isset($_SESSION['email'])) {
    $_SESSION['redirect_after_login'] = 'games-homepage.php';
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'classes/db.php';
?>

<?php include 'header.php'; ?>
<div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 90vh; margin-top: -50px;">
    <div class="row justify-content-center w-100">
        <div class="col-md-4">
            <div class="card shadow p-4 text-center">
                <h4 class="mb-4">Welcome to Accessible Games Database</h4>
                <?php if (!file_exists('games-homepage.php')): ?>
                    <div class="alert alert-danger">The games homepage file (games-homepage.php) is missing.</div>
                <?php else: ?>
                    <a href="games-homepage.php" class="btn btn-primary w-100">Go to Games Homepage</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
