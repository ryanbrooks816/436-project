<?php
$profilePicPath = 'images/placeholder.jpg';

$isEmployee = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'employee';

function getProfilePicturePath($pdo, $userType, $userId)
{
    $defaultProfilePicPath = 'images/placeholder.jpg';

    if ($userType === 'customer') {
        $sql = "SELECT profile_picture FROM Customers WHERE customer_id = ?";
    } elseif ($userType === 'employee') {
        $sql = "SELECT profile_picture FROM Employees WHERE employee_id = ?";
    } else {
        return $defaultProfilePicPath;
    }

    $user = pdo($pdo, $sql, [$userId])->fetch();

    if ($user && $user['profile_picture']) {
        $userFolder = 'images/pfps/' . md5($userId); // Absolute path to the user's folder
        $absoluteProfilePicPath = $userFolder . '/' . $user['profile_picture'];

        $relativeProfilePicPath = 'images/pfps/' . md5($userId) . '/' . $user['profile_picture'];
        // Check if the file exists
        if (!file_exists($absoluteProfilePicPath)) {
            return $defaultProfilePicPath;
        }
        return $relativeProfilePicPath;
    }

    return $defaultProfilePicPath;
}

// Check if the user is logged in and determine their type
if (isset($_SESSION['user_type'])) {
    $userType = $_SESSION['user_type'];
    $userId = ($userType === 'customer') ? $_SESSION['customer_id'] : $_SESSION['employee_id'];
    $profilePicPath = getProfilePicturePath($pdo, $userType, $userId);
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
            <img class="me-3" src="/436-project/images/logo.png" alt="Logo" height="70px">
            <h1 class="logo-text">Accessible Games<br>Support Center</h1>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="d-flex w-100 justify-content-end align-items-center" style="gap: 5%;">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= $isEmployee ? '../admin/game-list.php' : 'game-list.php'; ?>">Search
                            Games</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= $isEmployee ? '../admin/my-tickets.php' : 'my-tickets.php'; ?>">My
                            Tickets</a>
                    </li>
                    <?php if (isset($_SESSION['user_type'])): ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-white btn-xs mb-0 ms-2" href="profile.php">My Profile</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-white btn-xs mb-0 ms-2"
                                href="<?= $isEmployee ? '../login.php' : 'login.php'; ?>">Log In</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile" width="50"
                                height="50" class="rounded-circle">
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>