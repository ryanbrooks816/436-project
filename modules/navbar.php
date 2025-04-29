<?php
$customer = null;
$profilePicPath = 'images/placeholder.jpg';

// Only if the user is logged in
if (isset($_SESSION['email'])) {
  $email = $_SESSION['email'];
  $sql = "SELECT profile_picture FROM Customers WHERE cust_email = ?";
  $customer = pdo($pdo, $sql, [$email])->fetch();

  if ($customer && $customer['profile_picture']) {
    $userFolder = 'images/pfps/' . md5($email); // Absolute path to the user's folder
    $absoluteProfilePicPath = $userFolder . '/' . $customer['profile_picture'];

    $relativeProfilePicPath = 'images/pfps/' . md5($email) . '/' . $customer['profile_picture'];
    // Check if the file exists
    if (!file_exists($absoluteProfilePicPath)) {
      $relativeProfilePicPath = 'images/placeholder.jpg';
    }
    $profilePicPath = $relativeProfilePicPath;
  }
}
?>

<?php
$pagesWithCustomClass = ['login.php', 'register.php'];
$navbarClass = '';
$currentPage = basename($_SERVER['PHP_SELF']);
if (in_array($currentPage, $pagesWithCustomClass)) {
  $navbarClass = 'navbar-spo';
}
?>
<nav id="navbar" class="navbar bg-light navbar-expand-lg fixed-top top-nav-collapse <?php echo $navbarClass; ?>">
  <div class="container-fluid">
    <div class="navbar-brand d-flex align-items-center">
      <img class="me-3" src="images/logo.png" alt="Logo" height="70px">
      <h1 class="logo-text">Accessible Games<br>Support Center</h1>
    </div>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <div class="d-flex w-100 justify-content-end align-items-center" style="gap: 5%;">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" href="games-homepage.php">Search Games</a> <!-- Changed from search-games.php to games-homepage.php -->
          </li>
          <li class="nav-item">
            <a class="nav-link" href="my-tickets.php">My Tickets</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-white btn-xs mb-0 ms-2" href="login.php">LOG IN</a>
          </li>
        </ul>
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" href="profile.php">
              <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile" width="50" height="50"
                class="rounded-circle">
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>