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

<h1>Hiiiii</h1>

<div class="card" style="width: 18rem;">
    <div class="card-body">
        <h5 class="card-title">Card Title</h5>
        <p class="card-text">This is a simple card example using Bootstrap.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
    </div>
</div>
<?php include 'footer.php'; ?>
