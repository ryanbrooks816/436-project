<?php global $isAdminPage, $isEmployee; ?>

<footer>
    <section class="py-4 bg-dark">
        <div class="container py-4">
            <div class="row g-4">
                <div class="col-lg-5 col-md-6 col-12">
                    <div class="footer-brand d-flex align-items-start flex-wrap gap-3">
                        <img src="<?= $isAdminPage ? '../' : '' ?>images/logo-mono.png" alt="Accessible Games Support Center" class="img-fluid me-3">
                        <p>Accessible Games<br>Support Center</p>
                    </div>
                </div>
                <div class="col-lg-7 col-md-6 col-12">
                    <nav class="footer-nav">
                        <ul class="footer-nav-list d-flex flex-column flex-md-row justify-content-md-end">
                            <li class="footer-nav-item">
                                <?php if ($isEmployee): ?>
                                    <a href="<?= ($isAdminPage ? '../' : '') . 'admin/game-list.php'; ?>"
                                        class="footer-nav-link">Games List</a>
                                <?php else: ?>
                                    <a href="game-list.php" class="footer-nav-link">Search Games</a>
                                <?php endif; ?>
                            </li>
                            <li class="footer-nav-item">
                                <?php if ($isEmployee): ?>
                                    <a href="<?= ($isAdminPage ? '../' : '') . 'admin/my-tickets.php'; ?>"
                                        class="footer-nav-link">My Tickets</a>
                                <?php else: ?>
                                    <a href="my-tickets.php" class="footer-nav-link">My Tickets</a>
                                <?php endif; ?>
                            </li>
                            <li class="footer-nav-item">
                                <a href="<?= ($isAdminPage ? '../' : '') . 'profile.php'; ?>" class="footer-nav-link">My
                                    Profile</a>
                            </li>
                            <li class="footer-nav-item">
                                <a href="<?= ($isAdminPage ? '../' : '') . 'login.php'; ?>"
                                    class="footer-nav-link">Login</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </section>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
    integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    $(window).on('scroll', function () {
        if ($(window).scrollTop() === 0) {
            $('#navbar').addClass('bg-light');
        } else {
            $('#navbar').removeClass('bg-light');
        }
    });
</script>
</body>

</html>