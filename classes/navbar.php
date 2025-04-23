<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once 'db.php';

$customer = null;
$profilePicPath = '../images/placeholder.jpg';

// Only if the user is logged in
if (isset($_SESSION['email'])) {
  $email = $_SESSION['email'];
  $sql = "SELECT profile_picture FROM Customers WHERE cust_email = ?";
  $customer = pdo($pdo, $sql, [$email])->fetch();

  if ($customer && $customer['profile_picture']) {
    $userFolder = dirname(__DIR__) . '/images/' . md5($email); // Absolute path to the user's folder
    $absoluteProfilePicPath = $userFolder . '/' . $customer['profile_picture'];

    $relativeProfilePicPath = '../images/' . md5($email) . '/' . $customer['profile_picture'];
    // Check if the file exists
    if (!file_exists($absoluteProfilePicPath)) {
        $relativeProfilePicPath = '../images/placeholder.jpg';
    }
    $profilePicPath = $relativeProfilePicPath;
}
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm mb-4">
  <div class="container-fluid">
    <h1 class="navbar-brand text-primary" style="font-size: 2rem;">Accessible Games Support Center</h1>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <div class="d-flex w-100 justify-content-between align-items-center">
        <ul class="navbar-nav">
          <li class="nav-item ml-5">
            <a class="nav-link" href="/game_search.php" style="font-size: 1.5rem;">Game Search</a>
          </li>
          <li class="nav-item ml-5">
            <a class="nav-link" href="/tickets.php" style="font-size: 1.5rem;">Tickets</a>
          </li>
          <li class="nav-item ml-5">
            <a class="nav-link" href="../ticket_status.php" style="font-size: 1.5rem;">Submitted Tickets</a>
          </li>
        </ul>

        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" href="profile.php">
              <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile" width="50" height="50" class="rounded-circle">
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>
<?php
