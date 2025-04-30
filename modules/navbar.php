<?php
global $isAdminPage, $isEmployee;

function getProfilePicturePath($pdo, $userType, $userId, $isAdminPage = false)
{
    $defaultProfilePicPath = ($isAdminPage ? '../' : '') . 'images/placeholder.jpg';

    if ($userType === 'customer') {
        $sql = "SELECT profile_picture FROM Customers WHERE customer_id = ?";
    } elseif ($userType === 'employee') {
        $sql = "SELECT profile_picture FROM Employees WHERE employee_id = ?";
    } else {
        return $defaultProfilePicPath;
    }

    $user = pdo($pdo, $sql, [$userId])->fetch();

    if ($user && $user['profile_picture']) {
        $userFolder = 'images/pfps/' . md5($userId);
        $profilePicPath = $userFolder . '/' . $user['profile_picture'];
        $fullPath = ($isAdminPage ? '../' : '') . $profilePicPath;

        // Check if the file exists
        if (file_exists($fullPath)) {
            return $fullPath;
        }
    }

    return $defaultProfilePicPath;
}

// Check if the user is logged in and determine their type
if (isset($_SESSION['user_type'])) {
    $userType = $_SESSION['user_type'];
    $userId = ($userType === 'customer') ? $_SESSION['customer_id'] : $_SESSION['employee_id'];
    $profilePicPath = getProfilePicturePath($pdo, $userType, $userId, $isAdminPage);
} else {
    $profilePicPath = ($isAdminPage ? '../' : '') . 'images/placeholder.jpg';
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
            <?php if ($isEmployee): ?>
                <span class="badge bg-success ms-3">Employee</span>
            <?php endif; ?>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"><i class="bi bi-list"></i></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="d-flex w-100 justify-content-end align-items-center" style="gap: 5%;">
                <ul class="navbar-nav">
                    <?php if ($isEmployee): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= ($isAdminPage ? '../' : '') . 'admin/dashboard.php'; ?>">Dashboard</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ($isAdminPage ? '../' : '') . 'game-list.php'; ?>">
                            <?= $isEmployee ? 'Games List' : 'Search Games'; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <?php if ($isEmployee): ?>
                            <a class="nav-link" href="<?= ($isAdminPage ? '../' : '') . 'admin/my-tickets.php'; ?>">My
                                Tickets</a>
                        <?php else: ?>
                            <a class="nav-link" href="my-tickets.php">My Tickets</a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <?php if (isset($_SESSION['user_type'])): ?>
                            <a class="btn btn-outline-white btn-xs mb-0 ms-2"
                                href="<?= $isAdminPage ? '../profile.php' : 'profile.php'; ?>">My Profile</a>
                        <?php else: ?>
                            <a class="btn btn-outline-white btn-xs mb-0 ms-2"
                                href="<?= $isAdminPage ? '../login.php' : 'login.php'; ?>">Log In</a>
                        <?php endif; ?>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $isAdminPage ? '../profile.php' : 'profile.php'; ?>">
                            <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile" width="50"
                                height="50" class="rounded-circle">
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>