<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../classes/db.php';

// Fetch dropdown data
$publisher_stmt = $pdo->query("SELECT * FROM Publishers ORDER BY publisher_name ASC");
$publishers = $publisher_stmt->fetchAll();

$feature_stmt = $pdo->query("SELECT * FROM Accessibility_Features ORDER BY feature_name ASC");
$features = $feature_stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $game_name = $_POST['game_name'] ?? '';
  $description = $_POST['description'] ?? '';
  $game_rating = $_POST['game_rating'] ?? '';
  $publisher_ids = $_POST['publisher_ids'] ?? [];
  $feature_ids = $_POST['feature_ids'] ?? [];

  if (empty($game_name)) {
    die("Game name is required.");
  }

  // Insert new game
  $insert_game_sql = "INSERT INTO Games (game_name, description, game_rating) VALUES (:game_name, :description, :game_rating)";
  $stmt = $pdo->prepare($insert_game_sql);
  $stmt->execute([
    ':game_name' => $game_name,
    ':description' => $description,
    ':game_rating' => $game_rating
  ]);

  $game_id = $pdo->lastInsertId();

  // Insert into Game_Publishers
  $insert_pub = $pdo->prepare("INSERT INTO Game_Publishers (game_id, publisher_id) VALUES (?, ?)");
  foreach ($publisher_ids as $pid) {
    $insert_pub->execute([$game_id, $pid]);
  }

  // Insert into Game_Accessibility_Features
  $insert_feature = $pdo->prepare("INSERT INTO Game_Accessibility_Features (game_id, feature_id) VALUES (?, ?)");
  foreach ($feature_ids as $fid) {
    $insert_feature->execute([$game_id, $fid]);
  }

  header("Location: employee-game.php?game_id=$game_id");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create New Game</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      padding-top: 100px;
      padding-left: 250px;
    }
    .sidebar {
      width: 250px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background-color: #343a40;
      padding-top: 140px;
    }
    .sidebar a {
      padding: 15px;
      text-decoration: none;
      font-size: 18px;
      color: #ccc;
      display: block;
    }
    .sidebar a:hover {
      background-color: #495057;
      color: white;
    }
    .content {
      margin-left: 250px;
      padding: 20px;
      flex-grow: 1;
    }
    .navbar {
      z-index: 1001;
    }
  </style>
</head>

<?php include '../header.php'; ?>

<body class="bg-light">

<!-- Sidebar -->
<div class="sidebar">
  <a href="dashboard.php">Dashboard</a>
  <a href="game-list.php">Manage Games</a>
  <a href="manage-game-details.php">Manage Game Details</a>
  <a href="manage-users.php">Manage Users</a>
  <a href="#">Tickets</a>
</div>

<div class="container py-5">
  <h1 class="mb-4">Add New Game</h1>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Game Title</label>
      <input type="text" name="game_name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Rating</label>
      <input type="text" name="game_rating" class="form-control" placeholder="e.g. 1, 2, 3, 4, 5">
    </div>

    <div class="mb-3">
      <label class="form-label">Publishers</label>
      <select name="publisher_ids[]" class="form-select" multiple>
        <?php foreach ($publishers as $pub): ?>
          <option value="<?= $pub['publisher_id'] ?>"><?= htmlspecialchars($pub['publisher_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Accessibility Features</label>
      <select name="feature_ids[]" class="form-select" multiple>
        <?php foreach ($features as $feature): ?>
          <option value="<?= $feature['feature_id'] ?>"><?= htmlspecialchars($feature['feature_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="btn btn-success">Create Game</button>
    <a href="admin/game-list.php" class="btn btn-secondary ms-2">Cancel</a>
  </form>
</div>
</body>

<?php include '../footer.php'; ?>

</html>
