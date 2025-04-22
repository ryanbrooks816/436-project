<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require '/Applications/MAMP/htdocs/436-project/classes/db.php';

if (!isset($_GET['game_id'])) {
  die("No game ID provided.");
}

$game_id = $_GET['game_id'];

// Fetch game with joined info
$sql = "
  SELECT 
    g.game_id,
    g.game_name,
    g.game_rating,
    GROUP_CONCAT(DISTINCT af.feature_name ORDER BY af.feature_name SEPARATOR ', ') AS features,
    GROUP_CONCAT(DISTINCT pl.platform_name ORDER BY pl.platform_name SEPARATOR ', ') AS platforms,
    GROUP_CONCAT(DISTINCT cat.cat_name ORDER BY cat.cat_name SEPARATOR ', ') AS categories,
    GROUP_CONCAT(DISTINCT pub.publisher_name ORDER BY pub.publisher_name SEPARATOR ', ') AS publishers
  FROM Games g
  LEFT JOIN Game_Accessibility_Features gaf ON g.game_id = gaf.game_id
  LEFT JOIN Accessibility_Features af ON gaf.feature_id = af.feature_id
  LEFT JOIN Game_Platforms gp ON g.game_id = gp.game_id
  LEFT JOIN Platforms pl ON gp.platform_id = pl.platform_id
  LEFT JOIN Game_Categories gc ON g.game_id = gc.game_id
  LEFT JOIN Categories cat ON gc.cat_id = cat.cat_id
  LEFT JOIN Game_Publishers gpub ON g.game_id = gpub.game_id
  LEFT JOIN Publishers pub ON gpub.publisher_id = pub.publisher_id
  WHERE g.game_id = :game_id
  GROUP BY g.game_id, g.game_name, g.game_rating
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':game_id' => $game_id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
  die("Game not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($game['game_name']) ?> – Game Info</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<?php include 'header.php'; ?>

<body class="bg-light">
  <div class="container py-5">
    <h1 class="mb-3"><?= htmlspecialchars($game['game_name']) ?></h1>

    <div class="mb-3">
      <strong>Rating:</strong><br>
      <?= htmlspecialchars($game['game_rating'] ?? 'Unrated') ?>
    </div>

    <div class="mb-3">
      <strong>Accessibility Features:</strong><br>
      <?= $game['features'] ? htmlspecialchars($game['features']) : 'None listed' ?>
    </div>

    <div class="mb-3">
      <strong>Platforms:</strong><br>
      <?= $game['platforms'] ? htmlspecialchars($game['platforms']) : 'None listed' ?>
    </div>

    <div class="mb-3">
      <strong>Categories:</strong><br>
      <?= $game['categories'] ? htmlspecialchars($game['categories']) : 'None listed' ?>
    </div>

    <div class="mb-3">
      <strong>Publisher:</strong><br>
      <?= $game['publishers'] ? htmlspecialchars($game['publishers']) : 'None listed' ?>
    </div>

    <a href="employee-game-edit.php?game_id=<?= urlencode($game['game_id']) ?>" class="btn btn-primary mt-4">Edit Game</a>
    <a href="/436-project/employee-game-list.php" class="btn btn-secondary mt-4 ms-2">Back to List</a>
  </div>
</body>

<?php include 'footer.php'; ?>

</html>
