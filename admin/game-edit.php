<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../classes/db.php';

if (!isset($_GET['game_id'])) {
  die("No game ID provided.");
}

$game_id = $_GET['game_id'];

// Fetch game info
$sql = "
  SELECT g.*, 
         GROUP_CONCAT(DISTINCT f.feature_name) AS features,
         GROUP_CONCAT(DISTINCT pl.platform_name) AS platforms,
         GROUP_CONCAT(DISTINCT cat.cat_name) AS categories
  FROM Games g
  LEFT JOIN Game_Accessibility_Features af ON g.game_id = af.game_id
  LEFT JOIN Accessibility_Features f ON af.feature_id = f.feature_id
  LEFT JOIN Game_Platforms gp ON g.game_id = gp.game_id
  LEFT JOIN Platforms pl ON gp.platform_id = pl.platform_id
  LEFT JOIN Game_Categories gc ON g.game_id = gc.game_id
  LEFT JOIN Categories cat ON gc.cat_id = cat.cat_id
  WHERE g.game_id = :game_id
  GROUP BY g.game_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':game_id' => $game_id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

$feature_stmt = $pdo->query("SELECT * FROM Accessibility_Features ORDER BY feature_name ASC");
$all_features = $feature_stmt->fetchAll();

$current_feature_stmt = $pdo->prepare("SELECT feature_id FROM Game_Accessibility_Features WHERE game_id = ?");
$current_feature_stmt->execute([$game_id]);
$current_feature_ids = array_column($current_feature_stmt->fetchAll(), 'feature_id');

if (!$game) {
  die("Game not found.");
}

// Fetch all publishers
$publisher_stmt = $pdo->query("SELECT * FROM Publishers ORDER BY publisher_name ASC");
$publishers = $publisher_stmt->fetchAll();

// Get current publisher IDs for this game
$pub_stmt = $pdo->prepare("SELECT publisher_id FROM Game_Publishers WHERE game_id = ?");
$pub_stmt->execute([$game_id]);
$current_publisher_ids = array_column($pub_stmt->fetchAll(), 'publisher_id');

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $game_name = $_POST['game_name'] ?? '';
  //$description = $_POST['description'] ?? '';
  //$cover = $_POST['cover_image'] ?? '';
  $new_publisher_ids = $_POST['publisher_ids'] ?? [];

  $update_sql = "UPDATE Games SET game_name = :game_name WHERE game_id = :game_id";
  $stmt = $pdo->prepare($update_sql);
  $stmt->execute([
    ':game_name' => $game_name,
    //':description' => $description,
    //':cover' => $cover,
    ':game_id' => $game_id
  ]);

  // Update Game_Publishers join table
  $pdo->prepare("DELETE FROM Game_Publishers WHERE game_id = ?")->execute([$game_id]);
  $insert_pub = $pdo->prepare("INSERT INTO Game_Publishers (game_id, publisher_id) VALUES (?, ?)");
  foreach ($new_publisher_ids as $pid) {
    $insert_pub->execute([$game_id, $pid]);
  }

  $new_feature_ids = $_POST['feature_ids'] ?? [];

  // Clear existing features
  $pdo->prepare("DELETE FROM Game_Accessibility_Features WHERE game_id = ?")->execute([$game_id]);

  // Insert new feature links
  $insert_feature = $pdo->prepare("INSERT INTO Game_Accessibility_Features (game_id, feature_id) VALUES (?, ?)");
  foreach ($new_feature_ids as $fid) {
  $insert_feature->execute([$game_id, $fid]);
  }


  header("Location: admin/game-view.php?game_id=$game_id");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Game</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<?php include '../header.php'; ?>

<body class="bg-light">
<div class="container mt-5">
  <h1 class="mb-4">Edit Game: <?= htmlspecialchars($game['game_name']) ?></h1>

  <form method="POST">
    <div class="mb-3">
      <label for="game_name" class="form-label">Title</label>
      <input type="text" class="form-control" name="game_name" value="<?= htmlspecialchars($game['game_name']) ?>">
    </div>

    <div class="mb-3">
      <label for="publisher_ids" class="form-label">Publishers</label>
      <select name="publisher_ids[]" class="form-select" multiple>
        <?php foreach ($publishers as $pub): ?>
          <option value="<?= $pub['publisher_id'] ?>" <?= in_array($pub['publisher_id'], $current_publisher_ids) ? 'selected' : '' ?>>
            <?= htmlspecialchars($pub['publisher_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
        <label for="feature_ids" class="form-label">Accessibility Features</label>
        <select name="feature_ids[]" class="form-select" multiple>
            <?php foreach ($all_features as $feature): ?>
            <option value="<?= $feature['feature_id'] ?>" <?= in_array($feature['feature_id'], $current_feature_ids) ? 'selected' : '' ?>>
                <?= htmlspecialchars($feature['feature_name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>


    <button type="submit" class="btn btn-primary">Update Game</button>
    <a href="admin/game-view.php?game_id=<?= urlencode($game['game_id']) ?>" class="btn btn-secondary">Cancel</a>
  </form>

  <hr>
  <h3 class="mt-4">Manage Features, Platforms, and Categories</h3>
  <p><em>This section would use AJAX or separate endpoints to add/delete items from join tables.</em></p>
</div>
</body>

<?php include '../footer.php'; ?>

</html>
