<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../classes/db.php';
//require_once "../modules/require-login.php";

/*if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'employee') {
  header("Location: 403.php");
  exit;
}*/

function generateUniqueGameId(PDO $pdo): int {
  do {
      $id = random_int(100000, 999999); // you can adjust the range
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM Games WHERE game_id = ?");
      $stmt->execute([$id]);
  } while ($stmt->fetchColumn() > 0);
  return $id;
}

// Fetch dropdown data
$all_features = $pdo->query("SELECT * FROM Accessibility_Features ORDER BY feature_name ASC")->fetchAll();
$all_categories = $pdo->query("SELECT * FROM Categories ORDER BY cat_name ASC")->fetchAll();
$all_platforms = $pdo->query("SELECT * FROM Platforms ORDER BY platform_name ASC")->fetchAll();
$publishers = $pdo->query("SELECT * FROM Publishers ORDER BY publisher_name ASC")->fetchAll();

// Handle new game form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $game_id = 1917;
  //generateUniqueGameId($pdo);

  $game_name = $_POST['game_name'] ?? '';
  $game_rating = $_POST['game_rating'] ?? null;
  $release_date = $_POST['release_date'] ?? null;
  $feature_ids = $_POST['feature_ids'] ?? [];
  $category_ids = $_POST['category_ids'] ?? [];
  $platform_ids = $_POST['platform_ids'] ?? [];
  $publisher_ids = $_POST['publisher_ids'] ?? [];

  if (empty($game_name)) {
    die("Game name is required.");
  }

  try {
    // Insert game
    $stmt = $pdo->prepare("INSERT INTO Games (game_id, game_rating, game_name, game_release_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$game_id, $game_rating, $game_name, $release_date]);
    $game_id = $pdo->lastInsertId();
    error_log("New game created with ID: $game_id");

    // Insert into join tables
    $insert = fn($sql, $ids) => array_map(fn($id) => $pdo->prepare($sql)->execute([$game_id, $id]), $ids);

    //$insert("INSERT INTO Game_Accessibility_Features (game_id, feature_id) VALUES (?, ?)", $feature_ids);
    //$insert("INSERT INTO Game_Categories (game_id, cat_id) VALUES (?, ?)", $category_ids);
    //$insert("INSERT INTO Game_Platforms (game_id, platform_id) VALUES (?, ?)", $platform_ids);
    //$insert("INSERT INTO Game_Publishers (game_id, publisher_id) VALUES (?, ?)", $publisher_ids);

    header("Location: game-view.php?game_id=$game_id");
    exit;
  } catch (Exception $e) {
    die("Error: " . $e->getMessage());
  }
}

require '../header.php';
?>

<?php require '../modules/admin-sidebar.php' ?>

<main class="page-wrapper">
  <div class="top-space bg-primary text-white text-center py-5">
    <h1 class="text-white">Add New Game</h1>
  </div>
  <div class="after-sidebar-content">
    <div class="container py-5">
      <div class="card shadow">
        <div class="card-header">
          <h1 class="mb-0">Add New Game</h1>
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="form-group mb-3">
              <label class="form-label">Title</label>
              <input type="text" name="game_name" class="form-control-input" required>
            </div>
            <div class="row mb-3">
              <div class="col-md-6 form-group">
                <label class="form-label">Game Rating</label>
                <input type="text" name="game_rating" class="form-control-input">
              </div>
              <div class="col-md-6 form-group">
                <label class="form-label">Release Date</label>
                <input type="date" name="release_date" class="form-control-input">
              </div>
            </div>
            <div class="form-group mb-3">
              <label class="form-label">Accessibility Features</label>
              <select name="feature_ids[]" class="form-select select2-multi" multiple>
                <?php foreach ($all_features as $f): ?>
                  <option value="<?= $f['feature_id'] ?>"><?= htmlspecialchars($f['feature_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group mb-3">
              <label class="form-label">Categories</label>
              <select name="category_ids[]" class="form-select select2-multi" multiple>
                <?php foreach ($all_categories as $c): ?>
                  <option value="<?= $c['cat_id'] ?>"><?= htmlspecialchars($c['cat_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group mb-3">
              <label class="form-label">Platforms</label>
              <select name="platform_ids[]" class="form-select select2-multi" multiple>
                <?php foreach ($all_platforms as $p): ?>
                  <option value="<?= $p['platform_id'] ?>"><?= htmlspecialchars($p['platform_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group mb-4">
              <label class="form-label">Publishers</label>
              <select name="publisher_ids[]" class="form-select select2-multi" multiple>
                <?php foreach ($publishers as $pub): ?>
                  <option value="<?= $pub['publisher_id'] ?>"><?= htmlspecialchars($pub['publisher_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="text-end">
              <a href="game-list.php" class="btn btn-secondary">Cancel</a>
              <button type="submit" class="btn btn-success">Add Game</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require '../footer.php'; ?>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(() => {
    $('.select2-multi').select2({
      placeholder: "Select options...",
      width: '100%',
      allowClear: true
    });
  });
</script>
