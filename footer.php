<footer>
  <section class="py-4 bg-dark">
    <div class="container d-flex justify-content-between py-4">
      <div class="footer-brand d-flex">
        <img src="images/logo-mono.png" alt="Accessible Games Support Center" class="img-fluid me-3">
        <p>Accessible Games<br>Support Center</p>
      </div>
      <nav class="footer-nav">
        <ul class="footer-nav-list">
          <li class="footer-nav-item"><a href="index.php" class="footer-nav-link">Home</a></li>
          <li class="footer-nav-item"><a href="search-games.php" class="footer-nav-link">Search Games</a></li>
          <li class="footer-nav-item"><a href="my-tickets.php" class="footer-nav-link">My Tickets</a></li>
          <li class="footer-nav-item"><a href="my-profile.php" class="footer-nav-link">My Profile</a></li>
          <li class="footer-nav-item"><a href="login.php" class="footer-nav-link">Login</a></li>
        </ul>
      </nav>
    </div>
  </section>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
  integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
  crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script>
  $(function () {
    $('[data-toggle="tooltip"]').tooltip();
  });
</script>
<script>
  const quill = new Quill("#editor", {
    theme: "snow",
  });
</script>
<script src="js/ticket-history.js"></script>
</body>

</html>