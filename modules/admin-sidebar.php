<!-- Sidebar toggle button for mobile screens -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar -->
<nav id="sidebar" class="c-sidebar top-space">
    <div class="c-sidebar-header">
        <h5 class="text-light mb-0">Employee Overview</h5>
    </div>
    <div class="mt-3">
        <a href="dashboard.php"
            class="c-sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <i class="bi bi-house-door"></i> Dashboard
        </a>
        <a href="../game-list.php"
            class="c-sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'game-list.php' ? 'active' : '' ?>">
            <i class="bi bi-controller"></i> Manage Games
        </a>
        <a href="manage-game-details.php"
            class="c-sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'manage-game-details.php' ? 'active' : '' ?>">
            <i class="bi bi-info-circle"></i> Manage Game Details
        </a>
        <a href="manage-users.php"
            class="c-sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'manage-users.php' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Manage Users
        </a>
        <a href="my-tickets.php"
            class="c-sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'my-tickets.php' ? 'active' : '' ?>">
            <i class="bi bi-inbox"></i> Support Tickets
        </a>
    </div>
</nav>

<script>
    document.getElementById('sidebarToggle').addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
        this.classList.toggle('active');
    });

    // Close the sidebar when clicking outside of it
    document.addEventListener('click', function (event) {
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('sidebarToggle');
        if (!sidebar.contains(event.target) && !toggleButton.contains(event.target)) {
            sidebar.classList.remove('active');
            toggleButton.classList.remove('active');
        }
    });
</script>