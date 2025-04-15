<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['email'];
$sql = "SELECT profile_picture FROM Customers WHERE cust_email = ?";
$customer = pdo($pdo, $sql, [$email])->fetch();

$profilePicData = $customer && $customer['profile_picture']
    ? 'data:image/jpeg;base64,' . base64_encode($customer['profile_picture'])
    : '../images/placeholder.jpg';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm mb-4">
  <div class="container-fluid">
    <h1 class="navbar-brand text-primary" style="font-size: 2rem;">Accessible Games Support Center</h1>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mr-auto mb-2 mb-lg-0">
        <li class="nav-item ml-5">
          <a class="nav-link" href="/game_search.php" style="font-size: 1.5rem;">Game Search</a>
        </li>
        <li class="nav-item ml-5">
          <a class="nav-link" href="/tickets.php" style="font-size: 1.5rem;">Tickets</a>
        </li>
      </ul>
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="/user-profile/profile.php">
            <img src="<?php echo htmlspecialchars($profilePicData); ?>" alt="Profile" width="50" height="50" class="rounded-circle">
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<?php
